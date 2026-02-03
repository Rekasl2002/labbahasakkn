<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table = 'messages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'session_id','sender_type','sender_admin_id','sender_participant_id',
        'target_type','target_participant_id','body','created_at'
    ];
    protected $useTimestamps = false;
}
