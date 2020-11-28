<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Variable_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_variable';
        $this->primary_key = 'id';
        // $this->after_update[] = "update_after";
        $this->name = "variable";
        parent::__construct();
        $this->has_many['radio_values'] = array('foreign_model' => 'Variableradio_model', 'foreign_table' => 'pmp_variable_radio', 'foreign_key' => 'variable_id', 'local_key' => 'id');
    }

    function create_object($data)
    {
        $array = array(
            'name', 'deleted', 'description', 'template_id', 'type'
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
