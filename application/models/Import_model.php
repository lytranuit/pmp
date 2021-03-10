<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Import_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_import';
        $this->primary_key = 'id';
        parent::__construct();
        $this->has_one['user'] = array('foreign_model' => 'User_model', 'foreign_table' => 'users', 'foreign_key' => 'id', 'local_key' => 'user_id');
        $this->has_one['object'] = array('foreign_model' => 'Object_model', 'foreign_table' => 'pmp_object', 'foreign_key' => 'id', 'local_key' => 'object_id');
        $this->has_one['factory'] = array('foreign_model' => 'Factory_model', 'foreign_table' => 'pmp_factory', 'foreign_key' => 'id', 'local_key' => 'factory_id');

        $this->has_one['workshop'] = array('foreign_model' => 'Workshop_model', 'foreign_table' => 'pmp_workshop', 'foreign_key' => 'id', 'local_key' => 'workshop_id');

        $this->has_one['area'] = array('foreign_model' => 'Area_model', 'foreign_table' => 'pmp_area', 'foreign_key' => 'id', 'local_key' => 'area_id');
        // $this->has_one['department'] = array('foreign_model' => 'Department_model', 'foreign_table' => 'pmp_department', 'foreign_key' => 'id', 'local_key' => 'department_id');
        // $this->has_one['frequency'] = array('foreign_model' => 'Frequency_model', 'foreign_table' => 'pmp_frequency', 'foreign_key' => 'id', 'local_key' => 'frequency_id');
        // $this->has_one['target'] = array('foreign_model' => 'Target_model', 'foreign_table' => 'pmp_target', 'foreign_key' => 'id', 'local_key' => 'target_id');
    }

    function create_object($data)
    {
        $array = array(
            'name', 'deleted', 'file', 'file_name', 'user_id', 'date', 'status', 'note', 'object_id', 'factory_id', 'workshop_id', 'area_id'
        );
        $obj = array();
        foreach ($array as $key) {
            if (isset($data[$key])) {
                $obj[$key] = $data[$key];
            } else
                continue;
        }

        return $obj;
    }
}
