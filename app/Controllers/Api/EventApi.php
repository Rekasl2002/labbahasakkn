<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\SessionModel;
use App\Models\ParticipantModel;
use App\Models\SessionStateModel;
use App\Models\MaterialModel;
use App\Models\MaterialFileModel;

class EventApi extends BaseController
{
    private int $onlineWindowSeconds = 6;

    private function clearStudentAuth(): void
    {
        helper('remember');
        session()->remove([
            'participant_id',
            'session_id',
            'student_name',
            'class_name',
            'device_label',
            'student_waiting',
            'waiting_student_profile',
        ]);
        $this->response->deleteCookie(LAB_COOKIE_PARTICIPANT);
    }

    private function parseTextItems(array $material): array
    {
        $type = (string) ($material['type'] ?? '');
        if ($type === 'text') {
            $raw = (string) ($material['text_content'] ?? '');
            if (trim($raw) === '') return [];
            return [$raw];
        }
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
        $selectedFile = null;
        $selectedText = null;
        $fileId = isset($state['current_material_file_id']) ? (int) $state['current_material_file_id'] : 0;
        $textIndexRaw = $state['current_material_text_index'] ?? null;

        if ($fileId > 0) {
            foreach ($files as $f) {
                if ((int) $f['id'] === $fileId) {
                    $selectedFile = $f;
                    break;
                }
            }
        }

        if ($textIndexRaw !== null && $textIndexRaw !== '') {
            $idx = (int) $textIndexRaw;
            if (isset($textItems[$idx])) {
                $selectedText = ['index' => $idx, 'text' => $textItems[$idx]];
            }
        }

        if ($selectedFile) {
            $selected = ['type' => 'file', 'file' => $selectedFile];
        } elseif ($selectedText) {
            $selected = ['type' => 'text', 'index' => $selectedText['index'], 'text' => $selectedText['text']];
        }

        return [
            'material' => $material,
            'files' => $files,
            'text_items' => $textItems,
            'selected' => $selected,
            'selected_file' => $selectedFile,
            'selected_text' => $selectedText,
        ];
    }

