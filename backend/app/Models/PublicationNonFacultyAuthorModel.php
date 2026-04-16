<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicationNonFacultyAuthorModel extends Model
{
    protected $table            = 'publication_non_faculty_authors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'publication_id',
        'author_name',
        'author_type',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

