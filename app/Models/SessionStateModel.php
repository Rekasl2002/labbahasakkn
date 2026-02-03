<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionStateModel extends Model
{
    protected $table = 'session_state';
    protected $primaryKey = 'session_id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'session_id',
        'current_material_id',
        'broadcast_text',
        'allow_student_mic',
        'allow_student_speaker',
        'updated_at'
    ];
    protected $useTimestamps = false;

    /**
     * Pastikan kolom voice lock ada (fallback jika migrasi belum dijalankan).
     * Aman dipanggil berkali-kali.
     */
    private function ensureVoiceColumns(): void
    {
        $db = db_connect();
        $table = $this->table;

        $hasMic = $db->fieldExists('allow_student_mic', $table);
        $hasSpk = $db->fieldExists('allow_student_speaker', $table);

        if (!$hasMic) {
            try {
                $db->query("ALTER TABLE `{$table}` ADD `allow_student_mic` TINYINT(1) NOT NULL DEFAULT 1 AFTER `broadcast_text`");
            } catch (\Throwable $e) {
                // biarkan caller yang memutuskan, jangan hard-fail di sini
            }
            $hasMic = $db->fieldExists('allow_student_mic', $table);
        }

        if (!$hasSpk) {
            try {
                $db->query("ALTER TABLE `{$table}` ADD `allow_student_speaker` TINYINT(1) NOT NULL DEFAULT 1 AFTER `allow_student_mic`");
            } catch (\Throwable $e) {
                // biarkan caller yang memutuskan, jangan hard-fail di sini
            }
            $hasSpk = $db->fieldExists('allow_student_speaker', $table);
        }

        if (!$hasMic || !$hasSpk) {
            throw new \RuntimeException('Kolom voice lock belum tersedia. Jalankan migrasi terbaru.');
        }
    }

    public function ensureRow(int $sessionId): void
    {
        $row = $this->find($sessionId);
        if (!$row) {
            $db = db_connect();
            $data = [
                'session_id' => $sessionId,
                'current_material_id' => null,
                'broadcast_text' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($db->fieldExists('allow_student_mic', $this->table)) {
                $data['allow_student_mic'] = 1;
            }
            if ($db->fieldExists('allow_student_speaker', $this->table)) {
                $data['allow_student_speaker'] = 1;
            }

            $this->insert($data);
        }
    }

    public function setCurrentMaterial(int $sessionId, int $materialId): void
    {
        $this->ensureRow($sessionId);
        $this->update($sessionId, [
            'current_material_id' => $materialId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function setBroadcastText(int $sessionId, string $text): void
    {
        $this->ensureRow($sessionId);
        $this->update($sessionId, [
            'broadcast_text' => $text ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function setVoiceLocks(int $sessionId, array $data): void
    {
        if (empty($data)) return;
        $this->ensureVoiceColumns();
        $this->ensureRow($sessionId);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->update($sessionId, $data);
    }
}
