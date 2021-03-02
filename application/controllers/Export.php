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
        $this->load->model("objecttarget_model");
        $this->load->model("object_model");
        $this->load->model("system_model");
        $this->load->model("diagram_model");
        $this->load->model("diagram_position_model");
        $record = $this->report_model->where(array("id" => $id_record))->get();
        // print_r($record);
        // die();
        $object_id = $record->object_id;
        //        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 1;
        //        $object_name = isset($_COOKIE['SELECT_NAME']) ? $_COOKIE['SELECT_NAME'] : "";
        if ($object_id == 3) {

            $this->load->model("employee_model");
            $this->load->model("employeeresult_model");
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
            $area_list = $this->employeeresult_model->set_value_export($params)->with_area()->group_by("area_id")->get_all();
            $area_all = array_map(function ($item) use ($params) {
                $nhanvien = $this->employeeresult_model->set_value_export($params)->where(array('area_id' => $item->area_id))->with_employee()->group_by("employee_id")->get_all();
                $nhanvien =  array_map(function ($item) {
                    return $item->employee;
                }, $nhanvien);
                $item->area->nhanvien = $nhanvien;
                return $item->area;
            }, $area_list);
            usort($area_all, function ($a, $b) {
                return strcmp($a->name, $b->name);
            });

            // $area_all = $this->employeeresult_model->area_export($params);
            // $nhanvien_all = $this->employeeresult_model->set_value_export($params)->with_employee()->group_by("employee_id")->get_all();
            // $nhanvien_all = array_map(function ($item) {
            //     return $item->employee;
            // }, $nhanvien_all);
            // foreach ($area_all as &$row) {
            //     $new_array = array_values(array_filter($nhanvien_all, function ($obj) use ($row) {
            //         return $obj->area_id == $row->id;
            //     }));
            //     $row->nhanvien = $new_array;
            // }
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
            } elseif ($type == "TwoYear") {
                $type_bc = "mỗi hai năm";
                $type_bc_en = "every two year";
            }
            $templateProcessor->setValue('date_from', date("d/m/y", strtotime($params['date_from'])));
            $templateProcessor->setValue('date_from_prev', date("d/m/y", strtotime($params['date_from_prev'])));
            $templateProcessor->setValue('date_to', date("d/m/y", strtotime($params['date_to'])));
            $templateProcessor->setValue('date_to_prev', date("d/m/y", strtotime($params['date_to_prev'])));
            $templateProcessor->setValue('type_bc', $type_bc);
            $templateProcessor->setValue('type_bc_en', $type_bc_en);
            $templateProcessor->setValue('workshop_name', $workshop_name);
            $templateProcessor->setValue('workshop_name_en', $workshop_name_en);
            $templateProcessor->setValue('type_bc_cap', mb_strtoupper($type_bc, 'UTF-8'));
            $templateProcessor->setValue('type_bc_cap_en', mb_strtoupper($type_bc_en, 'UTF-8'));
            $templateProcessor->setValue('workshop_name_cap', mb_strtoupper($workshop_name, 'UTF-8'));
            $templateProcessor->setValue('workshop_name_cap_en', mb_strtoupper($workshop_name_en, 'UTF-8'));


            ////STYLE
            $cellRowSpan = array('valign' => 'center');
            $cellHCentered = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
            $cellHCenteredLEFT = array('alignment' => 'left');

            $styleCell = array('valign' => 'center');
            $fontCell = array('align' => 'center');
            $cellColSpan = array('gridSpan' => 4, 'valign' => 'center');
            $cellRowContinue = array('vMerge' => 'continue', 'valign' => 'center');
            $cellVCentered = array('valign' => 'center');

            $normal = array('size' => 10);
            $italic =  array('italic' => true, 'size' => 10);
            ////TABLE VITRI

            $table = new Table(array(
                'borderSize' => 3, 'cellMargin'  => 80, 'width' => 100 * 50, 'size' => 10, 'unit' => 'pct', 'valign' => 'center',
                'layout'      => "autofit"
            ));
            $table->addRow(null, array('tblHeader' => true));
            $textrun = $table->addCell(null, $styleCell);
            $textrun->addText('Vị trí lấy mẫu', $normal, $fontCell);
            $textrun->addText('Sampling locations', $italic, $fontCell);
            $textrun = $table->addCell(null, $styleCell);
            $textrun->addText('Tên nhân viên', $normal, $fontCell);
            $textrun->addText('Name of personnel', $italic, $fontCell);
            $textrun = $table->addCell(null, $styleCell);
            $textrun->addText('Mã số nhân viên', $normal, $fontCell);
            $textrun->addText('ID No.', $italic, $fontCell);
            $textrun = $table->addCell(null, $styleCell);
            $textrun->addText('Tần suất', $normal, $fontCell);
            $textrun->addText('Frequency', $italic, $fontCell);

            foreach ($area_all as $row2) {
                ///DATA
                $table->addRow();
                $cell1 = $table->addCell(null, $cellColSpan);
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText($row2->name, array('bold' => true));
                $table->addRow();
                $cell1 = $table->addCell(null, array('vMerge' => 'restart', 'valign' => 'center'));
                $textrun1 = $cell1->addTextRun(array('alignment' => 'left',));
                $textrun1->addText("Đầu / ");
                $textrun1->addText('Head', $italic);
                $textrun1->addTextBreak();
                $textrun1->addText("Mũi / ");
                $textrun1->addText('Nose', $italic);
                $textrun1->addTextBreak();
                $textrun1->addText("Ngực / ");
                $textrun1->addText('Chest', $italic);
                $textrun1->addTextBreak();
                $textrun1->addText("Cẳng tay trái / ");
                $textrun1->addText('Left forearm', $italic);
                $textrun1->addTextBreak();
                $textrun1->addText("Cẳng tay phải / ");
                $textrun1->addText('Right forearm', $italic);
                $textrun1->addTextBreak();
                $textrun1->addText("Dấu găng tay trái / ");
                $textrun1->addText('Left glove print 5 fingers', $italic);
                $textrun1->addTextBreak();
                $textrun1->addText("Dấu găng tay phải / ");
                $textrun1->addText('Right glove print 5 fingers', $italic);
                $textrun1->addTextBreak();
                $nhanvien = $row2->nhanvien;
                // echo "<pre>";
                // print_r($nhanvien);
                // die();
                if (count($nhanvien)) {
                    $table->addCell(2000, $cellVCentered)->addText($nhanvien[0]->name, null, $cellHCentered);
                    $table->addCell(1000, $cellVCentered)->addText($nhanvien[0]->string_id, null, $cellHCentered);
                }
                $cell1 = $table->addCell(null, array('vMerge' => 'restart', 'valign' => 'center'));
                $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                $textrun1->addText("Nhân viên phải được lấy mẫu sau khi hoàn tất hoạt động trong ngày và trước khi nhân viên ra khỏi khu vực vô trùng.");
                $textrun1->addTextBreak();
                $textrun1->addText('Samples shall be collected after completion of operations for that day, before personnel go out of the aseptic areas.', $italic);
                for ($i = 1; $i <= count($nhanvien) - 1; $i++) {
                    $table->addRow();
                    $table->addCell(null, $cellRowContinue);
                    $table->addCell(2000, $cellVCentered)->addText($nhanvien[$i]->name, null, $cellHCentered);
                    $table->addCell(1000, $cellVCentered)->addText($nhanvien[$i]->string_id, null, $cellHCentered);
                    $table->addCell(null, $cellRowContinue);
                }
            }

            $templateProcessor->setComplexBlock('table_vitri', $table);
            ////END TABLE VI TRI
            ///TABLE LIMIT
            $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 100 * 50, 'size' => 10, 'unit' => 'pct', 'valign' => 'center'));
            $table->addRow();
            $textrun = $table->addCell(null, $styleCell);
            $textrun->addText('Vị trí lấy mẫu', $normal, $fontCell);
            $textrun->addText('Sampling locations', $italic, $fontCell);
            $textrun = $table->addCell(null, $styleCell);
            $textrun->addText('Đầu', $normal, $fontCell);
            $textrun->addText('Head', $italic, $fontCell);
            $textrun = $table->addCell(null, $styleCell);
            $textrun->addText('Mũi', $normal, $fontCell);
            $textrun->addText('Nose', $italic, $fontCell);
            $textrun = $table->addCell(null, $styleCell);
            $textrun->addText('Ngực', $normal, $fontCell);
            $textrun->addText('Chest', $italic, $fontCell);
            $textrun = $table->addCell(null, $styleCell);
            $textrun->addText('Cẳng tay trái', $normal, $fontCell);
            $textrun->addText('Left forearm', $italic, $fontCell);
            $textrun = $table->addCell(null, $styleCell);
            $textrun->addText('Cẳng tay phải', $normal, $fontCell);
            $textrun->addText('Right forearm', $italic, $fontCell);
            $textrun = $table->addCell(null, $styleCell);
            $textrun->addText('Dấu găng tay trái', $normal, $fontCell);
            $textrun->addText('Left glove print 5 fingers', $italic, $fontCell);
            $textrun = $table->addCell(null, $styleCell);
            $textrun->addText('Dấu găng tay phải', $normal, $fontCell);
            $textrun->addText('Right glove print 5 fingers', $italic, $fontCell);

            foreach ($area_all as $row2) {
                $limit = $this->limit_model->where(array("area_id" => $row2->id, 'target_id' => 6))->where("day_effect", "<=", $params['date_from'])->order_by("day_effect", "DESC")->limit(1)->as_object()->get();
                // print_r($limit);
                // die();  
                //     ///DATA
                $table->addRow();
                $cell1 = $table->addCell(null, array('gridSpan' => 8, 'valign' => 'center'));
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText($row2->name, array('bold' => true));
                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Tiêu chuẩn chấp nhận', $normal, $fontCell);
                $textrun->addText('Acceptance criteria', $italic, $fontCell);
                $textrun = $table->addCell(null, array('gridSpan' => 7, 'valign' => 'center'));
                $value = isset($limit->standard_limit) ? $limit->standard_limit : "";
                $textrun->addText($value, $normal, $fontCell);
                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Giới hạn cảnh báo', $normal, $fontCell);
                $textrun->addText('Alert Limit', $italic, $fontCell);
                $textrun = $table->addCell(null, array('gridSpan' => 7, 'valign' => 'center'));
                $value = isset($limit->alert_limit) ? $limit->alert_limit : "";
                $textrun->addText($value, $normal, $fontCell);
                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Giới hạn hành động', $normal, $fontCell);
                $textrun->addText('Action Limit', $italic, $fontCell);
                $textrun = $table->addCell(null, array('gridSpan' => 7, 'valign' => 'center'));
                $value = isset($limit->action_limit) ? $limit->action_limit : "";
                $textrun->addText($value, $normal, $fontCell);
            }

            $templateProcessor->setComplexBlock('table_limit', $table);

            $templateProcessor->cloneBlock("area_block", count($area_all), true, true);
            foreach ($area_all as $key => $area) {
                $templateProcessor->setValue("area_heading#" . ($key + 1), "5." . ($key + 1));
                $templateProcessor->setValue("area_name#" . ($key + 1), $area->name);
                $templateProcessor->setValue("area_name_en#" . ($key + 1), $area->name_en);
                $nhanvien = $area->nhanvien;
                $length_employee = count($nhanvien);
                $templateProcessor->cloneBlock("department_block#" . ($key + 1), $length_employee, true, true);
                // echo "<pre>";
                // print_r($length_employee);
                // die();

                for ($key1 = 0; $key1 < $length_employee; $key1++) {
                    $employee = $nhanvien[$key1];
                    $id =  $employee->string_id;
                    $target_id = 6;
                    $name_chart = $object_id . "_" . $target_id . "_" . $area->id . "_" . $employee->id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . ".png";

                    ///CHART
                    $templateProcessor->setImageValue("chart_image#" . ($key + 1) . "#" . ($key1 + 1), array('path' => APPPATH . '../public/upload/chart/' . $name_chart, 'width' => 1000, 'height' => 300, 'ratio' => false));

                    $templateProcessor->setValue("department_heading#" . ($key + 1) . "#" . ($key1 + 1), "5." . ($key + 1) . "." . ($key1 + 1));
                    $templateProcessor->setValue("department_name#" . ($key + 1) . "#" . ($key1 + 1), $employee->name);
                    $templateProcessor->setValue("department_name_en#" . ($key + 1) . "#" . ($key1 + 1), $employee->name);
                    $templateProcessor->setValue("department_id#" . ($key + 1) . "#" . ($key1 + 1), $id);

                    $data = $this->employeeresult_model->set_value_export($params)->where(array('area_id' => $area->id, 'employee_id' => $employee->id))->as_array()->get_all();
                    $data_min_max = $this->employeeresult_model->get_data_minmax($employee->id, $area->id, $params['date_from'], $params['date_to']);
                    $data_min_max_prev = $this->employeeresult_model->get_data_minmax($employee->id, $area->id, $params['date_from_prev'], $params['date_to_prev']);

                    // echo "<pre>";
                    // print_r($data_min_max);
                    // die();
                    ///TABLE RESULT
                    $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 100 * 50, 'size' => 10, 'unit' => 'pct', 'valign' => 'center'));
                    $table->addRow(null, array('tblHeader' => true));
                    $cell1 = $table->addCell(null, $styleCell);
                    $cell1->addText("Stt", array('size' => 10), $fontCell);
                    $cell1->addText("No.", array('size' => 10, 'italic' => true), $fontCell);

                    $cell1 = $table->addCell(null, $styleCell);
                    $cell1->addText("Ngày", array('size' => 10), $fontCell);
                    $cell1->addText("Date", array('size' => 10, 'italic' => true), $fontCell);
                    $cell1->addText("(dd/mm/yy)", $normal, $fontCell);

                    $textrun = $table->addCell(null, $styleCell);
                    $textrun->addText('Đầu', array('size' => 10), $fontCell);
                    $textrun->addText('Head', array('size' => 10, 'italic' => true), $fontCell);

                    $textrun = $table->addCell(null, $styleCell);
                    $textrun->addText('Mũi', array('size' => 10), $fontCell);
                    $textrun->addText('Nose', array('size' => 10, 'italic' => true), $fontCell);

                    $textrun = $table->addCell(null, $styleCell);
                    $textrun->addText('Ngực', array('size' => 10), $fontCell);
                    $textrun->addText('Chest', array('size' => 10, 'italic' => true), $fontCell);

                    $textrun = $table->addCell(null, $styleCell);
                    $textrun->addText('Cẳng tay trái', array('size' => 10), $fontCell);
                    $textrun->addText('Left forearm', array('size' => 10, 'italic' => true), $fontCell);

                    $textrun = $table->addCell(null, $styleCell);
                    $textrun->addText('Cẳng tay phải', array('size' => 10), $fontCell);
                    $textrun->addText('Right forearm', array('size' => 10, 'italic' => true), $fontCell);

                    $textrun = $table->addCell(null, $styleCell);
                    $textrun->addText('Dấu găng tay trái', array('size' => 10), $fontCell);
                    $textrun->addText('Left glove print 5 fingers', array('size' => 10, 'italic' => true), $fontCell);

                    $textrun = $table->addCell(null, $styleCell);
                    $textrun->addText('Dấu găng tay phải', $normal, $fontCell);
                    $textrun->addText('Right glove print 5 fingers', $italic, $fontCell);
                    // echo "<pre>";
                    // print_r($data);
                    // die();
                    foreach ($data as $keystt => $stt) {
                        $table->addRow(null);

                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText(($keystt + 1), array('size' => 10), $fontCell);

                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText(date("d/m/y", strtotime($stt['date'])), array('size' => 10), $fontCell);


                        if ($stt["value_H"] == "") {
                            $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                        } else {
                            $cell1 = $table->addCell(null, $styleCell);
                            $cell1->addText($stt["value_H"], array('size' => 10), $fontCell);
                        }

                        if ($stt["value_N"] == "") {
                            $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                        } else {
                            $cell1 = $table->addCell(null, $styleCell);
                            $cell1->addText($stt["value_N"], array('size' => 10), $fontCell);
                        }

                        if ($stt["value_C"] == "") {
                            $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                        } else {
                            $cell1 = $table->addCell(null, $styleCell);
                            $cell1->addText($stt["value_C"], array('size' => 10), $fontCell);
                        }

                        if ($stt["value_LF"] == "") {
                            $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                        } else {
                            $cell1 = $table->addCell(null, $styleCell);
                            $cell1->addText($stt["value_LF"], array('size' => 10), $fontCell);
                        }

                        if ($stt["value_RF"] == "") {
                            $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                        } else {
                            $cell1 = $table->addCell(null, $styleCell);
                            $cell1->addText($stt["value_RF"], array('size' => 10), $fontCell);
                        }

                        if ($stt["value_LG"] == "") {
                            $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                        } else {
                            $cell1 = $table->addCell(null, $styleCell);
                            $cell1->addText($stt["value_LG"], array('size' => 10), $fontCell);
                        }
                        if ($stt["value_RG"] == "") {
                            $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                        } else {
                            $cell1 = $table->addCell(null, $styleCell);
                            $cell1->addText($stt["value_RG"], array('size' => 10), $fontCell);
                        }
                    }
                    //MAX
                    $table->addRow(null);

                    $cell1 = $table->addCell(null, array('gridSpan' => 2, 'valign' => 'center'));
                    $cell1->addText("Max", array('size' => 10, 'bold' => true), $fontCell);

                    if ($data_min_max["max_H"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["max_H"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max["max_N"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["max_N"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max["max_C"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["max_C"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max["max_LF"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["max_LF"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max["max_RF"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["max_RF"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max["max_LG"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["max_LG"], array('size' => 10), $fontCell);
                    }
                    if ($data_min_max["max_RG"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["max_RG"], array('size' => 10), $fontCell);
                    }
                    //MIN
                    $table->addRow(null);

                    $cell1 = $table->addCell(null, array('gridSpan' => 2, 'valign' => 'center'));
                    $cell1->addText("Min", array('size' => 10, 'bold' => true), $fontCell);

                    if ($data_min_max["min_H"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["min_H"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max["min_N"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["min_N"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max["min_C"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["min_C"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max["min_LF"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["min_LF"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max["min_RF"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["min_RF"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max["min_LG"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["min_LG"], array('size' => 10), $fontCell);
                    }
                    if ($data_min_max["min_RG"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max["min_RG"], array('size' => 10), $fontCell);
                    }
                    ////PREV
                    $table->addRow(null);
                    $cell1 = $table->addCell(null, array('gridSpan' => 9, 'valign' => 'center'));
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText("Kết quả của năm trước / ", array('size' => 10, 'bold' => true), $fontCell);
                    $textrun1->addText('Results of previous year', array('size' => 10, 'bold' => true, 'italic' => true), $fontCell);

                    //MAX
                    $table->addRow(null);
                    $cell1 = $table->addCell(null, array('gridSpan' => 2, 'valign' => 'center'));
                    $cell1->addText("Max", array('size' => 10, 'bold' => true), $fontCell);

                    if ($data_min_max_prev["max_H"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["max_H"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max_prev["max_N"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["max_N"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max_prev["max_C"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["max_C"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max_prev["max_LF"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["max_LF"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max_prev["max_RF"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["max_RF"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max_prev["max_LG"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["max_LG"], array('size' => 10), $fontCell);
                    }
                    if ($data_min_max_prev["max_RG"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["max_RG"], array('size' => 10), $fontCell);
                    }
                    //MIN
                    $table->addRow(null);

                    $cell1 = $table->addCell(null, array('gridSpan' => 2, 'valign' => 'center'));
                    $cell1->addText("Min", array('size' => 10, 'bold' => true), $fontCell);

                    if ($data_min_max_prev["min_H"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["min_H"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max_prev["min_N"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["min_N"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max_prev["min_C"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["min_C"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max_prev["min_LF"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["min_LF"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max_prev["min_RF"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["min_RF"], array('size' => 10), $fontCell);
                    }

                    if ($data_min_max_prev["min_LG"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["min_LG"], array('size' => 10), $fontCell);
                    }
                    if ($data_min_max_prev["min_RG"] == "") {
                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                    } else {
                        $cell1 = $table->addCell(null, $styleCell);
                        $cell1->addText($data_min_max_prev["min_RG"], array('size' => 10), $fontCell);
                    }
                    $templateProcessor->setComplexBlock("table_result#" . ($key + 1) . "#" . ($key1 + 1), $table);
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
            $target_list = $this->result_model->set_value_export($params)->with_target()->group_by("target_id")->get_all();

            $target_list = array_map(function ($item) {
                return $item->target;
            }, $target_list);

            $area_list = $this->result_model->set_value_export($params)->with_area()->group_by("area_id")->get_all();
            $area_list = array_map(function ($item) {
                return $item->area;
            }, $area_list);
            usort($area_list, function ($a, $b) {
                return strcmp($a->name, $b->name);
            });
            // echo "<pre>";
            // // print_r($params);
            // print_r($area_list);
            // die();
            $file = APPPATH . '../public/upload/template/template_phong_nam.docx';
            if ($type != "Year") {
                $file = APPPATH . '../public/upload/template/template_phong.docx';
            }
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
            } elseif ($type == "TwoYear") {
                $type_bc = "mỗi hai năm";
                $type_bc_en = "every two year";
            }
            $templateProcessor->setValue('date_from', date("d/m/y", strtotime($params['date_from'])));
            $templateProcessor->setValue('date_from_prev', date("d/m/y", strtotime($params['date_from_prev'])));
            $templateProcessor->setValue('date_to', date("d/m/y", strtotime($params['date_to'])));
            $templateProcessor->setValue('date_to_prev', date("d/m/y", strtotime($params['date_to_prev'])));
            $templateProcessor->setValue('type_bc', $type_bc);
            $templateProcessor->setValue('type_bc_en', $type_bc_en);
            $templateProcessor->setValue('workshop_name', $workshop_name);
            $templateProcessor->setValue('workshop_name_en', $workshop_name_en);
            $templateProcessor->setValue('type_bc_cap', mb_strtoupper($type_bc, 'UTF-8'));
            $templateProcessor->setValue('type_bc_cap_en', mb_strtoupper($type_bc_en, 'UTF-8'));
            $templateProcessor->setValue('workshop_name_cap', mb_strtoupper($workshop_name, 'UTF-8'));
            $templateProcessor->setValue('workshop_name_cap_en', mb_strtoupper($workshop_name_en, 'UTF-8'));
            // $templateProcessor->setValue('factory_name', $factory_name);
            // $templateProcessor->setValue('factory_name_en', $factory_name_en);


            ////STYLE
            $cellRowSpan = array('valign' => 'center');
            $cellHCentered = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
            $cellHCenteredLEFT = array('alignment' => 'left');

            $styleCell = array('valign' => 'center');
            $fontCell = array('align' => 'center');
            $cellColSpan = array('gridSpan' => 4, 'valign' => 'center');
            $cellRowContinue = array('vMerge' => 'continue');
            $cellVCentered = array('valign' => 'center');

            $normal = array('size' => 10);
            $italic =  array('italic' => true, 'size' => 10);
            ////BLOCK POSITION
            $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 10000, 'unit' => TblWidth::TWIP, 'valign' => 'center'));
            $table->addRow(null, array('tblHeader' => true));
            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Mã số', $normal, $fontCell);
            $cell1->addText('ID No.', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Phương pháp lấy mẫu', $normal, $fontCell);
            $cell1->addText('Sampling method', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Vị trí lấy mẫu', $normal, $fontCell);
            $cell1->addText('Sampling location', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Tên phòng', $normal, $fontCell);
            $cell1->addText('Room name', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Mã số phòng', $normal, $fontCell);
            $cell1->addText('ID No. of room', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Tần suất', $normal, $fontCell);
            $cell1->addText('Frequency', $italic, $fontCell);

            foreach ($area_list as $row) {
                $table->addRow();
                $cell1 = $table->addCell(null, array('gridSpan' => 6, 'valign' => 'center'));
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText($row->name . "/", array('bold' => true));
                $textrun1->addText($row->name_en, array('bold' => true, 'italic' => true), $fontCell);

                $position_list = $this->result_model->set_value_export($params)->where(array("area_id" => $row->id))->with_target()->with_position()->with_department()->group_by("position_id")->get_all();
                // echo "<pre>";
                // print_r($position_list);
                // die();
                foreach ($position_list as $row2) {
                    $position = $row2->position;
                    $department = $row2->department;
                    $target = $row2->target;
                    $table->addRow();

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText(htmlspecialchars($position->string_id), $normal, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($target->name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($target->name_en), $italic, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($position->name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($position->name_en), $italic, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($department->name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($department->name_en), $italic, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText(htmlspecialchars($department->string_id), $normal, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($position->frequency_name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($position->frequency_name_en), $italic, $fontCell);
                }
            }

            $templateProcessor->setComplexBlock('table_position', $table);

            ////BLOCK DIAGRAM
            $position_list = $this->result_model->set_value_export($params)->group_by("position_id")->get_all();
            if (!empty($position_list)) {
                $position_list = array_map(function ($item) {
                    return $item->position_id;
                }, $position_list);
                $diagram_list = $this->diagram_position_model->where("position_id", "IN", $position_list)->with_diagram(array("with" => array("relation" => "images")))->group_by("diagram_id")->get_all();

                $diagram_list = array_map(function ($item) {
                    return $item->diagram;
                }, $diagram_list);
                $templateProcessor->cloneBlock("diagram_block", count($diagram_list), true, true);
                foreach ($diagram_list as $key => $row) {
                    $templateProcessor->setValue("diagram_name#" .  ($key + 1), $row->name);
                    $templateProcessor->setValue("diagram_name_en#" . ($key + 1), $row->name_en);
                    if (isset($row->images)) {
                        $row->images = array_values((array) $row->images);
                        if (count($row->images)) {
                            $templateProcessor->cloneBlock("image_block#" .  ($key + 1), count($row->images), true, true);
                            foreach ($row->images as $key_image => $image) {
                                $templateProcessor->setImageValue("diagram_image#"  . ($key + 1) . "#" . ($key_image + 1), array('path' => APPPATH . '../' . $image->src, 'width' => 600, 'height' => 500, 'ratio' => true));
                            }
                        } else {
                            $templateProcessor->deleteBlock("image_block#" .  ($key + 1));
                        }
                    }
                }
            } else {
                $templateProcessor->deleteBlock('diagram_block');
            }
            // $target_list;
            ///TABLE LIMIT
            $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 10000, 'unit' => TblWidth::TWIP, 'valign' => 'center'));
            $table->addRow(null, array('tblHeader' => true));

            $cell1 = $table->addCell(null, $cellRowSpan);
            $textrun = $table->addCell(null, array('gridSpan' => 3, 'size' => 10, 'valign' => 'center'));
            $textrun->addText('Phương pháp lấy mẫu /', $normal, $fontCell);
            $textrun->addText('Sampling method (CFU/plate)', $italic, $fontCell);
            $table->addRow();
            $table->addCell(null, $cellRowContinue);
            for ($i = 0; $i < count($target_list); $i++) {
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText(htmlspecialchars($target_list[$i]->name), $normal, $fontCell);
                $textrun->addText(htmlspecialchars($target_list[$i]->name_en), $italic, $fontCell);
            }
            foreach ($area_list as $row2) {
                $table->addRow();
                $cell1 = $table->addCell(null, array('gridSpan' => count($target_list) + 1, 'valign' => 'center'));
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText(htmlspecialchars($row2->name . "/"), array('bold' => true));
                $textrun1->addText(htmlspecialchars($row2->name_en), array('bold' => true, 'italic' => true), $fontCell);

                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Tiêu chuẩn chấp nhận', $normal, $fontCell);
                $textrun->addText('Acceptance criteria', $italic, $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $this->limit_model->where(array("area_id" => $row2->id, 'target_id' =>  $target->id))->where("day_effect", "<=", $params['date_from'])->order_by("day_effect", "DESC")->limit(1)->as_object()->get();
                    $target_list[$key]->limit = $limit;
                    // print_r($limit);
                    // die();  
                    //     ///DATA
                    $textrun = $table->addCell(null, $fontCell);
                    $value = isset($limit->standard_limit) ? $limit->standard_limit : "";
                    $textrun->addText($value, $normal, $fontCell);
                }
                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Giới hạn cảnh báo', $normal, $fontCell);
                $textrun->addText('Alert Limit', $italic, $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $target->limit;
                    $textrun = $table->addCell(null, $fontCell);
                    $value = isset($limit->alert_limit) ? $limit->alert_limit : "";
                    $textrun->addText($value, $normal, $fontCell);
                }
                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Giới hạn hành động', $normal, $fontCell);
                $textrun->addText('Action Limit', $italic, $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $target->limit;
                    $textrun = $table->addCell(null, $fontCell);
                    $value = isset($limit->action_limit) ? $limit->action_limit : "";
                    $textrun->addText($value, $normal, $fontCell);
                }
            }

            $templateProcessor->setComplexBlock('table_limit', $table);

            /////RESULT 
            $templateProcessor->cloneBlock("result_target_block", count($target_list), true, true);
            $head = "5.1.";
            if ($type != "Year") {
                $head = "2.";
            }
            foreach ($target_list as $key => $target) {
                $templateProcessor->setValue("target_heading#" . ($key + 1), $head . ($key + 1));
                $templateProcessor->setValue("target_name#" . ($key + 1), $target->name);
                $templateProcessor->setValue("target_name_en#" . ($key + 1), $target->name_en);
                $area_results = $this->result_model->set_value_export($params)->where(array('target_id' => $target->id))->with_area()->group_by("area_id")->get_all();
                usort($area_results, function ($a, $b) {
                    return strcmp($a->area->name, $b->area->name);
                });
                $department_list = array();
                $length_area = count($area_results);
                $templateProcessor->cloneBlock("area_block#" . ($key + 1), $length_area, true, true);
                for ($key1 = 0; $key1 < $length_area; $key1++) {
                    $area = $area_results[$key1]->area;
                    $department_results = $this->result_model->set_value_export($params)->where(array('target_id' => $target->id))->where(array('area_id' => $area->id))->with_department()->group_by("department_id")->get_all();
                    $length_department = count($department_results);
                    $templateProcessor->setValue("area_heading#" . ($key + 1) . "#" . ($key1 + 1), $head . ($key + 1) . "." . ($key1 + 1));
                    $templateProcessor->setValue("area_name#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name));
                    $templateProcessor->setValue("area_name_en#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name_en));

                    $number_position = 0;
                    $list_department_tmp = array();
                    $table_data = array();
                    for ($key2 = 0; $key2 < $length_department; $key2++) {
                        $department = $department_results[$key2]->department;
                        $position_results = $this->result_model->set_value_export($params)->where(array('target_id' => $target->id))->where(array('department_id' => $department->id))->with_position()->group_by("position_id")->get_all();
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
                        $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 100 * 50, 'size' => 10, 'unit' => 'pct', 'valign' => 'center'));
                        $table->addRow(null, array('tblHeader' => true));
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                        $textrun1->addText(htmlspecialchars("Tên phòng:"), array('size' => 10, 'bold' => true));
                        $textrun1->addTextBreak();
                        $textrun1->addText(htmlspecialchars('Room name:'), array('size' => 10, 'bold' => true, 'italic' => true));
                        $position_list = array();
                        foreach ($t_data as $key3 => $department) {
                            $textrun1 = $table->addCell(null, array('gridSpan' => count($department->list_position), 'valign' => 'center'));
                            $textrun1->addText(htmlspecialchars($department->name), array('size' => 10), $fontCell);
                            if ($department->name != $department->name_en)
                                $textrun1->addText(htmlspecialchars($department->name_en), array('size' => 10, 'italic' => true), $fontCell);
                            $textrun1->addText(htmlspecialchars("(" . $department->string_id . ")"), array('size' => 10, 'italic' => true), $fontCell);
                            $position_list = array_merge($position_list, $department->list_position);
                        }
                        $table->addRow(null, array('tblHeader' => true));
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                        $textrun1->addText(htmlspecialchars("Vị trí lấy mẫu:"), array('size' => 10, 'bold' => true));
                        $textrun1->addTextBreak();
                        $textrun1->addText(htmlspecialchars('Sampling location:'), array('size' => 10, 'bold' => true, 'italic' => true));
                        foreach ($position_list as $key3 => $position) {
                            $textrun1 = $table->addCell(null, $styleCell);
                            $textrun1->addText(htmlspecialchars($position->string_id), array('size' => 10), $fontCell);
                        }
                        $table->addRow(null, array('tblHeader' => true));
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Ngày / "), array('size' => 10, 'bold' => true));
                        $textrun1->addText(htmlspecialchars("Date"), array('size' => 10, 'bold' => true, 'italic' => true));
                        $cell1 = $table->addCell(null, array('gridSpan' => count($position_list), 'valign' => 'center'));
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Kết quả / "), array('size' => 10, 'bold' => true));
                        $textrun1->addText(htmlspecialchars("Results"), array('size' => 10, 'bold' => true, 'italic' => true));
                        ///DATA
                        $data = $this->result_model->get_data_table_v2($position_list, $params);

                        $data_min_max = $this->result_model->get_data_minmax_v2($position_list, $params['date_from'], $params['date_to']);
                        $data_min_max_prev = $this->result_model->get_data_minmax_v2($position_list, $params['date_from_prev'], $params['date_to_prev']);
                        // if($target->id == 4){
                        //     echo "<pre>";
                        //     print_r($position_list);
                        //     print_r($data_min_max);
                        //     die();
                        // }
                        foreach ($data as $keystt => $stt) {
                            $date = date("d/m/y", strtotime($stt['date']));

                            $table->addRow();
                            $cell1 = $table->addCell(null, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText(htmlspecialchars($date), array('size' => 10));
                            foreach ($position_list as $position) {
                                $string_id = $position->string_id;
                                $value = $stt[$string_id];
                                if ($value == "") {
                                    $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                                } else {
                                    $cell1 = $table->addCell(null, $cellRowSpan);
                                    $textrun1 = $cell1->addTextRun($cellHCentered);
                                    $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                                }
                            }
                        }
                        $table->addRow(null);
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Max"), array('size' => 10, 'bold' => true));
                        foreach ($position_list as $position) {
                            $string_id = $position->string_id;
                            $value = $data_min_max["max_$string_id"];
                            if ($value == "") {
                                $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                            } else {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }
                        $table->addRow(null);
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Min"), array('size' => 10, 'bold' => true));
                        foreach ($position_list as $position) {
                            $string_id = $position->string_id;
                            $value = $data_min_max["min_$string_id"];
                            if ($value == "") {
                                $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                            } else {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }

                        $table->addRow(null);
                        $cell1 = $table->addCell(null, array('gridSpan' => count($position_list) + 1, 'valign' => 'center'));
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Kết quả trước đó / "), array('size' => 10, 'bold' => true));
                        $textrun1->addText(htmlspecialchars("Results of previous"), array('size' => 10, 'bold' => true, 'italic' => true));

                        $table->addRow(null);
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Max"), array('size' => 10, 'bold' => true));
                        foreach ($position_list as $position) {
                            $string_id = $position->string_id;
                            $value = $data_min_max_prev["max_$string_id"];
                            if ($value == "") {
                                $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                            } else {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }
                        $table->addRow(null);
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Min"), array('size' => 10, 'bold' => true));
                        foreach ($position_list as $position) {
                            $string_id = $position->string_id;
                            $value = $data_min_max_prev["min_$string_id"];
                            if ($value == "") {
                                $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                            } else {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }

                        // print_r($position_list);
                        $templateProcessor->setComplexBlock("area_table#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($key2 + 1), $table);
                    }
                }
            }
            // die();
            ////BIỂU ĐỒ

            $templateProcessor->cloneBlock("target_block", count($target_list), true, true);
            $head = "5.2.";
            if ($type != "Year") {
                $head = "3.";
            }
            foreach ($target_list as $key => $target) {
                $templateProcessor->setValue("target_heading#" . ($key + 1),  $head . ($key + 1));
                $templateProcessor->setValue("target_name#" . ($key + 1), $target->name);
                $templateProcessor->setValue("target_name_en#" . ($key + 1), $target->name_en);
                $department_results = $this->result_model->set_value_export($params)->where(array('target_id' => $target->id))->with_area()->with_department()->group_by("department_id")->get_all();
                usort($department_results, function ($a, $b) {
                    return strcmp($a->area->name, $b->area->name);
                });
                $department_list = array();
                $length_department = count($department_results);

                // $target_list[$key]->count = $length_department;
                $templateProcessor->cloneBlock("chart_block#" . ($key + 1), $length_department, true, true);
                for ($key1 = 0; $key1 < $length_department; $key1++) {
                    $department = $department_results[$key1]->department;
                    $area = $department_results[$key1]->area;
                    $target_id = $target->id;
                    $name_chart = $object_id . "_" . $target->id . "_" . $department->id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . ".png";

                    $templateProcessor->setImageValue("chart_image#" . ($key + 1) . "#" . ($key1 + 1), array('path' => APPPATH . '../public/upload/chart/' . $name_chart, 'width' => 1000, 'height' => 300, 'ratio' => false));

                    $heading =  $head . ($key + 1) . "." . ($key1 + 1) . ". $department->name ($department->string_id), $area->name / $department->name_en ($department->string_id), $area->name_en";
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
            if (!file_exists(APPPATH . '../public/export')) {
                mkdir(APPPATH . '../public/export', 0777, true);
            }
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
        } else if ($object_id == 10) { //vi sinh thiet bi
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
            // $year = date("Y", strtotime($params['date_from']));
            ///////DATA
            $target_list = $this->result_model->set_value_export($params)->with_target()->group_by("target_id")->get_all();
            $target_list = array_map(function ($item) {
                return $item->target;
            }, $target_list);

            $area_list = $this->result_model->set_value_export($params)->with_area()->group_by("area_id")->get_all();
            $area_list = array_map(function ($item) {
                return $item->area;
            }, $area_list);
            usort($area_list, function ($a, $b) {
                return strcmp($a->name, $b->name);
            });
            // echo "<pre>";
            // print_r($area_list);
            // die();
            $file = APPPATH . '../public/upload/template/template_thietbi_nam.docx';
            if ($type != "Year") {
                $file = APPPATH . '../public/upload/template/template_thietbi.docx';
            }
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
            } elseif ($type == "TwoYear") {
                $type_bc = "mỗi hai năm";
                $type_bc_en = "every two year";
            }

            $templateProcessor->setValue('date_from', date("d/m/y", strtotime($params['date_from'])));
            $templateProcessor->setValue('date_from_prev', date("d/m/y", strtotime($params['date_from_prev'])));
            $templateProcessor->setValue('date_to', date("d/m/y", strtotime($params['date_to'])));
            $templateProcessor->setValue('date_to_prev', date("d/m/y", strtotime($params['date_to_prev'])));
            $templateProcessor->setValue('type_bc', $type_bc);
            $templateProcessor->setValue('type_bc_en', $type_bc_en);
            $templateProcessor->setValue('workshop_name', $workshop_name);
            $templateProcessor->setValue('workshop_name_en', $workshop_name_en);
            $templateProcessor->setValue('type_bc_cap', mb_strtoupper($type_bc, 'UTF-8'));
            $templateProcessor->setValue('type_bc_cap_en', mb_strtoupper($type_bc_en, 'UTF-8'));
            $templateProcessor->setValue('workshop_name_cap', mb_strtoupper($workshop_name, 'UTF-8'));
            $templateProcessor->setValue('workshop_name_cap_en', mb_strtoupper($workshop_name_en, 'UTF-8'));


            ////STYLE
            $cellRowSpan = array('valign' => 'center');
            $cellHCentered = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
            $cellHCenteredLEFT = array('alignment' => 'left');

            $styleCell = array('valign' => 'center');
            $fontCell = array('align' => 'center');
            $cellColSpan = array('gridSpan' => 4, 'valign' => 'center');
            $cellRowContinue = array('vMerge' => 'continue');
            $cellVCentered = array('valign' => 'center');

            $normal = array('size' => 10);
            $italic =  array('italic' => true, 'size' => 10);
            ////BLOCK POSITION
            $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 10000, 'unit' => TblWidth::TWIP, 'valign' => 'center'));
            $table->addRow(null, array('tblHeader' => true));
            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Mã số', $normal, $fontCell);
            $cell1->addText('ID No.', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Phương pháp lấy mẫu', $normal, $fontCell);
            $cell1->addText('Sampling method', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Vị trí lấy mẫu', $normal, $fontCell);
            $cell1->addText('Sampling location', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Tên phòng', $normal, $fontCell);
            $cell1->addText('Room name', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Mã số phòng', $normal, $fontCell);
            $cell1->addText('ID No. of room', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Tần suất', $normal, $fontCell);
            $cell1->addText('Frequency', $italic, $fontCell);

            foreach ($area_list as $row) {
                $table->addRow();
                $cell1 = $table->addCell(null, array('gridSpan' => 6, 'valign' => 'center'));
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText($row->name . "/", array('bold' => true));
                $textrun1->addText($row->name_en, array('bold' => true, 'italic' => true), $fontCell);

                $position_list = $this->result_model->set_value_export($params)->where(array("area_id" => $row->id))->with_target()->with_position()->with_department()->group_by("position_id")->get_all();
                // echo "<pre>";
                // print_r($position_list);
                // die();
                foreach ($position_list as $row2) {
                    $position = $row2->position;
                    $department = $row2->department;
                    $target = $row2->target;
                    $table->addRow();

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText(htmlspecialchars($position->string_id), $normal, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($target->name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($target->name_en), $italic, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($position->name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($position->name_en), $italic, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($department->name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($department->name_en), $italic, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText(htmlspecialchars($department->string_id), $normal, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($position->frequency_name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($position->frequency_name_en), $italic, $fontCell);
                }
            }

            $templateProcessor->setComplexBlock('table_position', $table);

            ////BLOCK DIAGRAM
            $position_list = $this->result_model->set_value_export($params)->group_by("position_id")->get_all();
            if (!empty($position_list)) {
                $position_list = array_map(function ($item) {
                    return $item->position_id;
                }, $position_list);
                $diagram_list = $this->diagram_position_model->where("position_id", "IN", $position_list)->with_diagram(array("with" => array("relation" => "images")))->group_by("diagram_id")->get_all();

                $diagram_list = array_map(function ($item) {
                    return $item->diagram;
                }, $diagram_list);
                $templateProcessor->cloneBlock("diagram_block", count($diagram_list), true, true);
                foreach ($diagram_list as $key => $row) {
                    $templateProcessor->setValue("diagram_name#" .  ($key + 1), $row->name);
                    $templateProcessor->setValue("diagram_name_en#" . ($key + 1), $row->name_en);
                    if (isset($row->images)) {
                        $row->images = array_values((array) $row->images);
                        if (count($row->images)) {
                            $templateProcessor->cloneBlock("image_block#" .  ($key + 1), count($row->images), true, true);
                            foreach ($row->images as $key_image => $image) {
                                $templateProcessor->setImageValue("diagram_image#"  . ($key + 1) . "#" . ($key_image + 1), array('path' => APPPATH . '../' . $image->src, 'width' => 600, 'height' => 500, 'ratio' => true));
                            }
                        } else {
                            $templateProcessor->deleteBlock("image_block#" .  ($key + 1));
                        }
                    }
                }
            } else {
                $templateProcessor->deleteBlock('diagram_block');
            }
            // $target_list;
            ///TABLE LIMIT
            $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 10000, 'unit' => TblWidth::TWIP, 'valign' => 'center'));
            $table->addRow(null, array('tblHeader' => true));

            $cell1 = $table->addCell(null, $cellRowSpan);
            $textrun = $table->addCell(null, array('gridSpan' => 3, 'size' => 10, 'valign' => 'center'));
            $textrun->addText('Phương pháp lấy mẫu /', $normal, $fontCell);
            $textrun->addText('Sampling method (CFU/plate)', $italic, $fontCell);
            $table->addRow();
            $table->addCell(null, $cellRowContinue);
            for ($i = 0; $i < count($target_list); $i++) {
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText(htmlspecialchars($target_list[$i]->name), $normal, $fontCell);
                $textrun->addText(htmlspecialchars($target_list[$i]->name_en), $italic, $fontCell);
            }
            foreach ($area_list as $row2) {
                $table->addRow();
                $cell1 = $table->addCell(null, array('gridSpan' => count($target_list) + 1, 'valign' => 'center'));
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText(htmlspecialchars($row2->name . "/"), array('bold' => true));
                $textrun1->addText(htmlspecialchars($row2->name_en), array('bold' => true, 'italic' => true), $fontCell);

                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Tiêu chuẩn chấp nhận', $normal, $fontCell);
                $textrun->addText('Acceptance criteria', $italic, $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $this->limit_model->where(array("area_id" => $row2->id, 'target_id' =>  $target->id))->where("day_effect", "<=", $params['date_from'])->order_by("day_effect", "DESC")->limit(1)->as_object()->get();
                    $target_list[$key]->limit = $limit;
                    // print_r($limit);
                    // die();  
                    //     ///DATA
                    $textrun = $table->addCell(null, $fontCell);
                    $value = isset($limit->standard_limit) ? $limit->standard_limit : "";
                    $textrun->addText($value, $normal, $fontCell);
                }
                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Giới hạn cảnh báo', $normal, $fontCell);
                $textrun->addText('Alert Limit', $italic, $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $target->limit;
                    $textrun = $table->addCell(null, $fontCell);
                    $value = isset($limit->alert_limit) ? $limit->alert_limit : "";
                    $textrun->addText($value, $normal, $fontCell);
                }
                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Giới hạn hành động', $normal, $fontCell);
                $textrun->addText('Action Limit', $italic, $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $target->limit;
                    $textrun = $table->addCell(null, $fontCell);
                    $value = isset($limit->action_limit) ? $limit->action_limit : "";
                    $textrun->addText($value, $normal, $fontCell);
                }
            }

            $templateProcessor->setComplexBlock('table_limit', $table);

            /////RESULT 
            $templateProcessor->cloneBlock("result_target_block", count($target_list), true, true);
            $head = "5.1.";
            if ($type != "Year") {
                $head = "2.";
            }
            foreach ($target_list as $key => $target) {
                $templateProcessor->setValue("target_heading#" . ($key + 1), $head . ($key + 1));
                $templateProcessor->setValue("target_name#" . ($key + 1), $target->name);
                $templateProcessor->setValue("target_name_en#" . ($key + 1), $target->name_en);
                $area_results = $this->result_model->set_value_export($params)->where(array('target_id' => $target->id))->with_area()->group_by("area_id")->get_all();
                usort($area_results, function ($a, $b) {
                    return strcmp($a->area->name, $b->area->name);
                });
                $department_list = array();
                $length_area = count($area_results);
                $templateProcessor->cloneBlock("area_block#" . ($key + 1), $length_area, true, true);
                for ($key1 = 0; $key1 < $length_area; $key1++) {
                    $area = $area_results[$key1]->area;
                    $department_results = $this->result_model->set_value_export($params)->where(array('target_id' => $target->id))->where(array('area_id' => $area->id))->with_department()->group_by("department_id")->get_all();
                    $length_department = count($department_results);
                    $templateProcessor->setValue("area_heading#" . ($key + 1) . "#" . ($key1 + 1), $head . ($key + 1) . "." . ($key1 + 1));
                    $templateProcessor->setValue("area_name#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name));
                    $templateProcessor->setValue("area_name_en#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name_en));

                    $number_position = 0;
                    $list_department_tmp = array();
                    $table_data = array();
                    for ($key2 = 0; $key2 < $length_department; $key2++) {
                        $department = $department_results[$key2]->department;
                        $position_results = $this->result_model->set_value_export($params)->where(array('target_id' => $target->id))->where(array('department_id' => $department->id))->with_position()->group_by("position_id")->get_all();
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
                        $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 100 * 50, 'size' => 10, 'unit' => 'pct', 'valign' => 'center'));
                        $table->addRow(null, array('tblHeader' => true));
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                        $textrun1->addText(htmlspecialchars("Tên thiết bị:"), array('size' => 10, 'bold' => true));
                        $textrun1->addTextBreak();
                        $textrun1->addText(htmlspecialchars('Equipment name:'), array('size' => 10, 'bold' => true, 'italic' => true));
                        $position_list = array();
                        foreach ($t_data as $key3 => $department) {
                            $textrun1 = $table->addCell(null, array('gridSpan' => count($department->list_position), 'valign' => 'center'));
                            $textrun1->addText(htmlspecialchars($department->name), array('size' => 10), $fontCell);
                            if ($department->name != $department->name_en)
                                $textrun1->addText(htmlspecialchars($department->name_en), array('size' => 10, 'italic' => true), $fontCell);
                            $textrun1->addText(htmlspecialchars("(" . $department->string_id . ")"), array('size' => 10, 'italic' => true), $fontCell);
                            $position_list = array_merge($position_list, $department->list_position);
                        }
                        $table->addRow(null, array('tblHeader' => true));
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                        $textrun1->addText(htmlspecialchars("Vị trí lấy mẫu:"), array('size' => 10, 'bold' => true));
                        $textrun1->addTextBreak();
                        $textrun1->addText(htmlspecialchars('Sampling location:'), array('size' => 10, 'bold' => true, 'italic' => true));
                        foreach ($position_list as $key3 => $position) {
                            $textrun1 = $table->addCell(null, $styleCell);
                            $textrun1->addText(htmlspecialchars($position->string_id), array('size' => 10), $fontCell);
                        }
                        $table->addRow(null, array('tblHeader' => true));
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Ngày / "), array('size' => 10, 'bold' => true));
                        $textrun1->addText(htmlspecialchars("Date"), array('size' => 10, 'bold' => true, 'italic' => true));
                        $cell1 = $table->addCell(null, array('gridSpan' => count($position_list), 'valign' => 'center'));
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
                            $cell1 = $table->addCell(null, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText(htmlspecialchars($date), array('size' => 10));
                            foreach ($position_list as $position) {
                                $string_id = $position->string_id;
                                $value = $stt[$string_id];
                                if ($value == "") {
                                    $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                                } else {
                                    $cell1 = $table->addCell(null, $cellRowSpan);
                                    $textrun1 = $cell1->addTextRun($cellHCentered);
                                    $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                                }
                            }
                        }
                        $table->addRow(null);
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Max"), array('size' => 10, 'bold' => true));
                        foreach ($position_list as $position) {
                            $string_id = $position->string_id;
                            $value = $data_min_max["max_$string_id"];
                            if ($value == "") {
                                $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                            } else {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }
                        $table->addRow(null);
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Min"), array('size' => 10, 'bold' => true));
                        foreach ($position_list as $position) {
                            $string_id = $position->string_id;
                            $value = $data_min_max["min_$string_id"];
                            if ($value == "") {
                                $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                            } else {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }

                        $table->addRow(null);
                        $cell1 = $table->addCell(null, array('gridSpan' => count($position_list) + 1, 'valign' => 'center'));
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Kết quả trước đó / "), array('size' => 10, 'bold' => true));
                        $textrun1->addText(htmlspecialchars("Results of previous"), array('size' => 10, 'bold' => true, 'italic' => true));

                        $table->addRow(null);
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Max"), array('size' => 10, 'bold' => true));
                        foreach ($position_list as $position) {
                            $string_id = $position->string_id;
                            $value = $data_min_max_prev["max_$string_id"];
                            if ($value == "") {
                                $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                            } else {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }
                        $table->addRow(null);
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Min"), array('size' => 10, 'bold' => true));
                        foreach ($position_list as $position) {
                            $string_id = $position->string_id;
                            $value = $data_min_max_prev["min_$string_id"];
                            if ($value == "") {
                                $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                            } else {
                                $cell1 = $table->addCell(null, $cellRowSpan);
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
                    // $textrun1->addText(htmlspecialchars('Sampling location:'), $italic);
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
            $head = "5.2.";
            if ($type != "Year") {
                $head = "3.";
            }
            foreach ($target_list as $key => $target) {
                $templateProcessor->setValue("target_heading#" . ($key + 1),  $head . ($key + 1));
                $templateProcessor->setValue("target_name#" . ($key + 1), $target->name);
                $templateProcessor->setValue("target_name_en#" . ($key + 1), $target->name_en);
                $department_results = $this->result_model->set_value_export($params)->where(array('target_id' => $target->id))->with_area()->with_department()->group_by("department_id")->get_all();
                usort($department_results, function ($a, $b) {
                    return strcmp($a->area->name, $b->area->name);
                });
                $department_list = array();
                $length_department = count($department_results);
                // $target_list[$key]->count = $length_department;
                $templateProcessor->cloneBlock("chart_block#" . ($key + 1), $length_department, true, true);
                for ($key1 = 0; $key1 < $length_department; $key1++) {
                    $department = $department_results[$key1]->department;
                    $area = $department_results[$key1]->area;
                    $target_id = $target->id;
                    $name_chart = $object_id . "_" . $target->id . "_" . $department->id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . ".png";

                    $templateProcessor->setImageValue("chart_image#" . ($key + 1) . "#" . ($key1 + 1), array('path' => APPPATH . '../public/upload/chart/' . $name_chart, 'width' => 1000, 'height' => 300, 'ratio' => false));

                    $heading =  $head . ($key + 1) . "." . ($key1 + 1) . ". $department->name ($department->string_id), $area->name / $department->name_en ($department->string_id), $area->name_en";
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
            if (!file_exists(APPPATH . '../public/export')) {
                mkdir(APPPATH . '../public/export', 0777, true);
            }
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
        } else if ($object_id == 15 || $object_id == 14) { // Tieu phan
            $object = $this->object_model->where(array('id' => $object_id))->as_object()->get();
            $workshop_id = $record->workshop_id;

            $workshop = $this->workshop_model->where(array('id' => $workshop_id))->with_factory()->as_object()->get();
            $workshop_name = $workshop->name;
            $workshop_name_en = $workshop->name_en;
            $object_name = $object->name;
            $object_name_en = $object->name_en;
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
            $target_results = $this->result_model->set_value_export($params)->with_target()->group_by("target_id")->get_all();
            $target_parent = $target_list = array();
            foreach ($target_results as $temp) {
                $target = $temp->target;
                $target_object = $this->objecttarget_model->where(array("object_id" => $object_id, 'target_id' => $temp->target_id))->with_parent(array("with" => array('relation' => 'target')))->get();
                // echo "<pre>";
                // print_r($target_object);

                if (isset($target_object->parent) && !empty($target_object->parent)) {
                    $target->parent = $target_object->parent->target;
                    if (isset($target_parent[$target->parent->id])) {
                        $target_parent[$target->parent->id]->count_child++;
                        $target_parent[$target->parent->id]->child[] = $target;
                    } else {
                        $target_parent[$target->parent->id] = (object) array();
                        $target_parent[$target->parent->id]->id = $target->parent->id;
                        $target_parent[$target->parent->id]->name = $target->parent->name;
                        $target_parent[$target->parent->id]->name_en = $target->parent->name_en;
                        $target_parent[$target->parent->id]->unit = $target->parent->unit;
                        $target_parent[$target->parent->id]->count_child = 1;
                        $target_parent[$target->parent->id]->child = array($target);
                    }
                }
                $target_list[] = $target;
            }

            $target_parent = array_values($target_parent);
            usort($target_parent, function ($a, $b) {
                return $a->id > $b->id;
            });
            usort($target_list, function ($a, $b) {
                return $a->parent_id > $b->parent_id;
            });
            // echo "<pre>";
            // print_r($target_list);
            // print_r($target_parent);
            // die();
            $area_list = $this->result_model->set_value_export($params)->with_area()->group_by("area_id")->get_all();
            $area_list = array_map(function ($item) {
                return $item->area;
            }, $area_list);
            usort($area_list, function ($a, $b) {
                return strcmp($a->name, $b->name);
            });
            // echo "<pre>";
            // // print_r($params);
            // print_r($area_list);
            // die();
            $file = APPPATH . '../public/upload/template/template_tieuphan_nam.docx';
            if ($type != "Year") {
                $file = APPPATH . '../public/upload/template/template_tieuphan_1.docx';
            }

            //echo '<pre>';
            //print_r($type);
            //die();
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
            } elseif ($type == "TwoYear") {
                $type_bc = "mỗi hai năm";
                $type_bc_en = "every two year";
            }
            $templateProcessor->setValue('date_from', date("d/m/y", strtotime($params['date_from'])));
            $templateProcessor->setValue('date_from_prev', date("d/m/y", strtotime($params['date_from_prev'])));
            $templateProcessor->setValue('date_to', date("d/m/y", strtotime($params['date_to'])));
            $templateProcessor->setValue('date_to_prev', date("d/m/y", strtotime($params['date_to_prev'])));
            $templateProcessor->setValue('type_bc', $type_bc);
            $templateProcessor->setValue('type_bc_en', $type_bc_en);
            $templateProcessor->setValue('workshop_name', $workshop_name);
            $templateProcessor->setValue('workshop_name_en', $workshop_name_en);
            $templateProcessor->setValue('object_name', $object_name);
            $templateProcessor->setValue('object_name_en', $object_name_en);
            $templateProcessor->setValue('type_bc_cap', mb_strtoupper($type_bc, 'UTF-8'));
            $templateProcessor->setValue('type_bc_cap_en', mb_strtoupper($type_bc_en, 'UTF-8'));
            $templateProcessor->setValue('workshop_name_cap', mb_strtoupper($workshop_name, 'UTF-8'));
            $templateProcessor->setValue('workshop_name_cap_en', mb_strtoupper($workshop_name_en, 'UTF-8'));
            $templateProcessor->setValue('object_name_cap', mb_strtoupper($object_name, 'UTF-8'));
            $templateProcessor->setValue('object_name_cap_en', mb_strtoupper($object_name_en, 'UTF-8'));


            ////STYLE
            $cellRowSpan = array('valign' => 'center');
            $cellHCentered = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
            $cellHCenteredLEFT = array('alignment' => 'left');

            $styleCell = array('valign' => 'center');
            $fontCell = array('align' => 'center', 'size' => 10);
            $cellColSpan = array('gridSpan' => 5, 'valign' => 'center');
            $cellRowContinue = array('valign' => 'center', 'vMerge' => 'continue');
            $cellVCentered = array('valign' => 'center');
            $normal = array('size' => 10);
            $italic =  array('italic' => true, 'size' => 10);
            ////BLOCK POSITION
            $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 10000, 'unit' => TblWidth::TWIP, 'valign' => 'center'));
            $table->addRow(null, array('tblHeader' => true));
            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Mã số', $normal, $fontCell);
            $cell1->addText('ID No.', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Vị trí lấy mẫu', $normal, $fontCell);
            $cell1->addText('Sampling location', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Tên Phòng', $normal, $fontCell);
            $cell1->addText('Room Name', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Mã số phòng', $normal, $fontCell);
            $cell1->addText('ID No. of room', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Tần suất', $normal, $fontCell);
            $cell1->addText('Frequency', $italic, $fontCell);

            foreach ($area_list as $row) {
                $table->addRow();
                $cell1 = $table->addCell(null, array('gridSpan' => 5, 'valign' => 'center'));
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText($row->name . "/", array('bold' => true, 'size' => 10));
                $textrun1->addText($row->name_en, array('bold' => true, 'italic' => true, 'size' => 10), $fontCell);

                $position_list = $this->result_model->set_value_export($params)->where(array("area_id" => $row->id))->with_position()->with_department()->group_by("position_id")->get_all();
                // echo "<pre>";
                // print_r($position_list);
                // die();
                foreach ($position_list as $row2) {
                    $position = $row2->position;
                    $department = $row2->department;
                    $table->addRow();

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText(htmlspecialchars($position->string_id), $normal, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($position->name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($position->name_en), $italic, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($department->name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($department->name_en), $italic, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText(htmlspecialchars($department->string_id), $normal, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($position->frequency_name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($position->frequency_name_en), $italic, $fontCell);
                }
            }

            $templateProcessor->setComplexBlock('table_position', $table);

            ////BLOCK DIAGRAM
            $position_list = $this->result_model->set_value_export($params)->group_by("position_id")->get_all();
            if (!empty($position_list)) {
                $position_list = array_map(function ($item) {
                    return $item->position_id;
                }, $position_list);
                $diagram_list = $this->diagram_position_model->where("position_id", "IN", $position_list)->with_diagram(array("with" => array("relation" => "images")))->group_by("diagram_id")->get_all();

                $diagram_list = array_map(function ($item) {
                    return $item->diagram;
                }, $diagram_list);
                $templateProcessor->cloneBlock("diagram_block", count($diagram_list), true, true);
                foreach ($diagram_list as $key => $row) {
                    $templateProcessor->setValue("diagram_name#" .  ($key + 1), $row->name);
                    $templateProcessor->setValue("diagram_name_en#" . ($key + 1), $row->name_en);
                    if (isset($row->images)) {
                        $row->images = array_values((array) $row->images);
                        if (count($row->images)) {
                            $templateProcessor->cloneBlock("image_block#" .  ($key + 1), count($row->images), true, true);
                            foreach ($row->images as $key_image => $image) {
                                $templateProcessor->setImageValue("diagram_image#"  . ($key + 1) . "#" . ($key_image + 1), array('path' => APPPATH . '../' . $image->src, 'width' => 600, 'height' => 500, 'ratio' => true));
                            }
                        } else {
                            $templateProcessor->deleteBlock("image_block#" .  ($key + 1));
                        }
                    }
                }
            } else {
                $templateProcessor->deleteBlock('diagram_block');
            }
            // $target_list;
            ///TABLE LIMIT
            $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 100 * 50, 'size' => 10, 'unit' => 'pct', 'valign' => 'center'));

            $table->addRow();
            $table->addCell(null, $cellRowContinue);
            for ($i = 0; $i < count($target_parent); $i++) {
                $textrun = $table->addCell(null, array('gridSpan' => $target_parent[$i]->count_child, 'size' => 10, 'valign' => 'center'));
                $textrun->addText($target_parent[$i]->name, $normal, $fontCell);
                $textrun->addText($target_parent[$i]->name_en, $italic, $fontCell);
            }
            $table->addRow();
            $table->addCell(null, $cellRowContinue);
            for ($i = 0; $i < count($target_list); $i++) {
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText($target_list[$i]->name, $normal, $fontCell);
                $textrun->addText($target_list[$i]->name_en, $italic, $fontCell);
            }
            foreach ($area_list as $row2) {
                $table->addRow();
                $cell1 = $table->addCell(null, $cellColSpan);
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText($row2->name, array('bold' => true));

                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Tiêu chuẩn chấp nhận', $normal, $fontCell);
                $textrun->addText('Acceptance criteria', $italic, $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $this->limit_model->where(array("area_id" => $row2->id, 'target_id' =>  $target->id))->where("day_effect", "<=", $params['date_from'])->order_by("day_effect", "DESC")->limit(1)->as_object()->get();
                    $target_list[$key]->limit = $limit;
                    // print_r($limit);
                    // die();  
                    //     ///DATA
                    $textrun = $table->addCell(null, $fontCell);
                    $value = isset($limit->standard_limit) ? $limit->standard_limit : "";
                    $textrun->addText($value, $normal, $fontCell);
                }
                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Giới hạn cảnh báo', $normal, $fontCell);
                $textrun->addText('Alert Limit', $italic, $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $target->limit;
                    $textrun = $table->addCell(null, $fontCell);
                    $value = isset($limit->alert_limit) ? $limit->alert_limit : "";
                    $textrun->addText($value, $normal, $fontCell);
                }
                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Giới hạn hành động', $normal, $fontCell);
                $textrun->addText('Action Limit', $italic, $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $target->limit;
                    $textrun = $table->addCell(null, $fontCell);
                    $value = isset($limit->action_limit) ? $limit->action_limit : "";
                    $textrun->addText($value, $normal, $fontCell);
                }
            }

            $templateProcessor->setComplexBlock('table_limit', $table);

            /////RESULT 
            $area_results = $this->result_model->set_value_export($params)->with_area()->group_by("area_id")->get_all();
            usort($area_results, function ($a, $b) {
                return strcmp($a->area->name, $b->area->name);
            });
            $department_list = array();
            $length_area = count($area_results);
            $templateProcessor->cloneBlock("result_one_block", $length_area, true, true);
            for ($key = 0; $key < $length_area; $key++) {
                $area = $area_results[$key]->area;
                $department_results = $this->result_model->set_value_export($params)->where(array('area_id' => $area->id))->with_department()->with_area()->group_by("department_id")->get_all();
                $length_department = count($department_results);
                $head = "5.1";
                if ($type != "Year") {
                    $head = "2.";
                }
                $templateProcessor->setValue("one_heading#" . ($key + 1), $head . ($key + 1));
                $templateProcessor->setValue("one_name_heading#" . ($key + 1), htmlspecialchars($area->name));
                $templateProcessor->setValue("one_name_en_heading#" . ($key + 1), htmlspecialchars($area->name_en));

                $number_position = 0;
                $list_department_tmp = array();
                $table_data = array();
                $templateProcessor->cloneBlock("result_two_block#" . ($key + 1), $length_department, true, true);
                for ($key1 = 0; $key1 < $length_department; $key1++) {
                    $department = $department_results[$key1]->department;
                    $area = $department_results[$key1]->area;
                    $templateProcessor->setValue("department_name#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->name));
                    $templateProcessor->setValue("department_name_en#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->name_en));
                    $templateProcessor->setValue("area_name#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name));
                    $templateProcessor->setValue("area_name_en#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name_en));
                    $templateProcessor->setValue("department_id#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->string_id));

                    ////DRAW RESULT
                    $templateProcessor->setValue("two_heading#" . ($key + 1) . "#" . ($key1 + 1), $head . ($key + 1) . "." . ($key1 + 1));
                    $templateProcessor->setValue("two_name_heading#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->name));
                    $templateProcessor->setValue("two_name_en_heading#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->name_en));

                    ////DATA
                    $position_results = $this->result_model->set_value_export($params)->where(array('department_id' => $department->id))->with_position()->group_by("position_id")->get_all();
                    $length_position = count($position_results);

                    $templateProcessor->cloneBlock("position_block#" . ($key + 1) . "#" . ($key1 + 1), $length_position, true, true);
                    for ($key2 = 0; $key2 < $length_position; $key2++) {
                        $position = $position_results[$key2]->position;
                        $params['position_id'] = $position->id;
                        $string_id = $position->string_id;
                        $templateProcessor->setValue("position_string_id#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($key2 + 1), $position->string_id);
                        ///TABLE
                        $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 100 * 50, 'size' => 10, 'unit' => 'pct', 'valign' => 'center'));
                        $table->addRow(null, array('tblHeader' => true));
                        $cell1 = $table->addCell(null, $cellRowContinue);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Mã số điểm lấy mẫu /"), array('size' => 10));
                        $textrun1->addTextBreak();
                        $textrun1->addText(htmlspecialchars("ID of sampling points"), array('size' => 10, 'italic' => true));

                        $cell1 = $table->addCell(null, $cellRowContinue);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Ngày /"), array('size' => 10));
                        $textrun1->addText(htmlspecialchars("Date"), array('size' => 10, 'italic' => true));
                        $textrun1->addTextBreak();
                        $textrun1->addText(htmlspecialchars('(dd/mm/yy)'), array('size' => 10));
                        for ($i = 0; $i < count($target_parent); $i++) {
                            $textrun = $table->addCell(null, array('gridSpan' => $target_parent[$i]->count_child, 'size' => 10, 'valign' => 'center'));
                            $textrun->addText($target_parent[$i]->name, $normal, $fontCell);
                            $textrun->addText($target_parent[$i]->name_en, $italic, $fontCell);
                        }
                        $table->addRow(null, array('tblHeader' => true));
                        $table->addCell(null, $cellRowContinue);
                        $table->addCell(null, $cellRowContinue);
                        for ($i = 0; $i < count($target_list); $i++) {
                            $textrun = $table->addCell(null, $styleCell);
                            $textrun->addText($target_list[$i]->name, $normal, $fontCell);
                            $textrun->addText($target_list[$i]->name_en, $italic, $fontCell);
                        }


                        //     ///DATA
                        $data = $this->result_model->get_data_table_by_target($target_list, $params);
                        $data_min_max = $this->result_model->get_data_table_by_target_minmax($target_list, $params['object_id'], $params['position_id'], $params['date_from'], $params['date_to']);
                        $data_min_max_prev = $this->result_model->get_data_table_by_target_minmax($target_list, $params['object_id'], $params['position_id'], $params['date_from_prev'], $params['date_to_prev']);


                        foreach ($data as $keystt => $stt) {
                            $table->addRow();
                            $date = date("d/m/y", strtotime($stt['date']));

                            $cell1 = $table->addCell(null, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText(htmlspecialchars($string_id), array('size' => 10));

                            $cell1 = $table->addCell(null, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText(htmlspecialchars($date), array('size' => 10));

                            foreach ($target_list as $target) {
                                $target_id = $target->id;
                                $value = $stt[$target_id];
                                if ($value == "") {
                                    $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                                } else {
                                    $cell1 = $table->addCell(null, $cellRowSpan);
                                    $textrun1 = $cell1->addTextRun($cellHCentered);
                                    $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                                }
                            }
                        }
                        ///MIN MAX
                        $table->addRow();
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);

                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText("Max", array('size' => 10, 'bold' => true));
                        foreach ($target_list as $target) {
                            $target_id = $target->id;
                            $value = $data_min_max["max_$target_id"];
                            if ($value == "") {
                                $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                            } else {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }
                        $table->addRow();
                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);

                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText("Min", array('size' => 10, 'bold' => true));
                        foreach ($target_list as $target) {
                            $target_id = $target->id;
                            $value = $data_min_max["min_$target_id"];
                            if ($value == "") {
                                $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                            } else {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }

                        //$table->addRow(null);
                        //$cell1 = $table->addCell(null, array('gridSpan' => count($target_list) + 1, 'valign' => 'center'));
                        //$textrun1 = $cell1->addTextRun($cellHCentered);
                        //$textrun1->addText(htmlspecialchars("Kết quả trước đó / "), array('size' => 10, 'bold' => true));
                        //$textrun1->addText(htmlspecialchars("Results of previous"), array('size' => 10, 'bold' => true, 'italic' => true));

                        $table->addRow();
                        $cell1 = $table->addCell(null, $cellRowContinue);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars("Kết quả trước đó / "), array('size' => 10, 'bold' => true));
                        $textrun1->addTextBreak();
                        $textrun1->addText(htmlspecialchars("Results of previous"), array('size' => 10,  'bold' => true, 'italic' => true));

                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText("Max", array('size' => 10, 'bold' => true));
                        foreach ($target_list as $target) {
                            $target_id = $target->id;
                            $value = $data_min_max_prev["max_$target_id"];
                            if ($value == "") {
                                $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                            } else {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }
                        $table->addRow();
                        $table->addCell(null, $cellRowContinue);

                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText("Min", array('size' => 10, 'bold' => true));
                        foreach ($target_list as $target) {
                            $target_id = $target->id;
                            $value = $data_min_max_prev["min_$target_id"];
                            if ($value == "") {
                                $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                            } else {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }

                        $templateProcessor->setComplexBlock("result_table#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($key2 + 1), $table);
                    }
                    ///KIEM TRA CHART
                    $tmp_parent = array();
                    foreach ($target_parent as $k => $parent) {
                        $child = $parent->child;
                        $tmp = array();
                        for ($j = 0; $j < count($child); $j++) {
                            $target = $child[$j];
                            $name_chart = $object_id . "_" . $target->id . "_" . $department->id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . ".png";
                            // echo $name_chart . "<br>";
                            if (file_exists(APPPATH . '../public/upload/chart/' . $name_chart)) {
                                $tmp[] = $child[$j];
                            }
                        }

                        // $child = $tmp;
                        $parent_clone = clone $parent;
                        $parent_clone->child = $tmp;
                        if (!empty($tmp)) {
                            $tmp_parent[] = $parent_clone;
                        }
                    }
                    // echo "<pre>";
                    // print_r($tmp_parent);

                    /////DRAW TREND
                    $templateProcessor->cloneBlock("target_parent_block#" . ($key + 1) . "#" . ($key1 + 1), count($tmp_parent), true, true);
                    for ($i = 0; $i < count($tmp_parent); $i++) {
                        $parent = $tmp_parent[$i];
                        $child = $parent->child;
                        $templateProcessor->setValue("parent_name#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($i + 1), $parent->name);
                        $templateProcessor->setValue("parent_name_en#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($i + 1), $parent->name_en);

                        $templateProcessor->cloneBlock("target_block#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($i + 1), count($child), true, true);
                        for ($j = 0; $j < count($child); $j++) {
                            $target = $child[$j];
                            $templateProcessor->setValue("target_name#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($i + 1) . "#" . ($j + 1), $target->name);
                            $templateProcessor->setValue("target_name_en#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($i + 1) . "#" . ($j + 1), $target->name_en);

                            $name_chart = $object_id . "_" . $target->id . "_" . $department->id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . ".png";

                            $templateProcessor->setImageValue("chart_image#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($i + 1) . "#" . ($j + 1), array('path' => APPPATH . '../public/upload/chart/' . $name_chart, 'width' => 1000, 'height' => 300, 'ratio' => false));
                        }
                    }
                }
            }
            // die();
            $name_file = "Bao_cao_" . $object_id . "_" . $workshop_id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . "_" . time() . ".docx";
            $name_file = urlencode($name_file);
            if (!file_exists(APPPATH . '../public/export')) {
                mkdir(APPPATH . '../public/export', 0777, true);
            }
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
        } else if ($object_id == 16 || $object_id == 17) {
            $object = $this->object_model->where(array('id' => $object_id))->as_object()->get();
            $workshop_id = $record->workshop_id;

            $workshop = $this->workshop_model->where(array('id' => $workshop_id))->with_factory()->as_object()->get();
            $workshop_name = $workshop->name;
            $workshop_name_en = $workshop->name_en;
            $object_name = $object->name;
            $object_name_en = $object->name_en;
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
            $target_results = $this->result_model->set_value_export($params)->with_target()->group_by("target_id")->get_all();
            $target_parent = $target_list = array();
            foreach ($target_results as $temp) {
                $target = $temp->target;
                $target_object = $this->objecttarget_model->where(array("object_id" => $object_id, 'target_id' => $temp->target_id))->with_parent(array("with" => array('relation' => 'target')))->get();
                // echo "<pre>";
                // print_r($target_object);

                if (isset($target_object->parent) && !empty($target_object->parent)) {
                    $target->parent = $target_object->parent->target;
                    $target->parent_id = $target->parent->id;
                    if (isset($target_parent[$target->parent->id])) {
                        $target_parent[$target->parent->id]->count_child++;
                        $target_parent[$target->parent->id]->child[] = $target;
                    } else {
                        $target_parent[$target->parent->id] = (object) array();
                        $target_parent[$target->parent->id]->id = $target->parent->id;
                        $target_parent[$target->parent->id]->name = $target->parent->name;
                        $target_parent[$target->parent->id]->name_en = $target->parent->name_en;
                        $target_parent[$target->parent->id]->unit = $target->parent->unit;
                        $target_parent[$target->parent->id]->count_child = 1;
                        $target_parent[$target->parent->id]->child = array($target);
                    }
                }
                $target_list[] = $target;
            }

            $target_parent = array_values($target_parent);
            usort($target_parent, function ($a, $b) {
                return $a->id > $b->id;
            });
            usort($target_list, function ($a, $b) {
                return $a->parent_id > $b->parent_id;
            });
            // echo "<pre>";
            // print_r($target_list);
            // print_r($target_parent);
            // die();
            $area_list = $this->result_model->set_value_export($params)->with_area()->group_by("area_id")->get_all();
            $area_list = array_map(function ($item) {
                return $item->area;
            }, $area_list);
            usort($area_list, function ($a, $b) {
                return strcmp($a->name, $b->name);
            });
            // echo "<pre>";
            // // print_r($params);
            // print_r($area_list);
            // die();
            $file = APPPATH . '../public/upload/template/template_khi.docx';
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
            } elseif ($type == "TwoYear") {
                $type_bc = "mỗi hai năm";
                $type_bc_en = "every two year";
            }
            $templateProcessor->setValue('date_from', date("d/m/y", strtotime($params['date_from'])));
            $templateProcessor->setValue('date_from_prev', date("d/m/y", strtotime($params['date_from_prev'])));
            $templateProcessor->setValue('date_to', date("d/m/y", strtotime($params['date_to'])));
            $templateProcessor->setValue('date_to_prev', date("d/m/y", strtotime($params['date_to_prev'])));
            $templateProcessor->setValue('type_bc', $type_bc);
            $templateProcessor->setValue('type_bc_en', $type_bc_en);
            $templateProcessor->setValue('workshop_name', $workshop_name);
            $templateProcessor->setValue('workshop_name_en', $workshop_name_en);
            $templateProcessor->setValue('object_name', $object_name);
            $templateProcessor->setValue('object_name_en', $object_name_en);
            $templateProcessor->setValue('type_bc_cap', mb_strtoupper($type_bc, 'UTF-8'));
            $templateProcessor->setValue('type_bc_cap_en', mb_strtoupper($type_bc_en, 'UTF-8'));
            $templateProcessor->setValue('workshop_name_cap', mb_strtoupper($workshop_name, 'UTF-8'));
            $templateProcessor->setValue('workshop_name_cap_en', mb_strtoupper($workshop_name_en, 'UTF-8'));
            $templateProcessor->setValue('object_name_cap', mb_strtoupper($object_name, 'UTF-8'));
            $templateProcessor->setValue('object_name_cap_en', mb_strtoupper($object_name_en, 'UTF-8'));


            ////STYLE
            $cellRowSpan = array('valign' => 'center');
            $cellHCentered = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
            $cellHCenteredLEFT = array('alignment' => 'left');

            $styleCell = array('valign' => 'center');
            $fontCell = array('align' => 'center');
            $cellColSpan = array('gridSpan' => 4, 'valign' => 'center');
            $cellRowContinue = array('valign' => 'center', 'vMerge' => 'continue');
            $cellVCentered = array('valign' => 'center');


            $normal = array('size' => 10);
            $italic =  array('italic' => true, 'size' => 10);
            ////BLOCK POSITION
            $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 10000, 'unit' => TblWidth::TWIP, 'valign' => 'center'));
            $table->addRow(null, array('tblHeader' => true));
            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Mã số', $normal, $fontCell);
            $cell1->addText('ID No.', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Vị trí lấy mẫu', $normal, $fontCell);
            $cell1->addText('Sampling location', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Tên Phòng', $normal, $fontCell);
            $cell1->addText('Room Name', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Mã số phòng', $normal, $fontCell);
            $cell1->addText('ID No. of room', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Tần suất', $normal, $fontCell);
            $cell1->addText('Frequency', $italic, $fontCell);

            foreach ($area_list as $row) {
                $table->addRow();
                $cell1 = $table->addCell(null, array('gridSpan' => 5, 'valign' => 'center'));
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText($row->name . "/", array('bold' => true));
                $textrun1->addText($row->name_en, array('bold' => true, 'italic' => true), $fontCell);

                $position_list = $this->result_model->set_value_export($params)->where(array("area_id" => $row->id))->with_position()->with_department()->group_by("position_id")->get_all();
                // echo "<pre>";
                // print_r($position_list);
                // die();
                foreach ($position_list as $row2) {
                    $position = $row2->position;
                    $department = $row2->department;
                    $table->addRow();

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText(htmlspecialchars($position->string_id), $normal, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($position->name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($position->name_en), $italic, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($department->name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($department->name_en), $italic, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText(htmlspecialchars($department->string_id), $normal, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($position->frequency_name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($position->frequency_name_en), $italic, $fontCell);
                }
            }

            $templateProcessor->setComplexBlock('table_position', $table);

            ////BLOCK DIAGRAM
            $position_list = $this->result_model->set_value_export($params)->group_by("position_id")->get_all();
            if (!empty($position_list)) {
                $position_list = array_map(function ($item) {
                    return $item->position_id;
                }, $position_list);
                $diagram_list = $this->diagram_position_model->where("position_id", "IN", $position_list)->with_diagram(array("with" => array("relation" => "images")))->group_by("diagram_id")->get_all();

                $diagram_list = array_map(function ($item) {
                    return $item->diagram;
                }, $diagram_list);
                $templateProcessor->cloneBlock("diagram_block", count($diagram_list), true, true);
                foreach ($diagram_list as $key => $row) {
                    $templateProcessor->setValue("diagram_name#" .  ($key + 1), $row->name);
                    $templateProcessor->setValue("diagram_name_en#" . ($key + 1), $row->name_en);
                    if (isset($row->images)) {
                        $row->images = array_values((array) $row->images);
                        if (count($row->images)) {
                            $templateProcessor->cloneBlock("image_block#" .  ($key + 1), count($row->images), true, true);
                            foreach ($row->images as $key_image => $image) {
                                $templateProcessor->setImageValue("diagram_image#"  . ($key + 1) . "#" . ($key_image + 1), array('path' => APPPATH . '../' . $image->src, 'width' => 600, 'height' => 500, 'ratio' => true));
                            }
                        } else {
                            $templateProcessor->deleteBlock("image_block#" .  ($key + 1));
                        }
                    }
                }
            } else {
                $templateProcessor->deleteBlock('diagram_block');
            }
            // $target_list;
            ///TABLE LIMIT

            $templateProcessor->cloneBlock("limit_block", count($target_parent), true, true);
            for ($j = 0; $j < count($target_parent); $j++) {
                $child = $target_parent[$j]->child;
                $templateProcessor->setValue("limit_parent_name#" .  ($j + 1), $target_parent[$j]->name);
                $templateProcessor->setValue("limit_parent_name_en#" . ($j + 1), $target_parent[$j]->name_en);

                $table = new Table(array(
                    'borderSize' => 3, 'width' => 100 * 50, 'size' => 10, 'unit' => 'pct',
                    'cellMargin'  => 80, 'valign' => 'center'
                ));

                $table->addRow();
                $table->addCell(null, $cellRowContinue);
                for ($i = 0; $i < count($child); $i++) {
                    $textrun = $table->addCell(null, $styleCell);
                    $textrun->addText($child[$i]->name, array('bold' => true), $fontCell);
                    $textrun->addText($child[$i]->name_en, array('bold' => true, 'italic' => true), $fontCell);
                }
                foreach ($area_list as $row2) {
                    $table->addRow();
                    $cell1 = $table->addCell(null, array('gridSpan' => count($child) + 1, 'valign' => 'center'));
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText($row2->name, array('bold' => true));

                    $table->addRow();
                    $textrun = $table->addCell(null, $styleCell);
                    $textrun->addText('Tiêu chuẩn chấp nhận', $normal, $fontCell);
                    $textrun->addText('Acceptance criteria', $italic, $fontCell);
                    foreach ($child as $key => $target) {
                        $limit = $this->limit_model->where(array("area_id" => $row2->id, 'target_id' =>  $target->id))->where("day_effect", "<=", $params['date_from'])->order_by("day_effect", "DESC")->limit(1)->as_object()->get();
                        $child[$key]->limit = $limit;
                        // print_r($limit);
                        // die();  
                        //     ///DATA
                        $textrun = $table->addCell(null, $fontCell);
                        if ($target->type_data == "float") {
                            $value = isset($limit->standard_limit) ? $limit->standard_limit : "";
                        } else {
                            $value = isset($limit->standard_limit_text) ? $limit->standard_limit_text : "";
                        }
                        $textrun->addText($value, $normal, $fontCell);
                        if ($target->type_data != "float") {
                            $value_en = isset($limit->standard_limit_text_en) ? $limit->standard_limit_text_en : "";
                            if ($value_en != "" && $value != $value_en) {
                                $textrun->addText($value_en, $italic, $fontCell);
                            }
                        }
                    }
                    $table->addRow();
                    $textrun = $table->addCell(null, $styleCell);
                    $textrun->addText('Giới hạn cảnh báo', $normal, $fontCell);
                    $textrun->addText('Alert Limit', $italic, $fontCell);
                    foreach ($child as $key => $target) {
                        $limit = $target->limit;
                        $textrun = $table->addCell(null, $fontCell);
                        if ($target->type_data == "float") {
                            $value = isset($limit->alert_limit) ? $limit->alert_limit : "";
                        } else {
                            $value = isset($limit->alert_limit_text) ? $limit->alert_limit_text : "";
                        }
                        $textrun->addText($value, $normal, $fontCell);
                        if ($target->type_data != "float") {
                            $value_en = isset($limit->alert_limit_text_en) ? $limit->alert_limit_text_en : "";
                            if ($value_en != "" && $value != $value_en) {
                                $textrun->addText($value_en, $italic, $fontCell);
                            }
                        }
                    }
                    $table->addRow();
                    $textrun = $table->addCell(null, $styleCell);
                    $textrun->addText('Giới hạn hành động', $normal, $fontCell);
                    $textrun->addText('Action Limit', $italic, $fontCell);
                    foreach ($child as $key => $target) {
                        $limit = $target->limit;
                        $textrun = $table->addCell(null, $fontCell);
                        if ($target->type_data == "float") {
                            $value = isset($limit->action_limit) ? $limit->action_limit : "";
                        } else {
                            $value = isset($limit->action_limit_text) ? $limit->action_limit_text : "";
                        }
                        $textrun->addText($value, $normal, $fontCell);
                        if ($target->type_data != "float") {
                            $value_en = isset($limit->action_limit_text_en) ? $limit->action_limit_text_en : "";
                            if ($value_en != "" && $value != $value_en) {
                                $textrun->addText($value_en, $italic, $fontCell);
                            }
                        }
                    }
                }

                $templateProcessor->setComplexBlock('table_limit#' .  ($j + 1), $table);
            }


            /////RESULT 
            $area_results = $this->result_model->set_value_export($params)->with_area()->group_by("area_id")->get_all();
            usort($area_results, function ($a, $b) {
                return strcmp($a->area->name, $b->area->name);
            });
            $department_list = array();
            $length_area = count($area_results);
            $templateProcessor->cloneBlock("result_one_block", $length_area, true, true);
            for ($key = 0; $key < $length_area; $key++) {
                $area = $area_results[$key]->area;
                $department_results = $this->result_model->set_value_export($params)->where(array('area_id' => $area->id))->with_department()->with_area()->group_by("department_id")->get_all();
                $length_department = count($department_results);
                $head = "5.1";
                if ($type != "Year") {
                    $head = "2.";
                }
                $templateProcessor->setValue("one_heading#" . ($key + 1), $head . ($key + 1));
                $templateProcessor->setValue("one_name_heading#" . ($key + 1), htmlspecialchars($area->name));
                $templateProcessor->setValue("one_name_en_heading#" . ($key + 1), htmlspecialchars($area->name_en));

                $number_position = 0;
                $list_department_tmp = array();
                $table_data = array();
                $templateProcessor->cloneBlock("result_two_block#" . ($key + 1), $length_department, true, true);
                for ($key1 = 0; $key1 < $length_department; $key1++) {
                    $department = $department_results[$key1]->department;
                    $area = $department_results[$key1]->area;
                    $templateProcessor->setValue("department_name#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->name));
                    $templateProcessor->setValue("department_name_en#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->name_en));
                    $templateProcessor->setValue("area_name#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name));
                    $templateProcessor->setValue("area_name_en#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($area->name_en));
                    $templateProcessor->setValue("department_id#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->string_id));

                    ////DRAW RESULT
                    $templateProcessor->setValue("two_heading#" . ($key + 1) . "#" . ($key1 + 1), $head . ($key + 1) . "." . ($key1 + 1));
                    $templateProcessor->setValue("two_name_heading#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->name));
                    $templateProcessor->setValue("two_name_en_heading#" . ($key + 1) . "#" . ($key1 + 1), htmlspecialchars($department->name_en));

                    $position_results = $this->result_model->set_value_export($params)->where(array('department_id' => $department->id))->with_position()->group_by("position_id")->get_all();
                    $length_position = count($position_results);

                    $templateProcessor->cloneBlock("position_block#" . ($key + 1) . "#" . ($key1 + 1), $length_position, true, true);

                    // echo "<pre>";
                    // print_r($tmp_parent);
                    // die();
                    ////DATA

                    for ($key2 = 0; $key2 < $length_position; $key2++) {
                        $position = $position_results[$key2]->position;
                        $params['position_id'] = $position->id;
                        $templateProcessor->setValue("position_string_id#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($key2 + 1), $position->string_id);

                        ///KIEM TRA DATA
                        $tmp_parent = array();
                        foreach ($target_parent as $k => $parent) {
                            $child = $parent->child;
                            $tmp = array();
                            for ($j = 0; $j < count($child); $j++) {
                                $target = $child[$j];
                                $target_results = $this->result_model->set_value_export($params)->where(array("target_id" => $target->id, "position_id" => $position->id))->where(array('department_id' => $department->id))->group_by("target_id")->get_all();

                                if (!empty($target_results)) {
                                    $tmp[] = $child[$j];
                                }
                            }

                            // $child = $tmp;
                            $parent_clone = clone $parent;
                            $parent_clone->child = $tmp;
                            if (!empty($tmp)) {
                                $tmp_parent[] = $parent_clone;
                            }
                        }
                        $templateProcessor->cloneBlock("result_target_parent_block#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($key2 + 1), count($tmp_parent), true, true);

                        // echo "<pre>";
                        // print_r($tmp_parent);
                        for ($key3 = 0; $key3 < count($tmp_parent); $key3++) {
                            $child = $tmp_parent[$key3]->child;
                            $parent = $tmp_parent[$key3];
                            $templateProcessor->setValue("result_parent_name#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($key2 + 1) . "#" . ($key3 + 1), $parent->name);
                            $templateProcessor->setValue("result_parent_name_en#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($key2 + 1) . "#" . ($key3 + 1), $parent->name_en);

                            ///TABLE
                            $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 100 * 50, 'size' => 10, 'unit' => 'pct', 'valign' => 'center'));
                            $table->addRow(null, array('tblHeader' => true));

                            $cell1 = $table->addCell(null, $cellRowContinue);
                            $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                            $textrun1->addText(htmlspecialchars("Ngày /"), array('size' => 10));
                            $textrun1->addText(htmlspecialchars("Date"), array('size' => 10, 'italic' => true));
                            $textrun1->addTextBreak();
                            $textrun1->addText(htmlspecialchars('(dd/mm/yy)'), array('size' => 10));
                            // for ($i = 0; $i < count($target_parent); $i++) {
                            //     $textrun = $table->addCell(null, array('gridSpan' => $target_parent[$i]->count_child, 'size' => 10, 'valign' => 'center'));
                            //     $textrun->addText($target_parent[$i]->name, $normal, $fontCell);
                            //     $textrun->addText($target_parent[$i]->name_en, $italic, $fontCell);
                            // }
                            // $table->addRow(null, array('tblHeader' => true));
                            // $table->addCell(null, $cellRowContinue);
                            for ($i = 0; $i < count($child); $i++) {
                                $textrun = $table->addCell(null, $styleCell);
                                $textrun->addText($child[$i]->name, array('size' => 10), $fontCell);
                                $textrun->addText($child[$i]->name_en, array('italic' => true, 'size' => 10), $fontCell);
                            }


                            //     ///DATA
                            $data = $this->result_model->get_data_table_by_target($child, $params);
                            $data_min_max = $this->result_model->get_data_table_by_target_minmax($child, $params['object_id'], $params['position_id'], $params['date_from'], $params['date_to']);
                            $data_min_max_prev = $this->result_model->get_data_table_by_target_minmax($child, $params['object_id'], $params['position_id'], $params['date_from_prev'], $params['date_to_prev']);


                            foreach ($data as $keystt => $stt) {
                                $table->addRow();
                                $date = date("d/m/y", strtotime($stt['date']));
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($date), array('size' => 10));
                                foreach ($child as $target) {
                                    $target_id = $target->id;
                                    $value = $stt[$target_id];
                                    if ($value == "") {
                                        $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                                    } else {
                                        $cell1 = $table->addCell(null, $cellRowSpan);
                                        $textrun1 = $cell1->addTextRun($cellHCentered);
                                        $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                                    }
                                }
                            }
                            ///MIN MAX
                            $table->addRow();
                            $cell1 = $table->addCell(null, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText("Max", array('size' => 10, 'bold' => true));
                            foreach ($child as $target) {
                                $target_id = $target->id;
                                $value = $data_min_max["max_$target_id"];
                                if ($value == "") {
                                    $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                                } else {
                                    $cell1 = $table->addCell(null, $cellRowSpan);
                                    $textrun1 = $cell1->addTextRun($cellHCentered);
                                    $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                                }
                            }
                            $table->addRow();
                            $cell1 = $table->addCell(null, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText("Min", array('size' => 10, 'bold' => true));
                            foreach ($child as $target) {
                                $target_id = $target->id;
                                $value = $data_min_max["min_$target_id"];
                                if ($value == "") {
                                    $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                                } else {
                                    $cell1 = $table->addCell(null, $cellRowSpan);
                                    $textrun1 = $cell1->addTextRun($cellHCentered);
                                    $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                                }
                            }

                            $table->addRow(null);
                            $cell1 = $table->addCell(null, array('gridSpan' => count($child) + 1, 'valign' => 'center'));
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText(htmlspecialchars("Kết quả trước đó / "), array('size' => 10, 'bold' => true));
                            $textrun1->addText(htmlspecialchars("Results of previous"), array('size' => 10, 'bold' => true, 'italic' => true));

                            $table->addRow();
                            $cell1 = $table->addCell(null, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText("Max", array('size' => 10, 'bold' => true));
                            foreach ($child as $target) {
                                $target_id = $target->id;
                                $value = $data_min_max_prev["max_$target_id"];
                                if ($value == "") {
                                    $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                                } else {
                                    $cell1 = $table->addCell(null, $cellRowSpan);
                                    $textrun1 = $cell1->addTextRun($cellHCentered);
                                    $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                                }
                            }
                            $table->addRow();
                            $cell1 = $table->addCell(null, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText("Min", array('size' => 10, 'bold' => true));
                            foreach ($child as $target) {
                                $target_id = $target->id;
                                $value = $data_min_max_prev["min_$target_id"];
                                if ($value == "") {
                                    $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                                } else {
                                    $cell1 = $table->addCell(null, $cellRowSpan);
                                    $textrun1 = $cell1->addTextRun($cellHCentered);
                                    $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                                }
                            }

                            $templateProcessor->setComplexBlock("result_table#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($key2 + 1) . "#" . ($key3 + 1), $table);
                        }
                    }

                    ///KIEM TRA CHART
                    $tmp_parent = array();
                    foreach ($target_parent as $k => $parent) {
                        $child = $parent->child;
                        $tmp = array();
                        for ($j = 0; $j < count($child); $j++) {
                            $target = $child[$j];
                            $name_chart = $object_id . "_" . $target->id . "_" . $department->id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . ".png";
                            // echo $name_chart . "<br>";
                            if (file_exists(APPPATH . '../public/upload/chart/' . $name_chart) && $target->type_data == "float") {
                                $tmp[] = $child[$j];
                            }
                        }

                        // $child = $tmp;
                        $parent_clone = clone $parent;
                        $parent_clone->child = $tmp;
                        if (!empty($tmp)) {
                            $tmp_parent[] = $parent_clone;
                        }
                    }
                    /////DRAW TREND
                    $templateProcessor->cloneBlock("target_parent_block#" . ($key + 1) . "#" . ($key1 + 1), count($tmp_parent), true, true);
                    for ($i = 0; $i < count($tmp_parent); $i++) {
                        $parent = $tmp_parent[$i];
                        $child = $parent->child;
                        $templateProcessor->setValue("parent_name#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($i + 1), $parent->name);
                        $templateProcessor->setValue("parent_name_en#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($i + 1), $parent->name_en);

                        $templateProcessor->cloneBlock("target_block#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($i + 1), count($child), true, true);
                        for ($j = 0; $j < count($child); $j++) {
                            $target = $child[$j];

                            $templateProcessor->setValue("target_name#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($i + 1) . "#" . ($j + 1), $target->name);
                            $templateProcessor->setValue("target_name_en#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($i + 1) . "#" . ($j + 1), $target->name_en);

                            $name_chart = $object_id . "_" . $target->id . "_" . $department->id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . ".png";

                            $templateProcessor->setImageValue("chart_image#" . ($key + 1) . "#" . ($key1 + 1) . "#" . ($i + 1) . "#" . ($j + 1), array('path' => APPPATH . '../public/upload/chart/' . $name_chart, 'width' => 1000, 'height' => 300, 'ratio' => false));
                        }
                    }
                }
            }
            // die();
            $name_file = "Bao_cao_" . $object_id . "_" . $workshop_id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . "_" . time() . ".docx";
            $name_file = urlencode($name_file);
            if (!file_exists(APPPATH . '../public/export')) {
                mkdir(APPPATH . '../public/export', 0777, true);
            }
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
        } else if ($object_id == 18 || $object_id == 19 || $object_id == 20) { // NUOC
            $object = $this->object_model->where(array('id' => $object_id))->as_object()->get();
            $workshop_id = $record->workshop_id;

            $workshop = $this->workshop_model->where(array('id' => $workshop_id))->with_factory()->as_object()->get();
            $workshop_name = $workshop->name;
            $workshop_name_en = $workshop->name_en;
            $object_name = $object->name;
            $object_name_en = $object->name_en;
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

            // echo "<pre>";
            // print_r($diagram_list);
            // die();
            $target_results = $this->result_model->set_value_export($params)->with_target()->group_by("target_id")->get_all();

            $target_parent = $target_list = array();
            foreach ($target_results as $temp) {
                $target = $temp->target;
                $target_object = $this->objecttarget_model->where(array("object_id" => $object_id, 'target_id' => $temp->target_id))->with_parent(array("with" => array('relation' => 'target')))->get();
                // echo "<pre>";
                // print_r($target_object);

                if (isset($target_object->parent) && !empty($target_object->parent)) {
                    $target->parent = $target_object->parent->target;
                    $target->parent_id = $target->parent->id;
                    if (isset($target_parent[$target->parent->id])) {
                        $target_parent[$target->parent->id]->count_child++;
                        $target_parent[$target->parent->id]->child[] = $target;
                    } else {
                        $target_parent[$target->parent->id] = (object) array();
                        $target_parent[$target->parent->id]->id = $target->parent->id;
                        $target_parent[$target->parent->id]->name = $target->parent->name;
                        $target_parent[$target->parent->id]->name_en = $target->parent->name_en;
                        $target_parent[$target->parent->id]->unit = $target->parent->unit;
                        $target_parent[$target->parent->id]->count_child = 1;
                        $target_parent[$target->parent->id]->child = array($target);
                    }
                }
                $target_list[] = $target;
            }

            $target_parent = array_values($target_parent);
            usort($target_parent, function ($a, $b) {
                return $a->id > $b->id;
            });
            usort($target_list, function ($a, $b) {
                return $a->parent_id > $b->parent_id;
            });
            // echo "<pre>";
            // print_r($target_list);
            // print_r($target_parent);
            // die();
            $system_list = $this->result_model->set_value_export($params)->with_system()->group_by("system_id")->get_all();
            $system_list = array_map(function ($item) {
                return $item->system;
            }, $system_list);
            usort($system_list, function ($a, $b) {
                return strcmp($a->name, $b->name);
            });

            // echo "<pre>";
            // print_r($workshop);
            // print_r($system_list);
            // die();
            //Get main section

            $file = APPPATH . '../public/upload/template/template_nuoc_nam.docx';
            if ($type != "Year") {
                $file = APPPATH . '../public/upload/template/template_nuoc.docx';
            }
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file);
            $list_type = array(
                'TwoYear' => "Các điểm lấy mẫu hàng quý",
                'Year' => "Các điểm lấy mẫu hàng tháng",
                'HalfYear' => "Các điểm lấy mẫu 2 tuần",
                'Month' => "Các điểm lấy mẫu hàng ngày",
                "Quarter" => "Các điểm lấy mẫu hàng quý"
            );
            $list_type_en = array(
                'TwoYear' => "Quarter sampling points",
                'Year' => "Monthly sampling points",
                'HalfYear' => "Two Weekly sampling points",
                'Month' => "Daily sampling points",
                "Quarter" => "Quarterly sampling points"
            );
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
            } elseif ($type == "TwoYear") {
                $type_bc = "mỗi hai năm";
                $type_bc_en = "every two year";
            }
            $templateProcessor->setValue('date_from', date("d/m/y", strtotime($params['date_from'])));
            $templateProcessor->setValue('date_from_prev', date("d/m/y", strtotime($params['date_from_prev'])));
            $templateProcessor->setValue('date_to', date("d/m/y", strtotime($params['date_to'])));
            $templateProcessor->setValue('date_to_prev', date("d/m/y", strtotime($params['date_to_prev'])));
            $templateProcessor->setValue('type_bc', $type_bc);
            $templateProcessor->setValue('type_bc_en', $type_bc_en);
            $templateProcessor->setValue('workshop_name', $workshop_name);
            $templateProcessor->setValue('workshop_name_en', $workshop_name_en);
            $templateProcessor->setValue('object_name', $object_name);
            $templateProcessor->setValue('object_name_en', $object_name_en);
            $templateProcessor->setValue('type_bc_cap', mb_strtoupper($type_bc, 'UTF-8'));
            $templateProcessor->setValue('type_bc_cap_en', mb_strtoupper($type_bc_en, 'UTF-8'));
            $templateProcessor->setValue('workshop_name_cap', mb_strtoupper($workshop_name, 'UTF-8'));
            $templateProcessor->setValue('workshop_name_cap_en', mb_strtoupper($workshop_name_en, 'UTF-8'));
            $templateProcessor->setValue('object_name_cap', mb_strtoupper($object_name, 'UTF-8'));
            $templateProcessor->setValue('object_name_cap_en', mb_strtoupper($object_name_en, 'UTF-8'));


            ////STYLE
            $cellRowSpan = array('valign' => 'center');
            $cellHCentered = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER);
            $cellHCenteredLEFT = array('alignment' => 'left');

            $styleCell = array('valign' => 'center');
            $fontCell = array('align' => 'center');
            $cellColSpan = array('gridSpan' => 4, 'valign' => 'center');
            $cellRowContinue = array('valign' => 'center', 'vMerge' => 'continue');
            $cellVCentered = array('valign' => 'center');

            $normal = array('size' => 10);
            $italic =  array('italic' => true, 'size' => 10);
            ////BLOCK POSITION
            $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 10000, 'unit' => TblWidth::TWIP, 'valign' => 'center'));
            $table->addRow(null, array('tblHeader' => true));
            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Mã số', $normal, $fontCell);
            $cell1->addText('ID No.', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Vị trí lấy mẫu', $normal, $fontCell);
            $cell1->addText('Sampling location', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Mã số phòng/Khu vực', $normal, $fontCell);
            $cell1->addText('ID No. of room/area', $italic, $fontCell);

            $cell1 = $table->addCell(null, array('valign' => 'center', 'bgColor' => "#c5c6c7"));
            $cell1->addText('Tần suất', $normal, $fontCell);
            $cell1->addText('Frequency', $italic, $fontCell);

            foreach ($system_list as $row) {
                $table->addRow();
                $cell1 = $table->addCell(null, array('gridSpan' => 4, 'valign' => 'center'));
                $textrun1 = $cell1->addTextRun($cellHCentered);
                $textrun1->addText($row->name, array('bold' => true, 'size' => 10));
                $textrun1->addTextBreak();
                $textrun1->addText($row->name_en, array('bold' => true, 'size' => 10, 'italic' => true), $fontCell);

                $position_list = $this->result_model->set_value_export($params)->where(array("system_id" => $row->id))->with_position()->with_department()->group_by("position_id")->get_all();
                // echo "<pre>";
                // print_r($position_list);
                // die();
                foreach ($position_list as $row2) {
                    $position = $row2->position;
                    $department = $row2->department;
                    $table->addRow();

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText(htmlspecialchars($position->string_id), $normal, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($position->name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($position->name_en), $italic, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText(htmlspecialchars($department->string_id), $normal, $fontCell);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCenteredLEFT);
                    $textrun1->addText(htmlspecialchars($position->frequency_name), $normal, $fontCell);
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars($position->frequency_name_en), $italic, $fontCell);
                }
            }

            $templateProcessor->setComplexBlock('table_position', $table);

            ////BLOCK DIAGRAM
            $position_list = $this->result_model->set_value_export($params)->group_by("position_id")->get_all();
            if (!empty($position_list)) {
                $position_list = array_map(function ($item) {
                    return $item->position_id;
                }, $position_list);
                $diagram_list = $this->diagram_position_model->where("position_id", "IN", $position_list)->with_diagram(array("with" => array("relation" => "images")))->group_by("diagram_id")->get_all();

                $diagram_list = array_map(function ($item) {
                    return $item->diagram;
                }, $diagram_list);
                $templateProcessor->cloneBlock("diagram_block", count($diagram_list), true, true);
                foreach ($diagram_list as $key => $row) {
                    $templateProcessor->setValue("diagram_name#" .  ($key + 1), $row->name);
                    $templateProcessor->setValue("diagram_name_en#" . ($key + 1), $row->name_en);
                    if (isset($row->images)) {
                        $row->images = array_values((array) $row->images);
                        if (count($row->images)) {
                            $templateProcessor->cloneBlock("image_block#" .  ($key + 1), count($row->images), true, true);
                            foreach ($row->images as $key_image => $image) {
                                $templateProcessor->setImageValue("diagram_image#"  . ($key + 1) . "#" . ($key_image + 1), array('path' => APPPATH . '../' . $image->src, 'width' => 600, 'height' => 500, 'ratio' => true));
                            }
                        } else {
                            $templateProcessor->deleteBlock("image_block#" .  ($key + 1));
                        }
                    }
                }
            } else {
                $templateProcessor->deleteBlock('diagram_block');
            }
            // $target_list;
            ///TABLE LIMIT

            $templateProcessor->cloneBlock("limit_block", count($system_list), true, true);
            for ($j = 0; $j < count($system_list); $j++) {
                $row2 = $system_list[$j];
                $templateProcessor->setValue("limit_parent_name#" .  ($j + 1), $system_list[$j]->name);
                $templateProcessor->setValue("limit_parent_name_en#" . ($j + 1), $system_list[$j]->name_en);

                $table = new Table(array(
                    'borderSize' => 3, 'width' => 100 * 50, 'size' => 10, 'unit' => 'pct',
                    'cellMargin'  => 80, 'valign' => 'center'
                ));
                $target_list = $this->result_model->set_value_export($params)->where("system_id", $system_list[$j]->id)->with_target()->group_by("target_id")->get_all();
                $target_list = array_map(function ($item) {
                    return $item->target;
                }, $target_list);
                $system_list[$j]->target_list = $target_list;
                $table->addRow();
                $table->addCell(null, $cellRowContinue);
                for ($i = 0; $i < count($target_list); $i++) {
                    $textrun = $table->addCell(null, $styleCell);
                    $textrun->addText($target_list[$i]->name, $normal, $fontCell);
                    $textrun->addText($target_list[$i]->name_en, $italic, $fontCell);
                }
                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Tiêu chuẩn chấp nhận', $normal, $fontCell);
                $textrun->addText('Acceptance criteria', $italic, $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $this->limit_model->where(array("system_id" => $row2->id, 'target_id' =>  $target->id))->where("day_effect", "<=", $params['date_from'])->order_by("day_effect", "DESC")->limit(1)->as_object()->get();
                    $target_list[$key]->limit = $limit;
                    // print_r($limit);
                    // die();  
                    //     ///DATA
                    $textrun = $table->addCell(null, $fontCell);
                    if ($target->type_data == "float") {
                        $value = isset($limit->standard_limit) ? $limit->standard_limit : "";
                    } else {
                        $value = isset($limit->standard_limit_text) ? $limit->standard_limit_text : "";
                    }
                    $textrun->addText($value, $normal, $fontCell);
                    if ($target->type_data != "float") {
                        $value_en = isset($limit->standard_limit_text_en) ? $limit->standard_limit_text_en : "";
                        if ($value_en != "" && $value != $value_en) {
                            $textrun->addText($value_en, $italic, $fontCell);
                        }
                    }
                }
                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Giới hạn cảnh báo', $normal, $fontCell);
                $textrun->addText('Alert Limit', $italic, $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $target->limit;
                    $textrun = $table->addCell(null, $fontCell);
                    if ($target->type_data == "float") {
                        $value = isset($limit->alert_limit) ? $limit->alert_limit : "";
                    } else {
                        $value = isset($limit->alert_limit_text) ? $limit->alert_limit_text : "";
                    }
                    $textrun->addText($value, $normal, $fontCell);
                    if ($target->type_data != "float") {
                        $value_en = isset($limit->alert_limit_text_en) ? $limit->alert_limit_text_en : "";
                        if ($value_en != "" && $value != $value_en) {
                            $textrun->addText($value_en, $italic, $fontCell);
                        }
                    }
                }
                $table->addRow();
                $textrun = $table->addCell(null, $styleCell);
                $textrun->addText('Giới hạn hành động', $normal, $fontCell);
                $textrun->addText('Action Limit', $italic, $fontCell);
                foreach ($target_list as $key => $target) {
                    $limit = $target->limit;
                    $textrun = $table->addCell(null, $fontCell);
                    if ($target->type_data == "float") {
                        $value = isset($limit->action_limit) ? $limit->action_limit : "";
                    } else {
                        $value = isset($limit->action_limit_text) ? $limit->action_limit_text : "";
                    }
                    $textrun->addText($value, $normal, $fontCell);
                    if ($target->type_data != "float") {
                        $value_en = isset($limit->action_limit_text_en) ? $limit->action_limit_text_en : "";
                        if ($value_en != "" && $value != $value_en) {
                            $textrun->addText($value_en, $italic, $fontCell);
                        }
                    }
                }


                $templateProcessor->setComplexBlock('table_limit#' .  ($j + 1), $table);
            }


            /////RESULT 
            // $area_results = $this->result_model->set_value_export($params)->with_area()->group_by("area_id")->get_all();
            // usort($area_results, function ($a, $b) {
            //     return strcmp($a->area->name, $b->area->name);
            // });
            $department_list = array();
            $length_system = count($system_list);
            $templateProcessor->cloneBlock("result_two_block", $length_system, true, true);
            for ($key1 = 0; $key1 < $length_system; $key1++) {
                $system = $system_list[$key1];
                $target_list = $system_list[$key1]->target_list;
                $head = "5.1";
                if ($type != "Year") {
                    $head = "2.";
                }
                ////DRAW RESULT
                $templateProcessor->setValue("two_heading#"  . ($key1 + 1), $head . ($key + 1) . "." . ($key1 + 1));
                $templateProcessor->setValue("two_name_heading#"  . ($key1 + 1), htmlspecialchars($system->name));
                $templateProcessor->setValue("two_name_en_heading#"  . ($key1 + 1), htmlspecialchars($system->name_en));

                $position_results = $this->result_model->set_value_export($params)->where(array('system_id' => $system->id))->with_position()->group_by("position_id")->get_all();
                $length_position = count($position_results);

                $templateProcessor->cloneBlock("position_block#"  . ($key1 + 1), $length_position, true, true);

                // echo "<pre>";
                // print_r($tmp_parent);
                // die();
                ////DATA
                for ($key2 = 0; $key2 < $length_position; $key2++) {
                    $position = $position_results[$key2]->position;
                    $params['position_id'] = $position->id;
                    $string_id = $position->string_id;
                    $templateProcessor->setValue("position_string_id#"  . ($key1 + 1) . "#" . ($key2 + 1), $position->string_id);

                    ///TABLE
                    $table = new Table(array('borderSize' => 3, 'cellMargin'  => 80, 'width' => 100 * 50, 'size' => 10, 'unit' => 'pct', 'valign' => 'center'));
                    $table->addRow(null, array('tblHeader' => true));

                    $cell1 = $table->addCell(null, $cellRowContinue);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText(htmlspecialchars("Mã số điểm lấy mẫu /"), array('size' => 10));
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars("ID of sampling points"), array('size' => 10, 'italic' => true));

                    $cell1 = $table->addCell(null, $cellRowContinue);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText(htmlspecialchars("Ngày /"), array('size' => 10));
                    $textrun1->addText(htmlspecialchars("Date"), array('size' => 10, 'italic' => true));
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars('(dd/mm/yy)'), array('size' => 10));
                    // for ($i = 0; $i < count($target_parent); $i++) {
                    //     $textrun = $table->addCell(null, array('gridSpan' => $target_parent[$i]->count_child, 'size' => 10, 'valign' => 'center'));
                    //     $textrun->addText($target_parent[$i]->name, $normal, $fontCell);
                    //     $textrun->addText($target_parent[$i]->name_en, $italic, $fontCell);
                    // }
                    // $table->addRow(null, array('tblHeader' => true));
                    // $table->addCell(null, $cellRowContinue);
                    for ($i = 0; $i < count($target_list); $i++) {
                        $textrun = $table->addCell(null, $styleCell);
                        $textrun->addText($target_list[$i]->name, array('size' => 10), $fontCell);
                        $textrun->addText($target_list[$i]->name_en, array('italic' => true, 'size' => 10), $fontCell);
                    }


                    //     ///DATA
                    $data = $this->result_model->get_data_table_by_target($target_list, $params);
                    $data_min_max = $this->result_model->get_data_table_by_target_minmax($target_list, $params['object_id'], $params['position_id'], $params['date_from'], $params['date_to']);
                    $data_min_max_prev = $this->result_model->get_data_table_by_target_minmax($target_list, $params['object_id'], $params['position_id'], $params['date_from_prev'], $params['date_to_prev']);


                    foreach ($data as $keystt => $stt) {
                        $table->addRow();
                        $date = date("d/m/y", strtotime($stt['date']));

                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars($string_id), array('size' => 10));

                        $cell1 = $table->addCell(null, $cellRowSpan);
                        $textrun1 = $cell1->addTextRun($cellHCentered);
                        $textrun1->addText(htmlspecialchars($date), array('size' => 10));
                        foreach ($target_list as $target) {
                            $target_id = $target->id;
                            $value = $stt[$target_id];
                            if ($value == "") {
                                $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                            } else if ($target->type_data == "boolean" && $value == 1) {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText("Đạt", array('size' => 10));
                                $textrun1->addTextBreak();
                                $textrun1->addText("Comply", array('italic' => true, 'size' => 10));
                            } else if ($target->type_data == "boolean" && $value == 0) {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText("Không Đạt", array('size' => 10));
                                $textrun1->addTextBreak();
                                $textrun1->addText("Not comply", array('italic' => true, 'size' => 10));
                            } else if ($target->type_data == "float") {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(round($value, 3), array('size' => 10));
                            } else {
                                $cell1 = $table->addCell(null, $cellRowSpan);
                                $textrun1 = $cell1->addTextRun($cellHCentered);
                                $textrun1->addText(htmlspecialchars($value), array('size' => 10));
                            }
                        }
                    }
                    ///MIN MAX
                    $table->addRow();

                    $cell1 = $table->addCell(null, $cellRowSpan);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText("Max", array('size' => 10, 'bold' => true));
                    foreach ($target_list as $target) {
                        $target_id = $target->id;
                        $value = $data_min_max["max_$target_id"];
                        if ($value == "") {
                            $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                        } else {
                            $cell1 = $table->addCell(null, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText(htmlspecialchars(round($value, 3)), array('size' => 10));
                        }
                    }
                    $table->addRow();
                    $cell1 = $table->addCell(null, $cellRowSpan);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText("Min", array('size' => 10, 'bold' => true));
                    foreach ($target_list as $target) {
                        $target_id = $target->id;
                        $value = $data_min_max["min_$target_id"];
                        if ($value == "") {
                            $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                        } else {
                            $cell1 = $table->addCell(null, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText(htmlspecialchars(round($value, 3)), array('size' => 10));
                        }
                    }

                    //$table->addRow(null);
                    //$cell1 = $table->addCell(null, array('gridSpan' => count($target_list) + 1, 'valign' => 'center'));
                    //$textrun1 = $cell1->addTextRun($cellHCentered);
                    //$textrun1->addText(htmlspecialchars("Kết quả trước đó / "), array('size' => 10, 'bold' => true));
                    //$textrun1->addText(htmlspecialchars("Results of previous"), array('size' => 10, 'bold' => true, 'italic' => true));

                    $table->addRow();

                    $cell1 = $table->addCell(null, $cellRowContinue);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText(htmlspecialchars("Kết quả trước đó / "), array('size' => 10, 'bold' => true));
                    $textrun1->addTextBreak();
                    $textrun1->addText(htmlspecialchars("Results of previous"), array('size' => 10,  'bold' => true, 'italic' => true));

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText("Max", array('size' => 10, 'bold' => true));
                    foreach ($target_list as $target) {
                        $target_id = $target->id;
                        $value = $data_min_max_prev["max_$target_id"];
                        if ($value == "") {
                            $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                        } else {
                            $cell1 = $table->addCell(null, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText(htmlspecialchars(round($value, 3)), array('size' => 10));
                        }
                    }
                    $table->addRow();
                    $cell1 = $table->addCell(null, $cellRowContinue);

                    $cell1 = $table->addCell(null, $cellRowSpan);
                    $textrun1 = $cell1->addTextRun($cellHCentered);
                    $textrun1->addText("Min", array('size' => 10, 'bold' => true));
                    foreach ($target_list as $target) {
                        $target_id = $target->id;
                        $value = $data_min_max_prev["min_$target_id"];
                        if ($value == "") {
                            $cell1 = $table->addCell(null, array('bgColor' => "#c5c6c7"));
                        } else {
                            $cell1 = $table->addCell(null, $cellRowSpan);
                            $textrun1 = $cell1->addTextRun($cellHCentered);
                            $textrun1->addText(htmlspecialchars(round($value, 3)), array('size' => 10));
                        }
                    }

                    $templateProcessor->setComplexBlock("result_table#"  . ($key1 + 1) . "#" . ($key2 + 1), $table);
                }

                // /////DRAW TREND
                $target_float = array_values(array_filter($target_list, function ($item) {
                    return $item->type_data == "float";
                }));
                $templateProcessor->cloneBlock("target_block#"  . ($key1 + 1), count($target_float), true, true);
                for ($i = 0; $i < count($target_float); $i++) {
                    $target = $target_float[$i];
                    $templateProcessor->setValue("target_name#"  . ($key1 + 1) . "#" . ($i + 1), $target->name);
                    $templateProcessor->setValue("target_name_en#"  . ($key1 + 1) . "#" . ($i + 1), $target->name_en);

                    $fre_list = $this->result_model->set_value_export($params)->where("system_id", $system->id)->where("target_id", $target->id)->group_by("type_bc")->get_all();

                    $templateProcessor->cloneBlock("fre_block#"  . ($key1 + 1) . "#" . ($i + 1), count($fre_list), true, true);
                    for ($j = 0; $j < count($fre_list); $j++) {
                        $fre = $fre_list[$j];
                        $type_bc = $fre->type_bc;
                        $type_bc_name = $list_type[$type_bc];
                        $type_bc_name_en = $list_type_en[$type_bc];
                        $templateProcessor->setValue("fre_name#"  . ($key1 + 1) . "#" . ($i + 1) . "#" . ($j + 1), $type_bc_name);
                        $templateProcessor->setValue("fre_name_en#"  . ($key1 + 1) . "#" . ($i + 1) . "#" . ($j + 1), $type_bc_name_en);

                        $name_chart = $object_id . "_" . $target->id . "_" . $workshop_id . "_" . $system->id . "_" . $type_bc . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . ".png";

                        $templateProcessor->setImageValue("chart_image#"  . ($key1 + 1) . "#" . ($i + 1) . "#" . ($j + 1), array('path' => APPPATH . '../public/upload/chart/' . $name_chart, 'width' => 1000, 'height' => 300, 'ratio' => false));
                    }
                }
            }

            // die();
            $name_file = "Bao_cao_" . $object_id . "_" . $workshop_id . "_" . $params['type'] . "_" . str_replace("/", "_", str_replace(" ", "_", $params['selector'])) . "_" . time() . ".docx";
            $name_file = urlencode($name_file);
            if (!file_exists(APPPATH . '../public/export')) {
                mkdir(APPPATH . '../public/export', 0777, true);
            }
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
        $time = date("Y-m-d H:i:s", strtotime("-3 hours"));
        $reports = $this->report_model->where(array('deleted' => 0, 'status' => 2))->where('date', '<', $time)->order_by("id", "ASC")->get_all();
        if (!empty($report)) {
            foreach ($reports as $report) {
                $id_record = $report->id;
                $this->report_model->update(array('status' => 2), $id_record);
                $this->export($id_record);
            }
        }
        echo 1;
    }
    function test()
    {

        $phpWord = \PhpOffice\PhpWord\IOFactory::load(APPPATH . '../public/upload/template/vitri/nuoc.docx');
        $sections = $phpWord->getSections();
        $section = $sections[0];
        // echo "<pre>";
        // print_r($section);
        // die();
        $file = APPPATH . '../public/upload/template/template_test.docx';
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file);
        $templateProcessor->setComplexBlock("result_table", $section);

        $name_file = time() . ".docx";
        $name_file = urlencode($name_file);
        if (!file_exists(APPPATH . '../public/export')) {
            mkdir(APPPATH . '../public/export', 0777, true);
        }
        $templateProcessor->saveAs(APPPATH . '../public/export/' . $name_file);
    }
    ////////////

    function job()
    {
        ini_set("SMTP", "smtp.gmail.com");
        ini_set("smtp_port", 465);
        $this->load->model("job_schedule_model");
        $results = $this->job_schedule_model->get_send();
        // print_r($results);
        // die();   
        if (!empty($results)) {
            ///SEND MAIL
            print_r("oko ko ko ko ko k ok ok o k");
            $config = array(
                'mailtype' => 'html',
                'protocol' => "smtp",
                'smtp_host' => "smtp.gmail.com",
                'smtp_user' => "lytranuit@gmail.com", // actual values different
                'smtp_pass' => "ifftdbgwneocusuz",
                'smtp_crypto' => "ssl",
                'wordwrap' => TRUE,
                'smtp_port' => 465,
                'starttls' => true,
                'newline' => "\r\n",
                'crlf' => "\r\n",
            );
            $this->load->library("email", $config);

            //            /*
            //             * Send mail
            //             */
            //            $this->email->clear();
            //            print_r($email_to);
            //            die();

            $mails = array();
            foreach ($results as $send) {
                $id_record = $send->id;
                $mail = explode(",", $send->mail);
                $equipment_id = $send->equipment_id;
                $equipment_name = $send->equipment_name;
                $change_date = $send->change_date;
                $next_date = $send->next_date;
                $username = $send->username;
                foreach ($mail as $m) {
                    if (!isset($mails[$m])) {
                        $mails[$m] = array();
                    }
                    array_push($mails[$m], array(
                        'equipment_id' => $equipment_id,
                        'equipment_name' => $equipment_name,
                        'change_date' => $change_date,
                        'next_date' => $next_date,
                        'username' => $username
                    ));
                }

                $this->job_schedule_model->update_next_send($id_record);
            }
            // echo "<pre>";
            // print_r($mails);
            // die();
            foreach ($mails as $mail => $value) {
                $this->email->clear(TRUE);
                $this->email->CharSet = "UTF-8";
                $this->email->from("lytranuit@gmail.com", "Auto Send");
                $this->email->to($mail); /// $conf['email_contact']
                $this->email->subject("Thay đổi mật khẩu");
                $this->email->set_mailtype("html");
                // $html = "";
                $this->data['rows'] = $value;
                $html = $this->blade->view()->make('email/change_pass', $this->data)->render();
                // $html = "";
                $this->email->message($html);

                // $this->supplierproduct_model->update(array("is_sent" => 1), $quote->id);
                if ($this->email->send()) {
                    //                echo json_encode(array('code' => 400, 'msg' => lang('alert_400')));
                } else {
                    // $file_log = './log_' . $quote->id . '.log';
                    // file_put_contents($file_log, $this->email->print_debugger(), FILE_APPEND);
                }

                ///UPDATE
            }
        }
        echo 1;
    }
    function export_request()
    {

        set_time_limit(-1);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');

        $id = $this->input->get("id");
        $this->load->model("template_model");
        $this->load->model("request_model");
        $this->load->model("variable_model");
        $this->load->model("user_model");
        $request = $this->request_model->with_template()->with_values()->get($id);
        $template = $request->template;

        $file = APPPATH . '../' . $template->file;

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file);

        //echo "<pre>";
        //print_r($request);
        //die();
        $values = $request->values;
        $list = array_filter($values, function ($item) {
            return $item->variable_type == 2;
        });

        //echo "<pre>";
        //print_r($list);
        //die();
        foreach ($list as $l) {
            $templateProcessor->cloneRow($l->variable_key, $l->value);
        }

        foreach ($values as $value) {
            $v = $value->value;
            $key = $value->variable_key;
            $key_type = $value->variable_type;
            $explode_key = explode("#", $key);
            $key_real_name = count($explode_key) ? $explode_key[0] : "";
            if ($key_type == 2)
                continue;
            if ($key_type == 5) {
                $variable = $this->variable_model->where("name", $key_real_name)->with_radio_values()->get();
                $radios = $variable->radio_values;
                foreach ($radios as $radio) {
                    if ($radio->value == $v) {
                        $templateProcessor->setImageValue("radio_" . $radio->value . "_" . $key, array('path' => APPPATH . '../public/img/tick.png', 'width' => 14, 'height' => 14));
                    } else {
                        $templateProcessor->setImageValue("radio_" . $radio->value . "_" . $key, array('path' => APPPATH . '../public/img/untick.png', 'width' => 14, 'height' => 14));
                    }
                }
            }
            if ($key_type == 4) {
                $date_sign = $value->date;
                //echo $date_sign;
                //die();
                if ($date_sign != "") {
                    $user = $this->user_model->get($v);
                    $user_name = $user->last_name;

                    $templateProcessor->setValue($key, $user_name . "</w:t><w:p/><w:t>" . $date_sign);
                } else {
                    $templateProcessor->setValue($key, "");
                }
            } else {
                $templateProcessor->setValue($key, $v);
            }
        }


        $name_file = time() . ".docx";
        $name_file = urlencode($name_file);
        if (!file_exists(APPPATH . '../public/export_request')) {
            mkdir(APPPATH . '../public/export_request', 0777, true);
        }
        //echo $temp_file;
        //die();
        $file_export = APPPATH . '../public/export/' . $name_file;
        $templateProcessor->saveAs($file_export);

        // Your browser will name the file "myFile.docx"
        // regardless of what it's named on the server 
        header("Content-Disposition: attachment; filename=myFile.docx");

        readfile($file_export);
    }
}
