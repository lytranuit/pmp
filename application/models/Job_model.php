<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Job_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'job';
        $this->primary_key = 'id';
        // $this->after_update[] = "update_after";
        $this->name = "job";
        parent::__construct();
    }

    function create_object($data)
    {
        $array = array(
            'name', 'deleted', 'frequency'
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
