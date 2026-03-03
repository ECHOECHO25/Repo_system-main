<?php

namespace App\Models;

use CodeIgniter\Model;

class FacultyMasterlistModel extends Model
{
    protected $table = 'faculty_masterlist';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'campus',
        'name',
        'position',
        'college_division',
        'department_office_unit',
        'sex',
        'teaching_status'
    ];
    protected $useTimestamps = true;
}
