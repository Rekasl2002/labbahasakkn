<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SessionModel;
use App\Models\ParticipantModel;

class SessionApi extends BaseController
{
    public function active()
    {
        $active = (new SessionModel())->where('is_active', 1)->orderBy('id', 'DESC')->first();
        return $this->json(['ok' => true, 'active' => $active]);
    }

    public function heartbeat()
    {
        $participantId = (int) session()->get('participant_id');
        if (!$participantId) return $this->json(['ok' => false], 401);

        (new ParticipantModel())->update($participantId, ['last_seen_at' => date('Y-m-d H:i:s')]);
        return $this->json(['ok' => true]);
    }
}
