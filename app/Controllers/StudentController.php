<?php

namespace App\Controllers;

use App\Models\SessionModel;
use App\Models\ParticipantModel;
use App\Models\SessionStateModel;
use App\Models\MaterialModel;
use App\Models\MaterialFileModel;

class StudentController extends BaseController
{
    private function parseTextItems(array $material): array
    {
        $type = (string) ($material['type'] ?? '');
        if ($type !== 'folder') return [];

        $raw = (string) ($material['text_content'] ?? '');
        if ($raw === '') return [];

        $lines = preg_split("/\r\n|\n|\r/", $raw);
        $out = [];
        if (is_array($lines)) {
            foreach ($lines as $line) {
                $line = trim((string) $line);
                if ($line !== '') $out[] = $line;
            }
        }
        return $out;
    }

    private function buildCurrentMaterial(?array $material, ?array $state): ?array
    {
        if (!$material) return null;

        $files = (new MaterialFileModel())
            ->orderedForMaterial((int) $material['id'])
            ->findAll();

        foreach ($files as &$f) {
            if (isset($f['url_path'])) $f['url_path'] = (string) $f['url_path'];
            if (isset($f['preview_url_path'])) $f['preview_url_path'] = (string) $f['preview_url_path'];
            if (isset($f['cover_url_path'])) $f['cover_url_path'] = (string) $f['cover_url_path'];
        }
        unset($f);

        $textItems = $this->parseTextItems($material);

        $selected = null;
        $fileId = isset($state['current_material_file_id']) ? (int) $state['current_material_file_id'] : 0;
        $textIndexRaw = $state['current_material_text_index'] ?? null;

        if ($fileId > 0) {
            foreach ($files as $f) {
                if ((int) $f['id'] === $fileId) {
                    $selected = ['type' => 'file', 'file' => $f];
                    break;
                }
            }
        }

        if (!$selected && $textIndexRaw !== null && $textIndexRaw !== '') {
            $idx = (int) $textIndexRaw;
            if (isset($textItems[$idx])) {
                $selected = ['type' => 'text', 'index' => $idx, 'text' => $textItems[$idx]];
            }
        }

        if (!$selected) {
            $type = (string) ($material['type'] ?? '');
            if ($type === 'text' && !empty($material['text_content'])) {
                $selected = [
                    'type' => 'text',
                    'index' => null,
                    'text' => (string) $material['text_content'],
                    'mode' => 'full',
                ];
            } elseif ($type === 'file' && !empty($files)) {
                $selected = ['type' => 'file', 'file' => $files[0], 'mode' => 'default'];
            } elseif ($type === 'folder') {
                if (!empty($textItems)) {
                    $selected = ['type' => 'text', 'index' => 0, 'text' => $textItems[0], 'mode' => 'default'];
                } elseif (!empty($files)) {
                    $selected = ['type' => 'file', 'file' => $files[0], 'mode' => 'default'];
                }
            }
        }

        return [
            'material' => $material,
            'files' => $files,
            'text_items' => $textItems,
            'selected' => $selected,
        ];
    }

    public function dashboard()
    {
        $sessionId = (int) session()->get('session_id');
        $participantId = (int) session()->get('participant_id');

        if ($sessionId <= 0 || $participantId <= 0) {
            return redirect()->to('/login');
        }

        $active = $this->getActiveSession();
        if (!$active || (int) ($active['id'] ?? 0) !== $sessionId) {
            helper('remember');
            session()->remove(['participant_id', 'session_id', 'student_name', 'class_name']);
            $this->response->deleteCookie(LAB_COOKIE_PARTICIPANT);
            return redirect()->to('/login')->with('ok', 'Sesi sudah berakhir. Silakan tunggu sesi berikutnya.');
        }

        $session = (new SessionModel())->find($sessionId);
        $me = (new ParticipantModel())
            ->where('id', $participantId)
            ->where('session_id', $sessionId)
            ->first();
        if (!$me) {
            helper('remember');
            session()->remove(['participant_id', 'session_id', 'student_name', 'class_name']);
            $this->response->deleteCookie(LAB_COOKIE_PARTICIPANT);
            return redirect()->to('/login')->with('error', 'Data siswa pada sesi ini tidak ditemukan.');
        }

        $state = (new SessionStateModel())->where('session_id', $sessionId)->first();
        $currentMaterial = null;

        if ($state && !empty($state['current_material_id'])) {
            $material = (new MaterialModel())->find((int)$state['current_material_id']);
            $currentMaterial = $this->buildCurrentMaterial($material, $state);
        }

        return view('student/dashboard', [
            'session' => $session,
            'me' => $me,
            'state' => $state,
            'currentMaterial' => $currentMaterial,
        ]);
    }

    public function settings()
    {
        $tab = (string) $this->request->getGet('tab');
        $tab = $tab !== '' ? $tab : 'general';
        $allowedTabs = ['general'];
        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'general';
        }
        $embed = (string) $this->request->getGet('embed') === '1';

        return view($embed ? 'student/settings/embed' : 'student/settings/index', [
            'tab' => $tab,
            'embed' => $embed,
        ]);
    }
}
