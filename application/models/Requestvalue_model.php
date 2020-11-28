<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class requestvalue_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_request_value';
        $this->primary_key = 'id';
        // $this->after_update[] = "update_after";
        $this->name = "request_value";
        parent::__construct();
        $this->has_one['variable'] = array('foreign_model' => 'Variable_model', 'foreign_table' => 'pmp_variable', 'foreign_key' => 'id', 'local_key' => 'variable_id');
        $this->has_one['request'] = array('foreign_model' => 'Request_model', 'foreign_table' => 'pmp_request', 'foreign_key' => 'id', 'local_key' => 'request_id');
    }

    function create_object($data)
    {
        $array = array(
            'variable_id', 'request_id', 'value'
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
