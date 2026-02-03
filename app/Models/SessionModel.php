<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionModel extends Model
{
    protected $table = 'sessions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'is_active', 'started_at', 'ended_at', 'created_by_admin_id', 'created_at'];
    protected $useTimestamps = false;
}
