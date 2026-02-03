<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['session_id','audience','type','payload_json','created_at'];
    protected $useTimestamps = false;

    public function add(int $sessionId, string $audience, string $type, array $payload): int
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return (int) $this->insert([
            'session_id' => $sessionId,
            'audience' => $audience,
            'type' => $type,
            'payload_json' => $json,
            'created_at' => date('Y-m-d H:i:s'),
        ], true);
    }

    public function addForAll(int $sessionId, string $type, array $payload): int
    {
        return $this->add($sessionId, 'all', $type, $payload);
    }

    public function addForAdmin(int $sessionId, string $type, array $payload): int
    {
        return $this->add($sessionId, 'admin', $type, $payload);
    }

    public function addForParticipant(int $sessionId, int $participantId, string $type, array $payload): int
    {
        return $this->add($sessionId, 'participant:' . $participantId, $type, $payload);
    }

    /**
     * @return array<int, array{id:int,type:string,payload:array,created_at:string,audience:string}>
     */
    public function poll(int $sessionId, int $sinceId, bool $isAdmin, int $participantId): array
    {
        $db = db_connect();
        $b = $db->table($this->table)
            ->select('id,audience,type,payload_json,created_at')
            ->where('session_id', $sessionId)
            ->where('id >', $sinceId);

        if (!$isAdmin) {
            $audiences = ['all', 'participant:' . $participantId];
            $b->whereIn('audience', $audiences);
        }

        $rows = $b->orderBy('id', 'ASC')->limit(800)->get()->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $payload = [];
            if (!empty($r['payload_json'])) {
                $decoded = json_decode($r['payload_json'], true);
                if (is_array($decoded)) $payload = $decoded;
            }
            $out[] = [
                'id' => (int)$r['id'],
                'audience' => $r['audience'],
                'type' => $r['type'],
                'payload' => $payload,
                'created_at' => $r['created_at'],
            ];
        }
        return $out;
    }
}
