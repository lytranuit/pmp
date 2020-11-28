<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Variabletype_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_variable_type';
        $this->primary_key = 'id';
        // $this->after_update[] = "update_after";
        $this->name = "variable_type";
        parent::__construct();
    }

    function create_object($data)
    {
        $array = array(
            'name', 'description'
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
