<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BaseController extends Controller
{
    protected $helpers = ['url', 'form'];

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
}
