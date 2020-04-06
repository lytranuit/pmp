<?php

use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\TblWidth;

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
            base_url() . "public/admin/vendor/fonts/fontawesome/css/fontawesome-all.css"
        );
        $this->data['javascript_tag'] = array(
            base_url() . 'public/assets/scripts/jquery.min.js',
            base_url() . "public/assets/scripts/main.js?v=" . $version,
            base_url() . "public/lib/jquery-validation/jquery.validate.js",
            base_url() . "public/admin/vendor/inputmask/js/jquery.inputmask.bundle.js",
            base_url() . "public/admin/libs/js/moment.js",
            base_url() . "public/assets/scripts/jquery.cookies.2.2.0.min.js",
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
        // echo "<pre>";
        // print_r($params);
        // die();

        $department = $this->department_model->where(array('id' => $department_id))->as_object()->get();
        $area_id = $department->area_id;
        //        echo $department_id;
        $results = array('labels' => array(), 'datasets' => array());
        $data_limit = $this->limit_model->where(array('deleted' => 0, 'area_id' => $area_id, 'target_id' => $target_id))->as_array()->get();


        $params['department_id'] = $department_id;
        $params['target_id'] = $target_id;
        $data = $this->result_model->chartdata($params);
        // $data = $this->result_model->where(array('deleted' => 0, 'department_id' => $department_id, 'target_id' => $target_id))->with_position()->as_object()->get_all();
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
                    $value = $data_limit['action_limit'];
                } else if ($position_string_id == "Alert Limit") {
                    $value = $data_limit['alert_limit'];
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
        //        echo "<Pre>";
        //        print_r($results);
        //        die();
        echo json_encode($results);
    }

    public function datedata()
    {

        $type = $this->input->get('type', TRUE);
        $this->load->model("result_model");
        $data = $this->result_model->get_date_has_data($type);
        echo json_encode($data);
    }
    public function export()
    {
        // print_r($_COOKIE);
        // die();
        $this->load->model("workshop_model");
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 1;
        $object_name = isset($_COOKIE['SELECT_NAME']) ? $_COOKIE['SELECT_NAME'] : "";
        if ($object_id == 3) {
            $workshop_id = $this->input->get('workshop_id', TRUE);

            $workshop = $this->workshop_model->where(array('id' => $workshop_id))->with_factory()->as_object()->get();
            $workshop_name = $workshop->name;
            $factory_name = isset($workshop->factory->name) ? $workshop->factory->name : "";
            $type = $this->input->get('type', TRUE);
            $selector = $this->input->get('selector', TRUE);
            $daterange = $this->input->get('daterange', TRUE);
            $params = array(
                'type' => $type,
                'selector' => $selector,
                'daterange' => $daterange
            );
            $params = input_params($params);

            $file = APPPATH . '../public/upload/template/template_nhan_vien.docx';
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file);

            $templateProcessor->setValue('date_from', $params['date_from']);
            $templateProcessor->setValue('date_from_prev', $params['date_from_prev']);
            $templateProcessor->setValue('date_to', $params['date_to']);
            $templateProcessor->setValue('date_to_prev', $params['date_to_prev']);
            $templateProcessor->setValue('workshop_name', $workshop_name);
            $templateProcessor->setValue('factory_name', $factory_name);
            $replacements = array(
                array('customer_name' => 'Batman', 'customer_address' => 'Gotham City'),
                array('customer_name' => 'Superman', 'customer_address' => 'Metropolis'),
            );
            $templateProcessor->cloneBlock('block_name', 3, true, true);

            $table = new Table(array('borderSize' => 12, 'borderColor' => 'green', 'width' => 6000, 'unit' => TblWidth::TWIP));
            $table->addRow();
            $table->addCell(150)->addText('Cell A1');
            $table->addCell(150)->addText('Cell A2');
            $table->addCell(150)->addText('Cell A3');
            $table->addRow();
            $table->addCell(150)->addText('Cell B1');
            $table->addCell(150)->addText('Cell B2');
            $table->addCell(150)->addText('Cell B3');
            $templateProcessor->setComplexBlock('table', $table);
            $templateProcessor->saveAs('MyWordFile.docx');
            redirect("MyWordFile.docx", 'refresh');
            // header("Location: " . $_SERVER['HTTP_HOST'] . "/MyWordFile.docx");
        }
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

    function wordtest()
    {

        $file = APPPATH . '../public/upload/template/1.docx';
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file);

        $templateProcessor->setValue('date', date("d-m-Y"));
        $templateProcessor->saveAs('MyWordFile.docx');
    }
}
