<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Debt_model extends MY_Model {

    public function __construct() {
        $this->table = 'tbl_debt';
        $this->primary_key = 'id';
        parent::__construct();
    }

    function create_object($data) {
        $array = array(
            'amount', 'note', 'date', 'deleted'
        );
        $obj = array();
        foreach ($array as $key) {
            if (isset($data[$key])) {
                if ($key == "amount" || $key == "amount_remain") {
                    $obj[$key] = str_replace(",", "", $data[$key]);

                    $obj[$key] = (float) str_replace(" VND", "", $obj[$key]);
                } else
                    $obj[$key] = $data[$key];
            } else
                continue;
        }

        return $obj;
    }

    function amount_debt() {
        $sql = "select sum(a.amount) as amount from tbl_debt as a where deleted = 0";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return isset($result[0]['amount']) ? $result[0]['amount'] : 0;
    }

}
