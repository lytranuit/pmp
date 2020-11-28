<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Request_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_request';
        $this->primary_key = 'id';
        // $this->after_update[] = "update_after";
        $this->name = "request";
        parent::__construct();
        $this->has_many['values'] = array('foreign_model' => 'Requestvalue_model', 'foreign_table' => 'pmp_request_value', 'foreign_key' => 'request_id', 'local_key' => 'id');
        $this->has_one['template'] = array('foreign_model' => 'Template_model', 'foreign_table' => 'pmp_template', 'foreign_key' => 'id', 'local_key' => 'template_id');
    }

    function create_object($data)
    {
        $array = array(
            'name', 'deleted', 'description', 'template_id'
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
