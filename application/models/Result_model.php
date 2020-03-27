<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Result_model extends MY_Model {

    public function __construct() {
        $this->table = 'pmp_result';
        $this->primary_key = 'id';
        parent::__construct();
        $this->has_one['area'] = array('foreign_model' => 'Area_model', 'foreign_table' => 'pmp_area', 'foreign_key' => 'id', 'local_key' => 'area_id');
        $this->has_one['department'] = array('foreign_model' => 'Department_model', 'foreign_table' => 'pmp_department', 'foreign_key' => 'id', 'local_key' => 'department_id');

        $this->has_one['position'] = array('foreign_model' => 'Position_model', 'foreign_table' => 'pmp_position', 'foreign_key' => 'id', 'local_key' => 'position_id');
        $this->has_one['target'] = array('foreign_model' => 'Target_model', 'foreign_table' => 'pmp_target', 'foreign_key' => 'id', 'local_key' => 'target_id');
    }

    function create_object($data) {
        $array = array(
            'deleted', 'target_id', 'position_id', 'department_id', 'area_id', 'value', 'date', 'created_at', 'deleted_at'
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
