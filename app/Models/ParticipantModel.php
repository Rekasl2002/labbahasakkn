<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantModel extends Model
{
    protected $table = 'participants';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'session_id','student_name','class_name','device_label','ip_address',
        'mic_on','speaker_on','joined_at','last_seen_at','left_at'
    ];
    protected $useTimestamps = false;
}
