<?php

use function GuzzleHttp\json_decode;

class Dashboard extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->data['is_admin'] = $this->ion_auth->is_admin();
        $this->data['userdata'] = $this->session->userdata();
        $this->data['template'] = "admin";
        $this->data['title'] = "Admin";
        $version = $this->config->item("version");
        $this->data['stylesheet_tag'] = array(
            base_url() . "public/assets/css/main.css?v=" . $version,
            base_url() . "public/assets/css/custom.css?v=" . $version,
            base_url() . "public/admin/vendor/fonts/fontawesome/css/fontawesome-all.css"
        );
        $this->data['javascript_tag'] = array(
            base_url() . 'public/assets/scripts/jquery.min.js',
            base_url() . "public/assets/scripts/main.js?v=" . $version,
            base_url() . "public/lib/jquery-validation/jquery.validate.js",
            base_url() . "public/lib/mustache/mustache.min.js",
            base_url() . "public/admin/vendor/inputmask/js/jquery.inputmask.bundle.js",
            base_url() . "public/admin/libs/js/moment.js",
            base_url() . "public/assets/scripts/jquery.cookies.2.2.0.min.js",
            base_url() . "public/lib/canvg/rgbcolor.js",
            base_url() . "public/lib/canvg/canvg.min.js",
            "https://code.highcharts.com/highcharts.js",
            "https://code.highcharts.com/modules/exporting.js",
            base_url() . "public/assets/scripts/custom.js?v=" . $version
        );
    }

    public function _remap($method, $params = array())
    {
        if (!method_exists($this, $method)) {
            show_404();
        }
        $group = array('admin', 'manager');

        if (!$this->ion_auth->in_group($group)) {
            //redirect them to the login page
            redirect("index/login", "refresh");
        } elseif ($this->has_right($method, $params)) {
            $this->$method($params);
        } else {
            show_404();
        }
    }

    private function has_right($method, $params = array())
    {

        /*
         * SET PERMISSION
         */
        //        $role_user = $this->session->userdata('role');
        //        $this->user_model->set_permission($role_user);
        //
        //        /* Change method */
        //        switch ($method) {
        //            case 'updatetintuc':
        //                $method = 'edittintuc';
        //                break;
        //            case 'editmenu':
        //                $method = 'quanlymenu';
        //                break;
        //            case 'updatenoibat':
        //                $method = 'editnoibat';
        //                break;
        //            case 'updatenoibo':
        //                $method = 'editnoibo';
        //                break;
        //            case 'updateproduct':
        //                $method = 'editproduct';
        //                break;
        //            case 'viewtin':
        //                $method = 'quanlynoibo';
        //                break;
        //            case 'updatepage':
        //                $method = "editpage";
        //                break;
        //            case 'slider':
        //            case 'saveslider':
        //            case 'gioithieu':
        //            case 'savegioithieu':
        //            case 'quanlycategory':
        //            case 'themcategory':
        //            case 'editcategory':
        //            case 'updatecategory':
        //            case 'removecategory':
        //            case 'quanlyclient':
        //            case 'themclient':
        //            case 'editclient':
        //            case 'updateclient':
        //            case 'removeclient':
        //            case 'quanlyhappy':
        //            case 'themhappy':
        //            case 'edithappy':
        //            case 'updatehappy':
        //            case 'removehappy':
        //                $method = 'trangchu';
        //                break;
        //        }
        //        if (has_permission($method) && !is_permission($method)) {
        //            return false;
        //        }
        /* Tin đăng check */
        //        $fun_tin = array(
        //            "edittin",
        //            "activate_tin",
        //            "deactivate_tin",
        //            "remove_tin",
        //        );
        //        if (in_array($method, $fun_tin)) {
        //            $id = $params[0];
        //            $id_user = $this->session->userdata('user_id');
        //            $this->load->model("tin_model");
        //            $tin = $this->tin_model->where(array('deleted' => 0, 'id_user' => $id_user, 'id_tin' => $id))->as_array()->get_all();
        //            if (!count($tin)) {
        //                return false;
        //            }
        //        }
        return true;
    }

    public function index()
    {
        /////// trang ca nhan
        $this->load->model("factory_model");
        $this->data['factory'] = $this->factory_model->where(array('deleted' => 0))->as_object()->get_all();

        $this->load->model("workshop_model");
        $factory_id = isset($this->data['factory'][0]->id) ? $this->data['factory'][0]->id : 0;
        $this->data['workshop'] = $this->workshop_model->where(array('deleted' => 0, 'factory_id' => $factory_id))->as_object()->get_all();

        $this->load->model("area_model");
        $workshop_id = isset($this->data['workshop'][0]->id) ? $this->data['workshop'][0]->id : 0;
        $this->data['area'] = $this->area_model->where(array('deleted' => 0, 'workshop_id' => $workshop_id))->as_object()->get_all();

        $this->load->model("department_model");
        $area_id = isset($this->data['area'][0]->id) ? $this->data['area'][0]->id : 0;
        $this->data['department'] = $this->department_model->where(array('deleted' => 0, 'area_id' => $area_id))->as_object()->get_all();




        $this->load->model("target_model");
        $this->data['target'] = $this->target_model->where(array('deleted' => 0))->as_object()->get_all();
        load_daterangepicker($this->data);
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    public function view()
    {
        /////// trang ca nhan
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        if ($object_id == 3) {
            $this->load->model("employeeresult_model");
            $this->data['factory'] = $this->employeeresult_model->where(array('deleted' => 0))->with_factory()->group_by("factory_id")->as_object()->get_all();
            $this->data['factory'] = array_map(function ($item) {
                return $item->factory;
            }, $this->data['factory']);
        } else {
            $this->load->model("result_model");
            $this->data['factory'] = $this->result_model->where(array('deleted' => 0, 'object_id' => $object_id))->with_factory()->group_by("factory_id")->as_object()->get_all();
            $this->data['factory'] = array_map(function ($item) {
                return $item->factory;
            }, $this->data['factory']);
        }
        $this->data['object_id'] = $object_id;
        load_daterangepicker($this->data);
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    public function savechart()
    {
        $this->load->model("report_model");
        $this->load->model("workshop_model");
        $this->load->model("employeeresult_model");
        $this->load->model("result_model");
        $this->load->model("limit_model");

        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $workshop_id = $this->input->get('workshop_id', TRUE);
        $type = $this->input->get('type', TRUE);
        $selector = $this->input->get('selector', TRUE);
        $daterange = $this->input->get('daterange', TRUE);
        if ($type == "Custom") {
            $selector = $daterange;
        }
        $data_up = array(
            'type' => $type,
            'selector' => $selector,
            'workshop_id' => $workshop_id,
            'object_id' => $object_id,
            'status' => 1,
            'date' => date("Y-m-d H:i:s"),
            'user_id' => $this->data['userdata']['user_id']
        );
        $this->report_model->insert($data_up);
        $params = array(
            'type' => $type,
            'selector' => $selector,
            'daterange' => $daterange,
            'workshop_id' => $workshop_id
        );
        $params = input_params($params);
        $params['object_id'] = $object_id;
        $this->data['params'] = $params;

        if ($this->data['object_id'] == 3) {
            $department_list = $this->employeeresult_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0))->with_employee()->group_by(array("employee_id", "area_id"))->get_all();
            foreach ($department_list as $row) {
                $employee = $row->employee;
                $area_id = $row->area_id;
                $target_id = $row->target_id;
                $params['area_id'] = $area_id;
                $params['employee_id'] = $employee->id;
                $params['target_id'] = $target_id;
                $title = "Biểu đồ xu hướng vi sinh nhân viên $employee->name ($employee->string_id)";
                $subtitle = "Trend chart of microbiological monitoring of Personnel $employee->name ($employee->string_id)";

                $params['title'] = $title;
                $params['subtitle'] = $subtitle;
                $data = $this->employeeresult_model->chart_datav2($params);
                $row->department = $employee;
                $row->data = $data;
                $results[] = $row;
            }
        } else {
            $department_list = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->with_department()->with_target()->group_by(array("department_id", "target_id"))->get_all();
            foreach ($department_list as $row) {
                $department = $row->department;
                $area_id = $department->area_id;
                $target = $row->target;
                $params['area_id'] = $area_id;
                $params['department_id'] = $department->id;
                $params['target_id'] = $target->id;
                $title = "Trend chart of microbiological monitoring";
                $subtitle = "($target->name_en method) $department->name_en ($department->string_id)";

                $params['title'] = $title;
                $params['subtitle'] = $subtitle;
                $data = $this->result_model->chart_datav2($params);

                $row->data = $data;
                $results[] = $row;
            }
        }

        // echo "<pre>";
        // print_r($results);
        // die();
        $this->data['results'] = $results;
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }
    public function chartdatav3()
    {

        $this->load->model("employeeresult_model");
        $this->load->model("result_model");
        $this->load->model("limit_model");
        $this->load->model("employee_model");
        $this->load->model("department_model");

        $department_id = $this->input->get('department_id', TRUE);
        $area_id = $this->input->get('area_id', TRUE);
        $type = $this->input->get('type', TRUE);
        $selector = $this->input->get('selector', TRUE);
        $daterange = $this->input->get('daterange', TRUE);
        $params = array(
            'type' => $type,
            'selector' => $selector,
            'daterange' => $daterange
        );
        $params = input_params($params);
        if (!is_numeric($department_id)) {
            echo json_encode(array());
            die();
        }
        if ($this->data['object_id'] == 3) {
            $department = $this->employee_model->where(array('id' => $department_id))->as_object()->get();
            $params['employee_id'] = $department_id;
        } else {
            $department = $this->department_model->where(array('id' => $department_id))->as_object()->get();
            $params['department_id'] = $department_id;
        }
        // echo "<pre>";
        // print_r($department);
        // die();
        // $area_id = $department->area_id;
        $params['area_id'] = $area_id;

        $title = "";
        $subtitle = "";
        if ($this->data['object_id'] == 3) {
            $target_list = $this->employeeresult_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('employee_id' => $department_id, 'deleted' => 0))->with_target()->group_by("target_id")->get_all();
        } else {
            $target_list = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('department_id' => $department_id, 'deleted' => 0))->with_target()->group_by("target_id")->get_all();
        }
        $results = [];

        for ($i = 0; $i < count($target_list); $i++) {
            $target = $target_list[$i]->target;
            $params['target_id'] = $target->id;
            if ($this->data['object_id'] == 3) {
                $title = "Biểu đồ xu hướng vi sinh nhân viên $department->name ($department->string_id)";
                $subtitle = "Trend chart of microbiological monitoring of Personnel $department->name ($department->string_id)";
            } else {
                $title = "Trend chart of microbiological monitoring";
                $subtitle = "($target->name_en method) $department->name_en ($department->string_id)";
            }
            $params['title'] = $title;
            $params['subtitle'] = $subtitle;
            // if()
            if ($this->data['object_id'] == 3) {
                $data = $this->employeeresult_model->chart_datav2($params);
            } else {
                $data = $this->result_model->chart_datav2($params);
            }
            $target->data = $data;
            $results[] = $target;
        }

        echo json_encode($results);
    }
    public function datedata()
    {
        $type = $this->input->get('type', TRUE);
        if ($this->data['object_id'] == 3) {
            $this->load->model("employeeresult_model");
            $data = $this->employeeresult_model->get_date_has_data($type);
        } else {
            $this->load->model("result_model");
            $data = $this->result_model->get_date_has_data($type);
        }

        echo json_encode($data);
    }


    function getworkshop($params)
    {
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $id = $params[0];
        if ($object_id == 3) {
            $this->load->model("employeeResult_model");
            $data = $this->employeeResult_model->where(array('deleted' => 0, 'factory_id' => $id))->with_workshop()->group_by("workshop_id")->as_object()->get_all();
            $data = array_map(function ($item) {
                return $item->workshop;
            }, $data);
        } else {
            $this->load->model("result_model");
            $data = $this->result_model->where(array('deleted' => 0, 'object_id' => $object_id, 'factory_id' => $id))->with_workshop()->group_by("workshop_id")->as_object()->get_all();
            $data = array_map(function ($item) {
                return $item->workshop;
            }, $data);
        }
        usort($data, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });
        echo json_encode($data);
    }
    function getarea($params)
    {
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $id = $params[0];
        if ($object_id == 3) {
            $this->load->model("employeeResult_model");
            $data = $this->employeeResult_model->where(array('deleted' => 0, 'workshop_id' => $id))->with_area()->group_by("area_id")->as_object()->get_all();
            $data = array_map(function ($item) {
                return $item->area;
            }, $data);
        } else {
            $this->load->model("result_model");
            $data = $this->result_model->where(array('deleted' => 0, 'object_id' => $object_id, 'workshop_id' => $id))->with_area()->group_by("area_id")->as_object()->get_all();
            $data = array_map(function ($item) {
                return $item->area;
            }, $data);
        }
        usort($data, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });
        echo json_encode($data);
    }
    function getdepartment($params)
    {
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $id = $params[0];
        if ($object_id == 3) {
            $this->load->model("employeeResult_model");
            $data = $this->employeeResult_model->where(array('deleted' => 0, 'area_id' => $id))->with_employee()->group_by("employee_id")->as_object()->get_all();
            $data = array_map(function ($item) {
                return $item->employee;
            }, $data);
        } else {
            $this->load->model("result_model");
            $data = $this->result_model->where(array('deleted' => 0, 'object_id' => $object_id, 'area_id' => $id))->with_department()->group_by("department_id")->as_object()->get_all();
            $data = array_map(function ($item) {
                return $item->department;
            }, $data);
        }
        usort($data, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });
        echo json_encode($data);
    }
    function test()
    {
        $this->load->model("employeeResult_model");
        $data = $this->employeeResult_model->where('id', 'IN', array(5, 6))->get_all();
        echo json_encode($data);
    }
}
