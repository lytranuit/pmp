<?php


use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class Export extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
    }


    public function export($params)
    {
        set_time_limit(-1);

        $id_record = $params[0];
        // $id_record = $this->input->get('id_record', TRUE);

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
                    $name_chart = $target_id . "_" . $department->id . "_" . $params['type'] . "_" . $params['selector'] . ".png";
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


            $name_file = "Bao_cao_" . $object_id . "_" . $workshop_id . "_" . $params['type'] . "_" . $params['selector'] . "_" . time() . ".docx";
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
        }
    }
    function cronjob()
    {
        $this->load->model("report_model");
        $report = $this->report_model->where(array('deleted' => 0, 'status' => 1))->order_by("id", "ASC")->limit(1)->get();
        if (!empty($report)) {
            $id_record = $report->id;
            $this->report_model->update(array('status' => 2), $id_record);
            $this->export(array($id_record));
        }
        echo 1;
    }
    ////////////
}
