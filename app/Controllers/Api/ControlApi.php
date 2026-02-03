<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SessionModel;
use App\Models\ParticipantModel;
use App\Models\SessionStateModel;
use App\Models\EventModel;

class ControlApi extends BaseController
{
    /**
     * Student: toggle mic diri sendiri (mic_on).
     * POST /api/control/mic/toggle
     */
    public function toggleMic()
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return $this->json(['ok' => false, 'error' => 'Method not allowed'], 405);
        }

        $participantId = (int) session()->get('participant_id');
        $sessionId = (int) session()->get('session_id');

        if (!$participantId || !$sessionId) {
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }

        $state = (new SessionStateModel())->where('session_id', $sessionId)->first();
        if ($state && array_key_exists('allow_student_mic', $state) && !(int)$state['allow_student_mic']) {
            return $this->json(['ok' => false, 'error' => 'Mic dikunci admin.'], 403);
        }

        $pm = new ParticipantModel();

        // Pastikan peserta benar-benar ada di session tsb (anti spoof / session mismatch)
        $me = $pm->where('id', $participantId)
                 ->where('session_id', $sessionId)
                 ->first();

        if (!$me) {
            return $this->json(['ok' => false, 'error' => 'Participant not found'], 404);
        }

        $new = !empty($me['mic_on']) ? 0 : 1;

        $pm->update($participantId, ['mic_on' => $new]);

        // Broadcast agar admin & siswa lain bisa update UI (peers list).
        (new EventModel())->addForAll($sessionId, 'mic_changed', [
            'participant_id' => $participantId,
            'mic_on' => $new,
        ]);

        return $this->json(['ok' => true, 'mic_on' => $new]);
    }

    /**
     * Student: toggle speaker diri sendiri (speaker_on).
     * POST /api/control/speaker/toggle
     */
    public function toggleSpeaker()
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return $this->json(['ok' => false, 'error' => 'Method not allowed'], 405);
        }

        $participantId = (int) session()->get('participant_id');
        $sessionId = (int) session()->get('session_id');

        if (!$participantId || !$sessionId) {
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }

        $state = (new SessionStateModel())->where('session_id', $sessionId)->first();
        if ($state && array_key_exists('allow_student_speaker', $state) && !(int)$state['allow_student_speaker']) {
            return $this->json(['ok' => false, 'error' => 'Speaker dikunci admin.'], 403);
        }

        $pm = new ParticipantModel();

        $me = $pm->where('id', $participantId)
                 ->where('session_id', $sessionId)
                 ->first();

        if (!$me) {
            return $this->json(['ok' => false, 'error' => 'Participant not found'], 404);
        }

        $new = !empty($me['speaker_on']) ? 0 : 1;

        $pm->update($participantId, ['speaker_on' => $new]);

        (new EventModel())->addForAll($sessionId, 'speaker_changed', [
            'participant_id' => $participantId,
            'speaker_on' => $new,
        ]);

        return $this->json(['ok' => true, 'speaker_on' => $new]);
    }

    /**
     * Admin: set mic participant tertentu.
     * POST /api/control/admin/mic
     */
    public function adminSetMic()
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return $this->json(['ok' => false, 'error' => 'Method not allowed'], 405);
        }

        if (!$this->isAdmin()) {
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }

        $active = $this->getActiveSession();
        if (!$active) {
            return $this->json(['ok' => false, 'error' => 'No active session'], 400);
        }

        $sessionId = (int) $active['id'];

        $pid = (int) $this->request->getPost('participant_id');
        if ($pid <= 0) {
            return $this->json(['ok' => false, 'error' => 'participant_id required'], 400);
        }

        $mic = (int) $this->request->getPost('mic_on');
        $mic = $mic ? 1 : 0;

        $pm = new ParticipantModel();

        // Pastikan participant ada dan milik session aktif
        $p = $pm->select('id')
                ->where('id', $pid)
                ->where('session_id', $sessionId)
                ->first();

        if (!$p) {
            return $this->json(['ok' => false, 'error' => 'Participant not found'], 404);
        }

        $pm->update($pid, ['mic_on' => $mic]);

        (new EventModel())->addForAll($sessionId, 'mic_changed', [
            'participant_id' => $pid,
            'mic_on' => $mic,
            'forced_by_admin' => true,
        ]);

        return $this->json(['ok' => true]);
    }

    /**
     * Admin: set speaker participant tertentu (speaker_on).
     * POST /api/control/admin/speaker
     */
    public function adminSetSpeaker()
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return $this->json(['ok' => false, 'error' => 'Method not allowed'], 405);
        }

        if (!$this->isAdmin()) {
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }

        $active = $this->getActiveSession();
        if (!$active) {
            return $this->json(['ok' => false, 'error' => 'No active session'], 400);
        }

        $sessionId = (int) $active['id'];

        $pid = (int) $this->request->getPost('participant_id');
        if ($pid <= 0) {
            return $this->json(['ok' => false, 'error' => 'participant_id required'], 400);
        }

        $spk = (int) $this->request->getPost('speaker_on');
        $spk = $spk ? 1 : 0;

        $pm = new ParticipantModel();

        // Pastikan participant ada dan milik session aktif
        $p = $pm->select('id')
                ->where('id', $pid)
                ->where('session_id', $sessionId)
                ->first();

        if (!$p) {
            return $this->json(['ok' => false, 'error' => 'Participant not found'], 404);
        }

        $pm->update($pid, ['speaker_on' => $spk]);

        // speaker_changed sengaja broadcast ke all supaya siswa tahu speaker-nya “dipaksa admin”
        (new EventModel())->addForAll($sessionId, 'speaker_changed', [
            'participant_id' => $pid,
            'speaker_on' => $spk,
            'forced_by_admin' => true,
        ]);

        return $this->json(['ok' => true]);
    }

    /**
     * Admin: set mic/speaker untuk semua participant di session aktif.
     * POST /api/control/admin/all
     */
    public function adminSetAll()
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return $this->json(['ok' => false, 'error' => 'Method not allowed'], 405);
        }

        if (!$this->isAdmin()) {
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }

        $active = $this->getActiveSession();
        if (!$active) {
            return $this->json(['ok' => false, 'error' => 'No active session'], 400);
        }

        $sessionId = (int) $active['id'];

        // Boleh set salah satu atau keduanya
        $mic = $this->request->getPost('mic_on');
        $spk = $this->request->getPost('speaker_on');

        $data = [];
        if ($mic !== null) $data['mic_on'] = ((int) $mic) ? 1 : 0;
        if ($spk !== null) $data['speaker_on'] = ((int) $spk) ? 1 : 0;

        if (!$data) {
            return $this->json(['ok' => false, 'error' => 'No data'], 400);
        }

        // Update massal (lebih cepat daripada loop update satu-satu)
        $db = db_connect();
        $db->table('participants')
            ->where('session_id', $sessionId)
            ->update($data);

        // Emit events agar UI update
        $em = new EventModel();

        if (array_key_exists('mic_on', $data)) {
            $em->addForAll($sessionId, 'mic_all_changed', [
                'mic_on' => (int) $data['mic_on'],
                'forced_by_admin' => true,
            ]);
        }

        if (array_key_exists('speaker_on', $data)) {
            $em->addForAll($sessionId, 'speaker_all_changed', [
                'speaker_on' => (int) $data['speaker_on'],
                'forced_by_admin' => true,
            ]);
        }

        return $this->json(['ok' => true]);
    }

    /**
     * Admin: set teks broadcast.
     * POST /api/control/admin/broadcast-text
     */
    public function adminSetBroadcastText()
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return $this->json(['ok' => false, 'error' => 'Method not allowed'], 405);
        }

        if (!$this->isAdmin()) {
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }

        $active = $this->getActiveSession();
        if (!$active) {
            return $this->json(['ok' => false, 'error' => 'No active session'], 400);
        }

        $text = trim((string) $this->request->getPost('broadcast_text'));

        // Batasi panjang agar aman untuk UI & DB
        if (function_exists('mb_substr')) {
            $text = mb_substr($text, 0, 255);
        } else {
            $text = substr($text, 0, 255);
        }

        $sessionId = (int) $active['id'];

        (new SessionStateModel())->setBroadcastText($sessionId, $text);

        (new EventModel())->addForAll($sessionId, 'broadcast_text_changed', [
            'broadcast_text' => $text,
        ]);

        return $this->json(['ok' => true, 'broadcast_text' => $text]);
    }

    /**
     * Admin: kunci/izinkan kontrol mic & speaker untuk siswa.
     * POST /api/control/admin/voice-lock
     */
    public function adminSetVoiceLock()
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return $this->json(['ok' => false, 'error' => 'Method not allowed'], 405);
        }

        if (!$this->isAdmin()) {
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }

        $active = $this->getActiveSession();
        if (!$active) {
            return $this->json(['ok' => false, 'error' => 'No active session'], 400);
        }

        $allowMic = $this->request->getPost('allow_student_mic');
        $allowSpk = $this->request->getPost('allow_student_speaker');

        $data = [];
        if ($allowMic !== null) $data['allow_student_mic'] = ((int) $allowMic) ? 1 : 0;
        if ($allowSpk !== null) $data['allow_student_speaker'] = ((int) $allowSpk) ? 1 : 0;

        if (!$data) {
            return $this->json(['ok' => false, 'error' => 'No data'], 400);
        }

        $sessionId = (int) $active['id'];

        try {
            (new SessionStateModel())->setVoiceLocks($sessionId, $data);
        } catch (\Throwable $e) {
            return $this->json([
                'ok' => false,
                'error' => 'Gagal menyimpan kontrol mic/speaker. Pastikan migrasi terbaru sudah dijalankan.'
            ], 500);
        }

        (new EventModel())->addForAll($sessionId, 'voice_lock_changed', [
            'allow_student_mic' => $data['allow_student_mic'] ?? null,
            'allow_student_speaker' => $data['allow_student_speaker'] ?? null,
        ]);

        return $this->json(['ok' => true] + $data);
    }

    /* =========================================================
     * Helpers (opsional, tapi bikin API lebih rapih & aman)
     * ========================================================= */

    private function getActiveSession(): ?array
    {
        $active = (new SessionModel())
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->first();

        return $active ?: null;
    }
}
