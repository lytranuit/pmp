<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Employee_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_employee';
        $this->primary_key = 'id';
        $this->name = 'employee';
        parent::__construct();
    }

    function create_object($data)
    {
        $array = array(
            'name', 'deleted', 'string_id'
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
