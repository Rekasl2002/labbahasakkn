<?php

namespace App\Controllers;

use App\Models\SessionModel;
use App\Models\SessionStateModel;
use App\Models\ParticipantModel;
use App\Models\MessageModel;
use App\Models\EventModel;
use App\Models\AdminModel;
use App\Models\MaterialModel;
use App\Models\MaterialFileModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class AdminController extends BaseController
{
    public function dashboard()
    {
        $sessionModel = new SessionModel();
        $active = $this->getActiveSession();
        $sessionHistory = $sessionModel
            ->where('started_at IS NOT NULL', null, false)
            ->orderBy('id', 'DESC')
            ->limit(30)
            ->findAll();

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
            'sessionTiming' => $active ? $this->getSessionTiming($active) : null,
            'sessionHistory' => $sessionHistory,
        ]);
    }

    public function recap(int $sessionId = 0)
    {
        $session = $this->findSession($sessionId);
        if (!$session) {
            return redirect()->to('/admin')->with('error', 'Sesi tidak valid atau tidak ditemukan.');
        }

        return view('admin/recap', $this->buildRecapData($session));
    }

    public function exportRecapExcel(int $sessionId = 0)
    {
        $session = $this->findSession($sessionId);
        if (!$session) {
            return redirect()->to('/admin')->with('error', 'Sesi tidak valid atau tidak ditemukan.');
        }

        $data = $this->buildRecapData($session);
        $filename = $this->reportBaseFilename($session) . '.xls';
        $html = view('admin/reports/recap_excel', [
            'session' => $data['session'],
            'participants' => $data['participants'],
            'messagesCount' => $data['messagesCount'],
            'materialsUsed' => $data['materialsUsed'],
            'durationSec' => $data['durationSec'],
            'generatedAt' => date('Y-m-d H:i:s'),
            'durationText' => $this->durationText((int) $data['durationSec']),
            'limitText' => $this->sessionLimitText($data['session']),
        ]);

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setBody("\xEF\xBB\xBF" . $html);
    }

    public function exportRecapPdf(int $sessionId = 0)
    {
        $session = $this->findSession($sessionId);
        if (!$session) {
            return redirect()->to('/admin')->with('error', 'Sesi tidak valid atau tidak ditemukan.');
        }

        try {
            $data = $this->buildRecapData($session);
            $html = view('admin/reports/recap_pdf', [
                'session' => $data['session'],
                'participants' => $data['participants'],
                'messagesCount' => $data['messagesCount'],
                'materialsUsed' => $data['materialsUsed'],
                'durationSec' => $data['durationSec'],
                'generatedAt' => date('Y-m-d H:i:s'),
                'durationText' => $this->durationText((int) $data['durationSec']),
                'limitText' => $this->sessionLimitText($data['session']),
            ]);

            if (!class_exists(Dompdf::class)) {
                throw new \RuntimeException('Library dompdf/dompdf tidak tersedia di server.');
            }

            $options = $this->buildPdfOptions();

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $pdfBytes = $dompdf->output();
            $filename = $this->reportBaseFilename($session) . '.pdf';

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->setBody($pdfBytes);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal membuat PDF rekap sesi {sessionId}: {message}', [
                'sessionId' => (string) $sessionId,
                'message' => $e->getMessage(),
            ]);

            $errorMessage = 'Gagal membuat PDF rekap sesi.';
            if (ENVIRONMENT !== 'production') {
                $errorMessage .= ' ' . $e->getMessage();
            }

            return redirect()->to('/admin/session/' . (int) $sessionId . '/recap')
                ->with('error', $errorMessage);
        }
    }

    public function settings()
    {
        helper('settings');
        $settings = lab_load_settings();

        $tab = (string) $this->request->getGet('tab');
        $tab = $tab !== '' ? $tab : 'auto-detect';
        $allowedTabs = ['auto-detect', 'password', 'materials'];
        $embed = (string) $this->request->getGet('embed') === '1';

        $editId = (int) $this->request->getGet('edit_id');
        $materialsTab = (string) $this->request->getGet('mat');
        $allowedMaterialsTabs = ['list', 'add', 'edit'];
        if (!in_array($materialsTab, $allowedMaterialsTabs, true)) {
            $materialsTab = '';
        }
        if ($editId > 0) {
            $tab = 'materials';
            $materialsTab = 'edit';
        }
        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'auto-detect';
        }
        if ($tab === 'materials' && $materialsTab === '') {
            $materialsTab = 'list';
        }

        $material = null;
        $files = [];
        $file = null;
        $mode = 'create';
        $materials = [];

        if ($tab === 'materials') {
            $materials = (new MaterialModel())->orderBy('id', 'DESC')->findAll();
            if ($materialsTab === 'edit') {
                if ($editId <= 0) {
                    $embedQuery = $embed ? '&embed=1' : '';
                    return redirect()->to('/admin/settings?tab=materials&mat=list' . $embedQuery)
                        ->with('error', 'Materi tidak ditemukan.');
                }
                $material = (new MaterialModel())->find($editId);
                if (!$material) {
                    $embedQuery = $embed ? '&embed=1' : '';
                    return redirect()->to('/admin/settings?tab=materials&mat=list' . $embedQuery)
                        ->with('error', 'Materi tidak ditemukan.');
                }
                $mode = 'edit';
                $files = (new MaterialFileModel())
                    ->orderedForMaterial($editId)
                    ->findAll();
                $file = $files[0] ?? null;
            } else {
                $mode = 'create';
            }
        }

        return view($embed ? 'admin/settings/embed' : 'admin/settings/index', [
            'settings' => $settings,
            'materials' => $materials,
            'mode' => $mode,
            'material' => $material,
            'file' => $file,
            'files' => $files,
            'tab' => $tab,
            'materialsTab' => $materialsTab,
            'embed' => $embed,
        ]);
    }

    public function saveSettings()
    {
        helper('settings');

        $ipStart = $this->postString('ip_range_start', 60);
        $ipEnd = $this->postString('ip_range_end', 60);
        $labelFormat = $this->postString('label_format', 80);
        $labelList = $this->postString('label_list', 3000);
        $labelList = preg_replace("/\r\n?/", "\n", trim($labelList));

        $errors = [];
        if ($ipStart !== '' || $ipEnd !== '') {
            if ($ipStart === '' || $ipEnd === '') {
                $errors[] = 'IP awal dan IP akhir wajib diisi.';
            }
            if ($ipStart !== '' && filter_var($ipStart, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
                $errors[] = 'IP awal tidak valid.';
            }
            if ($ipEnd !== '' && filter_var($ipEnd, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
                $errors[] = 'IP akhir tidak valid.';
            }
        }

        if ($labelFormat === '' && $labelList === '') {
            $labelFormat = 'Komputer {n}';
        }

        if (!empty($errors)) {
            return redirect()->back()->with('error', implode(' ', $errors));
        }

        $ok = lab_save_settings([
            'ip_range_start' => $ipStart,
            'ip_range_end' => $ipEnd,
            'label_format' => $labelFormat,
            'label_list' => $labelList,
        ]);

        if (!$ok) {
            return redirect()->back()->with('error', 'Gagal menyimpan pengaturan.');
        }

        $embed = (string) $this->request->getPost('embed') === '1' || (string) $this->request->getGet('embed') === '1';
        $target = '/admin/settings?tab=auto-detect' . ($embed ? '&embed=1' : '');
        return redirect()->to($target)->with('ok', 'Pengaturan disimpan.');
    }

    public function updatePassword()
    {
        $current = (string) $this->request->getPost('current_password');
        $new = (string) $this->request->getPost('new_password');
        $confirm = (string) $this->request->getPost('confirm_password');

        if ($current === '' || $new === '' || $confirm === '') {
            return redirect()->back()->with('error', 'Semua field password wajib diisi.');
        }

        if ($new !== $confirm) {
            return redirect()->back()->with('error', 'Konfirmasi password tidak cocok.');
        }

        if (strlen($new) < 6) {
            return redirect()->back()->with('error', 'Password baru minimal 6 karakter.');
        }

        $adminId = (int) session()->get('admin_id');
        $admin = (new AdminModel())->find($adminId);
        if (!$admin || !password_verify($current, $admin['password_hash'])) {
            return redirect()->back()->with('error', 'Password sekarang salah.');
        }

        $hash = password_hash($new, PASSWORD_DEFAULT);
        if (!$hash) {
            return redirect()->back()->with('error', 'Gagal memproses password.');
        }

        (new AdminModel())->update($adminId, [
            'password_hash' => $hash,
        ]);

        $embed = (string) $this->request->getPost('embed') === '1' || (string) $this->request->getGet('embed') === '1';
        $target = '/admin/settings?tab=password' . ($embed ? '&embed=1' : '');
        return redirect()->to($target)->with('ok', 'Password admin berhasil diubah.');
    }

    public function startSession()
    {
        $name = trim((string) $this->request->getPost('name'));
        $name = $name ?: ('Sesi ' . date('Y-m-d H:i'));
        $durationMinutes = (int) $this->request->getPost('duration_minutes');
        if ($durationMinutes <= 0) {
            $durationMinutes = 90;
        }
        $durationMinutes = max(15, min(1440, $durationMinutes));

        $sessionModel = new SessionModel();
        $startedAt = date('Y-m-d H:i:s');
        $deadlineAt = date('Y-m-d H:i:s', strtotime($startedAt . ' +' . $durationMinutes . ' minutes'));

        // matikan sesi lain (kalau ada)
        $active = $this->getActiveSessionRaw();
        if ($active) {
            $this->closeSession($active, 'manual');
        }

        $id = $sessionModel->insert([
            'name' => $name,
            'is_active' => 1,
            'started_at' => $startedAt,
            'duration_limit_minutes' => $durationMinutes,
            'deadline_at' => $deadlineAt,
            'extension_minutes' => 0,
            'created_by_admin_id' => (int) session()->get('admin_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ], true);

        (new SessionStateModel())->ensureRow($id);

        (new EventModel())->addForAll($id, 'session_started', [
            'session_id' => $id,
            'name' => $name,
            'started_at' => $startedAt,
            'duration_limit_minutes' => $durationMinutes,
            'deadline_at' => $deadlineAt,
        ]);

        return redirect()->to('/admin');
    }

    public function extendSession()
    {
        $active = $this->getActiveSession();
        if (!$active) {
            return redirect()->to('/admin')->with('error', 'Tidak ada sesi aktif.');
        }

        $deadlineAt = trim((string) ($active['deadline_at'] ?? ''));
        if ($deadlineAt === '') {
            return redirect()->to('/admin')->with('error', 'Sesi ini tidak memiliki batas waktu.');
        }

        $deadlineTs = strtotime($deadlineAt);
        if ($deadlineTs === false) {
            return redirect()->to('/admin')->with('error', 'Deadline sesi tidak valid.');
        }

        $newDeadline = date('Y-m-d H:i:s', $deadlineTs + (30 * 60));
        $extensionMinutes = max(0, (int) ($active['extension_minutes'] ?? 0)) + 30;

        (new SessionModel())->update((int) $active['id'], [
            'deadline_at' => $newDeadline,
            'extension_minutes' => $extensionMinutes,
        ]);

        (new EventModel())->addForAll((int) $active['id'], 'session_extended', [
            'session_id' => (int) $active['id'],
            'deadline_at' => $newDeadline,
            'extension_minutes' => $extensionMinutes,
            'added_minutes' => 30,
        ]);

        return redirect()->to('/admin');
    }

    public function endSession()
    {
        $active = $this->getActiveSessionRaw();

        if (!$active) {
            return redirect()->to('/admin')->with('error', 'Tidak ada sesi aktif.');
        }

        $this->closeSession($active, 'manual');

        return $this->recap((int) $active['id']);
    }

    private function buildRecapData(array $session): array
    {
        $sessionId = (int) ($session['id'] ?? 0);

        $participants = (new ParticipantModel())
            ->where('session_id', $sessionId)
            ->orderBy('id', 'ASC')
            ->findAll();

        $messagesCount = (new MessageModel())
            ->where('session_id', $sessionId)
            ->countAllResults();

        $events = (new EventModel())
            ->select('payload_json')
            ->where('session_id', $sessionId)
            ->where('type', 'material_changed')
            ->findAll();

        $materialIds = [];
        foreach ($events as $event) {
            $payloadJson = (string) ($event['payload_json'] ?? '');
            if ($payloadJson === '') {
                continue;
            }

            $decoded = json_decode($payloadJson, true);
            if (!is_array($decoded)) {
                continue;
            }

            $materialId = (int) ($decoded['material_id'] ?? 0);
            if ($materialId > 0) {
                $materialIds[$materialId] = true;
            }
        }

        $durationSec = 0;
        $startedAt = (string) ($session['started_at'] ?? '');
        $endedAt = (string) ($session['ended_at'] ?? '');
        $deadlineAt = (string) ($session['deadline_at'] ?? '');
        if ($startedAt !== '') {
            $durationEnd = $endedAt !== '' ? $endedAt : date('Y-m-d H:i:s');

            $deadlineTs = $deadlineAt !== '' ? strtotime($deadlineAt) : false;
            $durationEndTs = strtotime($durationEnd);
            if ($deadlineTs !== false && $durationEndTs !== false && $durationEndTs > $deadlineTs) {
                $durationEnd = date('Y-m-d H:i:s', $deadlineTs);
            }

            $startedTs = strtotime($startedAt);
            $endedTs = strtotime($durationEnd);
            if ($startedTs !== false && $endedTs !== false) {
                $durationSec = max(0, $endedTs - $startedTs);
            }
        }

        return [
            'session' => $session,
            'participants' => $participants,
            'messagesCount' => $messagesCount,
            'materialsUsed' => count($materialIds),
            'durationSec' => $durationSec,
        ];
    }

    private function findSession(int $sessionId): ?array
    {
        if ($sessionId <= 0) {
            return null;
        }

        $session = (new SessionModel())->find($sessionId);
        return $session ?: null;
    }

    private function durationText(int $durationSec): string
    {
        $durationSec = max(0, $durationSec);
        $minute = (int) floor($durationSec / 60);
        $second = $durationSec % 60;
        return $minute . ' menit ' . $second . ' detik';
    }

    private function sessionLimitText(array $session): string
    {
        $durationLimitMinutes = (int) ($session['duration_limit_minutes'] ?? 0);
        $extensionMinutes = (int) ($session['extension_minutes'] ?? 0);
        if ($durationLimitMinutes <= 0) {
            return '-';
        }

        $out = $durationLimitMinutes . ' menit';
        if ($extensionMinutes > 0) {
            $out .= ' (+' . $extensionMinutes . ' menit)';
        }

        return $out;
    }

    private function reportBaseFilename(array $session): string
    {
        $sessionId = (int) ($session['id'] ?? 0);
        $rawName = trim((string) ($session['name'] ?? ''));
        $rawName = $rawName !== '' ? $rawName : 'sesi';

        $slug = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $rawName));
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = 'sesi';
        }

        return 'rekap-sesi-' . $sessionId . '-' . $slug;
    }

    private function buildPdfOptions(): Options
    {
        $separator = DIRECTORY_SEPARATOR;
        $cacheDir = rtrim(WRITEPATH, '\\/') . $separator . 'cache' . $separator . 'dompdf';
        $fontDir = rtrim(WRITEPATH, '\\/') . $separator . 'fonts' . $separator . 'dompdf';

        $this->ensureWritableDir($cacheDir);
        $this->ensureWritableDir($fontDir);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('defaultFont', 'Helvetica');
        $options->set('tempDir', $cacheDir);
        $options->set('fontDir', $fontDir);
        $options->set('fontCache', $fontDir);

        $chroot = realpath(FCPATH . '..');
        if (is_string($chroot) && $chroot !== '') {
            $options->set('chroot', $chroot);
        }

        return $options;
    }

    private function ensureWritableDir(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
            throw new \RuntimeException('Tidak bisa membuat direktori writable: ' . $path);
        }

        if (!is_writable($path)) {
            throw new \RuntimeException('Direktori tidak writable: ' . $path);
        }
    }
}
