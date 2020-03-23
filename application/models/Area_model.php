<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Area_model extends MY_Model {

    public function __construct() {
        $this->table = 'pmp_area';
        $this->primary_key = 'id';
        parent::__construct();
    }

    function create_object($data) {
        $array = array(
            'name', 'deleted',
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
