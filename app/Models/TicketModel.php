<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table            = 'int_saksiam_ticket';
    protected $primaryKey       = 'int_saksiam_ticket_id ';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'int_saksiam_ticket_id',
        'int_saksiam_ticket_subject',
        'int_saksiam_ticket_typeloan',
        'int_saksiam_ticket_cradit',
        'int_saksiam_ticket_contedtime',
        'int_saksiam_ticket_message',
        'int_saksiam_ticket_customerid',
        'int_saksiam_ticket_loanhistory',
        'int_saksiam_ticket_detail',
        'int_saksiam_ticket_type',
        'int_saksiam_ticket_status',
        'int_saksiam_ticket_branchid',
        'int_saksiam_ticket_branchid2',
        'int_saksiam_ticket_createname',
        'int_saksiam_ticket_note',
        'int_saksiam_ticket_projectname',
        'int_saksiam_ticket_responseDate',
        'int_saksiam_ticket_responsename',
        'int_saksiam_ticket_iscontact',
        'int_saksiam_ticket_resultDate',
        'int_saksiam_ticket_resultname',
        'int_saksiam_ticket_approvedate',
        'int_saksiam_ticket_approvename',
        'int_saksiam_ticket_action',
        'int_saksiam_ticket_solce',
        'int_saksiam_ticket_PerWP',
        'int_saksiam_ticket_PerBL',
        'int_saksiam_ticket_updatename',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_ticket_createdate';
    protected $updatedField  = 'int_saksiam_ticket_updateDate';
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
