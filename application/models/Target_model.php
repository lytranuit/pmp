<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Target_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_target';
        $this->primary_key = 'id';
        $this->name = "target/method";
        parent::__construct();
        $this->has_one['parent'] = array('foreign_model' => 'Target_model', 'foreign_table' => 'pmp_target', 'foreign_key' => 'id', 'local_key' => 'parent_id');

        // $this->has_one['parent_object'] = array('foreign_model' => 'Target_model', 'foreign_table' => 'pmp_target', 'foreign_key' => 'id', 'local_key' => 'parent_id');
    }

    function create_object($data)
    {
        $array = array(
            'name', 'name_en', 'parent_id', 'unit', 'has_data', 'type_data', 'text_data', 'text_data_en', 'deleted'
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
