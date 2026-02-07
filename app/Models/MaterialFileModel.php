<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialFileModel extends Model
{
    protected $table = 'material_files';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'material_id',
        'sort_order',
        'filename',
        'mime',
        'size',
        'url_path',
        'preview_url_path',
        'cover_url_path',
        'created_at'
    ];
    protected $useTimestamps = false;

    public function orderedForMaterial(int $materialId)
    {
        $builder = $this->where('material_id', $materialId);
        $db = db_connect();
        if ($db->fieldExists('sort_order', $this->table)) {
            $builder->orderBy('sort_order', 'ASC');
        }
        $builder->orderBy('id', 'ASC');
        return $builder;
    }
}
