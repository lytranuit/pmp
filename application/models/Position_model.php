<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Position_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_position';
        $this->primary_key = 'id';
        parent::__construct();
        $this->has_one['department'] = array('foreign_model' => 'Department_model', 'foreign_table' => 'pmp_department', 'foreign_key' => 'id', 'local_key' => 'department_id');
        $this->has_one['frequency'] = array('foreign_model' => 'Frequency_model', 'foreign_table' => 'pmp_frequency', 'foreign_key' => 'id', 'local_key' => 'frequency_id');
    }

    function create_object($data)
    {
        $array = array(
            'name', 'deleted', 'string_id', 'frequency_id', 'department_id', 'frequency_name'
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
