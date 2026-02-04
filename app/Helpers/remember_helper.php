<?php

use App\Models\AdminModel;
use App\Models\SessionModel;
use App\Models\ParticipantModel;

if (!defined('LAB_COOKIE_ADMIN')) {
    define('LAB_COOKIE_ADMIN', 'lab_admin');
}
if (!defined('LAB_COOKIE_PARTICIPANT')) {
    define('LAB_COOKIE_PARTICIPANT', 'lab_participant');
}
if (!defined('LAB_COOKIE_DEVICE')) {
    define('LAB_COOKIE_DEVICE', 'lab_device');
}

if (!function_exists('lab_remember_secret')) {
    function lab_remember_secret(): string
    {
        $key = (string) env('encryption.key');
        if ($key === '') {
            $key = (string) (config('Encryption')->key ?? '');
        }
        return $key !== '' ? $key : 'lab-bahasa-remember';
    }
}

if (!function_exists('lab_remember_pack')) {
    function lab_remember_pack(string $raw): string
    {
        $data = base64_encode($raw);
        $sig = hash_hmac('sha256', $data, lab_remember_secret());
        return $data . '.' . $sig;
    }
}

if (!function_exists('lab_remember_unpack')) {
    function lab_remember_unpack(?string $token): ?string
    {
        if (!$token) {
            return null;
        }
        $pos = strrpos($token, '.');
        if ($pos === false) {
            return null;
        }
        $data = substr($token, 0, $pos);
        $sig = substr($token, $pos + 1);
        $expected = hash_hmac('sha256', $data, lab_remember_secret());
        if (!hash_equals($expected, $sig)) {
            return null;
        }
        $raw = base64_decode($data, true);
        return $raw === false ? null : $raw;
    }
}

if (!function_exists('lab_remember_expire_seconds')) {
    function lab_remember_expire_seconds(): int
    {
        return 60 * 60 * 24 * 30;
    }
}

if (!function_exists('lab_generate_device_key')) {
    function lab_generate_device_key(): string
    {
        return bin2hex(random_bytes(16));
    }
}

if (!function_exists('lab_restore_admin_from_cookie')) {
    function lab_restore_admin_from_cookie($request): bool
    {
        $token = (string) $request->getCookie(LAB_COOKIE_ADMIN);
        $raw = lab_remember_unpack($token);
        $adminId = (int) ($raw ?? 0);
        if ($adminId <= 0) {
            return false;
        }

        $admin = (new AdminModel())->find($adminId);
        if (!$admin) {
            return false;
        }

        session()->set([
            'admin_id' => $admin['id'],
            'admin_username' => $admin['username'] ?? '',
        ]);

        return true;
    }
}

if (!function_exists('lab_restore_participant_from_cookie')) {
    function lab_restore_participant_from_cookie($request): bool
    {
        $token = (string) $request->getCookie(LAB_COOKIE_PARTICIPANT);
        $raw = lab_remember_unpack($token);
        $participantId = (int) ($raw ?? 0);
        if ($participantId <= 0) {
            return false;
        }

        $participantModel = new ParticipantModel();
        $participant = $participantModel->find($participantId);
        if (!$participant) {
            return false;
        }

        $active = (new SessionModel())->where('is_active', 1)->orderBy('id', 'DESC')->first();
        if (!$active || (int) $participant['session_id'] !== (int) $active['id']) {
            return false;
        }

        session()->set([
            'session_id' => (int) $participant['session_id'],
            'participant_id' => (int) $participant['id'],
            'student_name' => (string) ($participant['student_name'] ?? ''),
            'class_name' => (string) ($participant['class_name'] ?? ''),
        ]);

        $participantModel->update($participant['id'], [
            'last_seen_at' => date('Y-m-d H:i:s'),
            'left_at' => null,
        ]);

        return true;
    }
}
