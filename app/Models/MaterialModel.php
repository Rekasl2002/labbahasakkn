<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialModel extends Model
{
    protected $table = 'materials';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['title','type','text_content','created_by_admin_id','created_at','updated_at'];
    protected $useTimestamps = false;
}
