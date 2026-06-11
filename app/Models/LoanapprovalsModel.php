<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanapprovalsModel extends Model
{
    protected $table            = 'int_saksiam_loanapprovals';
    protected $primaryKey       = 'int_saksiam_loanapprovals_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'int_saksiam_loanapprovals_id',
        'int_saksiam_loanapprovals_ticket',
        'int_saksiam_loanapprovals_loaname',
        'int_saksiam_loanapprovals_credit',
        'int_saksiam_loanapprovals_typeloan',
        'int_saksiam_loanapprovals_chanellevel1',
        'int_saksiam_loanapprovals_chanellevel2',
        'int_saksiam_loanapprovals_appovalname',
        'int_saksiam_loanapprovals_appovaldate',
        'int_saksiam_loanapprovals_cer'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_loanapprovals_appovaldate';
    protected $updatedField  = '';
    protected $deletedField  = '';

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
}
