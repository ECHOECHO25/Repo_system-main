<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['username', 'password_hash', 'role', 'status'];
    protected $useTimestamps    = true;

    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[100]',
        'password_hash' => 'required'
    ];
}
