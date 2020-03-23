<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Option_model extends MY_Model {

    public function __construct() {
        $this->table = 'options';
        $this->primary_key = 'id';
        parent::__construct();
    }

    function get_options_in($id) {
        if (is_array($id)) {
            $id = implode("','", $id);
        }
        $sql = "SELECT * FROM `options` WHERE `key` IN('$id')";
//        echo $sql . "<br>";
        $query = $this->db->query($sql);
        $rows = $query->result_array();
        $return = array();
        foreach ($rows as $row) {
            $return[$row['key']] = $row;
        }
        return $return;
    }

}
