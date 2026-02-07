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

class AdminController extends BaseController
{
    public function dashboard()
    {
        $sessionModel = new SessionModel();
        $active = $sessionModel->where('is_active', 1)->orderBy('id', 'DESC')->first();

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
        ]);
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

        $sessionModel = new SessionModel();

        // matikan sesi lain (kalau ada)
        $sessionModel->where('is_active', 1)->set(['is_active' => 0, 'ended_at' => date('Y-m-d H:i:s')])->update();

        $id = $sessionModel->insert([
            'name' => $name,
            'is_active' => 1,
            'started_at' => date('Y-m-d H:i:s'),
            'created_by_admin_id' => (int) session()->get('admin_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ], true);

        (new SessionStateModel())->ensureRow($id);

        (new EventModel())->addForAll($id, 'session_started', [
            'session_id' => $id,
            'name' => $name,
            'started_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin');
    }

    public function endSession()
    {
        $sessionModel = new SessionModel();
        $active = $sessionModel->where('is_active', 1)->orderBy('id', 'DESC')->first();

        if (!$active) {
            return redirect()->to('/admin')->with('error', 'Tidak ada sesi aktif.');
        }

        $endedAt = date('Y-m-d H:i:s');
        $sessionModel->update($active['id'], [
            'is_active' => 0,
            'ended_at' => $endedAt,
        ]);

        (new EventModel())->addForAll($active['id'], 'session_ended', [
            'session_id' => $active['id'],
            'ended_at' => $endedAt,
        ]);

        // Rekap
        $participantModel = new ParticipantModel();
        $messageModel = new MessageModel();

        $participants = $participantModel->where('session_id', $active['id'])->orderBy('id', 'ASC')->findAll();
        $messagesCount = $messageModel->where('session_id', $active['id'])->countAllResults();

        // hitung materi dipakai dari events
        $db = db_connect();
        $materialsUsed = $db->table('events')
            ->select('JSON_EXTRACT(payload_json, "$.material_id") AS mid')
            ->where('session_id', $active['id'])
            ->where('type', 'material_changed')
            ->groupBy('mid')
            ->countAllResults();

        $durationSec = 0;
        if (!empty($active['started_at'])) {
            $durationSec = max(0, strtotime($endedAt) - strtotime($active['started_at']));
        }

        return view('admin/recap', [
            'session' => array_merge($active, ['ended_at' => $endedAt]),
            'participants' => $participants,
            'messagesCount' => $messagesCount,
            'materialsUsed' => $materialsUsed,
            'durationSec' => $durationSec,
        ]);
    }
}
