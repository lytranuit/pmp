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
            'deleted', 'stt_in_day', 'target_id', 'object_id', 'position_id', 'department_id', 'area_id', 'factory_id', 'workshop_id', 'value', 'date', 'created_at', 'deleted_at'
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
    function dateIn()
    {
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

    function chartdata_limit($params)
    {
        $where = "WHERE a.deleted = 0 and a.area_id = " . $this->db->escape($params['area_id']) . " and a.target_id = " . $this->db->escape($params['target_id']) . "";

        $sql = "SELECT a.* FROM pmp_limit as a $where";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $result = count($result) ? $result[0] : array();
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

    function chart_data($params)
    {
        $results = array('labels' => array(), 'datasets' => array());
        $data = $this->chartdata($params);
        $data_limit = $this->chartdata_limit($params);
        // echo "<pre>";
        // print_r($data_limit);
        // die();
        $labels = array();
        // $labels[] = array()
        $position_list = array();
        $datatmp = array();
        $datasets = array();
        $datasets[] = array(
            'backgroundColor' => 'red',
            'borderColor' => 'red',
            'label' => "Action Limit",
            'data' => array(),
            'pointRadius' => 0,
            'fill' => 'false'
        );
        $datasets[] = array(
            'backgroundColor' => 'orange',
            'borderColor' => 'orange',
            'label' => "Alert Limit",
            'data' => array(),
            'pointRadius' => 0,
            'fill' => 'false'
        );
        // echo "<pre>";
        // print_r($params);
        // die();
        $lineAtIndex = null;
        foreach ($data as $row) {
            $date = $row->date;
            $position = $row->position_string_id;
            $value = $row->value;
            if (!in_array($date, $labels)) {
                $labels[] = $date;
                ///CHECK MỐC 
                if ($lineAtIndex === null && $params['date_from_prev'] != "" && $date >= $params['date_from']) {
                    $lineAtIndex = count($labels) - 1;
                }
            }
            if (!in_array($position, $position_list)) {
                $position_list[] = $position;
                $color = getRandomColor();
                $datasets[] = array(
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'label' => $position,
                    'data' => array(),
                    'fill' => 'false'
                );
            }
            $datatmp[$date][$position] = $value;
        }
        foreach ($labels as $date) {
            foreach ($datasets as &$position) {
                $position_string_id = $position['label'];
                $value = isset($datatmp[$date][$position_string_id]) ? $datatmp[$date][$position_string_id] : 0;
                if ($position_string_id == "Action Limit") {
                    $value = isset($data_limit['action_limit']) ? $data_limit['action_limit'] : 0;
                } else if ($position_string_id == "Alert Limit") {
                    $value = isset($data_limit['alert_limit']) ? $data_limit['alert_limit'] : 0;
                }
                $position['data'][] = $value;
                //                $index = array_search($position_string_id, $position_list);
            }
        }
        $results = array(
            'labels' => $labels,
            'datasets' => $datasets,
            'lineAtIndex' => $lineAtIndex
        );
        return $results;
    }
    function chart_datav2($params)
    {
        $results = array('labels' => array(), 'datasets' => array());
        $data = $this->chartdata($params);
        $data_limit = $this->chartdata_limit($params);
        $department = $params['department'];
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
            'name' => "Action Limit",
            'data' => array(),
        );
        $datasets[] = array(
            'marker' => array(
                'enabled' => false
            ),
            'name' => "Alert Limit",
            'data' => array(),
        );
        // echo "<pre>";
        // print_r($params);
        // die();
        $lineAtIndex = null;
        foreach ($data as $row) {
            $date = $row->date;
            $position = $row->position_string_id;
            $value = $row->value;
            if (!in_array($date, $labels)) {
                $labels[] = $date;
                ///CHECK MỐC 
                if ($lineAtIndex === null && $params['date_from_prev'] != "" && $date >= $params['date_from']) {
                    $lineAtIndex = count($labels) - 1;
                }
            }
            if (!in_array($position, $position_list)) {
                $position_list[] = $position;
                $color = getRandomColor();
                $datasets[] = array(
                    'name' => $position,
                    'data' => array(),
                );
            }
            $datatmp[$date][$position] = $value;
        }
        foreach ($labels as $date) {
            foreach ($datasets as &$position) {
                $position_string_id = $position['name'];
                $value = isset($datatmp[$date][$position_string_id]) ? (float) $datatmp[$date][$position_string_id] : 0;
                if ($position_string_id == "Action Limit") {
                    $value = isset($data_limit['action_limit']) ? (float) $data_limit['action_limit'] : 0;
                } else if ($position_string_id == "Alert Limit") {
                    $value = isset($data_limit['alert_limit']) ? (float) $data_limit['alert_limit'] : 0;
                }
                if ($value > $max) {
                    $max = $value;
                }
                $position['data'][] = $value;
                //                $index = array_search($position_string_id, $position_list);
            }
        }
        $title = $department->name;
        if ($department->type == 3) {
            $list = explode("_", $department->string_id);
            $id = $list[1];
            $title = $department->name . " / " . $id;
        }

        $results = array(
            'title' => array('text' => $title),
            'xAxis' => array(
                'categories' => $labels,
                'plotLines' => array(array(
                    'color' => '#FF0000', // Red
                    'width' => 2,
                    'value' => $lineAtIndex
                ))
            ),
            'yAxis' => array(
                'min' => 0,
                'max' => $max,
                'startOnTick' => false,
                'endOnTick' => false
            ),
            'series' => $datasets,
        );
        return $results;
    }
    function get_data_table_v2($position_list, $params)
    {
        $subsql = "";
        $list_id = array();
        foreach ($position_list as $position) {
            $list_id[] =  $position->id;
            $subsql .= ",SUM(IF(position_id = $position->id,value,NULL)) as $position->string_id";
        }
        $where = "WHERE deleted = 0 and position_id IN (" . implode(",", $list_id) . ")";
        $where .= " AND date between '" . $params['date_from'] . "' and '" . $params['date_to'] . "'";
        $sql = "SELECT date $subsql FROM
                    pmp_result 
                $where  
                GROUP BY date,stt_in_day ";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }
    function get_data_minmax_v2($position_list, $date_from, $date_to)
    {
        $subsql = "";
        $list_id = array();
        foreach ($position_list as $position) {
            $list_id[] =  $position->id;
            $subsql .= ",MIN(IF(position_id = $position->id,value,0)) as min_$position->string_id,MAX(IF(position_id = $position->id,value,0)) as max_$position->string_id";
        }
        $where = "WHERE deleted = 0 and department_id IN (" . implode(",", $list_id) . ")";
        if ($date_from == "") {
            $date_from = $date_to = date("Y-m-d");
        }
        $where .= " AND date between '" . $date_from . "' and '" . $date_to . "'";
        $sql = "SELECT date $subsql FROM
                    pmp_result 
                $where";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $result = isset($result[0]) ? $result[0] : array();
        return $result;
    }
    function get_data_table($department_id, $position_list, $params)
    {
        $subsql = "";
        foreach ($position_list as $position) {
            $subsql .= ",SUM(IF(position_id = $position->id,value,NULL)) as $position->string_id";
        }
        $where = "WHERE deleted = 0 and department_id IN ($department_id)";
        $where .= " AND date between '" . $params['date_from'] . "' and '" . $params['date_to'] . "'";
        $sql = "SELECT date $subsql FROM
                    pmp_result 
                $where  
                GROUP BY DATE ";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }
    function get_data_minmax($department_id, $position_list, $date_from, $date_to)
    {
        $subsql = "";
        foreach ($position_list as $position) {
            $subsql .= ",MIN(IF(position_id = $position->id,value,0)) as min_$position->string_id,MAX(IF(position_id = $position->id,value,0)) as max_$position->string_id";
        }
        $where = "WHERE deleted = 0 and department_id IN ($department_id)";
        if ($date_from == "") {
            $date_from = $date_to = date("Y-m-d");
        }
        $where .= " AND date between '" . $date_from . "' and '" . $date_to . "'";
        $sql = "SELECT date $subsql FROM
                    pmp_result 
                $where";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $result = isset($result[0]) ? $result[0] : array();
        return $result;
    }
    function max_stt_in_day($position, $date)
    {
        $sql = "SELECT MAX(stt_in_day) as max_stt FROM
                    pmp_result 
                WHERE position_id = $position and date = '$date' and deleted = 0";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $result = isset($result[0]['max_stt']) ? (int) $result[0]['max_stt'] + 1 : 1;

        // echo "<pre>";
        // print_r($result);
        // die();
        return $result;
    }
}
