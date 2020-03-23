<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Hinhanh_model extends MY_Model {

    public function __construct() {
        $this->table = 'tbl_hinhanh';
        $this->primary_key = 'id_hinhanh';
        parent::__construct();
    }

    public function hinhanh_sudung() {
        $sql = "SELECT GROUP_CONCAT(id_hinhanh) as id FROM((SELECT id_hinhanh FROM tbl_slider WHERE deleted = 0)
            UNION ALL 
            (SELECT a.id_hinhanh FROM 
            (SELECT a.id_hinhanh FROM tbl_hinhanh_tin AS a RIGHT JOIN tbl_tin AS b ON a.id_tin = b.id_tin WHERE a.deleted = 0 AND b.deleted = 0) AS a
            )
            UNION ALL 
            (SELECT a.id_hinhanh FROM 
            (SELECT a.id_hinhanh FROM tbl_hinhanh_tintuc AS a RIGHT JOIN tbl_tintuc AS b ON a.id_tintuc = b.id_tintuc WHERE a.deleted = 0 AND b.deleted = 0) AS a
            )) as a
            ";
        $query = $this->db->query($sql);
        $return = $query->result_array();
        return $return;
    }

    public function delete_img_not($id_hinh) {
        $sql = "UPDATE tbl_hinhanh SET deleted = 1 where id_hinhanh NOT IN ($id_hinh) and `default` = 0";
        $query = $this->db->query($sql);
        //$return = $query->result_array();
        return $query;
    }

}
