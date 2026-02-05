<?php

namespace App\Models;

use CodeIgniter\Model;

class TrackingLogModel extends Model
{
    protected $table            = 'tracking_log';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'action_type',
        'entity_type',
        'entity_id',
        'description',
        'status',
        'updated_by',
        'notes',
        'created_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getRecent($limit = 10)
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit((int)$limit)
            ->findAll();
    }

    public function getActivityStats($days = 30)
    {
        $db = \Config\Database::connect();
        $since = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $byType = $db->table($this->table)
            ->select('action_type, COUNT(*) as count')
            ->where('created_at >=', $since)
            ->groupBy('action_type')
            ->orderBy('count', 'DESC')
            ->get()
            ->getResultArray();

        $byEntity = $db->table($this->table)
            ->select('entity_type, COUNT(*) as count')
            ->where('created_at >=', $since)
            ->groupBy('entity_type')
            ->orderBy('count', 'DESC')
            ->get()
            ->getResultArray();

        $total = $db->table($this->table)
            ->where('created_at >=', $since)
            ->countAllResults();

        return [
            'total' => (int)$total,
            'by_type' => $byType,
            'by_entity' => $byEntity,
            'since' => $since,
        ];
    }
}
