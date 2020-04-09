<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Workshop_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_workshop';
        $this->primary_key = 'id';
        parent::__construct();
        $this->has_one['factory'] = array('foreign_model' => 'Factory_model', 'foreign_table' => 'pmp_factory', 'foreign_key' => 'id', 'local_key' => 'factory_id');
    }

    function create_object($data)
    {
        $array = array(
            'name', 'name_en', 'deleted', 'factory_id'
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
