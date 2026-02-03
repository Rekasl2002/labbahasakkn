<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\MaterialFileModel;
use App\Models\SessionModel;
use App\Models\SessionStateModel;
use App\Models\EventModel;

class MaterialController extends BaseController
{
    public function index()
    {
        $materials = (new MaterialModel())->orderBy('id', 'DESC')->findAll();
        return view('admin/materials/index', ['materials' => $materials]);
    }

    public function create()
    {
        return view('admin/materials/form', ['mode' => 'create', 'material' => null, 'file' => null]);
    }

    public function edit(int $id)
    {
        $material = (new MaterialModel())->find($id);
        $file = (new MaterialFileModel())->where('material_id', $id)->first();
        return view('admin/materials/form', ['mode' => 'edit', 'material' => $material, 'file' => $file]);
    }

    public function store()
    {
        $title = trim((string) $this->request->getPost('title'));
        $type  = (string) $this->request->getPost('type');
        $text  = (string) $this->request->getPost('text_content');

        if ($title === '' || !in_array($type, ['text', 'file'], true)) {
            return redirect()->back()->with('error', 'Title & type wajib.');
        }

        $materialModel = new MaterialModel();
        $id = $materialModel->insert([
            'title' => $title,
            'type' => $type,
            'text_content' => ($type === 'text') ? $text : null,
            'created_by_admin_id' => (int) session()->get('admin_id'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], true);

        if ($type === 'file') {
            $file = $this->request->getFile('file');
            if (!$file || !$file->isValid()) {
                return redirect()->back()->with('error', 'File upload gagal.');
            }
            if ($file->getSize() > 50 * 1024 * 1024) {
                return redirect()->back()->with('error', 'Max file 50MB (MVP).');
            }

            $safeName = $file->getRandomName();
            $targetDir = ROOTPATH . 'public/uploads/materials';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0775, true);
            }
            $file->move($targetDir, $safeName);

            (new MaterialFileModel())->insert([
                'material_id' => $id,
                'filename' => $file->getClientName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'url_path' => '/uploads/materials/' . $safeName,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('/admin/materials')->with('ok', 'Materi dibuat.');
    }

    public function update(int $id)
    {
        $materialModel = new MaterialModel();
        $material = $materialModel->find($id);
        if (!$material) return redirect()->to('/admin/materials')->with('error', 'Materi tidak ditemukan.');

        $title = trim((string) $this->request->getPost('title'));
        $type  = (string) $this->request->getPost('type');
        $text  = (string) $this->request->getPost('text_content');

        if ($title === '' || !in_array($type, ['text', 'file'], true)) {
            return redirect()->back()->with('error', 'Title & type wajib.');
        }

        $materialModel->update($id, [
            'title' => $title,
            'type' => $type,
            'text_content' => ($type === 'text') ? $text : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // optional: upload file baru
        if ($type === 'file') {
            $file = $this->request->getFile('file');
            if ($file && $file->isValid()) {
                if ($file->getSize() > 50 * 1024 * 1024) {
                    return redirect()->back()->with('error', 'Max file 50MB (MVP).');
                }
                $safeName = $file->getRandomName();
                $targetDir = ROOTPATH . 'public/uploads/materials';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0775, true);
                }
                $file->move($targetDir, $safeName);

                $mf = new MaterialFileModel();
                $existing = $mf->where('material_id', $id)->first();
                $payload = [
                    'material_id' => $id,
                    'filename' => $file->getClientName(),
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'url_path' => '/uploads/materials/' . $safeName,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                if ($existing) $mf->update($existing['id'], $payload);
                else $mf->insert($payload);
            }
        }

        return redirect()->to('/admin/materials')->with('ok', 'Materi diupdate.');
    }

    public function delete(int $id)
    {
        (new MaterialModel())->delete($id);
        (new MaterialFileModel())->where('material_id', $id)->delete();
        return redirect()->to('/admin/materials')->with('ok', 'Materi dihapus.');
    }

    public function broadcast(int $id)
    {
        $session = (new SessionModel())->where('is_active', 1)->orderBy('id', 'DESC')->first();
        if (!$session) return redirect()->to('/admin/materials')->with('error', 'Tidak ada sesi aktif.');

        (new SessionStateModel())->setCurrentMaterial($session['id'], $id);

        (new EventModel())->addForAll($session['id'], 'material_changed', [
            'material_id' => $id,
        ]);

        return redirect()->to('/admin')->with('ok', 'Materi dibroadcast.');
    }
}
