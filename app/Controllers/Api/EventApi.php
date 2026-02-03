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
    public function poll()
    {
        // Only GET (biar konsisten dan gampang di-cache-control)
        if (strtoupper($this->request->getMethod()) !== 'GET') {
            return $this->json(['ok' => false, 'error' => 'Method not allowed'], 405);
        }

        $since = (int) $this->request->getGet('since');
        $since = max(0, $since);

        $forceSnapshot = (int) $this->request->getGet('snapshot') === 1;

        $isAdmin = (bool) session()->get('admin_id');
        $participantId = (int) session()->get('participant_id');

        // Harus login admin atau siswa
        if (!$isAdmin && $participantId <= 0) {
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }

        // Tentukan session_id
        $sessionId = 0;

        if ($isAdmin) {
            $active = (new SessionModel())
                ->where('is_active', 1)
                ->orderBy('id', 'DESC')
                ->first();

            $sessionId = $active ? (int) $active['id'] : 0;
        } else {
            $sessionId = (int) session()->get('session_id');
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
            $me = $pm->select('id,session_id,last_seen_at')
                ->where('id', $participantId)
                ->first();

            if (!$me || (int) $me['session_id'] !== $sessionId) {
                return $this->json(['ok' => false, 'error' => 'Invalid session'], 403);
            }

            // Throttle heartbeat update biar tidak update DB setiap 1.2 detik
            // Update hanya jika last_seen_at kosong atau sudah lebih dari 10 detik
            $nowTs = time();
            $lastSeenTs = !empty($me['last_seen_at']) ? strtotime($me['last_seen_at']) : 0;

            if ($lastSeenTs <= 0 || ($nowTs - $lastSeenTs) >= 10) {
                // Pakai query builder langsung agar ringan
                $db = db_connect();
                $db->table('participants')
                    ->where('id', $participantId)
                    ->update(['last_seen_at' => date('Y-m-d H:i:s')]);
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
            ->select('id,last_seen_at')
            ->where('session_id', $sessionId)
            ->findAll();

        $now = time();
        $presence = [];
        foreach ($presenceRows as $r) {
            $seen = !empty($r['last_seen_at']) ? strtotime($r['last_seen_at']) : 0;
            $presence[] = [
                'id' => (int) $r['id'],
                'online' => ($seen > 0 && ($now - $seen) <= 35),
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
                if ($material) {
                    $file = (new MaterialFileModel())->where('material_id', $material['id'])->first();

                    // Opsional: jika file ada, pastikan ada url_path string
                    if ($file && isset($file['url_path'])) {
                        $file['url_path'] = (string) $file['url_path'];
                    }

                    $currentMaterial = [
                        'material' => $material,
                        'file' => $file,
                    ];
                }
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
