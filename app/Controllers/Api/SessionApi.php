<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ParticipantModel;

class SessionApi extends BaseController
{
    private int $onlineWindowSeconds = 6;
    private int $activeWriteThrottleSeconds = 2;
    private array $allowedPresencePages = ['session', 'settings', 'about'];

    public function active()
    {
        $active = $this->getActiveSession();
        return $this->json(['ok' => true, 'active' => $active]);
    }

    public function heartbeat()
    {
        $participantId = (int) session()->get('participant_id');
        $sessionId = (int) session()->get('session_id');
        if (!$participantId || $sessionId <= 0) {
            return $this->json(['ok' => false], 401);
        }

        $active = $this->getActiveSession();
        if (!$active || (int) ($active['id'] ?? 0) !== $sessionId) {
            return $this->json(['ok' => false, 'error' => 'No active session'], 400);
        }

        $pm = new ParticipantModel();
        $me = $pm
            ->select('id,session_id,last_seen_at,presence_state,presence_page,presence_reason,presence_updated_at')
            ->where('id', $participantId)
            ->where('session_id', $sessionId)
            ->first();

        if (!$me) {
            return $this->json(['ok' => false, 'error' => 'Participant not found'], 404);
        }

        [$presenceState, $presencePage, $presenceReason] = $this->normalizePresenceInput();
        $now = date('Y-m-d H:i:s');
        $nowTs = time();
        $lastSeenTs = !empty($me['last_seen_at']) ? (int) strtotime((string) $me['last_seen_at']) : 0;
        $lastPresenceTs = !empty($me['presence_updated_at']) ? (int) strtotime((string) $me['presence_updated_at']) : 0;

        $stateChanged = $presenceState !== (string) ($me['presence_state'] ?? '');
        $pageChanged = $presencePage !== (string) ($me['presence_page'] ?? '');
        $reasonChanged = $presenceReason !== (string) ($me['presence_reason'] ?? '');
        $presenceStale = $lastPresenceTs <= 0 || ($nowTs - $lastPresenceTs) >= $this->activeWriteThrottleSeconds;
        $seenStale = $lastSeenTs <= 0 || ($nowTs - $lastSeenTs) >= $this->activeWriteThrottleSeconds;

        $mustUpdate = $stateChanged || $pageChanged || $reasonChanged;
        if ($presenceState === 'active') {
            $mustUpdate = $mustUpdate || $seenStale || $presenceStale;
        } else {
            $mustUpdate = $mustUpdate || $lastSeenTs > 0;
        }

        if ($mustUpdate) {
            $updateData = [
                'presence_state' => $presenceState,
                'presence_page' => $presencePage,
                'presence_reason' => $presenceReason,
                'presence_updated_at' => $now,
                'last_seen_at' => $presenceState === 'active' ? $now : null,
            ];
            $pm->update($participantId, $updateData);
            $me = array_merge($me, $updateData);
        }

        $seenTs = !empty($me['last_seen_at']) ? (int) strtotime((string) $me['last_seen_at']) : 0;
        $online = $presenceState === 'active' && $seenTs > 0 && (($nowTs - $seenTs) <= $this->onlineWindowSeconds);

        return $this->json([
            'ok' => true,
            'online' => $online,
            'presence_state' => $presenceState,
            'presence_page' => $presencePage,
            'presence_reason' => $presenceReason,
            'server_time' => $now,
        ]);
    }

    /**
     * @return array{0:string,1:string,2:string}
     */
    private function normalizePresenceInput(): array
    {
        $presence = strtolower(trim((string) $this->request->getPost('presence')));
        $page = strtolower(trim((string) $this->request->getPost('page')));
        $reason = strtolower(trim((string) $this->request->getPost('reason')));

        if (!in_array($presence, ['active', 'away', 'offline'], true)) {
            $presence = 'active';
        }
        if ($page === '') {
            $page = 'session';
        }
        if (!in_array($page, ['session', 'settings', 'about', 'other'], true)) {
            $page = 'other';
        }

        $isAllowedPage = in_array($page, $this->allowedPresencePages, true);
        if ($presence === 'active' && !$isAllowedPage) {
            $presence = 'away';
        }

        if ($reason === '') {
            if ($presence === 'active') {
                $reason = 'active';
            } elseif ($presence === 'offline') {
                $reason = 'pagehide';
            } elseif (!$isAllowedPage) {
                $reason = 'outside_session_page';
            } else {
                $reason = 'tab_hidden';
            }
        }

        if (function_exists('mb_substr')) {
            $reason = mb_substr($reason, 0, 60);
        } else {
            $reason = substr($reason, 0, 60);
        }

        return [$presence, $page, $reason];
    }
}
