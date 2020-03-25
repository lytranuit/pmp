<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Limit_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_limit';
        $this->primary_key = 'id';
        parent::__construct();
        $this->has_one['area'] = array('foreign_model' => 'Area_model', 'foreign_table' => 'pmp_area', 'foreign_key' => 'id', 'local_key' => 'area_id');
        $this->has_one['target'] = array('foreign_model' => 'Target_model', 'foreign_table' => 'pmp_target', 'foreign_key' => 'id', 'local_key' => 'target_id');
    }

    function create_object($data)
    {
        $array = array(
            'deleted', 'area_id', 'target_id', 'alert_limit', 'action_limit', 'standard_limit'
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
