<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Object_model extends MY_Model {

    public function __construct() {
        $this->table = 'pmp_object';
        $this->primary_key = 'id';
        parent::__construct();
    }

    function create_object($data) {
        $array = array(
            'name', 'parent_id', 'deleted'
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
