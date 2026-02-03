<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionStateModel extends Model
{
    protected $table = 'session_state';
    protected $primaryKey = 'session_id';
    protected $returnType = 'array';
    protected $allowedFields = ['session_id','current_material_id','broadcast_text','updated_at'];
    protected $useTimestamps = false;

    public function ensureRow(int $sessionId): void
    {
        $row = $this->find($sessionId);
        if (!$row) {
            $this->insert([
                'session_id' => $sessionId,
                'current_material_id' => null,
                'broadcast_text' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
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
}