    public function poll()
    {
        // Only GET (biar konsisten dan gampang di-cache-control)
        if (strtoupper($this->request->getMethod()) !== 'GET') {
            return $this->json(['ok' => false, 'error' => 'Metode tidak diizinkan'], 405);
        }

        $since = (int) $this->request->getGet('since');
        $since = max(0, $since);

        $forceSnapshot = (int) $this->request->getGet('snapshot') === 1;

        $isAdmin = (bool) session()->get('admin_id');
        $participantId = (int) session()->get('participant_id');

        // Harus login admin atau siswa
        if (!$isAdmin && $participantId <= 0) {
            return $this->json(['ok' => false, 'error' => 'Akses ditolak'], 401);
        }

        $active = $this->getActiveSessionRaw();
        $closedByTimeout = $this->closeSessionIfExpired($active);
        if ($closedByTimeout) {
            $active = null;
        }

        // Tentukan session_id
        $sessionId = 0;

        if ($isAdmin) {
            if ($active) {
                $sessionId = (int) $active['id'];
            } elseif ($closedByTimeout) {
                // Tetap kirim event `session_ended` sekali ke admin.
                $sessionId = (int) $closedByTimeout['id'];
            } elseif ($since > 0) {
                $lastSession = (new SessionModel())
                    ->select('id')
                    ->where('started_at IS NOT NULL', null, false)
                    ->orderBy('id', 'DESC')
                    ->first();
                $sessionId = $lastSession ? (int) $lastSession['id'] : 0;
            }
        } else {
            $sessionId = (int) session()->get('session_id');
        }

        if (!$isAdmin && $sessionId <= 0) {
            $this->clearStudentAuth();
            return $this->jsonNoStore(['ok' => false, 'error' => 'Sesi sudah berakhir'], 401);
        }

        if (!$isAdmin) {
            $isCurrentSessionActive = $active && (int) ($active['id'] ?? 0) === $sessionId;
            if (!$isCurrentSessionActive) {
                $this->clearStudentAuth();
                return $this->jsonNoStore(['ok' => false, 'error' => 'Sesi sudah berakhir'], 401);
            }
        }

        // Kalau tidak ada sesi aktif (admin) atau siswa kehilangan session_id
        if ($sessionId <= 0) {
            $payload = [
                'ok' => true,
                'session_id' => 0,
                'last_id' => $since,
                'events' => [],
                'presence' => [],
                'server_time' => date('Y-m-d H:i:s'),
            ];

            return $this->jsonNoStore($payload);
        }

        // Validasi siswa benar-benar milik session ini (anti mismatch)
        if (!$isAdmin) {
            $pm = new ParticipantModel();
            $me = $pm->select('id,session_id')
                ->where('id', $participantId)
                ->first();

            if (!$me || (int) $me['session_id'] !== $sessionId) {
                $this->clearStudentAuth();
                return $this->jsonNoStore(['ok' => false, 'error' => 'Sesi tidak valid'], 401);
            }
        }

        // Ambil events
        $eventModel = new EventModel();
        $events = $eventModel->poll($sessionId, $since, $isAdmin, $participantId);

        // last_id
        $lastId = $since;
        foreach ($events as $e) {
            $id = (int) ($e['id'] ?? 0);
            if ($id > $lastId) $lastId = $id;
        }

        $payload = [
            'ok' => true,
            'session_id' => $sessionId,
            'last_id' => $lastId,
            'events' => $events,
            'server_time' => date('Y-m-d H:i:s'),
        ];

        // Presence map (dipakai admin.js)
        $presenceRows = (new ParticipantModel())
            ->select('id,last_seen_at,presence_state,presence_page,presence_reason,presence_updated_at')
            ->where('session_id', $sessionId)
            ->findAll();

        $now = time();
        $presence = [];
        foreach ($presenceRows as $r) {
            $seen = !empty($r['last_seen_at']) ? strtotime($r['last_seen_at']) : 0;
            $state = strtolower(trim((string) ($r['presence_state'] ?? '')));
            $page = strtolower(trim((string) ($r['presence_page'] ?? '')));
            $reason = strtolower(trim((string) ($r['presence_reason'] ?? '')));
            $isOnline = ($state === 'active' && $seen > 0 && ($now - $seen) <= $this->onlineWindowSeconds);

            if ($page === '') {
                $page = 'other';
            }

            if (!$isOnline) {
                if ($state === 'active' && ($reason === '' || $reason === 'active')) {
                    $reason = 'heartbeat_timeout';
                } elseif ($reason === '') {
                    if ($state === 'away') {
                        $reason = 'tab_hidden';
                    } else {
                        $reason = 'heartbeat_timeout';
                    }
                }
            }

            $presence[] = [
                'id' => (int) $r['id'],
                'online' => $isOnline,
                'state' => $isOnline ? 'online' : ($state === 'away' ? 'away' : 'offline'),
                'page' => $page,
                'reason' => $reason,
                'last_seen_at' => (string) ($r['last_seen_at'] ?? ''),
                'presence_updated_at' => (string) ($r['presence_updated_at'] ?? ''),
            ];
        }
        $payload['presence'] = $presence;

        // Snapshot saat first-load (since=0) atau dipaksa snapshot=1
        if ($since === 0 || $forceSnapshot) {
            $session = (new SessionModel())->find($sessionId);

            $pm = new ParticipantModel();
            $participants = $pm->where('session_id', $sessionId)->orderBy('id', 'ASC')->findAll();

            // Sanitasi snapshot untuk siswa (jangan bocorin ip_address dsb)
            if (!$isAdmin && is_array($participants)) {
                $safe = [];
                foreach ($participants as $p) {
                    $safe[] = [
                        'id' => (int) ($p['id'] ?? 0),
                        'student_name' => (string) ($p['student_name'] ?? ''),
                        'class_name' => (string) ($p['class_name'] ?? ''),
                        'device_label' => (string) ($p['device_label'] ?? ''),
                        'mic_on' => (int) (!empty($p['mic_on']) ? 1 : 0),
                        'speaker_on' => (int) (!empty($p['speaker_on']) ? 1 : 0),
                        'last_seen_at' => (string) ($p['last_seen_at'] ?? ''),
                    ];
                }
                $participants = $safe;

                // Session juga bisa disederhanakan
                if (is_array($session)) {
                    $session = [
                        'id' => (int) ($session['id'] ?? 0),
                        'name' => (string) ($session['name'] ?? ''),
                        'started_at' => (string) ($session['started_at'] ?? ''),
                        'is_active' => (int) (!empty($session['is_active']) ? 1 : 0),
                    ];
                }
            }

            $stateRow = (new SessionStateModel())->where('session_id', $sessionId)->first();

            // Current Material
            $currentMaterial = null;
            if ($stateRow && !empty($stateRow['current_material_id'])) {
                $material = (new MaterialModel())->find((int) $stateRow['current_material_id']);
                $currentMaterial = $this->buildCurrentMaterial($material, $stateRow);
            }

            $payload['snapshot'] = [
                'session' => $session,
                'participants' => $participants,
                'state' => $stateRow,
                'currentMaterial' => $currentMaterial,
            ];
        }

        return $this->jsonNoStore($payload);
    }

}

