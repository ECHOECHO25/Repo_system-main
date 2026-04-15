<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicationAuthorLinkModel extends Model
{
    protected $table            = 'publication_author_links';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'publication_id',
        'faculty_id',
        'author_name',
        'match_type',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
