<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\ParticipantModel;

/**
 * WebRTC signaling endpoint (offer/answer/candidate/hangup)
 * Signaling dikirim lewat tabel events (polling).
 */
class RtcApi extends BaseController
{
    /**
     * POST /api/rtc/signal
     *
     * Required:
     * - signal_type: offer|answer|candidate|hangup
     * - call_id: string (uuid / token)
     *
     * Optional:
     * - to_type: admin|participant (default admin)
     * - to_participant_id: int (required if to_type=participant)
     * - data: JSON object (string)
     * - session_id: int (optional override for admin only)
     */
    public function signal()
    {
        // 0) Only allow POST (optional but recommended)
        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return $this->json(['ok' => false, 'error' => 'Metode tidak diizinkan'], 405);
        }

        $isAdmin = (bool) session()->get('admin_id');
        $participantId = (int) session()->get('participant_id');

        // 1) Basic auth: must be admin OR logged-in participant
        if (!$isAdmin && !$participantId) {
            return $this->json(['ok' => false, 'error' => 'Akses ditolak'], 401);
        }

        // 2) Inputs
        $toType = (string) $this->request->getPost('to_type');
        $toType = $toType !== '' ? strtolower(trim($toType)) : 'admin';

        // support alias (optional)
        if ($toType === 'student') $toType = 'participant';

        $toParticipantId = (int) $this->request->getPost('to_participant_id');
        $signalType = (string) $this->request->getPost('signal_type');
        $signalType = strtolower(trim($signalType));

        $callId = (string) $this->request->getPost('call_id');
        $callId = trim($callId);

        $dataRaw = (string) $this->request->getPost('data');

        // 3) Validate signal_type
        $allowedSignal = ['offer', 'answer', 'candidate', 'hangup'];
        if (!in_array($signalType, $allowedSignal, true)) {
            return $this->json(['ok' => false, 'error' => 'signal_type tidak valid'], 400);
        }

        // 4) Validate to_type
        $allowedTo = ['admin', 'participant'];
        if (!in_array($toType, $allowedTo, true)) {
            return $this->json(['ok' => false, 'error' => 'to_type tidak valid'], 400);
        }

        // 5) Validate call_id (avoid weird payload)
        if ($callId === '' || strlen($callId) > 80) {
            return $this->json(['ok' => false, 'error' => 'call_id tidak valid'], 400);
        }

        // optional strict pattern (allow uuid-ish / token)
        // If you want to be more permissive, you can comment this block.
        if (!preg_match('/^[a-zA-Z0-9\-\_\.]+$/', $callId)) {
            return $this->json(['ok' => false, 'error' => 'Format call_id tidak valid'], 400);
        }

        // 6) Decode data JSON (must be JSON object)
        $data = [];

        // optional: limit payload size (candidate can get big)
        if ($dataRaw !== '' && strlen($dataRaw) > 65000) {
            return $this->json(['ok' => false, 'error' => 'Data terlalu besar'], 413);
        }

        if ($dataRaw !== '') {
            $decoded = json_decode($dataRaw, true);
            if (!is_array($decoded)) {
                return $this->json(['ok' => false, 'error' => 'Data tidak valid (harus objek JSON)'], 400);
            }
            $data = $decoded;
        }

        // 7) Determine session_id
        $sessionId = $this->resolveSessionId($isAdmin);
        if ($sessionId <= 0) {
            return $this->json(['ok' => false, 'error' => 'Tidak ada sesi aktif'], 400);
        }
        if (!$isAdmin) {
            $active = $this->getActiveSession();
            if (!$active || (int) ($active['id'] ?? 0) !== $sessionId) {
                return $this->json(['ok' => false, 'error' => 'Tidak ada sesi aktif'], 400);
            }
        }

        // 8) Optional: throttle / rate limit signaling
        // Candidate events can spam DB. Keep limit generous.
        if (!$this->allowRate($isAdmin, $participantId, $signalType)) {
            return $this->json(['ok' => false, 'error' => 'Terlalu banyak permintaan'], 429);
        }

