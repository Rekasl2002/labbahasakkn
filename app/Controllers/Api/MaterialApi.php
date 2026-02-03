<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SessionModel;
use App\Models\SessionStateModel;
use App\Models\MaterialModel;
use App\Models\MaterialFileModel;

class MaterialApi extends BaseController
{
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
            $file = $material ? (new MaterialFileModel())->where('material_id', $material['id'])->first() : null;
            $currentMaterial = ['material' => $material, 'file' => $file];
        }

        return $this->json([
            'ok' => true,
            'state' => $state,
            'currentMaterial' => $currentMaterial,
        ]);
    }
}
