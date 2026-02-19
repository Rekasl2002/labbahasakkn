<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ParticipantModel;

class SessionApi extends BaseController
{
    public function active()
    {
        $active = $this->getActiveSession();
        return $this->json(['ok' => true, 'active' => $active]);
    }

    public function heartbeat()
    {
        $participantId = (int) session()->get('participant_id');
        $sessionId = (int) session()->get('session_id');
        if (!$participantId || $sessionId <= 0) {
            return $this->json(['ok' => false], 401);
        }

        $active = $this->getActiveSession();
        if (!$active || (int) ($active['id'] ?? 0) !== $sessionId) {
            return $this->json(['ok' => false, 'error' => 'No active session'], 400);
        }

        (new ParticipantModel())->update($participantId, ['last_seen_at' => date('Y-m-d H:i:s')]);
        return $this->json(['ok' => true]);
    }
}
