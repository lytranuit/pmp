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

    public function savechart()
    {
        $this->load->model("report_model");
        $this->load->model("workshop_model");
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
        $this->data['params'] = $params;

        $target_list = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->with_target()->group_by("target_id")->get_all();
        $results = array();
        for ($i = 0; $i < count($target_list); $i++) {
            $target = $target_list[$i]->target;
            $area_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('target_id' => $target->id))->with_area()->group_by("area_id")->get_all();
            $area_list = array();
            for ($j = 0; $j < count($area_results); $j++) {
                $area = $area_results[$j]->area;

                $department_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('target_id' => $target->id))->where(array('area_id' => $area->id))->with_department()->group_by("department_id")->get_all();
                $department_list = array();
                for ($k = 0; $k < count($department_results); $k++) {
                    $department = $department_results[$k]->department;
                    $params['department_id'] = $department->id;
                    $params['target_id'] = $target->id;
                    $params['area_id'] = $area->id;
                    $params['department'] = $department;
                    $department->data = $this->result_model->chart_datav2($params);
                    $department_list[] = $department;
                }
                $area->department_list = $department_list;
                $area_list[] = $area;
            }
            $target->area_list = $area_list;
            $results[] = $target;
        }
        $this->data['results'] = $results;
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    public function chartdata()
    {

        $this->load->model("result_model");
        $this->load->model("limit_model");
        $this->load->model("department_model");

        $department_id = $this->input->get('department_id', TRUE);
        $target_id = $this->input->get('target_id', TRUE);
        $type = $this->input->get('type', TRUE);
        $selector = $this->input->get('selector', TRUE);
        $daterange = $this->input->get('daterange', TRUE);
        $params = array(
            'type' => $type,
            'selector' => $selector,
            'daterange' => $daterange
        );
        $params = input_params($params);

        $department = $this->department_model->where(array('id' => $department_id))->as_object()->get();
        $area_id = $department->area_id;
        $params['area_id'] = $area_id;
        $params['department_id'] = $department_id;
        $params['target_id'] = $target_id;

        $results = $this->result_model->chart_data($params);

        echo json_encode($results);
    }
    public function chartdatav2()
    {

        $this->load->model("result_model");
        $this->load->model("limit_model");
        $this->load->model("department_model");

        $department_id = $this->input->get('department_id', TRUE);
        $target_id = $this->input->get('target_id', TRUE);
        $type = $this->input->get('type', TRUE);
        $selector = $this->input->get('selector', TRUE);
        $daterange = $this->input->get('daterange', TRUE);
        $params = array(
            'type' => $type,
            'selector' => $selector,
            'daterange' => $daterange
        );
        $params = input_params($params);

        $department = $this->department_model->where(array('id' => $department_id))->as_object()->get();
        $area_id = $department->area_id;
        $params['area_id'] = $area_id;
        $params['department_id'] = $department_id;
        $params['target_id'] = $target_id;
        $params['department'] = $department;
        $results = $this->result_model->chart_datav2($params);

        echo json_encode($results);
    }
    public function datedata()
    {

        $type = $this->input->get('type', TRUE);
        $this->load->model("result_model");
        $data = $this->result_model->get_date_has_data($type);
        echo json_encode($data);
    }

    function printyear()
    {
        //          echo "<pre>";
        //        print_r($tin);
        //        die();
        //            PRINT BILL
        // boost the memory limit if it's low ;)
        ini_set('memory_limit', '256M');
        // load library
        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        // retrieve data from model
        //        $this->data['cart'] = $tin;
        $pdf->allow_charset_conversion = true;  // Set by default to TRUE
        $pdf->charset_in = 'UTF-8';
        //   $pdf->SetDirectionality('rtl');
        $pdf->autoLangToFont = true;

        $header = $this->blade->view()->make('pdf/header', $this->data)->render();
        //        print_r($header);
        //        die();
        $pdf->SetHTMLHeader($header);
        $footer = $this->blade->view()->make('pdf/footer', $this->data)->render();
        $pdf->SetHTMLFooter($footer);
        //        echo $html;die();
        // render the view into HTML
        $html = $this->blade->view()->make('pdf/year', $this->data)->render();
        $pdf->WriteHTML($html);
        // write the HTML into the PDF
        $output = 'itemreport' . date('Y_m_d_H_i_s') . '_.pdf';
        $pdf->Output("$output", 'I');
        // save to file because we can exit();
        // - See more at: http://webeasystep.com/blog/view_article/codeigniter_tutorial_pdf_to_create_your_reports#sthash.QFCyVGLu.dpuf
    }

    function getalldatachart()
    {

        $this->load->model("workshop_model");
        $this->load->model("result_model");
        $this->load->model("limit_model");

        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $is_cache = true;
        $workshop_id = $this->input->get('workshop_id', TRUE);
        $type = $this->input->get('type', TRUE);
        $selector = $this->input->get('selector', TRUE);
        $daterange = $this->input->get('daterange', TRUE);
        if ($type == "Custom") {
            $selector = $daterange;
        }
        $params_cache = array(
            'object_id' => $object_id,
            'workshop_id' => $workshop_id,
            'type' => $type,
            'selector' => $selector
        );

        $encrypted_string = md5(json_encode($params_cache));
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        if (!$is_cache)
            $this->cache->clean();
        ////CHECK CACHE 
        $results = $this->cache->get($encrypted_string);
        if (!empty($results)) {
            goto end;
        }
        $params = array(
            'type' => $type,
            'selector' => $selector,
            'daterange' => $daterange
        );
        $params = input_params($params);
        $target_list = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->with_target()->group_by("target_id")->get_all();

        $results = array();
        $charts = array();
        for ($i = 0; $i < count($target_list); $i++) {
            $target = $target_list[$i]->target;
            $area_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('target_id' => $target->id))->with_area()->group_by("area_id")->get_all();
            $area_list = array();
            for ($j = 0; $j < count($area_results); $j++) {
                $area = $area_results[$j]->area;

                $department_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('target_id' => $target->id))->where(array('area_id' => $area->id))->with_department()->group_by("department_id")->get_all();
                $department_list = array();
                for ($k = 0; $k < count($department_results); $k++) {
                    $department = $department_results[$k]->department;
                    $params['department_id'] = $department->id;
                    $params['target_id'] = $target->id;
                    $params['area_id'] = $area->id;
                    $params['department'] = $department;

                    // $department->params = $params;
                    $charts[$department->id . "_" . $target->id] = $this->result_model->chart_datav2($params);
                    $department_list[] = $department;
                }
                $area->department_list = $department_list;
                $area_list[] = $area;
            }
            $target->area_list = $area_list;
            $results[] = $target;
        }
        $this->data['charts'] = $charts;
        $this->data['results'] = $results;
        $results = $this->blade->view()->make('template/chart', $this->data)->render();;
        if ($is_cache) {
            // Save into the cache for 5 minutes
            $this->cache->save($encrypted_string, $results, 2592000);
        }

        end: echo $results;
    }
    function cleancache()
    {
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        $this->cache->clean();
        echo 1;
    }

    // function test()
    // {
    //     $this->load->model("result_model");
    //     $params = array(
    //         'type' => "Quarter",
    //         'date_from' => '2019-01-01',
    //         'date_to' => '2019-12-31',
    //         'workshop_id' => 4,
    //         'object_id' => 11
    //     );
    //     $reports = $this->result_model->set_value_export($params);
    //     $data = $reports->with_area()->with_department()->group_by("department_id")->get_all();
    //     echo json_encode($data);
    // }
}
