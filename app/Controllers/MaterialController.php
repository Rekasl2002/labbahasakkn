<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\MaterialFileModel;
use App\Models\SessionStateModel;
use App\Models\EventModel;

class MaterialController extends BaseController
{
    public function index()
    {
        return redirect()->to('/admin/settings?tab=materials&mat=list' . $this->embedQuery());
    }

    public function create()
    {
        return redirect()->to('/admin/settings?tab=materials&mat=add' . $this->embedQuery());
    }

    public function edit(int $id)
    {
        return redirect()->to('/admin/settings?tab=materials&mat=edit&edit_id=' . $id . $this->embedQuery());
    }

    public function store()
    {
        $title = trim((string) $this->request->getPost('title'));
        $type  = (string) $this->request->getPost('type');
        $text  = (string) $this->request->getPost('text_content');
        $textItems = (string) $this->request->getPost('text_items');

        if ($title === '' || !in_array($type, ['text', 'file', 'folder'], true)) {
            return redirect()->back()->with('error', 'Title & type wajib.');
        }

        $materialModel = new MaterialModel();
        $id = $materialModel->insert([
            'title' => $title,
            'type' => $type,
            'text_content' => ($type === 'text') ? $text : (($type === 'folder') ? $textItems : null),
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

            $this->storeMaterialFile($id, $file, 1);
        }

        if ($type === 'folder') {
            $files = $this->request->getFileMultiple('files');
            if (is_array($files)) {
                $nextOrder = $this->getNextSortOrder($id);
                foreach ($files as $file) {
                    if (!$file || !$file->isValid()) continue;
                    if ($file->getSize() > 50 * 1024 * 1024) {
                        return redirect()->back()->with('error', 'Max file 50MB (MVP).');
                    }
                }
                foreach ($files as $file) {
                    if (!$file || !$file->isValid()) continue;
                    $this->storeMaterialFile($id, $file, $nextOrder);
                    $nextOrder++;
                }
            }
        }

        return redirect()->to('/admin/settings?tab=materials&mat=list' . $this->embedQuery())
            ->with('ok', 'Materi berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        $materialModel = new MaterialModel();
        $material = $materialModel->find($id);
        if (!$material) {
            return redirect()->to('/admin/settings?tab=materials&mat=list' . $this->embedQuery())
                ->with('error', 'Materi tidak ditemukan.');
        }

        $title = trim((string) $this->request->getPost('title'));
        $type  = (string) $this->request->getPost('type');
        $text  = (string) $this->request->getPost('text_content');
        $textItems = (string) $this->request->getPost('text_items');

        if ($title === '' || !in_array($type, ['text', 'file', 'folder'], true)) {
            return redirect()->back()->with('error', 'Title & type wajib.');
        }

        $materialModel->update($id, [
            'title' => $title,
            'type' => $type,
            'text_content' => ($type === 'text') ? $text : (($type === 'folder') ? $textItems : null),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($type === 'text') {
            $this->deleteAllMaterialFiles($id);
            return redirect()->to('/admin/settings?tab=materials&mat=list' . $this->embedQuery())
                ->with('ok', 'Materi berhasil diedit.');
        }

        // optional: upload file baru (single)
        if ($type === 'file') {
            $deleteFile = (int) $this->request->getPost('delete_file') === 1;
            if ($deleteFile) {
                $this->deleteAllMaterialFiles($id);
            }
            $file = $this->request->getFile('file');
            if ($file && $file->isValid()) {
                if ($file->getSize() > 50 * 1024 * 1024) {
                    return redirect()->back()->with('error', 'Max file 50MB (MVP).');
                }
                $this->deleteAllMaterialFiles($id);
                $this->storeMaterialFile($id, $file, 1);
            }
            // Pastikan hanya 1 file untuk type "file"
            $mf = new MaterialFileModel();
            $existing = $mf->orderedForMaterial($id)->findAll();
            if (count($existing) > 1) {
                $keep = array_shift($existing);
                foreach ($existing as $row) {
                    $this->deleteMaterialFileRow($row);
                }
                $mf->where('material_id', $id)->where('id !=', $keep['id'])->delete();
            }
        }

        if ($type === 'folder') {
            $deleteIds = $this->request->getPost('delete_files');
            if (is_array($deleteIds) && !empty($deleteIds)) {
                $this->deleteMaterialFilesByIds($id, $deleteIds);
            }

            $orderIds = $this->request->getPost('file_order');
            if (is_array($orderIds) && !empty($orderIds)) {
                $this->applyFileOrder($id, $orderIds);
            }

            $files = $this->request->getFileMultiple('files');
            if (is_array($files)) {
                $nextOrder = $this->getNextSortOrder($id);
                foreach ($files as $file) {
                    if (!$file || !$file->isValid()) continue;
                    if ($file->getSize() > 50 * 1024 * 1024) {
                        return redirect()->back()->with('error', 'Max file 50MB (MVP).');
                    }
                }
                foreach ($files as $file) {
                    if (!$file || !$file->isValid()) continue;
                    $this->storeMaterialFile($id, $file, $nextOrder);
                    $nextOrder++;
                }
            }
        }

        return redirect()->to('/admin/settings?tab=materials&mat=list' . $this->embedQuery())
            ->with('ok', 'Materi berhasil diedit.');
    }

    public function delete(int $id)
    {
        $this->deleteAllMaterialFiles($id);
        (new MaterialModel())->delete($id);
        return redirect()->to('/admin/settings?tab=materials&mat=list' . $this->embedQuery())
            ->with('ok', 'Materi dihapus.');
    }

    public function broadcast(int $id)
    {
        $session = $this->getActiveSession();
        if (!$session) {
            return redirect()->to('/admin/settings?tab=materials&mat=list' . $this->embedQuery())
                ->with('error', 'Tidak ada sesi aktif.');
        }

        $stateModel = new SessionStateModel();
        $stateModel->setCurrentMaterial($session['id'], $id);
        // Saat materi dibroadcast, jangan langsung menampilkan item ke siswa.
        // Guru harus menekan tombol "Tampilkan" dulu dari panel materi.
        $stateModel->setCurrentMaterialItem($session['id'], null, null);

        (new EventModel())->addForAll($session['id'], 'material_changed', [
            'material_id' => $id,
        ]);

        if ($this->embedQuery() !== '') {
            return redirect()->to('/admin/settings?tab=materials&mat=list&embed=1')->with('ok', 'Materi dibroadcast.');
        }
        return redirect()->to('/admin')->with('ok', 'Materi dibroadcast.');
    }

    private function embedQuery(): string
    {
        $embed = (string) $this->request->getGet('embed') === '1'
            || (string) $this->request->getPost('embed') === '1';
        return $embed ? '&embed=1' : '';
    }

    private function storeMaterialFile(int $materialId, $file, ?int $sortOrder = null): void
    {
        $safeName = $file->getRandomName();
        $targetDir = ROOTPATH . 'public/uploads/materials';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }
        $file->move($targetDir, $safeName);

        $db = db_connect();
        $hasSort = $db->fieldExists('sort_order', 'material_files');
        $hasPreview = $db->fieldExists('preview_url_path', 'material_files');
        $hasCover = $db->fieldExists('cover_url_path', 'material_files');

        $mime = $file->getClientMimeType();
        $previewUrl = $this->maybeConvertOfficeToPdf($targetDir . DIRECTORY_SEPARATOR . $safeName, $safeName);
        $coverUrl = $hasCover
            ? $this->maybeExtractMediaCover($targetDir . DIRECTORY_SEPARATOR . $safeName, $safeName, $mime)
            : null;
        $finalSort = $hasSort ? ($sortOrder ?? $this->getNextSortOrder($materialId)) : null;

        $data = [
            'material_id' => $materialId,
            'filename' => $file->getClientName(),
            'mime' => $mime,
            'size' => $file->getSize(),
            'url_path' => '/uploads/materials/' . $safeName,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if ($hasSort) {
            $data['sort_order'] = $finalSort;
        }
        if ($hasPreview) {
            $data['preview_url_path'] = $previewUrl;
        }
        if ($hasCover) {
            $data['cover_url_path'] = $coverUrl;
        }

        (new MaterialFileModel())->insert($data);
    }

    private function getNextSortOrder(int $materialId): int
    {
        $db = db_connect();
        if (!$db->fieldExists('sort_order', 'material_files')) {
            return 1;
        }
        $mf = new MaterialFileModel();
        $row = $mf->selectMax('sort_order', 'max_sort')->where('material_id', $materialId)->first();
        $max = isset($row['max_sort']) ? (int) $row['max_sort'] : 0;
        return $max + 1;
    }

    private function deleteAllMaterialFiles(int $materialId): void
    {
        $mf = new MaterialFileModel();
        $files = $mf->where('material_id', $materialId)->findAll();
        foreach ($files as $row) {
            $this->deleteMaterialFileRow($row);
        }
        $mf->where('material_id', $materialId)->delete();
    }

    private function deleteMaterialFilesByIds(int $materialId, array $ids): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids), static fn($v) => $v > 0));
        if (empty($ids)) return;

        $mf = new MaterialFileModel();
        $files = $mf->where('material_id', $materialId)->whereIn('id', $ids)->findAll();
        foreach ($files as $row) {
            $this->deleteMaterialFileRow($row);
        }
        $mf->where('material_id', $materialId)->whereIn('id', $ids)->delete();
    }

