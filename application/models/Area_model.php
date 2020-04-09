<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Area_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_area';
        $this->primary_key = 'id';
        parent::__construct();
        $this->has_one['factory'] = array('foreign_model' => 'Factory_model', 'foreign_table' => 'pmp_factory', 'foreign_key' => 'id', 'local_key' => 'factory_id');
        $this->has_one['workshop'] = array('foreign_model' => 'Workshop_model', 'foreign_table' => 'pmp_workshop', 'foreign_key' => 'id', 'local_key' => 'workshop_id');
    }

    function create_object($data)
    {
        $array = array(
            'name', 'name_en', 'deleted', 'factory_id', 'workshop_id'
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
