<?php

namespace App\Models;

use CodeIgniter\Model;

class AcknowledgementItemModel extends Model
{
    protected $table            = 'acknowledgement_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'acknowledgement_id',
        'volume',
        'copies',
        'remark'
    ];
    protected $useTimestamps = true;
}
