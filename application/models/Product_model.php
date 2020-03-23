<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Product_model extends MY_Model {

    public function __construct() {
        $this->table = 'product';
        $this->primary_key = 'id';
        $this->has_one['hinhanh'] = array('foreign_model' => 'Hinhanh_model', 'foreign_table' => 'tbl_hinhanh', 'foreign_key' => 'id_hinhanh', 'local_key' => 'id_hinhanh');

        $this->has_many_pivot['category'] = array(
            'foreign_model' => 'Category_model',
            'pivot_table' => 'product_category',
            'local_key' => 'id',
            'pivot_local_key' => 'product_id', /* this is the related key in the pivot table to the local key
              this is an optional key, but if your column name inside the pivot table
              doesn't respect the format of "singularlocaltable_primarykey", then you must set it. In the next title
              you will see how a pivot table should be set, if you want to  skip these keys */
            'pivot_foreign_key' => 'category_id', /* this is also optional, the same as above, but for foreign table's keys */
            'foreign_key' => 'id',
            'get_relate' => TRUE /* another optional setting, which is explained below */
        );
        $this->has_many_pivot['files'] = array(
            'foreign_model' => 'Hinhanh_model',
            'pivot_table' => 'product_hinhanh',
            'local_key' => 'id',
            'pivot_local_key' => 'product_id', /* this is the related key in the pivot table to the local key
              this is an optional key, but if your column name inside the pivot table
              doesn't respect the format of "singularlocaltable_primarykey", then you must set it. In the next title
              you will see how a pivot table should be set, if you want to  skip these keys */
            'pivot_foreign_key' => 'hinhanh_id', /* this is also optional, the same as above, but for foreign table's keys */
            'foreign_key' => 'id_hinhanh',
            'get_relate' => TRUE /* another optional setting, which is explained below */
        );
        parent::__construct();
    }

    function create_object($data) {
        $array = array(
            'name', 'code', 'sort', 'detail', 'description', 'guide', 'price', 'quantity', 'id_hinhanh', 'size_id', 'color_id', 'parent', 'active', 'deleted'
        );
        $obj = array();
//        print_r($data);
//        die();
        foreach ($array as $key) {
            if (isset($data[$key])) {
                if ($key == "price" || $key == "quantity") {
                    $obj[$key] = str_replace(",", "", $data[$key]);

                    $obj[$key] = (float) str_replace(" VND", "", $obj[$key]);
                } else
                    $obj[$key] = $data[$key];
            } else
                continue;
        }

//        print_r($obj);
//        die();
        return $obj;
    }

    function get_all_color($id) {
        $sql = "SELECT b.* FROM product as a JOIN tbl_color as b ON a.color_id = b.id WHERE (a.id = $id OR a.parent = $id) and a.deleted = 0";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    function get_all_size($id) {
        $sql = "SELECT b.* FROM product as a JOIN tbl_size as b ON a.size_id = b.id WHERE (a.id = $id OR a.parent = $id) and a.deleted = 0";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    function get_product_by_category($category, $except = "", $limit = "") {
        if (is_array($category)) {
            $category = implode(",", $category);
        }
        if (is_array($except)) {
            $except = implode(",", $except);
        }
        $where = "WHERE a.deleted = 0";
        if ($category != "") {
            $where .= " and b.category_id IN($category)";
        }
        if ($except != "") {
            $where .= " and a.id NOT IN($except)";
        }
        if ($limit > 0) {
            $limit = " LIMIT 0,$limit";
        }
        $sql = "SELECT a.*,c.*,b.id as product_category_id FROM product as a JOIN product_category as b ON a.id = b.product_id LEFT JOIN tbl_hinhanh as c ON a.id_hinhanh = c.id_hinhanh $where GROUP BY a.id ORDER BY b.sort DESC,a.id DESC $limit";
//        echo $sql . "<br>";die();
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    function get_product_in($products) {
        if (is_array($products)) {
            $products = implode(",", $products);
        }
        $sql = "SELECT a.*,c.* FROM product as a LEFT JOIN tbl_hinhanh as c ON a.id_hinhanh = c.id_hinhanh WHERE a.id IN($products) GROUP BY a.id ORDER BY a.id DESC";
//        echo $sql . "<br>";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

}