        // 9) Security: validate student -> participant
        if (!$isAdmin && $toType === 'participant') {
            if ($toParticipantId <= 0) {
                return $this->json(['ok' => false, 'error' => 'to_participant_id wajib diisi'], 400);
            }
            if ($toParticipantId === $participantId) {
                return $this->json(['ok' => false, 'error' => 'Tidak bisa mengirim sinyal ke diri sendiri'], 400);
            }
        }

        // 10) Validate sender participant is in session (anti spoof)
        if (!$isAdmin) {
            if (!$this->participantInSession($participantId, $sessionId)) {
                return $this->json(['ok' => false, 'error' => 'Peserta tidak ada dalam sesi ini'], 403);
            }
        }

        // 11) Validate target participant (admin/student -> participant)
        if ($toType === 'participant') {
            if ($toParticipantId <= 0) {
                return $this->json(['ok' => false, 'error' => 'to_participant_id wajib diisi'], 400);
            }

            if (!$this->participantInSession($toParticipantId, $sessionId)) {
                return $this->json(['ok' => false, 'error' => 'Peserta tidak ditemukan'], 404);
            }
        }

        // 12) Build payload
        $payload = [
            'from_type' => $isAdmin ? 'admin' : 'student',
            'from_participant_id' => $participantId ?: null,
            'to_type' => $toType,
            'to_participant_id' => ($toType === 'participant') ? ($toParticipantId ?: null) : null,
            'signal_type' => $signalType,
            'call_id' => $callId,
            'data' => $data,

            // optional useful debug fields (safe to keep)
            'sent_at' => date('Y-m-d H:i:s'),
            'ip' => $this->request->getIPAddress(),
        ];

        // 13) Emit event
        $eventModel = new EventModel();

        if ($toType === 'admin') {
            $eventModel->addForAdmin($sessionId, 'rtc_signal', $payload);
            return $this->json(['ok' => true]);
        }

        if ($toType === 'participant') {
            $eventModel->addForParticipant($sessionId, $toParticipantId, 'rtc_signal', $payload);
            return $this->json(['ok' => true]);
        }

        // should never reach here
        return $this->json(['ok' => false, 'error' => 'to_type tidak valid'], 400);
    }

    /**
     * Resolve session id based on role.
     * - Admin: uses active session (is_active=1), or optional session_id from POST (if you enable it).
     * - Student: uses session('session_id').
     */
    private function resolveSessionId(bool $isAdmin): int
    {
        if ($isAdmin) {
            // Optional override (only if you *really* need it)
            // $postedSession = (int) $this->request->getPost('session_id');
            // if ($postedSession > 0) return $postedSession;

            $active = $this->getActiveSession();

            return $active ? (int) $active['id'] : 0;
        }

        return (int) session()->get('session_id');
    }

    /**
     * Validate participant existence in a session.
     */
    private function participantInSession(int $participantId, int $sessionId): bool
    {
        if ($participantId <= 0 || $sessionId <= 0) return false;

        $p = (new ParticipantModel())
            ->select('id')
            ->where('id', $participantId)
            ->where('session_id', $sessionId)
            ->first();

        return (bool) $p;
    }

    /**
     * Soft rate limit to prevent DB spam.
     * Generous limits so ICE candidates won't be blocked too easily.
     */
    private function allowRate(bool $isAdmin, int $participantId, string $signalType): bool
    {
        // If cache service not configured, just allow.
        try {
            $cache = cache();
        } catch (\Throwable $e) {
            return true;
        }

        $who = $isAdmin ? 'admin' : ('p' . (int)$participantId);
        $key = 'rtc_rate_' . $who;

        $bucket = $cache->get($key);
        if (!is_array($bucket)) {
            $bucket = ['ts' => time(), 'count' => 0];
        }

        $now = time();
        $window = 10; // seconds

        if (($now - (int)$bucket['ts']) >= $window) {
            $bucket = ['ts' => $now, 'count' => 0];
        }

        // allow more for candidate signals (student mesh needs higher limit)
        if ($signalType === 'candidate') {
            $limit = $isAdmin ? 220 : 600;
        } else {
            $limit = $isAdmin ? 60 : 120;
        }

        $bucket['count'] = (int)$bucket['count'] + 1;
        $cache->save($key, $bucket, 15);

        return ((int)$bucket['count'] <= $limit);
    }
}
