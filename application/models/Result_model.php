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
        $this->has_one['system'] = array('foreign_model' => 'System_model', 'foreign_table' => 'pmp_system', 'foreign_key' => 'id', 'local_key' => 'system_id');
        $this->has_one['factory'] = array('foreign_model' => 'Factory_model', 'foreign_table' => 'pmp_factory', 'foreign_key' => 'id', 'local_key' => 'factory_id');
        $this->has_one['workshop'] = array('foreign_model' => 'Workshop_model', 'foreign_table' => 'pmp_workshop', 'foreign_key' => 'id', 'local_key' => 'workshop_id');
        $this->has_one['department'] = array('foreign_model' => 'Department_model', 'foreign_table' => 'pmp_department', 'foreign_key' => 'id', 'local_key' => 'department_id');
        $this->has_one['object'] = array('foreign_model' => 'Object_model', 'foreign_table' => 'pmp_object', 'foreign_key' => 'id', 'local_key' => 'object_id');

        $this->has_one['position'] = array('foreign_model' => 'Position_model', 'foreign_table' => 'pmp_position', 'foreign_key' => 'id', 'local_key' => 'position_id');
        $this->has_one['target'] = array('foreign_model' => 'Target_model', 'foreign_table' => 'pmp_target', 'foreign_key' => 'id', 'local_key' => 'target_id');
    }
    // protected function log_create($data)
    // {
    //     $text =   "'" . $this->session->userdata('username') . "' insert into table '$this->table' a new record($data)";
    //     $this->trail(1, 'insert', $this->table, $data, null, $text);
    //     return $data;
    // }
    // protected function log_delete($data)
    // {
    //     $text =   "'" . $this->session->userdata('username') . "' insert into table '$this->table' a new record($data)";
    //     $this->trail(1, 'insert', $this->table, $data, null, $text);
    //     return $data;
    // }
    function create_object($data)
    {
        $array = array(
            'note', 'type_bc', 'deleted', 'user_id', 'stt_in_day', 'target_id', 'object_id', 'position_id', 'department_id', 'area_id', 'system_id', 'factory_id', 'workshop_id', 'value', 'value_text', 'date', 'created_at', 'deleted_at'
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
    function get_date_has_data($params)
    {

        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $type = $params['type'];
        if ($object_id == 18 || $object_id == 19 || $object_id == 20)
            $where = "where deleted = 0 and object_id = $object_id and system_id = " . $this->db->escape($params['system_id']) . " and workshop_id = " . $this->db->escape($params['workshop_id']);
        else
            $where = "where deleted = 0 and object_id = $object_id and department_id = " . $this->db->escape($params['department_id']);
        if ($type == "Year") {
            $sql = "select YEAR(date) as value from pmp_result $where GROUP BY YEAR(date) ORDER BY date DESC";
        } else if ($type == "TwoYear") {
            $sql = "select CONCAT(YEAR(DATE),'-',YEAR(DATE_SUB(DATE, INTERVAL 1 YEAR))) AS value FROM pmp_result $where GROUP BY YEAR(DATE) ORDER BY DATE DESC";
        } else if ($type == "HalfYear") {
            $sql = "select CONCAT(YEAR(date),'-',FLOOR(QUARTER(DATE) / 3) + 1) as value from pmp_result $where GROUP BY CONCAT(YEAR(date),'-',FLOOR(QUARTER(DATE) / 3) + 1) ORDER BY date DESC";
        } else if ($type == "Quarter") {
            $sql = "select CONCAT(YEAR(date),'-',QUARTER(date)) as value from pmp_result $where GROUP BY CONCAT(YEAR(date),'-',QUARTER(date)) ORDER BY date DESC";
        } else if ($type == "Month") {
            $sql = "select DATE_FORMAT(date,'%Y-%m') as value from pmp_result $where GROUP BY DATE_FORMAT(date,'%Y-%m') ORDER BY date DESC";
        } else {
            return array();
        }
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }
    function chartdataEmployee($params)
    {
        $where = "WHERE a.deleted = 0 and a.area_id = " . $this->db->escape($params['area_id']) . " and a.employee_id = " . $this->db->escape($params['employee_id']) . " and a.target_id = " . $this->db->escape($params['target_id']) . "";
        if ($params['date_from_prev'] != "") {
            $where .= " AND a.date between '" . $params['date_from_prev'] . "' and '" . $params['date_to'] . "'";
        } else {
            $where .= " AND a.date between '" . $params['date_from'] . "' and '" . $params['date_to'] . "'";
        }
        $sql = "SELECT a.*,b.string_id as position_string_id FROM pmp_employee_result as a JOIN pmp_employee as b ON a.employee_id = b.id $where ORDER BY a.date ASC";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }
    function chartdata($params)
    {
        $where = "WHERE a.deleted = 0 and a.object_id = " . $this->db->escape($params['object_id']) . " and a.department_id = " . $this->db->escape($params['department_id']) . " and a.target_id = " . $this->db->escape($params['target_id']) . "";
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

        $sql = "SELECT a.* FROM pmp_limit as a $where ORDER BY day_effect DESC";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }
    function chartdatanuoc($params)
    {
        $where = "WHERE a.deleted = 0 and a.object_id = " . $this->db->escape($params['object_id']) . " and a.workshop_id = " . $this->db->escape($params['workshop_id']) . " and a.system_id = " . $this->db->escape($params['system_id']) . " and a.target_id = " . $this->db->escape($params['target_id']) . " AND a.type_bc = " . $this->db->escape($params['type_bc']);

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
    function chartdatanuoc_limit($params)
    {

        $where = "WHERE a.deleted = 0 and a.system_id = " . $this->db->escape($params['system_id']) . " and a.target_id = " . $this->db->escape($params['target_id']) . "";

        $sql = "SELECT a.* FROM pmp_limit as a $where ORDER BY day_effect DESC";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }
    function area_export($params)
    {
        $where = "WHERE a.deleted = 0 and a.workshop_id = " . $this->db->escape($params['workshop_id']) . " and c.object_id = " . $this->db->escape($params['object_id']);

        $where .= " AND a.date between '" . $params['date_from'] . "' and '" . $params['date_to'] . "'";

        $sql = "SELECT b.* FROM pmp_result as a JOIN pmp_area as b ON a.area_id = b.id JOIN pmp_position as c ON a.position_id = c.id $where GROUP BY a.area_id ORDER BY b.name ASC";

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
    function chart_data_work($params)
    {
        $this->load->model("target_model");
        $results = array('labels' => array(), 'datasets' => array());
        $data = $this->chartdata($params);
        $data_limit = $this->chartdata_limit($params);
        $target = $this->target_model->get($params['target_id']);
        //if ($params['target_id'] == 4) {

        //    echo "<pre>";
        //    print_r($data);
        //    die();
        //}
        $max = 1;
        $labels = array();
        // $labels[] = array()
        $position_list = array();
        $datatmp = array();

        $datasets = array();
        // echo "<pre>";
        // print_r($params);
        // die();
        $annotations = array(
            'labels' => array()
        );
        //array(
        //    'point' => "max",
        //    'text' => "Max"
        //),
        $lineAtIndex = null;
        $list_limit = array();
        $list_id_limit =  array();
        foreach ($data as $key => $row) {
            $date = $row->date;

            $limit = array_values(array_filter($data_limit, function ($item) use ($date) {
                return $item['day_effect'] <= $date;
            }));
            if (isset($limit[0]) && !in_array($limit[0]['id'], $list_id_limit)) {
                $list_id_limit[] = $limit[0]['id'];
                $limit[0]['day_effect_to'] = date("Y-m-d");
                if (count($list_limit) > 0) {
                    $list_limit[count($list_limit) - 1]['day_effect_to'] = $limit[0]['day_effect'];
                }
                $list_limit[] = $limit[0];
            }
            if (!in_array($date, $labels)) {
                $labels[] = $date;
            }
            $position = $row->position_string_id;
            $value = $row->value;
            ///CHECK MỐC 
            if ($lineAtIndex === null && $params['date_from_prev'] != "" && $row->date >= $params['date_from']) {
                $lineAtIndex = count($labels) - 1;
            }
            if (!in_array($position, $position_list)) {
                $position_list[] = $position;
                $datasets[] = array(
                    'name' => $position,
                    'data' => array(),
                );
            }
            $datatmp[$date][$position] = $value;
        }
        if (count($list_limit)) {
            $alert_limit = array(
                'marker' => array(
                    'enabled' => false
                ),
                'data_limit' => $limit,
                'color' => 'orange',
                'index' => $key,
                'name' => "Alert Limit",
                'data' => array(),
            );
            $action_limit = array(
                'marker' => array(
                    'enabled' => false
                ),
                'data_limit' => $limit,
                'color' => 'red',
                'index' => $key,
                'name' => "Action Limit",
                'data' => array(),
            );
            array_unshift($datasets, $action_limit, $alert_limit);
            foreach ($list_limit as $key => $limit) {
                $alert_limit = array(
                    'marker' => array(
                        'enabled' => false
                    ),
                    'data_limit' => $limit,
                    'color' => 'orange',
                    'index' => $key,
                    'name' => "Alert",
                    'data' => array(),
                );
                $action_limit = array(
                    'marker' => array(
                        'enabled' => false
                    ),
                    'data_limit' => $limit,
                    'color' => 'red',
                    'index' => $key,
                    'name' => "Action",
                    'data' => array(),
                );
                // if ($key > 0) {
                $alert_limit['showInLegend'] = false;
                $action_limit['showInLegend'] = false;
                // }
                array_unshift($datasets, $action_limit, $alert_limit);

                //
                $annotations['labels'][] = array(
                    'point' => "alert_" . $limit['id'],
                    'text' => $limit['alert_limit'],
                    'backgroundColor' => 'orange'
                );
                $annotations['labels'][] = array(
                    'point' => "action_" . $limit['id'],
                    'text' => $limit['action_limit'],
                    'backgroundColor' => 'red'
                );
            }
        }
        //echo "<pre>";
        //print_r($datatmp);
        //die();
        $limit_prev = null;
        foreach ($labels as $key => &$date_real) {
            $date = date("d/m/y", strtotime($date_real));

            foreach ($datasets as &$position) {
                $position_string_id = $position['name'];
                $value = isset($datatmp[$date_real][$position_string_id]) ? (float) $datatmp[$date_real][$position_string_id] : null;
                if ($position_string_id == "Action") {
                    $limit = $position['data_limit'];
                    $value = isset($limit['action_limit']) && isset($limit['day_effect']) && $limit['day_effect'] <= $date_real && $limit['day_effect_to'] >= $date_real ? (float) $limit['action_limit'] : null;
                    $position['data'][] = $value;
                } else if ($position_string_id == "Alert") {
                    $limit = $position['data_limit'];
                    $value = isset($limit['alert_limit']) && isset($limit['day_effect']) && $limit['day_effect'] <= $date_real && $limit['day_effect_to'] >= $date_real  ? (float) $limit['alert_limit'] : null;
                    $position['data'][] = $value;
                } elseif ($position_string_id == "Alert Limit" || $position_string_id == "Action Limit") {
                    $value = null;
                    $position['data'][] = $value;
                } else {
                    $position['data'][] = $value;
                }
                if ($value > $max) {
                    $max = $value;
                }

                //                $index = array_search($position_string_id, $position_list);
            }
            $date_real = $date;
        }
        //$yAxis_title = $target->unit;
        //$title = $params['title'];
        //$subtitle = $params['subtitle'];
        $results = array(
            'categories' => $labels,
            'series' => $datasets,
        );
        return $results;
    }
    function chart_datav2($params)
    {
        $this->load->model("target_model");
        $results = array('labels' => array(), 'datasets' => array());
        $data = $this->chartdata($params);
        $data_limit = $this->chartdata_limit($params);
        $target = $this->target_model->get($params['target_id']);
        //if ($params['target_id'] == 4) {

        //    echo "<pre>";
        //    print_r($data);
        //    die();
        //}
        $max = 1;
        $labels = array();
        // $labels[] = array()
        $position_list = array();
        $datatmp = array();

        $datasets = array();
        // echo "<pre>";
        // print_r($params);
        // die();
        $annotations = array(
            'labels' => array()
        );
        //array(
        //    'point' => "max",
        //    'text' => "Max"
        //),
        $lineAtIndex = null;
        $list_limit = array();
        $list_id_limit =  array();
        foreach ($data as $key => $row) {
            $date = $row->date;

            $limit = array_values(array_filter($data_limit, function ($item) use ($date) {
                return $item['day_effect'] <= $date;
            }));
            if (isset($limit[0]) && !in_array($limit[0]['id'], $list_id_limit)) {
                $list_id_limit[] = $limit[0]['id'];
                $limit[0]['day_effect_to'] = date("Y-m-d");
                if (count($list_limit) > 0) {
                    $list_limit[count($list_limit) - 1]['day_effect_to'] = $limit[0]['day_effect'];
                }
                $list_limit[] = $limit[0];
            }
            if (!in_array($date, $labels)) {
                $labels[] = $date;
            }
            $position = $row->position_string_id;
            $value = $row->value;
            ///CHECK MỐC 
            if ($lineAtIndex === null && $params['date_from_prev'] != "" && $row->date >= $params['date_from']) {
                $lineAtIndex = count($labels) - 1;
            }
            if (!in_array($position, $position_list)) {
                $position_list[] = $position;
                $datasets[] = array(
                    'name' => $position,
                    'data' => array(),
                );
            }
            $datatmp[$date][$position] = $value;
        }
        if (count($list_limit)) {
            $alert_limit = array(
                'marker' => array(
                    'enabled' => false
                ),
                'data_limit' => $limit,
                'color' => 'orange',
                'index' => $key,
                'name' => "Alert Limit",
                'data' => array(),
            );
            $action_limit = array(
                'marker' => array(
                    'enabled' => false
                ),
                'data_limit' => $limit,
                'color' => 'red',
                'index' => $key,
                'name' => "Action Limit",
                'data' => array(),
            );
            array_unshift($datasets, $action_limit, $alert_limit);
            foreach ($list_limit as $key => $limit) {
                $alert_limit = array(
                    'marker' => array(
                        'enabled' => false
                    ),
                    'data_limit' => $limit,
                    'color' => 'orange',
                    'index' => $key,
                    'name' => "Alert",
                    'data' => array(),
                );
                $action_limit = array(
                    'marker' => array(
                        'enabled' => false
                    ),
                    'data_limit' => $limit,
                    'color' => 'red',
                    'index' => $key,
                    'name' => "Action",
                    'data' => array(),
                );
                // if ($key > 0) {
                $alert_limit['showInLegend'] = false;
                $action_limit['showInLegend'] = false;
                // }
                array_unshift($datasets, $action_limit, $alert_limit);

                //
                $annotations['labels'][] = array(
                    'point' => "alert_" . $limit['id'],
                    'text' => $limit['alert_limit'],
                    'backgroundColor' => 'orange'
                );
                $annotations['labels'][] = array(
                    'point' => "action_" . $limit['id'],
                    'text' => $limit['action_limit'],
                    'backgroundColor' => 'red'
                );
            }
        }
        //echo "<pre>";
        //print_r($datatmp);
        //die();
        $limit_prev = null;
        foreach ($labels as $key => &$date_real) {
            $date = date("d/m/y", strtotime($date_real));

            foreach ($datasets as &$position) {
                $position_string_id = $position['name'];
                $value = isset($datatmp[$date_real][$position_string_id]) ? (float) $datatmp[$date_real][$position_string_id] : null;
                if ($position_string_id == "Action") {

                    $limit = $position['data_limit'];
                    $value = isset($limit['action_limit']) && isset($limit['day_effect']) && $limit['day_effect'] <= $date_real && $limit['day_effect_to'] >= $date_real ? (float) $limit['action_limit'] : null;
                    $position['data'][] = array('y' => $value, 'id' => "action_" . $limit['id']);
                } else if ($position_string_id == "Alert") {
                    $limit = $position['data_limit'];
                    $value = isset($limit['alert_limit']) && isset($limit['day_effect']) && $limit['day_effect'] <= $date_real && $limit['day_effect_to'] >= $date_real  ? (float) $limit['alert_limit'] : null;
                    $position['data'][] = array('y' => $value, 'id' => "alert_" . $limit['id']);
                } elseif ($position_string_id == "Alert Limit" || $position_string_id == "Action Limit") {
                    $value = null;
                    $position['data'][] = $value;
                } else {
                    $position['data'][] = $value;
                }
                if ($value > $max) {
                    $max = $value;
                }

                //                $index = array_search($position_string_id, $position_list);
            }
            $date_real = $date;
        }
        $yAxis_title = $target->unit;
        $title = $params['title'];
        $subtitle = $params['subtitle'];
        $results = array(
            'title' => array('text' => $title),
            'subtitle' => array('text' => $subtitle, 'style' => array("fontSize" => 18)),
            'plotOptions' => array(
                'series' => array(
                    'connectNulls' => true,
                )
            ),
            'annotations' => array(
                $annotations
            ),
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
                'allowDecimals' => false,
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
                'endOnTick' => true
            ),
            'series' => $datasets,
        );
        return $results;
    }

    function chart_data_nuoc($params)
    {
        $this->load->model("target_model");
        $results = array('labels' => array(), 'datasets' => array());
        $data = $this->chartdatanuoc($params);
        $data_limit = $this->chartdatanuoc_limit($params);
        $target = $this->target_model->get($params['target_id']);
        // echo "<pre>";
        // print_r($data);
        // die();
        $max = 1;
        $labels = array();
        // $labels[] = array()
        $position_list = array();
        $datatmp = array();

        $datasets = array();
        // echo "<pre>";
        // print_r($params);
        // die();

        $annotations = array(
            'labelOptions' => array(
                'y' => 15,
                'verticalAlign' => 'bottom',
                'distance' => 25
            ),
            'labels' => array()
        );
        $lineAtIndex = null;
        $list_limit = array();
        $list_id_limit =  array();
        foreach ($data as $key => $row) {
            $date = $row->date;

            $limit = array_values(array_filter($data_limit, function ($item) use ($date) {
                return $item['day_effect'] <= $date;
            }));
            if (isset($limit[0]) && !in_array($limit[0]['id'], $list_id_limit)) {
                $list_id_limit[] = $limit[0]['id'];
                $limit[0]['day_effect_to'] = date("Y-m-d");
                if (count($list_limit) > 0) {
                    $list_limit[count($list_limit) - 1]['day_effect_to'] = $limit[0]['day_effect'];
                }
                $list_limit[] = $limit[0];
            }
            $labels[] = $date;
            $position = $row->position_string_id;
            $value = $row->value;
            ///CHECK MỐC 
            if ($lineAtIndex === null && $params['date_from_prev'] != "" && $row->date >= $params['date_from']) {
                $lineAtIndex = count($labels) - 1;
            }
            if (!in_array($position, $position_list)) {
                $position_list[] = $position;
                $datasets[] = array(
                    'name' => $position,
                    'data' => array(),
                );
            }
            $datatmp[$key][$position] = $value;
        }

        if (count($list_limit)) {
            $alert_limit = array(
                'marker' => array(
                    'enabled' => false
                ),
                'data_limit' => $limit,
                'color' => 'orange',
                'index' => $key,
                'name' => "Alert Limit",
                'data' => array(),
            );
            $action_limit = array(
                'marker' => array(
                    'enabled' => false
                ),
                'data_limit' => $limit,
                'color' => 'red',
                'index' => $key,
                'name' => "Action Limit",
                'data' => array(),
            );
            array_unshift($datasets, $action_limit, $alert_limit);
            foreach ($list_limit as $key => $limit) {
                $alert_limit = array(
                    'marker' => array(
                        'enabled' => false
                    ),
                    'data_limit' => $limit,
                    'color' => 'orange',
                    'index' => $key,
                    'name' => "Alert",
                    'data' => array(),
                );
                $action_limit = array(
                    'marker' => array(
                        'enabled' => false
                    ),
                    'data_limit' => $limit,
                    'color' => 'red',
                    'index' => $key,
                    'name' => "Action",
                    'data' => array(),
                );
                // if ($key > 0) {
                $alert_limit['showInLegend'] = false;
                $action_limit['showInLegend'] = false;
                // }
                array_unshift($datasets, $action_limit, $alert_limit);
                //
                $annotations['labels'][] = array(
                    'point' => "alert_" . $limit['id'],
                    'text' => $limit['alert_limit'],
                    'backgroundColor' => 'orange'
                );
                $annotations['labels'][] = array(
                    'point' => "action_" . $limit['id'],
                    'text' => $limit['action_limit'],
                    'backgroundColor' => 'red'
                );
            }
        }
        // echo "<pre>";
        // print_r($list_limit);
        // die();
        $limit_prev = null;
        foreach ($labels as $key => &$date_real) {
            $date = date("d/m/y", strtotime($date_real));

            foreach ($datasets as &$position) {
                $position_string_id = $position['name'];
                $value = isset($datatmp[$key][$position_string_id]) ? (float) $datatmp[$key][$position_string_id] : null;
                if ($position_string_id == "Action") {

                    $limit = $position['data_limit'];
                    $value = isset($limit['action_limit']) && isset($limit['day_effect']) && $limit['day_effect'] <= $date_real && $limit['day_effect_to'] >= $date_real ? (float) $limit['action_limit'] : null;
                    $position['data'][] = array('y' => $value, 'id' => "action_" . $limit['id']);
                } else if ($position_string_id == "Alert") {
                    $limit = $position['data_limit'];
                    $value = isset($limit['alert_limit']) && isset($limit['day_effect']) && $limit['day_effect'] <= $date_real && $limit['day_effect_to'] >= $date_real  ? (float) $limit['alert_limit'] : null;
                    $position['data'][] = array('y' => $value, 'id' => "alert_" . $limit['id']);
                } elseif ($position_string_id == "Alert Limit" || $position_string_id == "Action Limit") {
                    $value = null;
                    $position['data'][] = $value;
                } else {
                    $position['data'][] = $value;
                }
                if ($value > $max) {
                    $max = $value;
                }
                //                $index = array_search($position_string_id, $position_list);
            }
            $date_real = $date;
        }
        $yAxis_title = $target->unit;
        $title = $params['title'];
        $subtitle = $params['subtitle'];
        $results = array(
            'title' => array('text' => $title),
            'subtitle' => array('text' => $subtitle, 'style' => array("fontSize" => 18)),
            'plotOptions' => array(
                'series' => array(
                    'connectNulls' => true
                )
            ),
            'annotations' => array(
                $annotations
            ),
            'xAxis' => array(
                'title' => array(
                    'align' => 'high',
                    'offset' => 0,
                    'text' => "Date",
                    'rotation' => 0,
                    'x' => 50
                ),
                //'labels'=>array(

                //    'rotation' => -90,
                //),
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

                'showLastLabel' => true,
                'showLastLabel' => true,
                'min' => 0,
                'max' => $max + 1,
                'startOnTick' => false,
                'endOnTick' => true
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
    function get_data_table_by_target($target_list, $params)
    {
        $subsql = "";
        $list_id = array();
        foreach ($target_list as $target) {
            $list_id[] =  $target->id;
            if ($target->type_data == "float" || $target->type_data == "boolean") {
                $subsql .= ",SUM(IF(a.target_id = $target->id,value,NULL)) as '$target->id'";
            } else {
                $subsql .= ",MAX(IF(a.target_id = $target->id,value_text,NULL)) as '$target->id'";
            }
        }
        $where = "WHERE a.deleted = 0 and a.target_id IN (" . implode(",", $list_id) . ")";
        if (isset($params['position_id'])) {
            $where .= " AND a.position_id = " . $params['position_id'];
        }
        if (isset($params['object_id'])) {
            $where .= " AND a.object_id = " . $params['object_id'];
        }
        $where .= " AND date between '" . $params['date_from'] . "' and '" . $params['date_to'] . "'";
        $sql = "SELECT date $subsql FROM
                    pmp_result as a 
                $where  
                GROUP BY date,stt_in_day ORDER BY DATE ASC ";

        // echo "<pre>";
        // print_r($sql);
        // die();
        $query = $this->db->query($sql);
        $result = $query->result_array();
        return $result;
    }

    function get_data_table_by_target_minmax($target_list, $object_id, $position_id, $date_from, $date_to)
    {
        $subsql = "";
        $list_id = array();
        foreach ($target_list as $target) {
            $list_id[] =  $target->id;
            if ($target->type_data == "float") {
                $subsql .= ",MIN(IF(target_id = $target->id,value,NULL)) as min_$target->id,MAX(IF(target_id = $target->id,value,NULL)) as max_$target->id";
            } else {
                $subsql .= ",MIN(IF(target_id = $target->id,value_text,NULL)) as min_$target->id,MAX(IF(target_id = $target->id,value_text,NULL)) as max_$target->id";
            }
        }
        $where = "WHERE deleted = 0 and target_id IN (" . implode(",", $list_id) . ") AND object_id = $object_id AND position_id = $position_id";
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
    function get_data_minmax_v2($position_list, $date_from, $date_to)
    {
        $subsql = "";
        $list_id = array();
        foreach ($position_list as $position) {
            $list_id[] =  $position->id;
            $subsql .= ",MIN(IF(position_id = $position->id,value,NULL)) as min_$position->string_id,MAX(IF(position_id = $position->id,value,NULL)) as max_$position->string_id";
        }
        $where = "WHERE deleted = 0 and position_id IN (" . implode(",", $list_id) . ")";
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

    function max_stt_have_target_in_day($position, $date, $target_id = 0)
    {
        $sql = "SELECT MAX(stt_in_day) as max_stt FROM
                    pmp_result 
                WHERE position_id = $position and date = '$date' and deleted = 0 and target_id = $target_id";

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
    function max_stt_in_day($position, $date, $except_id = 0)
    {
        $sql = "SELECT MAX(stt_in_day) as max_stt FROM
                    pmp_result 
                WHERE position_id = $position and date = '$date' and deleted = 0 and id <> $except_id";

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
    function set_value_export($params)
    {
        if ($params['type'] == "Month") {
            $this->where('type_bc', "Month");
        } elseif ($params['type'] == "Quarter") {
            $this->where('type_bc', "Quarter");
        } elseif ($params['type'] == "HalfYear") {
            $this->where('type_bc', "HalfYear");
        } elseif ($params['type'] == "TwoYear") {
            $this->where('type_bc', "TwoYear");
        }
        $this->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $params['workshop_id'], 'deleted' => 0, 'object_id' => $params['object_id']));
        return $this;
    }
}
