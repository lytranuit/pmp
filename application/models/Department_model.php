<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Department_model extends MY_Model {

    public function __construct() {
        $this->table = 'pmp_department';
        $this->primary_key = 'id';
        parent::__construct();
        $this->has_one['area'] = array('foreign_model' => 'Area_model', 'foreign_table' => 'pmp_area', 'foreign_key' => 'id', 'local_key' => 'area_id');
    }

    function create_object($data) {
        $array = array(
            'name', 'deleted', 'string_id', 'area_id'
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
