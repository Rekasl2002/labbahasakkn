<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MessageModel;
use App\Models\EventModel;

class ChatApi extends BaseController
{
    public function send()
    {
        $isAdmin = (bool) session()->get('admin_id');
        $participantId = (int) session()->get('participant_id');

        $active = $this->getActiveSession();
        $sessionId = 0;

        if ($isAdmin && $active) {
            $sessionId = (int) $active['id'];
        }
        if (!$isAdmin) {
            $sessionId = (int) session()->get('session_id');
            if (!$active || (int) ($active['id'] ?? 0) !== $sessionId) {
                $sessionId = 0;
            }
        }

        if ($sessionId <= 0) return $this->json(['ok' => false, 'error' => 'No session'], 400);

        $body = trim((string) $this->request->getPost('body'));
        $targetType = (string) $this->request->getPost('target_type'); // public|private_admin|private_student
        $targetPid  = (int) $this->request->getPost('target_participant_id');

        if ($body === '') return $this->json(['ok' => false, 'error' => 'Empty'], 400);
        if (!in_array($targetType, ['public', 'private_admin', 'private_student'], true)) $targetType = 'public';
        if ($targetType === 'private_student' && $targetPid <= 0) return $this->json(['ok' => false, 'error' => 'target_participant_id required'], 400);

        // Student only can message public or private_admin
        if (!$isAdmin && $targetType === 'private_student') {
            return $this->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        $mm = new MessageModel();
        $msgId = $mm->insert([
            'session_id' => $sessionId,
            'sender_type' => $isAdmin ? 'admin' : 'student',
            'sender_admin_id' => $isAdmin ? (int) session()->get('admin_id') : null,
            'sender_participant_id' => $isAdmin ? null : $participantId,
            'target_type' => $targetType,
            'target_participant_id' => ($targetType === 'private_student') ? $targetPid : null,
            'body' => $body,
            'created_at' => date('Y-m-d H:i:s'),
        ], true);

        $event = new EventModel();
        $payload = [
            'message_id' => $msgId,
            'sender_type' => $isAdmin ? 'admin' : 'student',
            'sender_participant_id' => $participantId ?: null,
            'target_type' => $targetType,
            'target_participant_id' => ($targetType === 'private_student') ? $targetPid : null,
            'body' => $body,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($targetType === 'public') {
            $event->addForAll($sessionId, 'message_sent', $payload);
        } elseif ($targetType === 'private_admin') {
            // hanya admin (dan pengirim sendiri kalau student)
            $event->addForAdmin($sessionId, 'message_private_admin', $payload);
            if (!$isAdmin && $participantId) {
                $event->addForParticipant($sessionId, $participantId, 'message_private_admin', $payload);
            }
        } else { // private_student (admin -> student)
            $event->addForAdmin($sessionId, 'message_private_student', $payload);
            $event->addForParticipant($sessionId, $targetPid, 'message_private_student', $payload);
        }

        return $this->json(['ok' => true, 'message_id' => $msgId]);
    }
}
