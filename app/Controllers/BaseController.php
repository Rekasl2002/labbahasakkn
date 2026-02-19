<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\SessionModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BaseController extends Controller
{
    protected $helpers = ['url', 'form'];
    protected int $sessionWarningSeconds = 600;

    /**
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * CI4 recommended init hook.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Session service (lebih nyaman daripada session() helper di mana-mana)
        $this->session = \Config\Services::session();

        // Header aman yang "low risk" untuk web app biasa
        // (Tidak mengganggu WebRTC. Hindari set Permissions-Policy yang terlalu ketat di sini.)
        $this->response->setHeader('X-Content-Type-Options', 'nosniff');
        $this->response->setHeader('Referrer-Policy', 'same-origin');
        $this->response->setHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    /**
     * JSON responder standar untuk semua API controller.
     */
    protected function json($data, int $status = 200)
    {
        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($data);
    }

    /**
     * Untuk endpoint polling/RTC: jangan sampai ke-cache.
     */
    protected function jsonNoStore($data, int $status = 200)
    {
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        return $this->json($data, $status);
    }

    /* =========================================================
     * Helpers kecil (opsional, tapi bikin controller lain rapi)
     * ========================================================= */

    protected function isAdmin(): bool
    {
        return (bool) ($this->session ? $this->session->get('admin_id') : session()->get('admin_id'));
    }

    protected function participantId(): int
    {
        return (int) ($this->session ? $this->session->get('participant_id') : session()->get('participant_id'));
    }

    protected function sessionId(): int
    {
        return (int) ($this->session ? $this->session->get('session_id') : session()->get('session_id'));
    }

    /**
     * Return Response jika unauthorized, kalau ok return null.
     * Pemakaian:
     *   if ($resp = $this->requireAdmin()) return $resp;
     */
    protected function requireAdmin()
    {
        if (!$this->isAdmin()) {
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }
        return null;
    }

    /**
     * Return Response jika unauthorized, kalau ok return null.
     */
    protected function requireParticipant()
    {
        if ($this->participantId() <= 0 || $this->sessionId() <= 0) {
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }
        return null;
    }

    /**
     * Ambil string POST dengan trim + max length (opsional).
     */
    protected function postString(string $key, int $maxLen = 255): string
    {
        $v = trim((string) $this->request->getPost($key));

        if (function_exists('mb_substr')) {
            return mb_substr($v, 0, $maxLen);
        }

        return substr($v, 0, $maxLen);
    }

    /**
     * Ambil sesi aktif terbaru. Opsional auto-close bila melewati deadline.
     */
    protected function getActiveSession(bool $autoCloseExpired = true): ?array
    {
        $active = $this->getActiveSessionRaw();
        if (!$active) {
            return null;
        }

        if ($autoCloseExpired && $this->isSessionExpired($active)) {
            $this->closeSession($active, 'timeout');
            return null;
        }

        return $active;
    }

    /**
     * Ambil sesi aktif tanpa auto-close.
     */
    protected function getActiveSessionRaw(): ?array
    {
        $active = (new SessionModel())
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->first();

        return $active ?: null;
    }

    /**
     * Tutup sesi aktif jika sudah melewati deadline.
     */
    protected function closeSessionIfExpired(?array $session = null): ?array
    {
        $target = $session ?: $this->getActiveSessionRaw();
        if (!$target || !$this->isSessionExpired($target)) {
            return null;
        }

        return $this->closeSession($target, 'timeout');
    }

    protected function isSessionExpired(array $session): bool
    {
        $deadlineAt = trim((string) ($session['deadline_at'] ?? ''));
        if ($deadlineAt === '') {
            return false;
        }

        $deadlineTs = strtotime($deadlineAt);
        if ($deadlineTs === false) {
            return false;
        }

        return time() >= $deadlineTs;
    }

    /**
     * Hitung info timing sesi untuk UI.
     *
     * @return array{
     *   has_limit:bool,
     *   warning_seconds:int,
     *   duration_limit_minutes:int,
     *   extension_minutes:int,
     *   total_minutes:int,
     *   deadline_at:string,
     *   remaining_seconds:?int,
     *   is_near_limit:bool,
     *   is_expired:bool
     * }
     */
    protected function getSessionTiming(array $session): array
    {
        $deadlineAt = trim((string) ($session['deadline_at'] ?? ''));
        $deadlineTs = $deadlineAt !== '' ? strtotime($deadlineAt) : false;
        $hasLimit = $deadlineAt !== '' && $deadlineTs !== false;

        $remainingSeconds = null;
        if ($hasLimit) {
            $remainingSeconds = max(0, (int) $deadlineTs - time());
        }

        $baseLimit = max(0, (int) ($session['duration_limit_minutes'] ?? 0));
        $extensionMinutes = max(0, (int) ($session['extension_minutes'] ?? 0));
        $totalMinutes = $baseLimit > 0 ? ($baseLimit + $extensionMinutes) : 0;
        $isExpired = $hasLimit ? ($remainingSeconds === 0) : false;

        return [
            'has_limit' => $hasLimit,
            'warning_seconds' => $this->sessionWarningSeconds,
            'duration_limit_minutes' => $baseLimit,
            'extension_minutes' => $extensionMinutes,
            'total_minutes' => $totalMinutes,
            'deadline_at' => $hasLimit ? $deadlineAt : '',
            'remaining_seconds' => $remainingSeconds,
            'is_near_limit' => $hasLimit && $remainingSeconds !== null && $remainingSeconds <= $this->sessionWarningSeconds,
            'is_expired' => $isExpired,
        ];
    }

    /**
     * Tutup sesi (manual/timeout) dan emit event `session_ended`.
     */
    protected function closeSession(array $session, string $reason = 'manual'): ?array
    {
        $sessionId = (int) ($session['id'] ?? 0);
        if ($sessionId <= 0) {
            return null;
        }

        $sessionModel = new SessionModel();
        $fresh = $sessionModel->find($sessionId);
        if (!$fresh) {
            return null;
        }

        if ((int) ($fresh['is_active'] ?? 0) !== 1) {
            return $fresh;
        }

        $nowTs = time();
        $endedAtTs = $nowTs;
        $deadlineAt = trim((string) ($fresh['deadline_at'] ?? ''));
        $deadlineTs = $deadlineAt !== '' ? strtotime($deadlineAt) : false;

        if ($deadlineTs !== false) {
            if ($reason === 'timeout' || $nowTs > $deadlineTs) {
                $endedAtTs = $deadlineTs;
            }
        }

        $endedAt = date('Y-m-d H:i:s', $endedAtTs);

        $sessionModel->update($sessionId, [
            'is_active' => 0,
            'ended_at' => $endedAt,
        ]);

        (new EventModel())->addForAll($sessionId, 'session_ended', [
            'session_id' => $sessionId,
            'ended_at' => $endedAt,
            'reason' => $reason,
        ]);

        $fresh['is_active'] = 0;
        $fresh['ended_at'] = $endedAt;

        return $fresh;
    }
}
