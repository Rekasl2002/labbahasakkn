<?php

namespace App\Controllers;

use App\Models\SessionModel;
use App\Models\ParticipantModel;
use App\Models\SessionStateModel;
use App\Models\MaterialModel;
use App\Models\MaterialFileModel;

class StudentController extends BaseController
{
    public function dashboard()
    {
        $sessionId = (int) session()->get('session_id');
        $participantId = (int) session()->get('participant_id');

        $session = (new SessionModel())->find($sessionId);
        $me = (new ParticipantModel())->find($participantId);

        $state = (new SessionStateModel())->where('session_id', $sessionId)->first();
        $currentMaterial = null;

        if ($state && !empty($state['current_material_id'])) {
            $material = (new MaterialModel())->find((int)$state['current_material_id']);
            if ($material) {
                $file = (new MaterialFileModel())->where('material_id', $material['id'])->first();
                $currentMaterial = [
                    'material' => $material,
                    'file' => $file,
                ];
            }
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
        return view('student/settings');
    }
}
