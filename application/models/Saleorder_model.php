<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Saleorder_model extends MY_Model {

    public function __construct() {
        $this->table = 'sale_order';
        $this->primary_key = 'id';
        $this->has_many['details'] = array('foreign_model' => 'SaleOrderLine_model', 'foreign_table' => 'sale_order_line', 'foreign_key' => 'order_id', 'local_key' => 'id');
        parent::__construct();
    }

    function create_object($data) {
        $array = array(
            'order_date', 'customer_name', 'customer_phone', 'customer_email', 'customer_address', 'notes', 'amount', 'total_amount', 'status', 'user_id'
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

    function amount_sale() {
        $sql = "select sum(a.amount) as amount from sale_order_line as a join sale_order as b on a.order_id = b.id where b.status = 4";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return isset($result[0]['amount']) ? $result[0]['amount'] : 0;
    }

    function amount_debt_has_order() {
        $sql = "select sum(a.amount) as amount from sale_order_line as a join sale_order as b on a.order_id = b.id where b.status = 5";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return isset($result[0]['amount']) ? $result[0]['amount'] : 0;
    }

    function amount_sale_in_day() {
        $date = date("Y-m-d");
        $sql = "select sum(a.amount) as amount from sale_order_line as a join sale_order as b on a.order_id = b.id where b.order_date = '$date' and b.status = 4";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return isset($result[0]['amount']) ? $result[0]['amount'] : 0;
    }

    function amount_category($categroy_id) {
        $sql = "select sum(a.amount) as amount from sale_order_line as a join sale_order as b on a.order_id = b.id where a.product_id IN(select product_id from product_category where category_id = $categroy_id) and status = 4";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return isset($result[0]['amount']) ? $result[0]['amount'] : 0;
    }

    function amount_category_in_day($categroy_id) {
        $date = date("Y-m-d");
        $sql = "select sum(a.amount) as amount from sale_order_line as a join sale_order as b on a.order_id = b.id where a.product_id IN(select product_id from product_category where category_id = $categroy_id) and b.order_date = '$date'  and status = 4";
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return isset($result[0]['amount']) ? $result[0]['amount'] : 0;
    }

    function amount_sale_group_by_time($time_type = "Day") {

        if ($time_type == "Week") {
            $subsql = "CONCAT('Week ',WEEK(b.order_date)) as time_type";
        } elseif ($time_type == "DayOfWeek") {
            $subsql = "WEEKDAY(b.order_date) + 2 as time_type ";
        } elseif ($time_type == "Month") {
            $subsql = "MONTH(b.order_date) as time_type";
        } elseif ($time_type == "Year") {
            $subsql = "YEAR(b.order_date) as time_type ";
        } else {
            $subsql = "b.order_date as time_type";
        }
        $sql = "select $subsql,sum(a.amount) as amount from sale_order_line as a join sale_order as b on a.order_id = b.id where status = 4 GROUP BY time_type";

        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }

    function amount_food_group_by_time($time_type = "Day") {

        if ($time_type == "Week") {
            $subsql = "CONCAT('Week ',WEEK(b.order_date)) as time_type";
        } elseif ($time_type == "DayOfWeek") {
            $subsql = "WEEKDAY(b.order_date) + 2 as time_type ";
        } elseif ($time_type == "Month") {
            $subsql = "MONTH(b.order_date) as time_type";
        } elseif ($time_type == "Year") {
            $subsql = "YEAR(b.order_date) as time_type ";
        } else {
            $subsql = "b.order_date as time_type";
        }
        $sql = "select $subsql,sum(a.amount) as amount from sale_order_line as a join sale_order as b on a.order_id = b.id where a.product_id IN(select product_id from product_category where category_id = 1) and status = 4 GROUP BY time_type";

        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }

    function best_sale() {
        $sql = "SELECT name, SUM(`quantity`) AS count_sale FROM sale_order_line GROUP BY product_id ORDER BY count_sale DESC";

        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }

}
