<?php

namespace App\Controllers;

use App\Models\SessionModel;
use App\Models\SessionStateModel;
use App\Models\ParticipantModel;
use App\Models\MessageModel;
use App\Models\EventModel;

class AdminController extends BaseController
{
    public function dashboard()
    {
        $sessionModel = new SessionModel();
        $active = $sessionModel->where('is_active', 1)->orderBy('id', 'DESC')->first();

        $participants = [];
        $state = null;

        if ($active) {
            $participants = (new ParticipantModel())->where('session_id', $active['id'])->orderBy('id', 'ASC')->findAll();
            $state = (new SessionStateModel())->where('session_id', $active['id'])->first();
        }

        return view('admin/dashboard', [
            'activeSession' => $active,
            'participants' => $participants,
            'state' => $state,
        ]);
    }

    public function settings()
    {
        return view('admin/settings');
    }

    public function startSession()
    {
        $name = trim((string) $this->request->getPost('name'));
        $name = $name ?: ('Sesi ' . date('Y-m-d H:i'));

        $sessionModel = new SessionModel();

        // matikan sesi lain (kalau ada)
        $sessionModel->where('is_active', 1)->set(['is_active' => 0, 'ended_at' => date('Y-m-d H:i:s')])->update();

        $id = $sessionModel->insert([
            'name' => $name,
            'is_active' => 1,
            'started_at' => date('Y-m-d H:i:s'),
            'created_by_admin_id' => (int) session()->get('admin_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ], true);

        (new SessionStateModel())->ensureRow($id);

        (new EventModel())->addForAll($id, 'session_started', [
            'session_id' => $id,
            'name' => $name,
            'started_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin');
    }

    public function endSession()
    {
        $sessionModel = new SessionModel();
        $active = $sessionModel->where('is_active', 1)->orderBy('id', 'DESC')->first();

        if (!$active) {
            return redirect()->to('/admin')->with('error', 'Tidak ada sesi aktif.');
        }

        $endedAt = date('Y-m-d H:i:s');
        $sessionModel->update($active['id'], [
            'is_active' => 0,
            'ended_at' => $endedAt,
        ]);

        (new EventModel())->addForAll($active['id'], 'session_ended', [
            'session_id' => $active['id'],
            'ended_at' => $endedAt,
        ]);

        // Rekap
        $participantModel = new ParticipantModel();
        $messageModel = new MessageModel();

        $participants = $participantModel->where('session_id', $active['id'])->orderBy('id', 'ASC')->findAll();
        $messagesCount = $messageModel->where('session_id', $active['id'])->countAllResults();

        // hitung materi dipakai dari events
        $db = db_connect();
        $materialsUsed = $db->table('events')
            ->select('JSON_EXTRACT(payload_json, "$.material_id") AS mid')
            ->where('session_id', $active['id'])
            ->where('type', 'material_changed')
            ->groupBy('mid')
            ->countAllResults();

        $durationSec = 0;
        if (!empty($active['started_at'])) {
            $durationSec = max(0, strtotime($endedAt) - strtotime($active['started_at']));
        }

        return view('admin/recap', [
            'session' => array_merge($active, ['ended_at' => $endedAt]),
            'participants' => $participants,
            'messagesCount' => $messagesCount,
            'materialsUsed' => $materialsUsed,
            'durationSec' => $durationSec,
        ]);
    }
}
