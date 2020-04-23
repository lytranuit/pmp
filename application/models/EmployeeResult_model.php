<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class EmployeeResult_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_employee_result';
        $this->primary_key = 'id';
        parent::__construct();
        $this->has_one['area'] = array('foreign_model' => 'Area_model', 'foreign_table' => 'pmp_area', 'foreign_key' => 'id', 'local_key' => 'area_id');
        $this->has_one['factory'] = array('foreign_model' => 'Factory_model', 'foreign_table' => 'pmp_factory', 'foreign_key' => 'id', 'local_key' => 'factory_id');
        $this->has_one['workshop'] = array('foreign_model' => 'Workshop_model', 'foreign_table' => 'pmp_workshop', 'foreign_key' => 'id', 'local_key' => 'workshop_id');
        $this->has_one['target'] = array('foreign_model' => 'Target_model', 'foreign_table' => 'pmp_target', 'foreign_key' => 'id', 'local_key' => 'target_id');

        $this->has_one['employee'] = array('foreign_model' => 'Employee_model', 'foreign_table' => 'pmp_employee', 'foreign_key' => 'id', 'local_key' => 'employee_id');
    }

    function create_object($data)
    {
        $array = array(
            'target_id', 'deleted', 'user_id', 'employee_id', 'area_id', 'factory_id', 'workshop_id', 'date', 'created_at', 'deleted_at', 'from_file', 'value_H', 'value_N', 'value_C', 'value_LF', 'value_RF', 'value_LG', 'value_RG'
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
            $sql = "select YEAR(date) as value from pmp_employee_result where deleted = 0 GROUP BY YEAR(date) ORDER BY date DESC";
        } else if ($type == "HalfYear") {
            $sql = "select CONCAT(YEAR(date),'-',FLOOR(QUARTER(DATE) / 3) + 1) as value from pmp_employee_result where deleted = 0 GROUP BY CONCAT(YEAR(date),'-',FLOOR(QUARTER(DATE) / 3) + 1) ORDER BY date DESC";
        } else if ($type == "Quarter") {
            $sql = "select CONCAT(YEAR(date),'-',QUARTER(date)) as value from pmp_employee_result where deleted = 0 GROUP BY CONCAT(YEAR(date),'-',QUARTER(date)) ORDER BY date DESC";
        } else if ($type == "Month") {
            $sql = "select DATE_FORMAT(date,'%Y-%m') as value from pmp_employee_result where deleted = 0 GROUP BY DATE_FORMAT(date,'%Y-%m') ORDER BY date DESC";
        } else {
            return array();
        }
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }
    function chart_datav2($params)
    {
        $this->load->model("target_model");
        $results = array('labels' => array(), 'datasets' => array());
        $data = $this->chartdata($params);
        $data_limit = $this->chartdata_limit($params);
        // echo "<pre>";
        // print_r($data_limit);
        // die();
        $max = 1;
        $labels = array();
        // $labels[] = array()
        $position_list = array();
        $datatmp = array();
        $datasets = array();
        $datasets[] = array(
            'marker' => array(
                'enabled' => false
            ),
            'color' => 'red',
            'name' => "Action Limit",
            'data' => array(),
        );
        $datasets[] = array(
            'marker' => array(
                'enabled' => false
            ),
            'color' => 'orange',
            'name' => "Alert Limit",
            'data' => array(),
        );
        $datasets[] =  array(
            'name' => "Head",
            'data' => array(),
        );
        $datasets[] =  array(
            'name' => "Noise",
            'data' => array(),
        );
        $datasets[] =  array(
            'name' => "Chest",
            'data' => array(),
        );
        $datasets[] =  array(
            'name' => "Left forearm",
            'data' => array(),
        );
        $datasets[] =  array(
            'name' => "Right forearm",
            'data' => array(),
        );
        $datasets[] =  array(
            'name' => "Left glove print 5 fingers",
            'data' => array(),
        );
        $datasets[] =  array(
            'name' => "Right glove print 5 fingers",
            'data' => array(),
        );
        $lineAtIndex = null;
        $labels = array();

        foreach ($data as $row) {
            $date = date("d/m/Y", strtotime($row->date));
            $labels[] = $date;
            if ($lineAtIndex === null && $params['date_from_prev'] != "" && $row->date >= $params['date_from']) {
                $lineAtIndex = count($row) - 1;
            }
            $limit = array_values(array_filter($data_limit, function ($item) use ($date) {
                $list = explode("/", $date);
                $year = $list[2];
                return $item['year'] == $year;
            }));
            $value_action = isset($limit[0]['action_limit']) ? (float) $limit[0]['action_limit'] : 0;
            $value_alert = isset($limit[0]['alert_limit']) ? (float) $limit[0]['alert_limit'] : 0;
            $value_H = (float) $row->value_H;
            $value_N = (float) $row->value_N;
            $value_C = (float) $row->value_C;
            $value_LF = (float) $row->value_LF;
            $value_RF = (float) $row->value_RF;
            $value_LG = (float) $row->value_LG;
            $value_RG = (float) $row->value_RG;
            $values = array($value_action, $value_alert, $value_H, $value_N, $value_C, $value_LF, $value_RF, $value_LG, $value_RG);
            foreach ($datasets as $key => &$position) {
                $position['data'][] = $values[$key];
                if ($values[$key] > $max) {
                    $max = $values[$key];
                }
            }
        }

        $yAxis_title = "CFU/Plate";
        $title = $params['title'];
        $subtitle = $params['subtitle'];
        $results = array(
            'title' => array('text' => $title),
            'subtitle' => array('text' => $subtitle, 'style' => array("fontSize" => 18)),
            'xAxis' => array(
                'title' => array(
                    'align' => 'high',
                    'offset' => 0,
                    'text' => "Date",
                    'rotation' => 0,
                    'x' => 50
                ),
                'categories' => $labels,
                'plotLines' => array(array(
                    'color' => 'gray', // Red
                    'width' => 2,
                    'value' => $lineAtIndex
                ))
            ),
            'yAxis' => array(
                'title' => array(
                    'align' => 'high',
                    'offset' => 0,
                    'text' => $yAxis_title,
                    'rotation' => 0,
                    'y' => -20
                ),
                'min' => 0,
                'max' => $max + 1,
                'startOnTick' => false,
                'endOnTick' => false
            ),
            'series' => $datasets,
        );
        return $results;
    }

    function chartdata($params)
    {
        $where = "WHERE a.deleted = 0 and a.employee_id = " . $this->db->escape($params['employee_id']) . " and a.area_id = " . $this->db->escape($params['area_id']) . " and a.target_id = " . $this->db->escape($params['target_id']) . "";
        if ($params['date_from_prev'] != "") {
            $where .= " AND a.date between '" . $params['date_from_prev'] . "' and '" . $params['date_to'] . "'";
        } else {
            $where .= " AND a.date between '" . $params['date_from'] . "' and '" . $params['date_to'] . "'";
        }
        $sql = "SELECT a.* FROM pmp_employee_result as a $where ORDER BY a.date ASC";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }
    function chartdata_limit($params)
    {

        $where = "WHERE a.deleted = 0 and a.area_id = " . $this->db->escape($params['area_id']) . " and a.target_id = " . $this->db->escape($params['target_id']) . "";

        $sql = "SELECT a.* FROM pmp_limit as a $where";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }
    function get_data_minmax($employee_id, $area_id, $date_from, $date_to)
    {
        $where = "WHERE deleted = 0 and area_id = $area_id and employee_id = $employee_id";
        if ($date_from == "") {
            $date_from = $date_to = date("Y-m-d");
        }
        $where .= " AND date between '" . $date_from . "' and '" . $date_to . "'";
        $sql = "SELECT date,
                        MIN(value_H) as min_H,MAX(value_H) as max_H,
                        MIN(value_N) as min_N,MAX(value_N) as max_N,
                        MIN(value_C) as min_C,MAX(value_C) as max_C,
                        MIN(value_LF) as min_LF,MAX(value_LF) as max_LF,
                        MIN(value_LG) as min_LG,MAX(value_LG) as max_LG,
                        MIN(value_RF) as min_RF,MAX(value_RF) as max_RF,
                        MIN(value_RG) as min_RG,MAX(value_RG) as max_RG
                    FROM
                    pmp_employee_result 
                $where";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $result = isset($result[0]) ? $result[0] : array();
        return $result;
    }
    function set_value_export($params)
    {
        $this->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $params['workshop_id'], 'deleted' => 0));
        return $this;
    }
}
