<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Category_model extends MY_Model {

    public function __construct() {
        $this->table = 'category';
        $this->primary_key = 'id';
        $this->has_one['hinhanh'] = array('foreign_model' => 'Hinhanh_model', 'foreign_table' => 'tbl_hinhanh', 'foreign_key' => 'id_hinhanh', 'local_key' => 'id_hinhanh');
        $this->has_many_pivot['products'] = array(
            'foreign_model' => 'Product_model',
            'pivot_table' => 'product_category',
            'local_key' => 'id',
            'pivot_local_key' => 'category_id', /* this is the related key in the pivot table to the local key
              this is an optional key, but if your column name inside the pivot table
              doesn't respect the format of "singularlocaltable_primarykey", then you must set it. In the next title
              you will see how a pivot table should be set, if you want to  skip these keys */
            'pivot_foreign_key' => 'product_id', /* this is also optional, the same as above, but for foreign table's keys */
            'foreign_key' => 'id',
            'get_relate' => TRUE /* another optional setting, which is explained below */
        );
        parent::__construct();
    }

    function create_object($data) {
        $array = array(
            'name', 'description', 'id_hinhanh', 'parent_id', 'sort', 'is_menu', 'deleted', 'active', 'hex_color'
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

    function get_color_by_category($id) {
        if (is_array($id)) {
            $id = implode(",", $id);
        }
        $sql = "SELECT 
                c.*
              FROM
                `product_category` AS a 
                JOIN product AS b 
                  ON a.`product_id` = b.`id` 
                  OR a.`product_id` = b.`parent` 
                JOIN tbl_color AS c 
                ON b.`color_id` = c.id
              WHERE category_id IN($id) and b.deleted = 0 and b.active = 1
                      GROUP BY b.color_id
              ";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    function get_size_by_category($id) {
        if (is_array($id)) {
            $id = implode(",", $id);
        }
        $sql = "SELECT 
                c.*
              FROM
                `product_category` AS a 
                JOIN product AS b 
                  ON a.`product_id` = b.`id` 
                  OR a.`product_id` = b.`parent` 
                JOIN tbl_size AS c 
                ON b.`size_id` = c.id
              WHERE category_id IN($id) and b.deleted = 0 and b.active = 1
                      GROUP BY b.size_id
              ";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

}
