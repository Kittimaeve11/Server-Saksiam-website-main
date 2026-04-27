<?php

namespace App\Models;

use CodeIgniter\Model;

class ContactModel extends Model
{
    protected $table            = 'int_saksiam_contact';
    protected $primaryKey       = 'int_saksiam_contact_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'int_saksiam_contact_id',
        'int_saksiam_contact_addressTH',
        'int_saksiam_contact_addressEN',
        'int_saksiam_contact_officehoursTH',
        'int_saksiam_contact_officehoursEN',
        'int_saksiam_contact_phonenumber',
        'int_saksiam_contact_callcenter',
        'int_saksiam_contact_fax',
        'int_saksiam_contact_emailmain',
        'int_saksiam_contact_emailsub',
        'int_saksiam_contact_googlemap',
        'int_saksiam_contact_Facbook',
        'int_saksiam_contact_line',
        'int_saksiam_contact_IG',
        'int_saksiam_contact_youtube',
        'int_saksiam_contact_tikkok',
        'int_saksiam_contact_savename',
        'int_saksiam_contact_updatename',
        'int_saksiam_contact_locationphoto'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'int_saksiam_contact_createAT';
    protected $updatedField  = 'int_saksiam_contact_updateAt';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['handleInsertTimestamps'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['handleUpdateTimestamps'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function handleInsertTimestamps(array $data)
    {
        // ตั้งค่า CreatedAt
        $data['data']['int_saksiam_contact_createAT'] = date('Y-m-d H:i:s');

        // Remove the UpdatedAt and report_date values to prevent them from being set
        unset($data['data']['int_saksiam_contact_updateAt']);

        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
        if (isset($data['data']['int_saksiam_contact_updatename'])) {
            $data['data']['int_saksiam_contact_updateAt'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

  
 // -----------------------------------------------------------------------------------------------   
    // <--Start Manager Sola -->

    public function createContactDATA($data)  // Receive the data to be saved
    {
        // Ensure that the $data variable is set before use
        if (empty($data)) {
            throw new \Exception('Data is required for insertion');
        }
        $data['int_saksiam_contact_createAT'] = date('Y-m-d H:i:s'); // Use the correct column names
        $this->insert($data);
        return $this->getInsertID();
    }

      public function updateContactDATA($contactID, $data)
    {
        // Verify if the provided ID matches the data in the database
        $existingData = $this->find($contactID);
        if (!$existingData) {
            return false; // No data to update
        }

        // If the ID is correct
        if (!empty($contactID)) {
            // Check if detail, active, namelist, or updatename are modified and save the new values; if not provided, use the existing values from the database
            // Set the data to be updated

            $this->set($data);

            // Update condition specified by ID
            $this->where('int_saksiam_contact_id', $contactID);

            // Perform the data update
            if ($this->update()) {
                return true; // Update successful
            } else {
                // Check if there are any errors
                return false;
            }
        } else {
            return false; // Case where ID is not provided
        }
    }

        public function showContactIDData($id = null)
    {
        if ($id !== null) {
            return $this->select('
             int_saksiam_contact.int_saksiam_contact_id AS id,
             int_saksiam_contact.int_saksiam_contact_addressTH AS address_th,
             int_saksiam_contact.int_saksiam_contact_addressEN AS address_en,
             int_saksiam_contact.int_saksiam_contact_officehoursTH AS officehours_th,
             int_saksiam_contact.int_saksiam_contact_officehoursEN AS officehours_en,
             int_saksiam_contact.int_saksiam_contact_phonenumber AS phone_number,
             int_saksiam_contact.int_saksiam_contact_callcenter AS call_center,
             int_saksiam_contact.int_saksiam_contact_fax AS fax,
             int_saksiam_contact.int_saksiam_contact_taxpayer AS taxpayer,
             int_saksiam_contact.int_saksiam_contact_emailmain AS email_main,
             int_saksiam_contact.int_saksiam_contact_emailsub AS email_sub,
             int_saksiam_contact.int_saksiam_contact_googlemap AS google_map,
             int_saksiam_contact.int_saksiam_contact_Facbook AS facebook,
             int_saksiam_contact.int_saksiam_contact_line AS line,
             int_saksiam_contact.int_saksiam_contact_IG AS instagram,
             int_saksiam_contact.int_saksiam_contact_youtube AS youtube,
             int_saksiam_contact.int_saksiam_contact_tikkok AS tiktok,
             int_saksiam_contact.int_saksiam_contact_locationphoto AS locationphoto,
        ')
                ->where('int_saksiam_contact.int_saksiam_contact_id', $id)
                ->first();
        }
        return null;
    }


    // <--end Manager Sola -->
// -----------------------------------------------------------------------------------------------   
    // <--Start Web Sola -->
    public function getShowWebsite()
    {
        return $this->select('
             int_saksiam_contact.int_saksiam_contact_id AS id,
             int_saksiam_contact.int_saksiam_contact_addressTH AS address_th,
             int_saksiam_contact.int_saksiam_contact_addressEN AS address_en,
             int_saksiam_contact.int_saksiam_contact_officehoursTH AS officehours_th,
             int_saksiam_contact.int_saksiam_contact_officehoursEN AS officehours_en,
             int_saksiam_contact.int_saksiam_contact_phonenumber AS phone_number,
             int_saksiam_contact.int_saksiam_contact_callcenter AS call_center,
             int_saksiam_contact.int_saksiam_contact_taxpayer AS taxpayer,
             int_saksiam_contact.int_saksiam_contact_fax AS fax,
             int_saksiam_contact.int_saksiam_contact_emailmain AS email_main,
             int_saksiam_contact.int_saksiam_contact_emailsub AS email_sub,
             int_saksiam_contact.int_saksiam_contact_googlemap AS google_map,
             int_saksiam_contact.int_saksiam_contact_Facbook AS facebook,
             int_saksiam_contact.int_saksiam_contact_line AS line,
             int_saksiam_contact.int_saksiam_contact_IG AS instagram,
             int_saksiam_contact.int_saksiam_contact_youtube AS youtube,
             int_saksiam_contact.int_saksiam_contact_tikkok AS tiktok,
             int_saksiam_contact.int_saksiam_contact_locationphoto AS locationphoto,
        ')
            ->findAll();
    }

    // <--End Web Sola -->
}
