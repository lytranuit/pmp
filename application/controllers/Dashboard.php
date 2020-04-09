<?php

use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class Dashboard extends MY_Controller {

    function __construct() {
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
            "https://code.highcharts.com/highcharts.js",
            "https://code.highcharts.com/modules/exporting.js",
            base_url() . "public/assets/scripts/custom.js?v=" . $version
        );
    }

    public function _remap($method, $params = array()) {
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

    private function has_right($method, $params = array()) {

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

    public function index() {
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

    public function view() {
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

    public function savechart() {
        $this->load->model("report_model");
        $this->load->model("workshop_model");
        $this->load->model("result_model");
        $this->load->model("limit_model");

        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 1;
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
                    $department->params = $params;
                    $department->data = $this->result_model->chart_data($params);
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

    public function chartdata() {

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

    public function datedata() {

        $type = $this->input->get('type', TRUE);
        $this->load->model("result_model");
        $data = $this->result_model->get_date_has_data($type);
        echo json_encode($data);
    }

    public function export() {
        set_time_limit(-1);

        $id_record = $this->input->get('id_record', TRUE);

        // print_r($_COOKIE);
        // die();
        $this->load->model("report_model");
        $this->load->model("workshop_model");
        $this->load->model("result_model");
        $this->load->model("limit_model");
        $this->load->model("object_model");
        $record = $this->report_model->where(array("id" => $id_record))->get();
        $object_id = $record->object_id;
//        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 1;
//        $object_name = isset($_COOKIE['SELECT_NAME']) ? $_COOKIE['SELECT_NAME'] : "";
        if ($object_id == 3) {
            $object = $this->object_model->where(array('id' => $object_id))->as_object()->get();
            $workshop_id = $record->workshop_id;

            $workshop = $this->workshop_model->where(array('id' => $workshop_id))->with_factory()->as_object()->get();
            $workshop_name = $workshop->name;
            $factory_name = isset($workshop->factory->name) ? $workshop->factory->name : "";
            $type = $record->type;
            $selector = $record->selector;
            $daterange = $record->selector;
            $params = array(
                'type' => $type,
                'selector' => $selector,
                'daterange' => $daterange,
            );

            $params = input_params($params);
            $params['workshop_id'] = $workshop_id;
            $params['object_id'] = $object_id;
            ///////DATA
            $area_all = $this->result_model->area_export($params);
            $nhanvien_all = $this->result_model->nhanvien_export($params);
            foreach ($area_all as &$row) {
                $new_array = array_values(array_filter($nhanvien_all, function ($obj) use ($row) {
                            return $obj->area_id == $row->id;
                        }));
                $row->nhanvien = $new_array;
            }
            // echo "<pre>";
            // print_r($area_all);
            // die();
            $file = APPPATH . '../public/upload/template/template_nhan_vien.docx';
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file);

            $templateProcessor->setValue('date_from', date("d/m/y", strtotime($params['date_from'])));
            $templateProcessor->setValue('date_from_prev', date("d/m/y", strtotime($params['date_from_prev'])));
            $templateProcessor->setValue('date_to', date("d/m/y", strtotime($params['date_to'])));
            $templateProcessor->setValue('date_to_prev', date("d/m/y", strtotime($params['date_to_prev'])));
            $templateProcessor->setValue('workshop_name', $workshop_name);
            $templateProcessor->setValue('factory_name', $factory_name);


            ////STYLE
            $cellRowSpan = array('vMerge' => 'restart', 'valign' => 'center');
            $cellHCentered = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
            $cellHCenteredLEFT = array('alignment' => 'left');

            $styleCell = array('valign' => 'center');
            $fontCell = array('align' => 'center');
            $cellColSpan = array('gridSpan' => 4, 'valign' => 'center');
            $cellRowContinue = array('vMerge' => 'continue');
            $cellVCentered = array('valign' => 'center');
            ////TABLE VITRI

            $table = new Table(array('borderSize' => 13, 'width' => 10000, 'unit' => TblWidth::TWIP, 'valign' => 'center'));
            $table->addRow();
            $textrun = $table->addCell(2000, $styleCell);
            $textrun->addText('Vị trí lấy mẫu', array(), $fontCell);
            $textrun->addText('Sampling locations', array('italic' => true), $fontCell);
            $textrun = $table->addCell(2000, $styleCell);
            $textrun->addText('Tên nhân viên', array(), $fontCell);
            $textrun->addText('Name of personnel', array('italic' => true), $fontCell);
            $textrun = $table->addCell(2000, $styleCell);
            $textrun->addText('Mã số nhân viên', array(), $fontCell);
            $textrun->addText('ID No.', array('italic' => true), $fontCell);
            $textrun = $table->addCell(2000, $styleCell);
            $textrun->addText('Tần suất', array(), $fontCell);
            $textrun->addText('Frequency', array('italic' => true), $fontCell);

            foreach ($area_all as $row2) {
                ///DATA
                $table->addRow();
                $cell1 = $table->addCell(8000, $cellColSpan);
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText($row2->name, array('bold' => true));
                $table->addRow();
                $cell1 = $table->addCell(2000, $cellRowSpan);
                $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                $textrun1->addText("Đầu / ");
                $textrun1->addText('Head', array('italic' => true));
                $textrun1->addTextBreak();
                $textrun1->addText("Mũi / ");
                $textrun1->addText('Nose', array('italic' => true));
                $textrun1->addTextBreak();
                $textrun1->addText("Ngực / ");
                $textrun1->addText('Chest', array('italic' => true));
                $textrun1->addTextBreak();
                $textrun1->addText("Cẳng tay trái / ");
                $textrun1->addText('Left forearm', array('italic' => true));
                $textrun1->addTextBreak();
                $textrun1->addText("Cẳng tay phải / ");
                $textrun1->addText('Right forearm', array('italic' => true));
                $textrun1->addTextBreak();
                $textrun1->addText("Dấu găng tay trái / ");
                $textrun1->addText('Left glove print 5 fingers', array('italic' => true));
                $textrun1->addTextBreak();
                $textrun1->addText("Dấu găng tay phải / ");
                $textrun1->addText('Right glove print 5 fingers', array('italic' => true));
                $textrun1->addTextBreak();
                $nhanvien = $row2->nhanvien;
                // echo "<pre>";
                // print_r($nhanvien);
                // die();
                if (count($nhanvien)) {
                    $list = explode("_", $nhanvien[0]->string_id);
                    $table->addCell(2000, $cellVCentered)->addText($nhanvien[0]->name, null, $cellHCentered);
                    $table->addCell(2000, $cellVCentered)->addText($list[1], null, $cellHCentered);
                }

                $cell1 = $table->addCell(2000, $cellRowSpan);
                $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                $textrun1->addText("Nhân viên phải được lấy mẫu sau khi hoàn tất hoạt động trong ngày và trước khi nhân viên ra khỏi khu vực vô trùng.");
                $textrun1->addTextBreak();
                $textrun1->addText('Samples shall be collected after completion of operations for that day, before personnel go out of the aseptic areas.', array('italic' => true));
                for ($i = 1; $i <= count($nhanvien) - 1; $i++) {
                    $table->addRow();
                    $table->addCell(null, $cellRowContinue);
                    $list = explode("_", $nhanvien[$i]->string_id);
                    $table->addCell(2000, $cellVCentered)->addText($nhanvien[$i]->name, null, $cellHCentered);
                    $table->addCell(2000, $cellVCentered)->addText($list[1], null, $cellHCentered);
                    $table->addCell(null, $cellRowContinue);
                }
            }

            $templateProcessor->setComplexBlock('table_vitri', $table);
            ////END TABLE VI TRI
            ///TABLE LIMIT
            $table = new Table(array('borderSize' => 13, 'width' => 10000, 'unit' => TblWidth::TWIP, 'valign' => 'center'));
            $table->addRow();
            $textrun = $table->addCell(2000, $styleCell);
            $textrun->addText('Vị trí lấy mẫu', array(), $fontCell);
            $textrun->addText('Sampling locations', array('italic' => true), $fontCell);
            $textrun = $table->addCell(2000, $styleCell);
            $textrun->addText('Đầu', array(), $fontCell);
            $textrun->addText('Head', array('italic' => true), $fontCell);
            $textrun = $table->addCell(2000, $styleCell);
            $textrun->addText('Mũi', array(), $fontCell);
            $textrun->addText('Nose', array('italic' => true), $fontCell);
            $textrun = $table->addCell(2000, $styleCell);
            $textrun->addText('Ngực', array(), $fontCell);
            $textrun->addText('Chest', array('italic' => true), $fontCell);
            $textrun = $table->addCell(2000, $styleCell);
            $textrun->addText('Cẳng tay trái', array(), $fontCell);
            $textrun->addText('Left forearm', array('italic' => true), $fontCell);
            $textrun = $table->addCell(2000, $styleCell);
            $textrun->addText('Cẳng tay phải', array(), $fontCell);
            $textrun->addText('Right forearm', array('italic' => true), $fontCell);
            $textrun = $table->addCell(2000, $styleCell);
            $textrun->addText('Dấu găng tay trái', array(), $fontCell);
            $textrun->addText('Left glove print 5 fingers', array('italic' => true), $fontCell);
            $textrun = $table->addCell(2000, $styleCell);
            $textrun->addText('Dấu găng tay phải', array(), $fontCell);
            $textrun->addText('Right glove print 5 fingers', array('italic' => true), $fontCell);

            foreach ($area_all as $row2) {

                $limit = $this->limit_model->where(array("area_id" => $row2->id, 'target_id' => 6))->as_object()->get();
                // print_r($limit);
                // die();  
                //     ///DATA
                $table->addRow();
                $cell1 = $table->addCell(8000, array('gridSpan' => 8, 'valign' => 'center'));
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText($row2->name, array('bold' => true));
                $table->addRow();
                $textrun = $table->addCell(2000, $styleCell);
                $textrun->addText('Tiêu chuẩn chấp nhận', array(), $fontCell);
                $textrun->addText('Acceptance criteria', array('italic' => true), $fontCell);
                $textrun = $table->addCell(2000, array('gridSpan' => 7, 'valign' => 'center'));
                $textrun->addText($limit->standard_limit, array(), $fontCell);
                $table->addRow();
                $textrun = $table->addCell(2000, $styleCell);
                $textrun->addText('Giới hạn cảnh báo', array(), $fontCell);
                $textrun->addText('Alert Limit', array('italic' => true), $fontCell);
                $textrun = $table->addCell(2000, array('gridSpan' => 7, 'valign' => 'center'));
                $textrun->addText($limit->alert_limit, array(), $fontCell);
                $table->addRow();
                $textrun = $table->addCell(2000, $styleCell);
                $textrun->addText('Giới hạn hành động', array(), $fontCell);
                $textrun->addText('Action Limit', array('italic' => true), $fontCell);
                $textrun = $table->addCell(2000, array('gridSpan' => 7, 'valign' => 'center'));
                $textrun->addText($limit->action_limit, array(), $fontCell);
                //     $cell1 = $table->addCell(2000, $cellRowSpan);
                //     $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                //     $textrun1->addText("Đầu / ");
                //     $textrun1->addText('Head', array('italic' => true));
                //     $textrun1->addTextBreak();
                //     $textrun1->addText("Mũi / ");
                //     $textrun1->addText('Nose', array('italic' => true));
                //     $textrun1->addTextBreak();
                //     $textrun1->addText("Ngực / ");
                //     $textrun1->addText('Chest', array('italic' => true));
                //     $textrun1->addTextBreak();
                //     $textrun1->addText("Cẳng tay trái / ");
                //     $textrun1->addText('Left forearm', array('italic' => true));
                //     $textrun1->addTextBreak();
                //     $textrun1->addText("Cẳng tay phải / ");
                //     $textrun1->addText('Right forearm', array('italic' => true));
                //     $textrun1->addTextBreak();
                //     $textrun1->addText("Dấu găng tay trái / ");
                //     $textrun1->addText('Left glove print 5 fingers', array('italic' => true));
                //     $textrun1->addTextBreak();
                //     $textrun1->addText("Dấu găng tay phải / ");
                //     $textrun1->addText('Right glove print 5 fingers', array('italic' => true));
                //     $textrun1->addTextBreak();
                //     $nhanvien = $row2->nhanvien;
                //     // echo "<pre>";
                //     // print_r($nhanvien);
                //     // die();
                //     if (count($nhanvien)) {
                //         $list = explode("_", $nhanvien[0]->string_id);
                //         $table->addCell(2000, $cellVCentered)->addText($nhanvien[0]->name, null, $cellHCentered);
                //         $table->addCell(2000, $cellVCentered)->addText($list[1], null, $cellHCentered);
                //     }
                //     $cell1 = $table->addCell(2000, $cellRowSpan);
                //     $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                //     $textrun1->addText("Nhân viên phải được lấy mẫu sau khi hoàn tất hoạt động trong ngày và trước khi nhân viên ra khỏi khu vực vô trùng.");
                //     $textrun1->addTextBreak();
                //     $textrun1->addText('Samples shall be collected after completion of operations for that day, before personnel go out of the aseptic areas.', array('italic' => true));
                //     for ($i = 1; $i <= count($nhanvien) - 1; $i++) {
                //         $table->addRow();
                //         $table->addCell(null, $cellRowContinue);
                //         $list = explode("_", $nhanvien[$i]->string_id);
                //         $table->addCell(2000, $cellVCentered)->addText($nhanvien[$i]->name, null, $cellHCentered);
                //         $table->addCell(2000, $cellVCentered)->addText($list[1], null, $cellHCentered);
                //         $table->addCell(null, $cellRowContinue);
                //     }
            }

            $templateProcessor->setComplexBlock('table_limit', $table);

            $templateProcessor->cloneBlock("area_block", count($area_all), true, true);
            foreach ($area_all as $key => $area) {
                $templateProcessor->setValue("area_heading#" . ($key + 1), "5." . ($key + 1));
                $templateProcessor->setValue("area_name#" . ($key + 1), $area->name);
                $department_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('area_id' => $area->id))->with_department()->group_by("department_id")->get_all();
                $department_list = array();
                $length_department = count($department_results);
                $templateProcessor->cloneBlock("department_block#" . ($key + 1), $length_department, true, true);
                for ($key1 = 0; $key1 < $length_department; $key1++) {
                    $department = $department_results[$key1]->department;
                    $list = explode("_", $department->string_id);
                    $id = $list[1];
                    $target_id = 6;
                    $name_chart = $target_id . "_" . $department->id . "_" . $params['type'] . "_" . $params['selector'] . ".png";
                    $position_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('area_id' => $area->id))->where(array('department_id' => $department->id))->with_position()->get_all();
                    $positions = array_map(function ($item) {
                        return $item->position;
                    }, $position_results);
                    $data = $this->result_model->get_data_table($department->id, $positions, $params);
                    $data_min_max = $this->result_model->get_data_minmax($department->id, $positions, $params);
                    $templateProcessor->cloneRow("stt#" . ($key + 1) . "#" . ($key1 + 1), count($data));


                    foreach ($data as $keystt => $stt) {
                        $templateProcessor->setValue("stt#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($keystt + 1), ($keystt + 1));
                        $templateProcessor->setValue("date#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($keystt + 1), date("d/m/y", strtotime($stt['date'])));
                        $templateProcessor->setValue("value_H#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($keystt + 1), $stt["$department->string_id" . "_H"]);
                        $templateProcessor->setValue("value_N#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($keystt + 1), $stt["$department->string_id" . "_N"]);
                        $templateProcessor->setValue("value_C#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($keystt + 1), $stt["$department->string_id" . "_C"]);
                        $templateProcessor->setValue("value_LF#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($keystt + 1), $stt["$department->string_id" . "_LF"]);
                        $templateProcessor->setValue("value_RF#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($keystt + 1), $stt["$department->string_id" . "_RF"]);
                        $templateProcessor->setValue("value_LG#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($keystt + 1), $stt["$department->string_id" . "_LG"]);
                        $templateProcessor->setValue("value_RG#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($keystt + 1), $stt["$department->string_id" . "_RG"]);
                    }
                    //MAX
                    $templateProcessor->setValue("max_H#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["max_$department->string_id" . "_H"]);
                    $templateProcessor->setValue("max_N#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["max_$department->string_id" . "_N"]);
                    $templateProcessor->setValue("max_C#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["max_$department->string_id" . "_C"]);
                    $templateProcessor->setValue("max_LF#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["max_$department->string_id" . "_LF"]);
                    $templateProcessor->setValue("max_RF#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["max_$department->string_id" . "_RF"]);
                    $templateProcessor->setValue("max_LG#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["max_$department->string_id" . "_LG"]);
                    $templateProcessor->setValue("max_RG#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["max_$department->string_id" . "_RG"]);

                    //MIN
                    $templateProcessor->setValue("min_H#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["min_$department->string_id" . "_H"]);
                    $templateProcessor->setValue("min_N#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["min_$department->string_id" . "_N"]);
                    $templateProcessor->setValue("min_C#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["min_$department->string_id" . "_C"]);
                    $templateProcessor->setValue("min_LF#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["min_$department->string_id" . "_LF"]);
                    $templateProcessor->setValue("min_RF#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["min_$department->string_id" . "_RF"]);
                    $templateProcessor->setValue("min_LG#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["min_$department->string_id" . "_LG"]);
                    $templateProcessor->setValue("min_RG#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max["min_$department->string_id" . "_RG"]);


                    $templateProcessor->setImageValue("chart_image#" . ($key + 1) . "#" . ($key1 + 1), array('path' => APPPATH . '../public/upload/chart/' . $name_chart, 'width' => 700, 'height' => 300, 'ratio' => false));

                    $templateProcessor->setValue("department_heading#" . ($key + 1) . "#" . ($key1 + 1), "5." . ($key + 1) . "." . ($key1 + 1));
                    $templateProcessor->setValue("department_name#" . ($key + 1) . "#" . ($key1 + 1), $department->name);
                    $templateProcessor->setValue("department_id#" . ($key + 1) . "#" . ($key1 + 1), $id);
                }
            }


            $name_file = "Bao_cao_" . implode("_", explode(" ", $object->name)) . "_" . $workshop_id . "_" . $params['type'] . "_" . $params['selector'] . "_" . time() . ".docx";
            $templateProcessor->saveAs(APPPATH . '../public/export/' . $name_file);

            // $templateProcessor->cloneRow("result_block#1", 3);
            $data_up = array(
                'name' => $name_file,
                'status' => 2
            );
            $this->report_model->update($data_up, $id_record);

            redirect("dashboard", 'refresh');
            // header("Location: " . $_SERVER['HTTP_HOST'] . "/MyWordFile.docx");
        }
    }

    function printyear() {
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

    function getalldatachart() {
        $this->load->model("workshop_model");
        $this->load->model("result_model");
        $this->load->model("limit_model");

        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 1;
        $workshop_id = $this->input->get('workshop_id', TRUE);
        $type = $this->input->get('type', TRUE);
        $selector = $this->input->get('selector', TRUE);
        $daterange = $this->input->get('daterange', TRUE);
        $params = array(
            'type' => $type,
            'selector' => $selector,
            'daterange' => $daterange
        );
        $params = input_params($params);
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
                    $department->params = $params;
                    $department->data = $this->result_model->chart_data($params);
                    $department_list[] = $department;
                }
                $area->department_list = $department_list;
                $area_list[] = $area;
            }
            $target->area_list = $area_list;
            $results[] = $target;
        }
        echo json_encode($results);
    }

}
