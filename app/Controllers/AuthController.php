<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\ParticipantModel;
use App\Models\MessageModel;
use App\Models\EventModel;
use App\Models\SessionStateModel;

class AuthController extends BaseController
{
    public function chooseRole()
    {
        if (session()->get('admin_id')) {
            return redirect()->to('/admin');
        }

        if (session()->get('participant_id') && session()->get('session_id')) {
            return redirect()->to('/student');
        }

        helper('remember');
        if (lab_restore_admin_from_cookie($this->request)) {
            return redirect()->to('/admin');
        }
        if (lab_restore_participant_from_cookie($this->request)) {
            return redirect()->to('/student');
        }

        helper('settings');
        $clientIp = (string) $this->request->getIPAddress();
        $settings = lab_load_settings();
        $deviceLabel = lab_device_label_for_ip($clientIp, $settings);

        return view('auth/choose_role', [
            'client_ip' => $clientIp,
            'device_label' => $deviceLabel,
        ]);
    }

    public function adminLogin()
    {
        helper('remember');
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

        $this->response->setCookie(
            LAB_COOKIE_ADMIN,
            lab_remember_pack((string) $admin['id']),
            lab_remember_expire_seconds()
        );

        return redirect()->to('/admin');
    }

    public function studentLogin()
    {
        helper('remember');
        $studentName = trim((string) $this->request->getPost('student_name'));
        $className   = trim((string) $this->request->getPost('class_name'));
        $deviceLabel = trim((string) $this->request->getPost('device_label'));

        if ($deviceLabel === '') {
            helper('settings');
            $deviceLabel = lab_device_label_for_ip((string) $this->request->getIPAddress());
        }

        if ($studentName === '' || $className === '') {
            return redirect()->back()->with('error', 'Nama & kelas wajib.');
        }

        $active = $this->getActiveSession();

        $deviceKeyToken = (string) $this->request->getCookie(LAB_COOKIE_DEVICE);
        $deviceKey = (string) (lab_remember_unpack($deviceKeyToken) ?? '');
        if ($deviceKey === '') {
            $deviceKey = lab_generate_device_key();
            $this->response->setCookie(
                LAB_COOKIE_DEVICE,
                lab_remember_pack($deviceKey),
                lab_remember_expire_seconds()
            );
        }

        if (!$active) {
            return view('auth/waiting_session', [
                'student_name' => $studentName,
                'class_name' => $className,
                'device_label' => $deviceLabel,
            ]);
        }

        $participantModel = new ParticipantModel();
        $participantId = 0;
        $existing = null;

        $pidToken = (string) $this->request->getCookie(LAB_COOKIE_PARTICIPANT);
        $pidFromCookie = (int) (lab_remember_unpack($pidToken) ?? 0);

        if ($pidFromCookie > 0) {
            $existing = $participantModel
                ->where('id', $pidFromCookie)
                ->where('session_id', $active['id'])
                ->first();

            if ($existing) {
                $sameName = strcasecmp((string) ($existing['student_name'] ?? ''), $studentName) === 0;
                $sameClass = strcasecmp((string) ($existing['class_name'] ?? ''), $className) === 0;
                if (!$sameName || !$sameClass) {
                    $existing = null;
                }
            }
        }

        if (!$existing && $deviceKey !== '') {
            $existing = $participantModel
                ->where('session_id', $active['id'])
                ->where('student_name', $studentName)
                ->where('class_name', $className)
                ->where('device_key', $deviceKey)
                ->first();
        }

        if ($existing) {
            $participantId = (int) $existing['id'];
            $participantModel->update($participantId, [
                'device_label' => $deviceLabel !== '' ? $deviceLabel : ($existing['device_label'] ?? null),
                'device_key' => $deviceKey !== '' ? $deviceKey : ($existing['device_key'] ?? null),
                'ip_address' => $this->request->getIPAddress(),
                'last_seen_at' => date('Y-m-d H:i:s'),
                'left_at' => null,
            ]);
        } else {
            $participantId = $participantModel->insert([
                'session_id'    => $active['id'],
                'student_name'  => $studentName,
                'class_name'    => $className,
                'device_label'  => $deviceLabel ?: null,
                'device_key'    => $deviceKey ?: null,
                'ip_address'    => $this->request->getIPAddress(),
                'mic_on'        => 0,
                'speaker_on'    => 1,
                'joined_at'     => date('Y-m-d H:i:s'),
                'last_seen_at'  => date('Y-m-d H:i:s'),
            ], true);
        }

        session()->set([
            'session_id' => $active['id'],
            'participant_id' => $participantId,
            'student_name' => $studentName,
            'class_name' => $className,
        ]);

        $this->response->setCookie(
            LAB_COOKIE_PARTICIPANT,
            lab_remember_pack((string) $participantId),
            lab_remember_expire_seconds()
        );

        if (!$existing) {
            (new EventModel())->addForAll($active['id'], 'participant_joined', [
                'participant_id' => $participantId,
                'student_name' => $studentName,
                'class_name' => $className,
                'device_label' => $deviceLabel,
                'ip_address' => $this->request->getIPAddress(),
                'mic_on' => 0,
                'speaker_on' => 1,
            ]);
        }

        // Pastikan session_state ada
        (new SessionStateModel())->ensureRow($active['id']);

        return redirect()->to('/student');
    }

    public function logout()
    {
        helper('remember');
        session()->destroy();
        $this->response->deleteCookie(LAB_COOKIE_ADMIN);
        $this->response->deleteCookie(LAB_COOKIE_PARTICIPANT);
        return redirect()->to('/');
    }

    public function studentLogout()
    {
        helper('remember');

        $sessionId = (int) session()->get('session_id');
        $participantId = (int) session()->get('participant_id');
        $active = $this->getActiveSessionRaw();
        $isCurrentSessionActive = $active && (int) ($active['id'] ?? 0) === $sessionId;

        if ($sessionId > 0 && $participantId > 0) {
            $participantModel = new ParticipantModel();
            $participant = $participantModel
                ->where('id', $participantId)
                ->where('session_id', $sessionId)
                ->first();

            if ($participant && $isCurrentSessionActive) {
                $db = db_connect();
                $db->transStart();

                // Hapus pesan milik/tujuan siswa ini dalam sesi.
                (new MessageModel())
                    ->groupStart()
                        ->where('session_id', $sessionId)
                        ->where('sender_participant_id', $participantId)
                    ->groupEnd()
                    ->orGroupStart()
                        ->where('session_id', $sessionId)
                        ->where('target_participant_id', $participantId)
                    ->groupEnd()
                    ->delete();

                // Hapus jejak siswa dari sesi agar tidak masuk daftar rekap.
                $participantModel->delete($participantId);

                (new EventModel())->addForAll($sessionId, 'participant_left', [
                    'participant_id' => $participantId,
                    'student_name' => (string) ($participant['student_name'] ?? ''),
                    'class_name' => (string) ($participant['class_name'] ?? ''),
                    'left_at' => date('Y-m-d H:i:s'),
                ]);

                $db->transComplete();
            }
        }

        session()->destroy();
        $this->response->deleteCookie(LAB_COOKIE_PARTICIPANT);
        $msg = $isCurrentSessionActive ? 'Sesi siswa telah diakhiri.' : 'Sesi sudah berakhir. Kamu telah keluar.';
        return redirect()->to('/')->with('ok', $msg);
    }
}
