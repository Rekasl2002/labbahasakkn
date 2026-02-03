<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\SessionModel;
use App\Models\ParticipantModel;
use App\Models\EventModel;
use App\Models\SessionStateModel;

class AuthController extends BaseController
{
    public function chooseRole()
    {
        return view('auth/choose_role');
    }

    public function adminLogin()
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        if ($username === '' || $password === '') {
            return redirect()->back()->with('error', 'Username/password wajib.');
        }

        $admin = (new AdminModel())->where('username', $username)->first();
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return redirect()->back()->with('error', 'Login admin gagal.');
        }

        session()->set([
            'admin_id' => $admin['id'],
            'admin_username' => $admin['username'],
        ]);

        return redirect()->to('/admin');
    }

    public function studentLogin()
    {
        $studentName = trim((string) $this->request->getPost('student_name'));
        $className   = trim((string) $this->request->getPost('class_name'));
        $deviceLabel = trim((string) $this->request->getPost('device_label'));

        if ($studentName === '' || $className === '') {
            return redirect()->back()->with('error', 'Nama & kelas wajib.');
        }

        $sessionModel = new SessionModel();
        $active = $sessionModel->where('is_active', 1)->orderBy('id', 'DESC')->first();

        if (!$active) {
            return view('auth/waiting_session', [
                'student_name' => $studentName,
                'class_name' => $className,
                'device_label' => $deviceLabel,
            ]);
        }

        $participantModel = new ParticipantModel();
        $participantId = $participantModel->insert([
            'session_id'    => $active['id'],
            'student_name'  => $studentName,
            'class_name'    => $className,
            'device_label'  => $deviceLabel ?: null,
            'ip_address'    => $this->request->getIPAddress(),
            'mic_on'        => 0,
            'speaker_on'    => 1,
            'joined_at'     => date('Y-m-d H:i:s'),
            'last_seen_at'  => date('Y-m-d H:i:s'),
        ], true);

        session()->set([
            'session_id' => $active['id'],
            'participant_id' => $participantId,
            'student_name' => $studentName,
            'class_name' => $className,
        ]);

        (new EventModel())->addForAll($active['id'], 'participant_joined', [
            'participant_id' => $participantId,
            'student_name' => $studentName,
            'class_name' => $className,
            'device_label' => $deviceLabel,
            'ip_address' => $this->request->getIPAddress(),
            'mic_on' => 0,
            'speaker_on' => 1,
        ]);

        // Pastikan session_state ada
        (new SessionStateModel())->ensureRow($active['id']);

        return redirect()->to('/student');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
