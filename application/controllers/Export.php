<?php

use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class Export extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    public function export($id_record)
    {
        set_time_limit(-1);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        // $id_record = $this->input->get('id_record', TRUE);
        // print_r($id_record);
        // die();
        $this->load->model("report_model");
        $this->load->model("workshop_model");
        $this->load->model("result_model");
        $this->load->model("limit_model");
        $this->load->model("object_model");
        $record = $this->report_model->where(array("id" => $id_record))->get();
        // print_r($record);
        // die();
        $object_id = $record->object_id;
        //        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 1;
        //        $object_name = isset($_COOKIE['SELECT_NAME']) ? $_COOKIE['SELECT_NAME'] : "";
        if ($object_id == 3) {
            $object = $this->object_model->where(array('id' => $object_id))->as_object()->get();
            $workshop_id = $record->workshop_id;

            $workshop = $this->workshop_model->where(array('id' => $workshop_id))->with_factory()->as_object()->get();
            $workshop_name = $workshop->name;
            $workshop_name_en = $workshop->name_en;
            $factory_name = isset($workshop->factory->name) ? $workshop->factory->name : "";
            $factory_name_en = isset($workshop->factory->name_en) ? $workshop->factory->name_en : "";
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
            $type_bc = "Hàng năm";
            $type_bc_en = "Yearly";
            if ($type == "Year") {
                $type_bc = "Hàng năm";
                $type_bc_en = "Yearly";
            } elseif ($type == "Month") {
                $type_bc = "Hàng tháng";
                $type_bc_en = "Monthly";
            } elseif ($type == "HalfYear") {
                $type_bc = "Nữa năm";
                $type_bc_en = "Half Year";
            } elseif ($type == "Quarter") {
                $type_bc = "Hàng Quý";
                $type_bc_en = "Quarter";
            }
            $templateProcessor->setValue('type_bc', $type_bc);
            $templateProcessor->setValue('type_bc_en', $type_bc_en);
            $templateProcessor->setValue('date_from', date("d/m/y", strtotime($params['date_from'])));
            $templateProcessor->setValue('date_from_prev', date("d/m/y", strtotime($params['date_from_prev'])));
            $templateProcessor->setValue('date_to', date("d/m/y", strtotime($params['date_to'])));
            $templateProcessor->setValue('date_to_prev', date("d/m/y", strtotime($params['date_to_prev'])));
            $templateProcessor->setValue('workshop_name', $workshop_name);
            $templateProcessor->setValue('factory_name', $factory_name);
            $templateProcessor->setValue('workshop_name_en', $workshop_name_en);
            $templateProcessor->setValue('factory_name_en', $factory_name_en);


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
                $templateProcessor->setValue("area_name_en#" . ($key + 1), $area->name_en);
                $department_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('area_id' => $area->id))->with_department()->group_by("department_id")->get_all();
                $department_list = array();
                $length_department = count($department_results);
                $templateProcessor->cloneBlock("department_block#" . ($key + 1), $length_department, true, true);
                for ($key1 = 0; $key1 < $length_department; $key1++) {
                    $department = $department_results[$key1]->department;
                    $list = explode("_", $department->string_id);
                    $id = $list[1];
                    $target_id = 6;
                    $name_chart = $target_id . "_" . $department->id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . ".png";
                    $position_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('area_id' => $area->id))->where(array('department_id' => $department->id))->with_position()->get_all();
                    $positions = array_map(function ($item) {
                        return $item->position;
                    }, $position_results);
                    $data = $this->result_model->get_data_table($department->id, $positions, $params);
                    $data_min_max = $this->result_model->get_data_minmax($department->id, $positions, $params['date_from'], $params['date_to']);
                    $data_min_max_prev = $this->result_model->get_data_minmax($department->id, $positions, $params['date_from_prev'], $params['date_to_prev']);
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

                    //MAX PREV
                    $templateProcessor->setValue("max_prev_H#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["max_$department->string_id" . "_H"]);
                    $templateProcessor->setValue("max_prev_N#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["max_$department->string_id" . "_N"]);
                    $templateProcessor->setValue("max_prev_C#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["max_$department->string_id" . "_C"]);
                    $templateProcessor->setValue("max_prev_LF#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["max_$department->string_id" . "_LF"]);
                    $templateProcessor->setValue("max_prev_RF#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["max_$department->string_id" . "_RF"]);
                    $templateProcessor->setValue("max_prev_LG#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["max_$department->string_id" . "_LG"]);
                    $templateProcessor->setValue("max_prev_RG#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["max_$department->string_id" . "_RG"]);

                    //MIN PREV
                    $templateProcessor->setValue("min_prev_H#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["min_$department->string_id" . "_H"]);
                    $templateProcessor->setValue("min_prev_N#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["min_$department->string_id" . "_N"]);
                    $templateProcessor->setValue("min_prev_C#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["min_$department->string_id" . "_C"]);
                    $templateProcessor->setValue("min_prev_LF#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["min_$department->string_id" . "_LF"]);
                    $templateProcessor->setValue("min_prev_RF#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["min_$department->string_id" . "_RF"]);
                    $templateProcessor->setValue("min_prev_LG#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["min_$department->string_id" . "_LG"]);
                    $templateProcessor->setValue("min_prev_RG#" . ($key + 1) . "#" . ($key1 + 1), $data_min_max_prev["min_$department->string_id" . "_RG"]);

                    $templateProcessor->setImageValue("chart_image#" . ($key + 1) . "#" . ($key1 + 1), array('path' => APPPATH . '../public/upload/chart/' . $name_chart, 'width' => 1000, 'height' => 300, 'ratio' => false));

                    $templateProcessor->setValue("department_heading#" . ($key + 1) . "#" . ($key1 + 1), "5." . ($key + 1) . "." . ($key1 + 1));
                    $templateProcessor->setValue("department_name#" . ($key + 1) . "#" . ($key1 + 1), $department->name);
                    $templateProcessor->setValue("department_name_en#" . ($key + 1) . "#" . ($key1 + 1), $department->name_en);
                    $templateProcessor->setValue("department_id#" . ($key + 1) . "#" . ($key1 + 1), $id);
                }
            }


            $name_file = "Bao_cao_" . $object_id . "_" . $workshop_id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . "_" . time() . ".docx";
            $name_file = urlencode($name_file);
            $templateProcessor->saveAs(APPPATH . '../public/export/' . $name_file);

            // $templateProcessor->cloneRow("result_block#1", 3);
            $data_up = array(
                'name' => $name_file,
                'status' => 3
            );
            $this->report_model->update($data_up, $id_record);

            // redirect("dashboard", 'refresh');
            // header("Location: " . $_SERVER['HTTP_HOST'] . "/MyWordFile.docx");
        } else if ($object_id == 11) {
            $object = $this->object_model->where(array('id' => $object_id))->as_object()->get();
            $workshop_id = $record->workshop_id;

            $workshop = $this->workshop_model->where(array('id' => $workshop_id))->with_factory()->as_object()->get();
            $workshop_name = $workshop->name;
            $workshop_name_en = $workshop->name_en;
            $factory_name = isset($workshop->factory->name) ? $workshop->factory->name : "";
            $factory_name_en = isset($workshop->factory->name_en) ? $workshop->factory->name_en : "";
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
            $target_list = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->with_target()->group_by("target_id")->get_all();
            $target_list = array_map(function ($item) {
                return $item->target;
            }, $target_list);

            $area_list = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->with_area()->group_by("area_id")->get_all();
            $area_list = array_map(function ($item) {
                return $item->area;
            }, $area_list);
            // echo "<pre>";
            // print_r($area_list);
            // die();
            $file = APPPATH . '../public/upload/template/template_phong.docx';
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file);
            $type_bc = "Hàng năm";
            $type_bc_en = "Yearly";
            if ($type == "Year") {
                $type_bc = "Hàng năm";
                $type_bc_en = "Yearly";
            } elseif ($type == "Month") {
                $type_bc = "Hàng tháng";
                $type_bc_en = "Monthly";
            } elseif ($type == "HalfYear") {
                $type_bc = "Nữa năm";
                $type_bc_en = "Half Year";
            } elseif ($type == "Quarter") {
                $type_bc = "Hàng Quý";
                $type_bc_en = "Quarter";
            }
            $templateProcessor->setValue('type_bc', $type_bc);
            $templateProcessor->setValue('type_bc_en', $type_bc_en);
            $templateProcessor->setValue('date_from', date("d/m/y", strtotime($params['date_from'])));
            $templateProcessor->setValue('date_from_prev', date("d/m/y", strtotime($params['date_from_prev'])));
            $templateProcessor->setValue('date_to', date("d/m/y", strtotime($params['date_to'])));
            $templateProcessor->setValue('date_to_prev', date("d/m/y", strtotime($params['date_to_prev'])));
            $templateProcessor->setValue('workshop_name', $workshop_name);
            $templateProcessor->setValue('factory_name', $factory_name);
            $templateProcessor->setValue('workshop_name_en', $workshop_name_en);
            $templateProcessor->setValue('factory_name_en', $factory_name_en);


            ////STYLE
            $cellRowSpan = array('vMerge' => 'restart', 'valign' => 'center');
            $cellHCentered = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
            $cellHCenteredLEFT = array('alignment' => 'left');

            $styleCell = array('valign' => 'center');
            $fontCell = array('align' => 'center');
            $cellColSpan = array('gridSpan' => 4, 'valign' => 'center');
            $cellRowContinue = array('vMerge' => 'continue');
            $cellVCentered = array('valign' => 'center');
            // $target_list;
            ///TABLE LIMIT
            $table = new Table(array('borderSize' => 3, 'width' => 10000, 'unit' => TblWidth::TWIP, 'valign' => 'center'));
            $table->addRow();

            $cell1 = $table->addCell(2000, $cellRowSpan);
            $textrun = $table->addCell(6250, array('gridSpan' => 3, 'size' => 12, 'valign' => 'center'));
            $textrun->addText('Phương pháp lấy mẫu /', array(), $fontCell);
            $textrun->addText('Sampling method (CFU/plate)', array('italic' => true), $fontCell);
            $table->addRow();
            $table->addCell(null, $cellRowContinue);
            for ($i = 0; $i < count($target_list); $i++) {
                $textrun = $table->addCell(2000, $styleCell);
                $textrun->addText($target_list[$i]->name, array(), $fontCell);
                $textrun->addText($target_list[$i]->name_en, array('italic' => true), $fontCell);
            }
            foreach ($area_list as $row2) {
                $table->addRow();
                $cell1 = $table->addCell(8000, $cellColSpan);
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText($row2->name, array('bold' => true));

                $table->addRow();
                $textrun = $table->addCell(2000, $styleCell);
                $textrun->addText('Tiêu chuẩn chấp nhận', array(), $fontCell);
                $textrun->addText('Acceptance criteria', array('italic' => true), $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $this->limit_model->where(array("area_id" => $row2->id, 'target_id' => $target->id))->as_object()->get();
                    $target_list[$key]->limit = $limit;
                    // print_r($limit);
                    // die();  
                    //     ///DATA
                    $textrun = $table->addCell(2000, $fontCell);
                    $value = isset($limit->standard_limit) ? $limit->standard_limit : 0;
                    $textrun->addText($value, array(), $fontCell);
                }
                $table->addRow();
                $textrun = $table->addCell(2000, $styleCell);
                $textrun->addText('Giới hạn cảnh báo', array(), $fontCell);
                $textrun->addText('Alert Limit', array('italic' => true), $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $target->limit;
                    $textrun = $table->addCell(2000, $fontCell);
                    $value = isset($limit->alert_limit) ? $limit->alert_limit : 0;
                    $textrun->addText($value, array(), $fontCell);
                }
                $table->addRow();
                $textrun = $table->addCell(2000, $styleCell);
                $textrun->addText('Giới hạn hành động', array(), $fontCell);
                $textrun->addText('Action Limit', array('italic' => true), $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $target->limit;
                    $textrun = $table->addCell(2000, $fontCell);
                    $value = isset($limit->action_limit) ? $limit->action_limit : 0;
                    $textrun->addText($value, array(), $fontCell);
                }
            }

            $templateProcessor->setComplexBlock('table_limit', $table);

            /////RESULT 
            $templateProcessor->cloneBlock("result_target_block", count($target_list), true, true);
            foreach ($target_list as $key => $target) {
                $templateProcessor->setValue("target_heading#" . ($key + 1), "5.1." . ($key + 1));
                $templateProcessor->setValue("target_name#" . ($key + 1), $target->name);
                $templateProcessor->setValue("target_name_en#" . ($key + 1), $target->name_en);
                $area_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('target_id' => $target->id))->with_area()->group_by("area_id")->get_all();
                $department_list = array();
                $length_area = count($area_results);
                $templateProcessor->cloneBlock("area_block#" . ($key + 1), $length_area, true, true);
                for ($key1 = 0; $key1 < $length_area; $key1++) {
                    $area = $area_results[$key1]->area;
                    $department_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('target_id' => $target->id))->where(array('area_id' => $area->id))->with_department()->group_by("department_id")->get_all();
                    $length_department = count($department_results);
                    $templateProcessor->setValue("area_heading#" . ($key + 1) . "#" . ($key1 + 1), "5.1." . ($key + 1) . "." . ($key1 + 1));
                    $templateProcessor->setValue("area_name#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name));
                    $templateProcessor->setValue("area_name_en#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name_en));
                    // $textrun1 = $table->addCell(1000, $styleCell);
                    // $textrun1->addText(htmlspecialchars("Tên phòng:"), $fontCell);
                    // $textrun1->addText(htmlspecialchars('Room name:'), array('italic' => true), $fontCell);
                    // $textrun1 = $table->addCell(1000, $styleCell);
                    // $textrun1->addText(htmlspecialchars("Tên phòng:"), $fontCell);
                    // $textrun1->addText(htmlspecialchars('Room name:'), array('italic' => true), $fontCell);
                    // $textrun1 = $table->addCell(1000, $styleCell);
                    // $textrun1->addText(htmlspecialchars("Tên phòng:"), $fontCell);
                    // $textrun1->addText(htmlspecialchars('Room name:'), array('italic' => true), $fontCell);
                    // $position_list = array();
                    $number_position = 0;
                    $list_department_tmp = array();
                    $table_data = array();
                    for ($key2 = 0; $key2 < $length_department; $key2++) {
                        $department = $department_results[$key2]->department;
                        $position_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('target_id' => $target->id))->where(array('department_id' => $department->id))->with_position()->group_by("position_id")->get_all();
                        $length_position = count($position_results);
                        $list_position = array();
                        for ($key3 = 0; $key3 < $length_position; $key3++) {
                            $position = $position_results[$key3]->position;
                            $list_position[] = $position;
                        }
                        $department->list_position = $list_position;

                        if ($length_position > 12) {
                            $table_data[] = array($department);
                            continue;
                        }
                        if ($number_position + $length_position > 12) {
                            $table_data[] = $list_department_tmp;
                            $number_position = 0;
                            $list_department_tmp = array();
                        } else {
                            $list_department_tmp[] = $department;
                            $number_position += $length_position;
                        }
                    }
                    if (count($list_department_tmp)) {
                        $table_data[] = $list_department_tmp;
                    }
                    // echo "<pre>";
                    // print_r($table_data);
                    // die();
                    $templateProcessor->cloneBlock("group_block#" . ($key + 1) . "#" . ($key1 + 1), count($table_data), true, true);
                    foreach ($table_data as $key2 => $t_data) {
                        ///TABLE
                        $table = new Table(array('borderSize' => 3, 'width' => 10000, 'size' => 10, 'unit' => TblWidth::TWIP, 'valign' => 'center'));
                        $table->addRow();
                        $cell1 = $table->addCell(2000, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                        $textrun1->addText(htmlspecialchars("Tên phòng:"), array('size' => 10, 'bold' => true));
                        $textrun1->addTextBreak();
                        $textrun1->addText(htmlspecialchars('Room name:'), array('size' => 10, 'bold' => true, 'italic' => true));
                        $position_list = array();
                        foreach ($t_data as $key3 => $department) {
                            $textrun1 = $table->addCell(1200, array('gridSpan' => count($department->list_position), 'valign' => 'center'));
                            $textrun1->addText(htmlspecialchars($department->name), array('size' => 10), $fontCell);
                            $textrun1->addText(htmlspecialchars($department->name_en), array('size' => 10, 'italic' => true), $fontCell);
                            $position_list = array_merge($position_list, $department->list_position);
                        }
                        $table->addRow();
                        $cell1 = $table->addCell(2000, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                        $textrun1->addText(htmlspecialchars("Vị trí lấy mẫu:"), array('size' => 10, 'bold' => true));
                        $textrun1->addTextBreak();
                        $textrun1->addText(htmlspecialchars('Sampling location:'), array('size' => 10, 'bold' => true, 'italic' => true));
                        foreach ($position_list as $key3 => $position) {
                            $textrun1 = $table->addCell(625, $styleCell);
                            $textrun1->addText(htmlspecialchars($position->string_id), array('size' => 10), $fontCell);
                        }
                        $table->addRow();
                        $cell1 = $table->addCell(2000, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Ngày / "), array('size' => 10, 'bold' => true));
                        $textrun1->addText(htmlspecialchars("Date"), array('size' => 10, 'bold' => true, 'italic' => true));
                        $cell1 = $table->addCell(8000, array('gridSpan' => count($position_list), 'valign' => 'center'));
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Kết quả / "), array('size' => 10, 'bold' => true));
                        $textrun1->addText(htmlspecialchars("Results"), array('size' => 10, 'bold' => true, 'italic' => true));
                        ///DATA
                        $data = $this->result_model->get_data_table_v2($position_list, $params);
                        $data_min_max = $this->result_model->get_data_minmax_v2($position_list, $params['date_from'], $params['date_to']);
                        $data_min_max_prev = $this->result_model->get_data_minmax_v2($position_list, $params['date_from_prev'], $params['date_to_prev']);

                        foreach ($data as $keystt => $stt) {
                            $date = date("d/m/y", strtotime($stt['date']));

                            $table->addRow();
                            $cell1 = $table->addCell(2000, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText(htmlspecialchars($date), array('size' => 10));
                            foreach ($position_list as $position) {
                                $string_id = $position->string_id;
                                $value = $stt[$string_id];
                                $cell1 = $table->addCell(625, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }
                        // print_r($position_list);
                        $templateProcessor->setComplexBlock("area_table#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($key2 + 1), $table);
                    }

                    // die();
                    // $table->addRow();
                    // $cell1 = $table->addCell(2000, $cellRowSpan);
                    // $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    // $textrun1->addText(htmlspecialchars("Vị trí lấy mẫu:"));
                    // $textrun1->addText(htmlspecialchars('Sampling location:'), array('italic' => true));
                    // for ($key2 = 0; $key2 < count($position_list); $key2++) {
                    //     $position = $position_list[$key2];
                    //     $cell1 = $table->addCell(2000, $cellHCentered);
                    //     $textrun1 = $cell1->addTextRun($cellHCentered);
                    //     // $textrun1->addText($position->string_id);
                    // }
                    // $templateProcessor->setComplexBlock("area_table#" . ($key + 1) . "#" . ($key1 + 1), $table);
                }
            }
            ////BIỂU ĐỒ

            $templateProcessor->cloneBlock("target_block", count($target_list), true, true);
            foreach ($target_list as $key => $target) {
                $templateProcessor->setValue("target_heading#" . ($key + 1), "5.2." . ($key + 1));
                $templateProcessor->setValue("target_name#" . ($key + 1), $target->name);
                $templateProcessor->setValue("target_name_en#" . ($key + 1), $target->name_en);
                $department_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('target_id' => $target->id))->with_area()->with_department()->group_by("department_id")->get_all();
                $department_list = array();
                $length_department = count($department_results);
                // $target_list[$key]->count = $length_department;
                $templateProcessor->cloneBlock("chart_block#" . ($key + 1), $length_department, true, true);
                for ($key1 = 0; $key1 < $length_department; $key1++) {
                    $department = $department_results[$key1]->department;
                    $area = $department_results[$key1]->area;
                    $target_id = $target->id;
                    $name_chart = $target->id . "_" . $department->id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . ".png";

                    $templateProcessor->setImageValue("chart_image#" . ($key + 1) . "#" . ($key1 + 1), array('path' => APPPATH . '../public/upload/chart/' . $name_chart, 'width' => 1000, 'height' => 300, 'ratio' => false));

                    $heading = "5.2." . ($key + 1) . "." . ($key1 + 1) . ". $department->name ($department->string_id), $area->name / $department->name_en ($department->string_id), $area->name_en";
                    $templateProcessor->setValue("chart_heading#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($heading));
                    $templateProcessor->setValue("department_name#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->name));
                    $templateProcessor->setValue("department_name_en#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->name_en));
                    $templateProcessor->setValue("area_name#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name));
                    $templateProcessor->setValue("area_name_en#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name_en));
                    $templateProcessor->setValue("department_id#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->string_id));
                }
            }
            // echo "<pre>";
            // print_r($target_list);
            // die();
            $name_file = "Bao_cao_" . $object_id . "_" . $workshop_id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . "_" . time() . ".docx";
            $name_file = urlencode($name_file);
            $templateProcessor->saveAs(APPPATH . '../public/export/' . $name_file);
            // die();
            // $templateProcessor->cloneRow("result_block#1", 3);
            $data_up = array(
                'name' => $name_file,
                'status' => 3
            );
            $this->report_model->update($data_up, $id_record);

            // redirect("dashboard", 'refresh');
            // header("Location: " . $_SERVER['HTTP_HOST'] . "/MyWordFile.docx");
        } else if ($object_id == 10) {
            $object = $this->object_model->where(array('id' => $object_id))->as_object()->get();
            $workshop_id = $record->workshop_id;

            $workshop = $this->workshop_model->where(array('id' => $workshop_id))->with_factory()->as_object()->get();
            $workshop_name = $workshop->name;
            $workshop_name_en = $workshop->name_en;
            $factory_name = isset($workshop->factory->name) ? $workshop->factory->name : "";
            $factory_name_en = isset($workshop->factory->name_en) ? $workshop->factory->name_en : "";
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
            $target_list = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->with_target()->group_by("target_id")->get_all();
            $target_list = array_map(function ($item) {
                return $item->target;
            }, $target_list);

            $area_list = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->with_area()->group_by("area_id")->get_all();
            $area_list = array_map(function ($item) {
                return $item->area;
            }, $area_list);
            // echo "<pre>";
            // print_r($area_list);
            // die();
            $file = APPPATH . '../public/upload/template/template_thietbi.docx';
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file);
            $type_bc = "Hàng năm";
            $type_bc_en = "Yearly";
            if ($type == "Year") {
                $type_bc = "Hàng năm";
                $type_bc_en = "Yearly";
            } elseif ($type == "Month") {
                $type_bc = "Hàng tháng";
                $type_bc_en = "Monthly";
            } elseif ($type == "HalfYear") {
                $type_bc = "Nữa năm";
                $type_bc_en = "Half Year";
            } elseif ($type == "Quarter") {
                $type_bc = "Hàng Quý";
                $type_bc_en = "Quarter";
            }
            $templateProcessor->setValue('type_bc', $type_bc);
            $templateProcessor->setValue('type_bc_en', $type_bc_en);
            $templateProcessor->setValue('date_from', date("d/m/y", strtotime($params['date_from'])));
            $templateProcessor->setValue('date_from_prev', date("d/m/y", strtotime($params['date_from_prev'])));
            $templateProcessor->setValue('date_to', date("d/m/y", strtotime($params['date_to'])));
            $templateProcessor->setValue('date_to_prev', date("d/m/y", strtotime($params['date_to_prev'])));
            $templateProcessor->setValue('workshop_name', $workshop_name);
            $templateProcessor->setValue('factory_name', $factory_name);
            $templateProcessor->setValue('workshop_name_en', $workshop_name_en);
            $templateProcessor->setValue('factory_name_en', $factory_name_en);


            ////STYLE
            $cellRowSpan = array('vMerge' => 'restart', 'valign' => 'center');
            $cellHCentered = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
            $cellHCenteredLEFT = array('alignment' => 'left');

            $styleCell = array('valign' => 'center');
            $fontCell = array('align' => 'center');
            $cellColSpan = array('gridSpan' => 4, 'valign' => 'center');
            $cellRowContinue = array('vMerge' => 'continue');
            $cellVCentered = array('valign' => 'center');
            // $target_list;
            ///TABLE LIMIT
            $table = new Table(array('borderSize' => 3, 'width' => 10000, 'unit' => TblWidth::TWIP, 'valign' => 'center'));
            $table->addRow();

            $cell1 = $table->addCell(2000, $cellRowSpan);
            $textrun = $table->addCell(6250, array('gridSpan' => 3, 'size' => 12, 'valign' => 'center'));
            $textrun->addText('Phương pháp lấy mẫu /', array(), $fontCell);
            $textrun->addText('Sampling method (CFU/plate)', array('italic' => true), $fontCell);
            $table->addRow();
            $table->addCell(null, $cellRowContinue);
            for ($i = 0; $i < count($target_list); $i++) {
                $textrun = $table->addCell(2000, $styleCell);
                $textrun->addText($target_list[$i]->name, array(), $fontCell);
                $textrun->addText($target_list[$i]->name_en, array('italic' => true), $fontCell);
            }
            foreach ($area_list as $row2) {
                $table->addRow();
                $cell1 = $table->addCell(8000, $cellColSpan);
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText($row2->name, array('bold' => true));

                $table->addRow();
                $textrun = $table->addCell(2000, $styleCell);
                $textrun->addText('Tiêu chuẩn chấp nhận', array(), $fontCell);
                $textrun->addText('Acceptance criteria', array('italic' => true), $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $this->limit_model->where(array("area_id" => $row2->id, 'target_id' => $target->id))->as_object()->get();
                    $target_list[$key]->limit = $limit;
                    // print_r($limit);
                    // die();  
                    //     ///DATA
                    $textrun = $table->addCell(2000, $fontCell);
                    $value = isset($limit->standard_limit) ? $limit->standard_limit : 0;
                    $textrun->addText($value, array(), $fontCell);
                }
                $table->addRow();
                $textrun = $table->addCell(2000, $styleCell);
                $textrun->addText('Giới hạn cảnh báo', array(), $fontCell);
                $textrun->addText('Alert Limit', array('italic' => true), $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $target->limit;
                    $textrun = $table->addCell(2000, $fontCell);
                    $value = isset($limit->alert_limit) ? $limit->alert_limit : 0;
                    $textrun->addText($value, array(), $fontCell);
                }
                $table->addRow();
                $textrun = $table->addCell(2000, $styleCell);
                $textrun->addText('Giới hạn hành động', array(), $fontCell);
                $textrun->addText('Action Limit', array('italic' => true), $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $target->limit;
                    $textrun = $table->addCell(2000, $fontCell);
                    $value = isset($limit->action_limit) ? $limit->action_limit : 0;
                    $textrun->addText($value, array(), $fontCell);
                }
            }

            $templateProcessor->setComplexBlock('table_limit', $table);

            /////RESULT 
            $templateProcessor->cloneBlock("result_target_block", count($target_list), true, true);
            foreach ($target_list as $key => $target) {
                $templateProcessor->setValue("target_heading#" . ($key + 1), "5.1." . ($key + 1));
                $templateProcessor->setValue("target_name#" . ($key + 1), $target->name);
                $templateProcessor->setValue("target_name_en#" . ($key + 1), $target->name_en);
                $area_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('target_id' => $target->id))->with_area()->group_by("area_id")->get_all();
                $department_list = array();
                $length_area = count($area_results);
                $templateProcessor->cloneBlock("area_block#" . ($key + 1), $length_area, true, true);
                for ($key1 = 0; $key1 < $length_area; $key1++) {
                    $area = $area_results[$key1]->area;
                    $department_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('target_id' => $target->id))->where(array('area_id' => $area->id))->with_department()->group_by("department_id")->get_all();
                    $length_department = count($department_results);
                    $templateProcessor->setValue("area_heading#" . ($key + 1) . "#" . ($key1 + 1), "5.1." . ($key + 1) . "." . ($key1 + 1));
                    $templateProcessor->setValue("area_name#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name));
                    $templateProcessor->setValue("area_name_en#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name_en));
                    // $textrun1 = $table->addCell(1000, $styleCell);
                    // $textrun1->addText(htmlspecialchars("Tên phòng:"), $fontCell);
                    // $textrun1->addText(htmlspecialchars('Room name:'), array('italic' => true), $fontCell);
                    // $textrun1 = $table->addCell(1000, $styleCell);
                    // $textrun1->addText(htmlspecialchars("Tên phòng:"), $fontCell);
                    // $textrun1->addText(htmlspecialchars('Room name:'), array('italic' => true), $fontCell);
                    // $textrun1 = $table->addCell(1000, $styleCell);
                    // $textrun1->addText(htmlspecialchars("Tên phòng:"), $fontCell);
                    // $textrun1->addText(htmlspecialchars('Room name:'), array('italic' => true), $fontCell);
                    // $position_list = array();
                    $number_position = 0;
                    $list_department_tmp = array();
                    $table_data = array();
                    for ($key2 = 0; $key2 < $length_department; $key2++) {
                        $department = $department_results[$key2]->department;
                        $position_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('target_id' => $target->id))->where(array('department_id' => $department->id))->with_position()->group_by("position_id")->get_all();
                        $length_position = count($position_results);
                        $list_position = array();
                        for ($key3 = 0; $key3 < $length_position; $key3++) {
                            $position = $position_results[$key3]->position;
                            $list_position[] = $position;
                        }
                        $department->list_position = $list_position;

                        if ($length_position > 12) {
                            $table_data[] = array($department);
                            continue;
                        }
                        if ($number_position + $length_position > 12) {
                            $table_data[] = $list_department_tmp;
                            $number_position = 0;
                            $list_department_tmp = array();
                        } else {
                            $list_department_tmp[] = $department;
                            $number_position += $length_position;
                        }
                    }
                    if (count($list_department_tmp)) {
                        $table_data[] = $list_department_tmp;
                    }
                    // echo "<pre>";
                    // print_r($table_data);
                    // die();
                    $templateProcessor->cloneBlock("group_block#" . ($key + 1) . "#" . ($key1 + 1), count($table_data), true, true);
                    foreach ($table_data as $key2 => $t_data) {
                        ///TABLE
                        $table = new Table(array('borderSize' => 3, 'width' => 10000, 'size' => 10, 'unit' => TblWidth::TWIP, 'valign' => 'center'));
                        $table->addRow();
                        $cell1 = $table->addCell(2000, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                        $textrun1->addText(htmlspecialchars("Tên phòng:"), array('size' => 10, 'bold' => true));
                        $textrun1->addTextBreak();
                        $textrun1->addText(htmlspecialchars('Room name:'), array('size' => 10, 'bold' => true, 'italic' => true));
                        $position_list = array();
                        foreach ($t_data as $key3 => $department) {
                            $textrun1 = $table->addCell(1200, array('gridSpan' => count($department->list_position), 'valign' => 'center'));
                            $textrun1->addText(htmlspecialchars($department->name), array('size' => 10), $fontCell);
                            $textrun1->addText(htmlspecialchars($department->name_en), array('size' => 10, 'italic' => true), $fontCell);
                            $position_list = array_merge($position_list, $department->list_position);
                        }
                        $table->addRow();
                        $cell1 = $table->addCell(2000, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                        $textrun1->addText(htmlspecialchars("Vị trí lấy mẫu:"), array('size' => 10, 'bold' => true));
                        $textrun1->addTextBreak();
                        $textrun1->addText(htmlspecialchars('Sampling location:'), array('size' => 10, 'bold' => true, 'italic' => true));
                        foreach ($position_list as $key3 => $position) {
                            $textrun1 = $table->addCell(625, $styleCell);
                            $textrun1->addText(htmlspecialchars($position->string_id), array('size' => 10), $fontCell);
                        }
                        $table->addRow();
                        $cell1 = $table->addCell(2000, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Ngày / "), array('size' => 10, 'bold' => true));
                        $textrun1->addText(htmlspecialchars("Date"), array('size' => 10, 'bold' => true, 'italic' => true));
                        $cell1 = $table->addCell(8000, array('gridSpan' => count($position_list), 'valign' => 'center'));
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Kết quả / "), array('size' => 10, 'bold' => true));
                        $textrun1->addText(htmlspecialchars("Results"), array('size' => 10, 'bold' => true, 'italic' => true));
                        ///DATA
                        $data = $this->result_model->get_data_table_v2($position_list, $params);
                        $data_min_max = $this->result_model->get_data_minmax_v2($position_list, $params['date_from'], $params['date_to']);
                        $data_min_max_prev = $this->result_model->get_data_minmax_v2($position_list, $params['date_from_prev'], $params['date_to_prev']);

                        foreach ($data as $keystt => $stt) {
                            $date = date("d/m/y", strtotime($stt['date']));

                            $table->addRow();
                            $cell1 = $table->addCell(2000, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText(htmlspecialchars($date), array('size' => 10));
                            foreach ($position_list as $position) {
                                $string_id = $position->string_id;
                                $value = $stt[$string_id];
                                $cell1 = $table->addCell(625, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }
                        // print_r($position_list);
                        $templateProcessor->setComplexBlock("area_table#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($key2 + 1), $table);
                    }

                    // die();
                    // $table->addRow();
                    // $cell1 = $table->addCell(2000, $cellRowSpan);
                    // $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    // $textrun1->addText(htmlspecialchars("Vị trí lấy mẫu:"));
                    // $textrun1->addText(htmlspecialchars('Sampling location:'), array('italic' => true));
                    // for ($key2 = 0; $key2 < count($position_list); $key2++) {
                    //     $position = $position_list[$key2];
                    //     $cell1 = $table->addCell(2000, $cellHCentered);
                    //     $textrun1 = $cell1->addTextRun($cellHCentered);
                    //     // $textrun1->addText($position->string_id);
                    // }
                    // $templateProcessor->setComplexBlock("area_table#" . ($key + 1) . "#" . ($key1 + 1), $table);
                }
            }
            ////BIỂU ĐỒ

            $templateProcessor->cloneBlock("target_block", count($target_list), true, true);
            foreach ($target_list as $key => $target) {
                $templateProcessor->setValue("target_heading#" . ($key + 1), "5.2." . ($key + 1));
                $templateProcessor->setValue("target_name#" . ($key + 1), $target->name);
                $templateProcessor->setValue("target_name_en#" . ($key + 1), $target->name_en);
                $department_results = $this->result_model->where('date', '>=', $params['date_from'])->where('date', '<=', $params['date_to'])->where(array('workshop_id' => $workshop_id, 'deleted' => 0, 'object_id' => $object_id))->where(array('target_id' => $target->id))->with_area()->with_department()->group_by("department_id")->get_all();
                $department_list = array();
                $length_department = count($department_results);
                // $target_list[$key]->count = $length_department;
                $templateProcessor->cloneBlock("chart_block#" . ($key + 1), $length_department, true, true);
                for ($key1 = 0; $key1 < $length_department; $key1++) {
                    $department = $department_results[$key1]->department;
                    $area = $department_results[$key1]->area;
                    $target_id = $target->id;
                    $name_chart = $target->id . "_" . $department->id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . ".png";

                    $templateProcessor->setImageValue("chart_image#" . ($key + 1) . "#" . ($key1 + 1), array('path' => APPPATH . '../public/upload/chart/' . $name_chart, 'width' => 1000, 'height' => 300, 'ratio' => false));

                    $heading = "5.2." . ($key + 1) . "." . ($key1 + 1) . ". $department->name ($department->string_id), $area->name / $department->name_en ($department->string_id), $area->name_en";
                    $templateProcessor->setValue("chart_heading#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($heading));
                    $templateProcessor->setValue("department_name#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->name));
                    $templateProcessor->setValue("department_name_en#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->name_en));
                    $templateProcessor->setValue("area_name#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name));
                    $templateProcessor->setValue("area_name_en#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name_en));
                    $templateProcessor->setValue("department_id#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->string_id));
                }
            }
            // echo "<pre>";
            // print_r($target_list);
            // die();
            $name_file = "Bao_cao_" . $object_id . "_" . $workshop_id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . "_" . time() . ".docx";
            $name_file = urlencode($name_file);
            $templateProcessor->saveAs(APPPATH . '../public/export/' . $name_file);
            // die();
            // $templateProcessor->cloneRow("result_block#1", 3);
            $data_up = array(
                'name' => $name_file,
                'status' => 3
            );
            $this->report_model->update($data_up, $id_record);

            // redirect("dashboard", 'refresh');
            // header("Location: " . $_SERVER['HTTP_HOST'] . "/MyWordFile.docx");
        }
    }

    function cronjob()
    {
        $this->load->model("report_model");
        $report = $this->report_model->where(array('deleted' => 0, 'status' => 1))->order_by("id", "ASC")->limit(1)->get();
        if (!empty($report)) {
            $id_record = $report->id;
            $this->report_model->update(array('status' => 2), $id_record);
            $this->export($id_record);
        }
        echo 1;
    }

    function cronjob2()
    {
        $this->load->model("report_model");
        $report = $this->report_model->where(array('deleted' => 0, 'status' => 1))->order_by("id", "ASC")->limit(1)->get();
        if (!empty($report)) {
            $id_record = $report->id;
            $this->report_model->update(array('status' => 2), $id_record);
            $this->export($id_record);
        }
        echo 1;
    }
    ////////////
}
