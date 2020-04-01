<?php

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class Import extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $is_admin = $this->ion_auth->is_admin();
        if (!$is_admin) {
            show_404();
        }
    }

    public function _remap($method, $params = array())
    {
        if (!method_exists($this, $method)) {
            show_404();
        }
        $this->$method($params);
    }

    public function vitri()
    {
        die();
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        $file = APPPATH . '../public/upload/vitri_phong/vitri.xlsx';
        echo $file;
        //Tiến hành xác thực file
        $objFile = PHPExcel_IOFactory::identify($file);
        $objData = PHPExcel_IOFactory::createReader($objFile);

        //Chỉ đọc dữ liệu
        // $objData->setReadDataOnly(true);
        // Load dữ liệu sang dạng đối tượng
        $objPHPExcel = $objData->load($file);

        //Lấy ra số trang sử dụng phương thức getSheetCount();
        // Lấy Ra tên trang sử dụng getSheetNames();
        //Chọn trang cần truy xuất
        $sheet = $objPHPExcel->setActiveSheetIndex(0);

        //Lấy ra số dòng cuối cùng
        $Totalrow = $sheet->getHighestRow();
        //Lấy ra tên cột cuối cùng
        $LastColumn = $sheet->getHighestColumn();
        //Chuyển đổi tên cột đó về vị trí thứ, VD: C là 3,D là 4
        $TotalCol = PHPExcel_Cell::columnIndexFromString($LastColumn);

        //Tạo mảng chứa dữ liệu
        $data = [];

        //Tiến hành lặp qua từng ô dữ liệu
        //----Lặp dòng, Vì dòng đầu là tiêu đề cột nên chúng ta sẽ lặp giá trị từ dòng 2
        for ($i = 3; $i <= $Totalrow; $i++) {
            //----Lặp cột
            for ($j = 0; $j < $TotalCol; $j++) {
                // Tiến hành lấy giá trị của từng ô đổ vào mảng
                $data[$i - 3][$j] = $sheet->getCellByColumnAndRow($j, $i)->getValue();
            }
        }

        //Hiển thị mảng dữ liệu

        $this->load->model("area_model");
        $area_A = $this->area_model->where(array('id' => 1))->as_object()->get();
        $area_B = $this->area_model->where(array('id' => 4))->as_object()->get();
        $area_C = $this->area_model->where(array('id' => 2))->as_object()->get();
        $area_D = $this->area_model->where(array('id' => 3))->as_object()->get();

        $temp_area = array(
            'A' => array(
                'area_id' => $area_A->id,
                'factory_id' => $area_A->factory_id,
                'workshop_id' => $area_A->workshop_id,
            ),
            'B' => array(
                'area_id' => $area_B->id,
                'factory_id' => $area_B->factory_id,
                'workshop_id' => $area_B->workshop_id,
            ),
            'C' => array(
                'area_id' => $area_C->id,
                'factory_id' => $area_C->factory_id,
                'workshop_id' => $area_C->workshop_id,
            ),
            'D' => array(
                'area_id' => $area_D->id,
                'factory_id' => $area_D->factory_id,
                'workshop_id' => $area_D->workshop_id,
            )
        );
        $temp_target = array(
            'Active' => 5,
            'Passive' => 3,
            'Rodac' => 4
        );
        echo '<pre>';
        // print_r($temp_area);
        // print_r($data);

        $this->load->model("position_model");
        $this->load->model("department_model");
        $this->load->model("target_model");
        // print_r($temp_phong);
        // die();
        ///THEM PHÒNG
        $temp_phong = $this->department_model->where(array('deleted' => 0))->as_object()->get_all();
        for ($i = 0; $i < count($data); $i++) {
            $area_string = $data[$i][3];
            if ($data[$i][3] == "" || !isset($temp_area[$area_string])) {
                continue;
            }

            $phong_name = $data[$i][1];
            $phong_string_id = $data[$i][2];

            if ($phong_name == "" || $phong_string_id == "") {
                continue;
            }
            $target_name = $data[$i][4];

            $area = $temp_area[$area_string];
            $position_name = $data[$i][6];
            $frequency_name = $data[$i][7];
            $position_string_id = $data[$i][5];
            ////
            $find_phong = false;
            foreach ($temp_phong as $phong) {
                if ($phong_string_id == $phong->string_id) {
                    $find_phong = $phong;
                    break;
                }
            }
            if (!$find_phong) {
                $data_phong = array(
                    'name' => $phong_name,
                    'string_id' => $phong_string_id,
                    'area_id' => $area['area_id'],
                    'workshop_id' => $area['workshop_id'],
                    'factory_id' => $area['factory_id']
                );
                $phong_id = $this->department_model->insert($data_phong);
                $find_phong = $this->department_model->where(array('id' => $phong_id))->as_object()->get();
                array_push($temp_phong, $find_phong);
            }
            // $phong_id = $find_phong->id;
        }
        ///THÊM VỊ TRÍ

        $temp_position = $this->position_model->where(array('deleted' => 0))->as_object()->get_all();
        for ($i = 0; $i < count($data); $i++) {
            $area_string = $data[$i][3];
            if ($data[$i][3] == "" || !isset($temp_area[$area_string])) {
                continue;
            }
            $target_name = $data[$i][4];
            $target_id = $temp_target[$target_name];
            $area = $temp_area[$area_string];
            $position_name = $data[$i][6];
            $frequency_name = $data[$i][7];
            $position_string_id = $data[$i][5];

            if ($position_string_id == "" || $position_name == "") {
                continue;
            }

            ////
            $position_tmp = explode("_", $position_string_id);
            $phong_string_id = $position_tmp[0];
            // print_r($phong_string_id);
            $find_phong = false;
            foreach ($temp_phong as $phong) {
                if ($phong_string_id == $phong->string_id) {
                    $find_phong = $phong;
                    break;
                }
            }
            if (!$find_phong) {
                continue;
            }
            $phong_id = $find_phong->id;
            // print_r($find_phong);
            ////
            $find_position = false;
            foreach ($temp_position as $position) {
                if ($position_string_id == $position->string_id) {
                    $find_position = $position;
                    break;
                }
            }
            if (!$find_position) {
                $data_position = array(
                    'name' => $position_name,
                    'string_id' => $position_string_id,
                    'frequency_name' => $frequency_name,
                    'target_id' => $target_id,
                    'department_id' => $phong_id,
                    'area_id' => $area['area_id'],
                    'workshop_id' => $area['workshop_id'],
                    'factory_id' => $area['factory_id']
                );
                $position_id = $this->position_model->insert($data_position);
                $find_position = $this->position_model->where(array('id' => $position_id))->as_object()->get();
                array_push($temp_position, $find_position);
            }
            // $phong_id = $find_phong->id;
        }
    }
    public function nhanvien()
    {
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        $file = APPPATH . '../public/upload/vitri_phong/nhanvien.xlsx';
        echo $file;
        //Tiến hành xác thực file
        $objFile = PHPExcel_IOFactory::identify($file);
        $objData = PHPExcel_IOFactory::createReader($objFile);

        //Chỉ đọc dữ liệu
        // $objData->setReadDataOnly(true);
        // Load dữ liệu sang dạng đối tượng
        $objPHPExcel = $objData->load($file);

        //Lấy ra số trang sử dụng phương thức getSheetCount();
        // Lấy Ra tên trang sử dụng getSheetNames();
        //Chọn trang cần truy xuất
        $sheet = $objPHPExcel->setActiveSheetIndex(0);

        //Lấy ra số dòng cuối cùng
        $Totalrow = $sheet->getHighestRow();
        //Lấy ra tên cột cuối cùng
        $LastColumn = $sheet->getHighestColumn();
        //Chuyển đổi tên cột đó về vị trí thứ, VD: C là 3,D là 4
        $TotalCol = PHPExcel_Cell::columnIndexFromString($LastColumn);

        //Tạo mảng chứa dữ liệu
        $data = [];

        //Tiến hành lặp qua từng ô dữ liệu
        //----Lặp dòng, Vì dòng đầu là tiêu đề cột nên chúng ta sẽ lặp giá trị từ dòng 2
        for ($i = 1; $i <= $Totalrow; $i++) {
            //----Lặp cột
            for ($j = 0; $j < $TotalCol; $j++) {
                // Tiến hành lấy giá trị của từng ô đổ vào mảng
                $data[$i - 1][$j] = $sheet->getCellByColumnAndRow($j, $i)->getValue();
            }
        }
        // echo "<pre>";
        // print_r($data);
        // die();
        //Hiển thị mảng dữ liệu

        $this->load->model("area_model");
        $area_A = $this->area_model->where(array('id' => 1))->as_object()->get();
        $area_B = $this->area_model->where(array('id' => 4))->as_object()->get();
        $area_C = $this->area_model->where(array('id' => 2))->as_object()->get();
        $area_D = $this->area_model->where(array('id' => 3))->as_object()->get();

        $temp_area = array(
            'A' => array(
                'area_id' => $area_A->id,
                'factory_id' => $area_A->factory_id,
                'workshop_id' => $area_A->workshop_id,
            ),
            'B' => array(
                'area_id' => $area_B->id,
                'factory_id' => $area_B->factory_id,
                'workshop_id' => $area_B->workshop_id,
            ),
            'C' => array(
                'area_id' => $area_C->id,
                'factory_id' => $area_C->factory_id,
                'workshop_id' => $area_C->workshop_id,
            ),
            'D' => array(
                'area_id' => $area_D->id,
                'factory_id' => $area_D->factory_id,
                'workshop_id' => $area_D->workshop_id,
            )
        );
        // $temp_target = array(
        //     'Active' => 5,
        //     'Passive' => 3,
        //     'Rodac' => 4
        // );
        // echo '<pre>';
        // print_r($temp_area);
        // print_r($data);

        $this->load->model("position_model");
        $this->load->model("department_model");
        $this->load->model("target_model");
        // print_r($temp_phong);
        // die();
        ///THEM NHÂN VIÊN
        $temp_nhanvien = $this->department_model->where(array('deleted' => 0))->as_object()->get_all();
        for ($i = 0; $i < count($data); $i++) {
            $area_string = $data[$i][2];
            if ($data[$i][2] == "" || !isset($temp_area[$area_string])) {
                continue;
            }

            $nhanvien_name = $data[$i][0];
            $nhanvien_string_id = "NV_" . $data[$i][1] . "_" . $area_string;

            if ($nhanvien_name == "" || $nhanvien_string_id == "") {
                continue;
            }

            $area = $temp_area[$area_string];
            $frequency_name = $data[$i][3];
            ////
            $find_nhanvien = false;
            foreach ($temp_nhanvien as $nhanvien) {
                if ($nhanvien_string_id == $nhanvien->string_id) {
                    $find_nhanvien = $nhanvien;
                    break;
                }
            }
            if (!$find_nhanvien) {
                $data_nhanvien = array(
                    'name' => $nhanvien_name,
                    'string_id' => $nhanvien_string_id,
                    'area_id' => $area['area_id'],
                    'workshop_id' => $area['workshop_id'],
                    'factory_id' => $area['factory_id']
                );
                $nhanvien_id = $this->department_model->insert($data_nhanvien);
                $find_nhanvien = $this->department_model->where(array('id' => $nhanvien_id))->as_object()->get();
                array_push($temp_nhanvien, $find_nhanvien);

                ///THÊM VỊ TRÍ CHO NHÂN VIÊN
                ///ĐẦU
                $data_nhanvien = array();
                $data_nhanvien[] = array(
                    'name' => "Đầu",
                    'string_id' => $nhanvien_string_id . "_H",
                    'frequency_name' => $frequency_name,
                    'target_id' => 6,
                    'department_id' => $nhanvien_id,
                    'area_id' => $area['area_id'],
                    'workshop_id' => $area['workshop_id'],
                    'factory_id' => $area['factory_id']
                );
                ///Mũi
                $data_nhanvien[] = array(
                    'name' => "Mũi",
                    'string_id' => $nhanvien_string_id . "_N",
                    'frequency_name' => $frequency_name,
                    'target_id' => 6,
                    'department_id' => $nhanvien_id,
                    'area_id' => $area['area_id'],
                    'workshop_id' => $area['workshop_id'],
                    'factory_id' => $area['factory_id']
                );
                ///Ngực
                $data_nhanvien[] = array(
                    'name' => "Ngực",
                    'string_id' => $nhanvien_string_id . "_C",
                    'frequency_name' => $frequency_name,
                    'target_id' => 6,
                    'department_id' => $nhanvien_id,
                    'area_id' => $area['area_id'],
                    'workshop_id' => $area['workshop_id'],
                    'factory_id' => $area['factory_id']
                );
                ///Cẳng tay trái
                $data_nhanvien[] = array(
                    'name' => "Cẳng tay trái",
                    'string_id' => $nhanvien_string_id . "_LF",
                    'frequency_name' => $frequency_name,
                    'target_id' => 6,
                    'department_id' => $nhanvien_id,
                    'area_id' => $area['area_id'],
                    'workshop_id' => $area['workshop_id'],
                    'factory_id' => $area['factory_id']
                );
                ///Cẳng tay phải
                $data_nhanvien[] = array(
                    'name' => "Cẳng tay phải",
                    'string_id' => $nhanvien_string_id . "_RF",
                    'frequency_name' => $frequency_name,
                    'target_id' => 6,
                    'department_id' => $nhanvien_id,
                    'area_id' => $area['area_id'],
                    'workshop_id' => $area['workshop_id'],
                    'factory_id' => $area['factory_id']
                );
                ///Dấu găng tay trái
                $data_nhanvien[] = array(
                    'name' => "Dấu găng tay trái",
                    'string_id' => $nhanvien_string_id . "_LG",
                    'frequency_name' => $frequency_name,
                    'target_id' => 6,
                    'department_id' => $nhanvien_id,
                    'area_id' => $area['area_id'],
                    'workshop_id' => $area['workshop_id'],
                    'factory_id' => $area['factory_id']
                );
                ///Dấu găng tay phải
                $data_nhanvien[] = array(
                    'name' => "Dấu găng tay phải",
                    'string_id' => $nhanvien_string_id . "_RG",
                    'frequency_name' => $frequency_name,
                    'target_id' => 6,
                    'department_id' => $nhanvien_id,
                    'area_id' => $area['area_id'],
                    'workshop_id' => $area['workshop_id'],
                    'factory_id' => $area['factory_id']
                );
                $this->position_model->insert($data_nhanvien);
            }
            // $phong_id = $find_phong->id;
        }
    }
    public function result()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/data/';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $insert = array();
        $sortedarray1 = array_values(array_diff(scandir($dir), array('..', '.')));
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $dir . $file_name;
            //Tiến hành xác thực file
            $objFile = PHPExcel_IOFactory::identify($file);
            $objData = PHPExcel_IOFactory::createReader($objFile);

            //Chỉ đọc dữ liệu
            // $objData->setReadDataOnly(true);
            // Load dữ liệu sang dạng đối tượng
            $objPHPExcel = $objData->load($file);

            //Lấy ra số trang sử dụng phương thức getSheetCount();
            // Lấy Ra tên trang sử dụng getSheetNames();
            //Chọn trang cần truy xuất
            $count_sheet = $objPHPExcel->getSheetCount();
            for ($k = 0; $k < $count_sheet; $k++) {
                $sheet_name = "sheet_" . $k  . "_" . $file_name;
                $sheet = $objPHPExcel->setActiveSheetIndex($k);

                //Lấy ra số dòng cuối cùng
                $Totalrow = $sheet->getHighestRow();
                //Lấy ra tên cột cuối cùng
                $LastColumn = $sheet->getHighestColumn();
                //Chuyển đổi tên cột đó về vị trí thứ, VD: C là 3,D là 4
                $TotalCol = PHPExcel_Cell::columnIndexFromString($LastColumn);

                //Tạo mảng chứa dữ liệu
                $data = [];

                //Tiến hành lặp qua từng ô dữ liệu
                //----Lặp dòng, Vì dòng đầu là tiêu đề cột nên chúng ta sẽ lặp giá trị từ dòng 2
                for ($i = 11; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - 11][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - 11][$j] instanceof PHPExcel_RichText) {
                            $data[$i - 11][$j] = $data[$i - 11][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - 11][$j] > 0) {

                            if (is_numeric($data[$i - 11][$j])) {
                                $data[$i - 11][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - 11][$j]));
                            } else if ($data[$i - 11][$j] == '26/09/16') {
                                $data[$i - 11][$j] = '2016-09-26';
                            }
                        }
                    }
                }
                //                echo "<pre>";
                // print_r($data);
                // die();
                ///LIST POSTION
                $list_position = array_shift($data);
                ///XOA 1 ROW
                array_shift($data);
                $this->load->model("position_model");
                echo "<pre>";
                $positions = array();
                for ($i = 0; $i < count($list_position); $i++) {
                    $position = $list_position[$i];
                    if ($position == "") {
                        continue;
                    }

                    $find_vitri = $this->position_model->where(array('string_id' => $position))->as_object()->get();
                    //            print_r($find_phong);
                    if (empty($find_vitri)) {
                        continue;
                    }
                    $find_vitri->col = $i;
                    array_push($positions, $find_vitri);
                }
                //        print_r($positions);
                // print_r($data);
                // die();
                // print_r($temp_phong);
                // die();
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    foreach ($positions as $position) {
                        $position_id = $position->id;
                        $area_id = $position->area_id;
                        $department_id = $position->department_id;
                        $target_id = $position->target_id;
                        $factory_id = $position->factory_id;
                        $workshop_id = $position->workshop_id;
                        $date = $row[1];
                        // if ($date == '2020-03-21') {
                        //     print_r($i);
                        //     print($data);
                        //     die();
                        // }
                        $value = $row[$position->col];
                        if (is_null($value) || !is_Date($date) || !is_numeric($value)) {
                            continue;
                        }
                        $data_up = array(
                            'value' => $value,
                            'position_id' => $position_id,
                            'area_id' => $area_id,
                            'department_id' => $department_id,
                            'target_id' => $target_id,
                            'factory_id' => $factory_id,
                            'workshop_id' => $workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name
                            //                        'workshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $insert[] = $data_up;
                    }

                    // $phong_id = $find_phong->id;
                }
            }
        }
        print_r($insert);
        $this->result_model->insert($insert);
    }
    public function result_nhanvien()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/data_nhanvien/';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $this->load->model("position_model");
        $this->load->model("department_model");
        $this->load->model("area_model");
        $area_A = $this->area_model->where(array('id' => 1))->as_object()->get();
        $area_B = $this->area_model->where(array('id' => 4))->as_object()->get();
        $area_C = $this->area_model->where(array('id' => 2))->as_object()->get();
        $area_D = $this->area_model->where(array('id' => 3))->as_object()->get();

        $temp_area = array(
            'A' => array(
                'area_id' => $area_A->id,
                'factory_id' => $area_A->factory_id,
                'workshop_id' => $area_A->workshop_id,
            ),
            'B' => array(
                'area_id' => $area_B->id,
                'factory_id' => $area_B->factory_id,
                'workshop_id' => $area_B->workshop_id,
            ),
            'C' => array(
                'area_id' => $area_C->id,
                'factory_id' => $area_C->factory_id,
                'workshop_id' => $area_C->workshop_id,
            ),
            'D' => array(
                'area_id' => $area_D->id,
                'factory_id' => $area_D->factory_id,
                'workshop_id' => $area_D->workshop_id,
            )
        );
        $insert = array();
        $sortedarray1 = array_values(array_diff(scandir($dir), array('..', '.')));
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $dir . $file_name;
            //Tiến hành xác thực file
            $objFile = PHPExcel_IOFactory::identify($file);
            $objData = PHPExcel_IOFactory::createReader($objFile);

            //Chỉ đọc dữ liệu
            // $objData->setReadDataOnly(true);
            // Load dữ liệu sang dạng đối tượng
            $objPHPExcel = $objData->load($file);

            //Lấy ra số trang sử dụng phương thức getSheetCount();
            // Lấy Ra tên trang sử dụng getSheetNames();
            //Chọn trang cần truy xuất
            $count_sheet = $objPHPExcel->getSheetCount();
            for ($k = 0; $k < $count_sheet; $k++) {
                $sheet_name = "sheet_" . $k  . "_" . $file_name;
                $sheet = $objPHPExcel->setActiveSheetIndex($k);

                //Lấy ra số dòng cuối cùng
                $Totalrow = $sheet->getHighestRow();
                //Lấy ra tên cột cuối cùng
                $LastColumn = $sheet->getHighestColumn();
                //Chuyển đổi tên cột đó về vị trí thứ, VD: C là 3,D là 4
                $TotalCol = PHPExcel_Cell::columnIndexFromString($LastColumn);

                //Tạo mảng chứa dữ liệu
                $data = [];

                //Tiến hành lặp qua từng ô dữ liệu
                //----Lặp dòng, Vì dòng đầu là tiêu đề cột nên chúng ta sẽ lặp giá trị từ dòng 2
                for ($i = 11; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - 11][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - 11][$j] instanceof PHPExcel_RichText) {
                            $data[$i - 11][$j] = $data[$i - 11][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - 11][$j] > 0) {

                            if (is_numeric($data[$i - 11][$j])) {
                                $data[$i - 11][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - 11][$j]));
                            } else if ($data[$i - 11][$j] == '26/09/16') {
                                $data[$i - 11][$j] = '2016-09-26';
                            }
                        }
                    }
                }
                $nhanvien_string_id = $sheet->getCellByColumnAndRow(5, 6)->getValue();
                $area_string = $sheet->getCellByColumnAndRow(2, 7)->getValue();
                // echo $nhanvien_string_id . "<br>" . $area_string;

                if (!isset($temp_area[$area_string])) {
                    continue;
                }
                $area = $temp_area[$area_string];
                $nhan_vien = $this->department_model->where(array('area_id' => $area['area_id'], 'string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string))->as_object()->get();

                // echo "<pre>";
                // print_r($nhan_vien);
                if (empty($nhan_vien)) {
                    continue;
                }
                $position_H = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_H"))->as_object()->get();
                $position_N = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_N"))->as_object()->get();
                $position_C = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_C"))->as_object()->get();
                $position_LF = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_LF"))->as_object()->get();
                $position_RF = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_RF"))->as_object()->get();
                $position_LG = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_LG"))->as_object()->get();
                $position_RG = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_RG"))->as_object()->get();

                // die();
                ///LIST POSTION
                $list_position = array_shift($data);
                ///XOA 1 ROW
                array_shift($data);

                // echo "<pre>";
                //        print_r($positions);
                // print_r($data);
                // die();
                // print_r($temp_phong);
                // die();
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    $date = $row[1];
                    $head = $row[2];
                    $nose = $row[3];
                    $chest = $row[4];
                    $lf = $row[5];
                    $rf = $row[6];
                    $lg = $row[7];
                    $rg = $row[8];
                    if (!is_Date($date)) {
                        continue;
                    }
                    if (is_numeric($head)) {
                        $data_up = array(
                            'value' => $head,
                            'position_id' => $position_H->id,
                            'area_id' => $position_H->area_id,
                            'department_id' => $position_H->department_id,
                            'target_id' => 6,
                            'factory_id' => $position_H->factory_id,
                            'workshop_id' => $position_H->workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name
                            //                        'workshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $insert[] = $data_up;
                    }
                    if (is_numeric($nose)) {
                        $data_up = array(
                            'value' => $nose,
                            'position_id' => $position_N->id,
                            'area_id' => $position_N->area_id,
                            'department_id' => $position_N->department_id,
                            'target_id' => 6,
                            'factory_id' => $position_N->factory_id,
                            'workshop_id' => $position_N->workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name
                            //                        'workshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $insert[] = $data_up;
                    }
                    if (is_numeric($chest)) {
                        $data_up = array(
                            'value' => $chest,
                            'position_id' => $position_C->id,
                            'area_id' => $position_C->area_id,
                            'department_id' => $position_C->department_id,
                            'target_id' => 6,
                            'factory_id' => $position_C->factory_id,
                            'workshop_id' => $position_C->workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name
                            //                        'workshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $insert[] = $data_up;
                    }
                    if (is_numeric($lf)) {
                        $data_up = array(
                            'value' => $lf,
                            'position_id' => $position_LF->id,
                            'area_id' => $position_LF->area_id,
                            'department_id' => $position_LF->department_id,
                            'target_id' => 6,
                            'factory_id' => $position_LF->factory_id,
                            'workshop_id' => $position_LF->workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name
                            //                        'workshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $insert[] = $data_up;
                    }
                    if (is_numeric($rf)) {
                        $data_up = array(
                            'value' => $rf,
                            'position_id' => $position_RF->id,
                            'area_id' => $position_RF->area_id,
                            'department_id' => $position_RF->department_id,
                            'target_id' => 6,
                            'factory_id' => $position_RF->factory_id,
                            'workshop_id' => $position_RF->workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name
                            //                        'workshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $insert[] = $data_up;
                    }
                    if (is_numeric($lg)) {
                        $data_up = array(
                            'value' => $lg,
                            'position_id' => $position_LG->id,
                            'area_id' => $position_LG->area_id,
                            'department_id' => $position_LG->department_id,
                            'target_id' => 6,
                            'factory_id' => $position_LG->factory_id,
                            'workshop_id' => $position_LG->workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name
                            //                        'workshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $insert[] = $data_up;
                    }
                    if (is_numeric($rg)) {
                        $data_up = array(
                            'value' => $rg,
                            'position_id' => $position_RG->id,
                            'area_id' => $position_RG->area_id,
                            'department_id' => $position_RG->department_id,
                            'target_id' => 6,
                            'factory_id' => $position_RG->factory_id,
                            'workshop_id' => $position_RG->workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name
                            //                        'workshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $insert[] = $data_up;
                    }
                }
            }
        }
        print_r($insert);
        $this->result_model->insert($insert);
    }
}
