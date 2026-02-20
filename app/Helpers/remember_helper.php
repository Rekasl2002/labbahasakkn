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
if (!defined('LAB_COOKIE_WAITING')) {
    define('LAB_COOKIE_WAITING', 'lab_waiting');
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
            'device_label' => (string) ($participant['device_label'] ?? ''),
        ]);
        session()->remove(['student_waiting', 'waiting_student_profile']);

        $participantModel->update($participant['id'], [
            'last_seen_at' => date('Y-m-d H:i:s'),
            'left_at' => null,
        ]);

        return true;
    }
}

if (!function_exists('lab_waiting_profile_normalize')) {
    function lab_waiting_profile_normalize($profile): ?array
    {
        if (!is_array($profile)) {
            return null;
        }

        $studentName = trim((string) ($profile['student_name'] ?? ''));
        $className = trim((string) ($profile['class_name'] ?? ''));
        $deviceLabel = trim((string) ($profile['device_label'] ?? ''));

        if ($studentName === '' || $className === '') {
            return null;
        }

        if (function_exists('mb_substr')) {
            $studentName = mb_substr($studentName, 0, 60);
            $className = mb_substr($className, 0, 60);
            $deviceLabel = mb_substr($deviceLabel, 0, 60);
        } else {
            $studentName = substr($studentName, 0, 60);
            $className = substr($className, 0, 60);
            $deviceLabel = substr($deviceLabel, 0, 60);
        }

        return [
            'student_name' => $studentName,
            'class_name' => $className,
            'device_label' => $deviceLabel,
        ];
    }
}

if (!function_exists('lab_waiting_profile_pack')) {
    function lab_waiting_profile_pack(array $profile): string
    {
        $normalized = lab_waiting_profile_normalize($profile) ?? [
            'student_name' => '',
            'class_name' => '',
            'device_label' => '',
        ];
        $json = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return lab_remember_pack((string) $json);
    }
}

if (!function_exists('lab_waiting_profile_unpack')) {
    function lab_waiting_profile_unpack(?string $token): ?array
    {
        $raw = lab_remember_unpack($token);
        if ($raw === null || $raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);
        return lab_waiting_profile_normalize($decoded);
    }
}

if (!function_exists('lab_restore_waiting_from_cookie')) {
    function lab_restore_waiting_from_cookie($request): bool
    {
        $token = (string) $request->getCookie(LAB_COOKIE_WAITING);
        $profile = lab_waiting_profile_unpack($token);
        if (!$profile) {
            return false;
        }

        session()->set([
            'student_waiting' => 1,
            'waiting_student_profile' => $profile,
            'student_name' => $profile['student_name'],
            'class_name' => $profile['class_name'],
            'device_label' => $profile['device_label'],
        ]);

        return true;
    }
}
