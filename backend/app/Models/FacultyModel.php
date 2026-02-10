<?php

namespace App\Models;

use CodeIgniter\Model;

class FacultyModel extends Model
{
    protected $table            = 'faculty';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'name', 'google_scholar_citations', 'h_index', 'i10_index',
        'google_scholar_account', 'college_institute', 'email', 'status'
    ];

    protected $useTimestamps = true;
    protected $validationRules = [
        'name' => 'required|max_length[255]',
    ];

    public function getMetrics()
    {
        $db = \Config\Database::connect();
        
        $totalFaculty = $this->where('status', 'active')->countAllResults(false);
        $withProfile = $db->table($this->table)
            ->where('google_scholar_account IS NOT NULL')
            ->where('google_scholar_account !=', '')
            ->where('status', 'active')
            ->countAllResults();
        $avgHIndex = $db->table($this->table)
                        ->selectAvg('h_index')
                        ->where('h_index IS NOT NULL')
                        ->where('status', 'active')
                        ->get()
                        ->getRow()
                        ->h_index ?? 0;

        $missingHIndex = $db->table($this->table)
            ->where('h_index IS NULL')
            ->where('status', 'active')
            ->countAllResults();

        $missingCitations = $db->table($this->table)
            ->where('google_scholar_citations IS NULL')
            ->where('status', 'active')
            ->countAllResults();

        $missingI10Index = $db->table($this->table)
            ->where('i10_index IS NULL')
            ->where('status', 'active')
            ->countAllResults();

        $lowCitations = $db->table($this->table)
            ->where('google_scholar_citations <', 100)
            ->where('status', 'active')
            ->countAllResults();
        
        return [
            'total_faculty' => $totalFaculty,
            'with_profile' => (int)$withProfile,
            'avg_h_index' => round($avgHIndex, 2),
            'missing_h_index' => (int)$missingHIndex,
            'missing_citations' => (int)$missingCitations,
            'missing_i10_index' => (int)$missingI10Index,
            'low_citations' => (int)$lowCitations,
        ];
    }

    public function getTopByCitations($limit = 10)
    {
        return $this->where('status', 'active')
            ->orderBy('google_scholar_citations', 'DESC')
            ->limit((int)$limit)
            ->findAll();
    }

    public function getTopByHIndex($limit = 10)
    {
        return $this->where('status', 'active')
            ->orderBy('h_index', 'DESC')
            ->limit((int)$limit)
            ->findAll();
    }

    public function getInactiveFaculty(array $years)
    {
        $db = \Config\Database::connect();
        $publicationTable = $db->table('publications');
        $inactive = [];

        $facultyList = $this->where('status', 'active')->findAll();
        foreach ($facultyList as $faculty) {
            $name = $faculty['name'];
            $count = $publicationTable
                ->select('id')
                ->groupStart()
                ->like('authors', $name)
                ->groupEnd()
                ->whereIn('year', $years)
                ->countAllResults();

            if ($count === 0) {
                $inactive[] = $faculty;
            }
        }

        return $inactive;
    }
}
