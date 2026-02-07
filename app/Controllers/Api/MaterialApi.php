<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SessionModel;
use App\Models\SessionStateModel;
use App\Models\MaterialModel;
use App\Models\MaterialFileModel;
use App\Models\EventModel;

class MaterialApi extends BaseController
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

    public function current()
    {
        $isAdmin = (bool) session()->get('admin_id');
        $participantId = (int) session()->get('participant_id');

        if (!$isAdmin && !$participantId) {
            // tetap boleh untuk guest (read-only) kalau mau, tapi sekarang kita batasi
            return $this->json(['ok' => false], 401);
        }

        $active = (new SessionModel())->where('is_active', 1)->orderBy('id', 'DESC')->first();
        $sessionId = $active ? (int)$active['id'] : (int) session()->get('session_id');
        if ($sessionId <= 0) return $this->json(['ok' => true, 'state' => null]);

        $state = (new SessionStateModel())->where('session_id', $sessionId)->first();

        $currentMaterial = null;
        if ($state && !empty($state['current_material_id'])) {
            $material = (new MaterialModel())->find((int)$state['current_material_id']);
            $currentMaterial = $this->buildCurrentMaterial($material, $state);
        }

        return $this->json([
            'ok' => true,
            'state' => $state,
            'currentMaterial' => $currentMaterial,
        ]);
    }

    public function selectItem()
    {
        $isAdmin = (bool) session()->get('admin_id');
        if (!$isAdmin) return $this->json(['ok' => false], 401);

        $itemType = (string) $this->request->getPost('item_type');
        $fileId = (int) $this->request->getPost('file_id');
        $textIndexRaw = $this->request->getPost('text_index');

        $active = (new SessionModel())->where('is_active', 1)->orderBy('id', 'DESC')->first();
        if (!$active) return $this->json(['ok' => false, 'message' => 'Tidak ada sesi aktif.'], 400);
        $sessionId = (int) $active['id'];

        $stateModel = new SessionStateModel();
        $state = $stateModel->where('session_id', $sessionId)->first();
        if (!$state || empty($state['current_material_id'])) {
            return $this->json(['ok' => false, 'message' => 'Belum ada materi aktif.'], 400);
        }

        $material = (new MaterialModel())->find((int) $state['current_material_id']);
        if (!$material) return $this->json(['ok' => false, 'message' => 'Materi tidak ditemukan.'], 404);

        if ($itemType === 'file') {
            if ($fileId <= 0) return $this->json(['ok' => false, 'message' => 'File tidak valid.'], 400);
            $file = (new MaterialFileModel())->where('material_id', $material['id'])->where('id', $fileId)->first();
            if (!$file) return $this->json(['ok' => false, 'message' => 'File tidak ditemukan.'], 404);
            $stateModel->setCurrentMaterialItem($sessionId, $fileId, null);
        } elseif ($itemType === 'text') {
            if ($textIndexRaw === null || $textIndexRaw === '') {
                return $this->json(['ok' => false, 'message' => 'Teks tidak valid.'], 400);
            }
            $textIndex = (int) $textIndexRaw;
            $textItems = $this->parseTextItems($material);
            if (!isset($textItems[$textIndex])) {
                return $this->json(['ok' => false, 'message' => 'Teks tidak ditemukan.'], 404);
            }
            $stateModel->setCurrentMaterialItem($sessionId, null, $textIndex);
        } else {
            return $this->json(['ok' => false, 'message' => 'Jenis item tidak valid.'], 400);
        }

        (new EventModel())->addForAll($sessionId, 'material_changed', [
            'material_id' => (int) $material['id'],
            'item_type' => $itemType,
            'file_id' => $fileId,
            'text_index' => $itemType === 'text' ? (int) $textIndexRaw : null,
        ]);

        return $this->json(['ok' => true]);
    }

    public function mediaControl()
    {
        $isAdmin = (bool) session()->get('admin_id');
        if (!$isAdmin) return $this->json(['ok' => false], 401);

        $action = (string) $this->request->getPost('action');
        $allowed = ['play', 'pause', 'seek', 'volume', 'rate', 'sync'];
        if (!in_array($action, $allowed, true)) {
            return $this->json(['ok' => false, 'message' => 'Aksi tidak valid.'], 400);
        }

        $active = (new SessionModel())->where('is_active', 1)->orderBy('id', 'DESC')->first();
        if (!$active) return $this->json(['ok' => false, 'message' => 'Tidak ada sesi aktif.'], 400);
        $sessionId = (int) $active['id'];

        $state = (new SessionStateModel())->where('session_id', $sessionId)->first();
        if (!$state || empty($state['current_material_id'])) {
            return $this->json(['ok' => false, 'message' => 'Belum ada materi aktif.'], 400);
        }

        $fileId = (int) $this->request->getPost('file_id');
        $currentTime = (float) $this->request->getPost('current_time');
        $volume = (float) $this->request->getPost('volume');
        $muted = (int) $this->request->getPost('muted');
        $paused = (int) $this->request->getPost('paused');
        $playbackRate = (float) $this->request->getPost('playback_rate');

        (new EventModel())->addForAll($sessionId, 'material_media_control', [
            'material_id' => (int) $state['current_material_id'],
            'file_id' => $fileId > 0 ? $fileId : null,
            'action' => $action,
            'current_time' => $currentTime,
            'volume' => $volume,
            'muted' => $muted ? 1 : 0,
            'paused' => $paused ? 1 : 0,
            'playback_rate' => $playbackRate > 0 ? $playbackRate : null,
        ]);

        return $this->json(['ok' => true]);
    }
}
