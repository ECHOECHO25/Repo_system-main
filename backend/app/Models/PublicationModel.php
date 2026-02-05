<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicationModel extends Model
{
    protected $table            = 'publications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'year',
        'college_institute',
        'title',
        'authors',
        'publication_type',
        'journal_book',
        'category',
        'keywords',
        'url',
        'citations',
        'remarks',
        'entry_by',
        'claim_for_incentive',
        'ors_registration',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
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

    public function getStatistics()
    {
        $db = \Config\Database::connect();
        $table = $db->table($this->table);

        $totalPublications = $table->countAllResults(false);

        $totalCitations = $db->table($this->table)
            ->selectSum('citations')
            ->get()
            ->getRow()
            ->citations ?? 0;

        $byYear = $db->table($this->table)
            ->select('year, COUNT(*) as count')
            ->groupBy('year')
            ->orderBy('year', 'DESC')
            ->get()
            ->getResultArray();

        $byType = $db->table($this->table)
            ->select('publication_type, COUNT(*) as count')
            ->groupBy('publication_type')
            ->orderBy('count', 'DESC')
            ->get()
            ->getResultArray();

        $byCollege = $this->countByCollege();

        return [
            'total_publications' => (int)$totalPublications,
            'total_citations' => (int)$totalCitations,
            'by_year' => $byYear,
            'by_type' => $byType,
            'by_college' => $byCollege,
        ];
    }

    public function getByYear($year)
    {
        return $this->where('year', $year)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getRecent($limit = 10)
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit((int)$limit)
            ->findAll();
    }

    public function getCollegeTrends(array $years)
    {
        $db = \Config\Database::connect();

        $rows = $db->table($this->table)
            ->select('year, college_institute')
            ->whereIn('year', $years)
            ->get()
            ->getResultArray();

        $counts = [];
        foreach ($rows as $row) {
            $year = (int)$row['year'];
            $colleges = $this->splitCollegeInstitutes($row['college_institute'] ?? '');
            foreach ($colleges as $college) {
                if (!isset($counts[$year])) {
                    $counts[$year] = [];
                }
                $counts[$year][$college] = ($counts[$year][$college] ?? 0) + 1;
            }
        }

        $result = [];
        ksort($counts);
        foreach ($counts as $year => $items) {
            ksort($items);
            foreach ($items as $college => $count) {
                $result[] = [
                    'year' => $year,
                    'college_institute' => $college,
                    'count' => $count,
                ];
            }
        }

        return $result;
    }

    private function countByCollege(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table($this->table)
            ->select('college_institute')
            ->get()
            ->getResultArray();

        $counts = [];
        foreach ($rows as $row) {
            $colleges = $this->splitCollegeInstitutes($row['college_institute'] ?? '');
            foreach ($colleges as $college) {
                $counts[$college] = ($counts[$college] ?? 0) + 1;
            }
        }

        arsort($counts);
        $result = [];
        foreach ($counts as $college => $count) {
            $result[] = [
                'college_institute' => $college,
                'count' => $count,
            ];
        }

        return $result;
    }

    private function splitCollegeInstitutes(string $value): array
    {
        $normalized = str_replace(['&', ','], '/', $value);
        $parts = preg_split('/\s*\/\s*/', $normalized);
        $cleaned = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part !== '') {
                $cleaned[] = $part;
            }
        }

        return $cleaned ?: ['Unspecified'];
    }
}
