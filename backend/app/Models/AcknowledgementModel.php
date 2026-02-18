<?php

namespace App\Models;

use CodeIgniter\Model;

class AcknowledgementModel extends Model
{
    protected $table            = 'acknowledgements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'date_issued',
        'time_issued',
        'name',
        'position',
        'affiliation',
        'issued_by',
        'received_by',
        'remarks'
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[150]'
    ];
}