    private function deleteMaterialFileRow(array $row): void
    {
        $urlPath = (string) ($row['url_path'] ?? '');
        if ($urlPath === '' || !str_starts_with($urlPath, '/uploads/materials/')) {
            return;
        }
        $path = ROOTPATH . 'public' . $urlPath;
        if (is_file($path)) {
            @unlink($path);
        }

        $preview = (string) ($row['preview_url_path'] ?? '');
        if ($preview !== '' && str_starts_with($preview, '/uploads/materials/')) {
            $previewPath = ROOTPATH . 'public' . $preview;
            if (is_file($previewPath)) {
                @unlink($previewPath);
            }
        }

        $cover = (string) ($row['cover_url_path'] ?? '');
        if ($cover !== '' && str_starts_with($cover, '/uploads/materials/')) {
            $coverPath = ROOTPATH . 'public' . $cover;
            if (is_file($coverPath)) {
                @unlink($coverPath);
            }
        }
    }

    private function applyFileOrder(int $materialId, array $orderIds): void
    {
        $db = db_connect();
        if (!$db->fieldExists('sort_order', 'material_files')) {
            return;
        }

        $ids = array_values(array_filter(array_map('intval', $orderIds), static fn($v) => $v > 0));
        if (empty($ids)) return;

        $mf = new MaterialFileModel();
        $existing = $mf->where('material_id', $materialId)->findAll();
        $existingIds = array_column($existing, 'id');

        $pos = 1;
        foreach ($ids as $id) {
            if (!in_array($id, $existingIds, true)) continue;
            $mf->update($id, ['sort_order' => $pos]);
            $pos++;
        }
    }

