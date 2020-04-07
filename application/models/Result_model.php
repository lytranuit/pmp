<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Result_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_result';
        $this->primary_key = 'id';
        parent::__construct();
        $this->has_one['area'] = array('foreign_model' => 'Area_model', 'foreign_table' => 'pmp_area', 'foreign_key' => 'id', 'local_key' => 'area_id');
        $this->has_one['factory'] = array('foreign_model' => 'Factory_model', 'foreign_table' => 'pmp_factory', 'foreign_key' => 'id', 'local_key' => 'factory_id');
        $this->has_one['workshop'] = array('foreign_model' => 'Workshop_model', 'foreign_table' => 'pmp_workshop', 'foreign_key' => 'id', 'local_key' => 'workshop_id');
        $this->has_one['department'] = array('foreign_model' => 'Department_model', 'foreign_table' => 'pmp_department', 'foreign_key' => 'id', 'local_key' => 'department_id');
        $this->has_one['object'] = array('foreign_model' => 'Object_model', 'foreign_table' => 'pmp_object', 'foreign_key' => 'id', 'local_key' => 'object_id');

        $this->has_one['position'] = array('foreign_model' => 'Position_model', 'foreign_table' => 'pmp_position', 'foreign_key' => 'id', 'local_key' => 'position_id');
        $this->has_one['target'] = array('foreign_model' => 'Target_model', 'foreign_table' => 'pmp_target', 'foreign_key' => 'id', 'local_key' => 'target_id');
    }

    function create_object($data)
    {
        $array = array(
            'deleted', 'target_id', 'object_id', 'position_id', 'department_id', 'area_id', 'factory_id', 'workshop_id', 'value', 'date', 'created_at', 'deleted_at'
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

    function get_date_has_data($type)
    {
        if ($type == "Year") {
            $sql = "select YEAR(pmp_result.date) as value from pmp_result where deleted = 0 GROUP BY YEAR(pmp_result.date) ORDER BY date DESC";
        } else if ($type == "HalfYear") {
            $sql = "select CONCAT(YEAR(pmp_result.date),'-',FLOOR(QUARTER(DATE) / 3) + 1) as value from pmp_result where deleted = 0 GROUP BY CONCAT(YEAR(pmp_result.date),'-',FLOOR(QUARTER(DATE) / 3) + 1) ORDER BY date DESC";
        } else if ($type == "Quarter") {
            $sql = "select CONCAT(YEAR(pmp_result.date),'-',QUARTER(pmp_result.date)) as value from pmp_result where deleted = 0 GROUP BY CONCAT(YEAR(pmp_result.date),'-',QUARTER(pmp_result.date)) ORDER BY date DESC";
        } else if ($type == "Month") {
            $sql = "select DATE_FORMAT(pmp_result.date,'%Y-%m') as value from pmp_result where deleted = 0 GROUP BY DATE_FORMAT(pmp_result.date,'%Y-%m') ORDER BY date DESC";
        } else {
            return array();
        }
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }
    function chartdata($params)
    {
        $where = "WHERE a.deleted = 0 and a.department_id = " . $this->db->escape($params['department_id']) . " and a.target_id = " . $this->db->escape($params['target_id']) . "";
        if ($params['date_from_prev'] != "") {
            $where .= " AND a.date between '" . $params['date_from_prev'] . "' and '" . $params['date_to'] . "'";
        } else {
            $where .= " AND a.date between '" . $params['date_from'] . "' and '" . $params['date_to'] . "'";
        }
        $sql = "SELECT a.*,b.string_id as position_string_id FROM pmp_result as a JOIN pmp_position as b ON a.position_id = b.id $where ORDER BY a.date ASC";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }
    function area_export($params)
    {
        $where = "WHERE a.deleted = 0 and a.workshop_id = " . $this->db->escape($params['workshop_id']) . " and c.object_id = " . $this->db->escape($params['object_id']);

        $where .= " AND a.date between '" . $params['date_from'] . "' and '" . $params['date_to'] . "'";

        $sql = "SELECT b.* FROM pmp_result as a JOIN pmp_area as b ON a.area_id = b.id JOIN pmp_position as c ON a.position_id = c.id $where GROUP BY a.area_id";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }
    function nhanvien_export($params)
    {
        $where = "WHERE a.deleted = 0 and a.workshop_id = " . $this->db->escape($params['workshop_id']) . " and c.object_id = " . $this->db->escape($params['object_id']);

        $where .= " AND a.date between '" . $params['date_from'] . "' and '" . $params['date_to'] . "'";

        $sql = "SELECT b.* FROM pmp_result as a JOIN pmp_department as b ON a.department_id = b.id JOIN pmp_position as c ON a.position_id = c.id $where GROUP BY a.department_id";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }
}
