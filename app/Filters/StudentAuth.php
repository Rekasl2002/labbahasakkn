<?php

namespace App\Filters;

use App\Models\ParticipantModel;
use App\Models\SessionModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class StudentAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        $participantId = (int) $session->get('participant_id');
        $sessionId = (int) $session->get('session_id');

        helper('remember');
        if ($participantId <= 0 || $sessionId <= 0) {
            if (lab_restore_participant_from_cookie($request)) {
                return;
            }
            return redirect()->to('/login');
        }

        $participant = (new ParticipantModel())
            ->select('id,session_id')
            ->find($participantId);
        $active = (new SessionModel())
            ->select('id')
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->first();

        $isParticipantMatch = $participant && (int) ($participant['session_id'] ?? 0) === $sessionId;
        $isSessionStillActive = $active && (int) ($active['id'] ?? 0) === $sessionId;

        if ($isParticipantMatch && $isSessionStillActive) {
            return;
        }

        $session->remove([
            'participant_id',
            'session_id',
            'student_name',
            'class_name',
            'device_label',
            'student_waiting',
            'waiting_student_profile',
        ]);

        $resp = redirect()->to('/login')->with('ok', 'Sesi sudah berakhir. Silakan tunggu sesi berikutnya.');
        $resp->deleteCookie(LAB_COOKIE_PARTICIPANT);
        return $resp;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
