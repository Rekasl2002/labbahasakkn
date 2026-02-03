<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialFileModel extends Model
{
    protected $table = 'material_files';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['material_id','filename','mime','size','url_path','created_at'];
    protected $useTimestamps = false;
}