    private function maybeConvertOfficeToPdf(string $filePath, string $safeName): ?string
    {
        $ext = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
        $officeExt = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        if (!in_array($ext, $officeExt, true)) return null;

        $bin = (string) getenv('SOFFICE_BIN');
        if ($bin === '') {
            $bin = 'soffice';
            $win = 'C:\\Program Files\\LibreOffice\\program\\soffice.exe';
            if (is_file($win)) $bin = $win;
        }

        $outDir = dirname($filePath);
        $cmd = '"' . $bin . '" --headless --convert-to pdf --outdir ' . escapeshellarg($outDir) . ' ' . escapeshellarg($filePath);
        $output = [];
        $code = 0;
        @exec($cmd . ' 2>&1', $output, $code);
        if ($code !== 0) return null;

        $base = pathinfo($safeName, PATHINFO_FILENAME);
        $pdfName = $base . '.pdf';
        $pdfPath = $outDir . DIRECTORY_SEPARATOR . $pdfName;
        if (!is_file($pdfPath)) return null;

        return '/uploads/materials/' . $pdfName;
    }

    private function maybeExtractMediaCover(string $filePath, string $safeName, string $mime): ?string
    {
        $mime = strtolower($mime);
        if (!str_starts_with($mime, 'audio/') && !str_starts_with($mime, 'video/')) {
            return null;
        }

        $bin = (string) getenv('FFMPEG_BIN');
        if ($bin === '') {
            $bin = 'ffmpeg';
            $candidates = [
                'C:\\ffmpeg\\bin\\ffmpeg.exe',
                'C:\\Program Files\\ffmpeg\\bin\\ffmpeg.exe',
                'C:\\Program Files\\FFmpeg\\bin\\ffmpeg.exe',
            ];
            foreach ($candidates as $candidate) {
                if (is_file($candidate)) {
                    $bin = $candidate;
                    break;
                }
            }
        }

        $coverDir = ROOTPATH . 'public/uploads/materials/covers';
        if (!is_dir($coverDir)) {
            @mkdir($coverDir, 0775, true);
        }

        $base = pathinfo($safeName, PATHINFO_FILENAME);
        $coverName = $base . '_cover.jpg';
        $coverPath = $coverDir . DIRECTORY_SEPARATOR . $coverName;

        $cmd = '"' . $bin . '" -y -i ' . escapeshellarg($filePath) . ' -an -frames:v 1 -q:v 2 ' . escapeshellarg($coverPath);
        $output = [];
        $code = 0;
        @exec($cmd . ' 2>&1', $output, $code);
        if ($code !== 0 || !is_file($coverPath)) {
            return null;
        }

        if (filesize($coverPath) <= 0) {
            @unlink($coverPath);
            return null;
        }

        return '/uploads/materials/covers/' . $coverName;
    }
}
