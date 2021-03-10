<?php

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class Import extends MY_Controller
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
            base_url() . "public/admin/vendor/inputmask/js/jquery.inputmask.bundle.js",
            base_url() . "public/admin/libs/js/moment.js",
            base_url() . "public/assets/scripts/jquery.cookies.2.2.0.min.js",
            base_url() . "public/assets/scripts/custom.js?v=" . $version
        );


        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $object_array = array(10, 11);
        if (!in_array((int) $object_id, $object_array)) {
            $this->redirect_result($object_id);
        }
    }

    public function _remap($method, $params = array())
    {
        if (!method_exists($this, $method)) {
            show_404();
        }

        if (!$this->ion_auth->in_group($this->group)) {
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
        return true;
    }

    public function index()
    { /////// trang ca nhan
        load_datatable($this->data);
        load_daterangepicker($this->data);
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    public function add()
    { /////// trang ca nhan
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        if (isset($_POST['dangtin'])) {
            $data = $_POST;
            $this->load->model("import_model");

            $data['user_id'] = $this->session->userdata('user_id');
            $data['date'] = date("Y-m-d H:i:s");
            $data['object_id'] = $object_id;

            ///FILE
            ini_set('post_max_size', '64M');
            ini_set('upload_max_filesize', '64M');
            $this->load->helper('file');
            $date = date("Y-m-d");
            $upload_path_url = "public/uploads/$date/";
            $dir = FCPATH . "public/uploads/$date/";
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $config['upload_path'] = $dir;
            $config['allowed_types'] = 'xlsx';
            $config['max_size'] = '10000';
            $this->load->library('upload', $config);

            $file_count = count($_FILES['files']['name']);

            $files = $_FILES;

            for ($i = 0; $i < $file_count; $i++) {
                $ext = pathinfo($files['files']['name'][$i], PATHINFO_EXTENSION);
                $real_name = $files['files']['name'][$i];

                // Define new $_FILES array - $_FILES['file']
                $_FILES['file']['name'] =  time() . "_$i." . $ext;
                $_FILES['file']['type'] = $_FILES['files']['type'][$i];
                $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$i];
                $_FILES['file']['error'] = $_FILES['files']['error'][$i];
                $_FILES['file']['size'] = $_FILES['files']['size'][$i];

                // Set preference

                if (!$this->upload->do_upload('file')) {
                    $errors = $this->upload->display_errors();
                    print_r($errors);
                }
                $data['file_name'] =  $real_name;
                $data['file'] =  $upload_path_url . $_FILES['file']['name'];
                //$data_upload = $this->upload->data();

                ////END FILE

                $data_up = $this->import_model->create_object($data);
                $id = $this->import_model->insert($data_up);

                /// Log audit trail
                $text =   "USER '" . $this->session->userdata('username') . "' added a new record($id) to the table 'pmp_import'";
                $this->import_model->trail($id, "insert", null, $data_up, null, $text);
            }
            redirect('import', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {

            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }
    public function remove($params)
    { /////// trang ca nhan
        $this->load->model("import_model");
        $id = $params[0];
        $status = $this->import_model->update(array("deleted" => 1), $id);

        /// Log audit trail
        $text =   "USER '" . $this->session->userdata('username') . "' removed record($id) to the table 'pmp_import'";
        $this->import_model->trail($status, "delete", null, null, $id, $text);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function table()
    {
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $this->load->model("import_model");
        $this->load->model("limit_model");
        $limit = $this->input->post('length');
        $start = $this->input->post('start');
        $page = ($start / $limit) + 1;


        if (empty($this->input->post('search')['value'])) {
            //            $max_page = ceil($totalFiltered / $limit);
            $where = $this->import_model->where(array("deleted" => 0, 'object_id' => $object_id));
            $totalFiltered = $where->count_rows();
            $where = $this->import_model->where(array("deleted" => 0, 'object_id' => $object_id));
        } else {
            $search = $this->input->post('search')['value'];
            $sWhere = "deleted = 0 and object_id = " . $this->db->escape($object_id) . " and note like '%" .  $this->db->escape($search) . "%'";
            $where = $this->import_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
            $totalFiltered = $where->count_rows();
            $where = $this->import_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
        }

        $posts = $where->order_by("id", "DESC")->with_user()->paginate($limit, NULL, $page);
        //        echo "<pre>";
        //        print_r($posts);
        //        die();
        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $post) {

                $nestedData['id'] = $post->id;
                $nestedData['user_name'] = isset($post->user->last_name) ? $post->user->last_name : "";
                $nestedData['date'] = $post->date;
                $nestedData['note'] = $post->note;
                $nestedData['file'] = "<a href=" . base_url()  . $post->file . ">" . $post->file_name . '</a><br>';
                $nestedData['action'] = "";
                $nestedData['logs'] = $post->logs;
                if ($post->status == 1) {
                    $nestedData['action'] =
                        '<a href="' . base_url() . 'import/import/' . $post->id . '" class="btn btn-primary btn-sm mr-1" data-type="confirm" title="import">'
                        . '<i class="fas fa-file-import"></i>'
                        . '</i>'
                        . '</a>';
                }
                if ($post->status == 2) {
                    $nestedData['action'] =
                        '<a href="' . base_url() . 'import/truncate/' . $post->id . '" class="btn btn-warning btn-sm mr-1" data-type="confirm" title="truncate">'
                        . '<i class="fas fa-file-export"></i>'
                        . '</i>'
                        . '</a>';
                }
                $nestedData['action'] .=
                    '<a href="' . base_url() . 'import/remove/' . $post->id . '" class="btn btn-danger btn-sm" data-type="confirm" title="remove">'
                    . '<i class="far fa-trash-alt">'
                    . '</i>'
                    . '</a>';

                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw" => intval($this->input->post('draw')),
            "recordsTotal" => intval($totalFiltered),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        );

        echo json_encode($json_data);
    }
    public function import($id_record, $refresh = true)
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        // $id_record = $this->input->get('id_record', TRUE);
        // print_r($id_record);
        // die();
        $this->load->model("import_model");
        $this->load->model("workshop_model");
        $this->load->model("position_model");
        $this->load->model("result_model");
        $this->load->model("limit_model");
        $this->load->model("objecttarget_model");
        $this->load->model("object_model");
        $this->load->model("system_model");
        $record = $this->import_model->where(array("id" => $id_record, 'deleted' => 0, 'status' => 1))->get();
        if (empty($record)) {
            redirect('import', 'refresh');
        }
        // print_r($record);
        // die();
        $object_id = $record->object_id;

        $analytics = array(
            'miss_row' => array(),
            'success' => 0
        );
        if ($object_id == 3) {
            $analytics['new_nhanvien'] = array();
            $file = APPPATH . "../" . $record->file;

            $area_id = $record->area_id;
            //echo '<pre>';
            //print_r($area_id);
            //die();
            //$area_id =
            $area = $this->area_model->where(array('id' => $area_id))->as_array()->get();
            //Tiến hành xác thực file
            $objFile = PHPExcel_IOFactory::identify($file);
            $objData = PHPExcel_IOFactory::createReader($objFile);

            //Chỉ đọc dữ liệu
            //$objData->setReadDataOnly(true);
            // Load dữ liệu sang dạng đối tượng
            $objPHPExcel = $objData->load($file);

            //Lấy ra số trang sử dụng phương thức getSheetCount();
            // Lấy Ra tên trang sử dụng getSheetNames();
            //Chọn trang cần truy xuất
            $count_sheet = $objPHPExcel->getSheetCount();
            for ($k = 0; $k < $count_sheet; $k++) {
                $sheet_name = "sheet_" . $k  . "_" . $file;
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
                $fisrt = 8;
                for ($i = $fisrt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i -  $fisrt][$j] = $cell->getCalculatedValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i -  $fisrt][$j] instanceof PHPExcel_RichText) {
                            $data[$i -  $fisrt][$j] = $data[$i -  $fisrt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i -  $fisrt][$j] > 0) {

                            if (is_numeric($data[$i -  $fisrt][$j])) {
                                $data[$i -  $fisrt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i -  $fisrt][$j]));
                            } else if ($data[$i -  $fisrt][$j] == '26/09/16') {
                                $data[$i -  $fisrt][$j] = '2016-09-26';
                            }
                        }
                    }
                }
                //echo "<pre>";
                //print_r($data);
                //die();
                //$nhanvien_string_id = $sheet->getCellByColumnAndRow(5, 6)->getValue();
                //$area_string = $sheet->getCellByColumnAndRow(2, 7)->getValue();
                // echo $nhanvien_string_id . "<br>" . $area_string;

                //if (!isset($temp_area[$area_string])) {
                //    continue;
                //}
                //$area = $temp_area[$area_string];
                //$nhan_vien = $this->employee_model->where(array('string_id' => $nhanvien_string_id))->as_object()->get();

                //// echo "<pre>";
                //// print_r($nhan_vien);
                //if (empty($nhan_vien)) {
                //    continue;
                //}

                // $position_H = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_H"))->as_object()->get();
                // $position_N = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_N"))->as_object()->get();
                // $position_C = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_C"))->as_object()->get();
                // $position_LF = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_LF"))->as_object()->get();
                // $position_RF = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_RF"))->as_object()->get();
                // $position_LG = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_LG"))->as_object()->get();
                // $position_RG = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_RG"))->as_object()->get();

                // die();
                ///LIST POSTION
                //$list_position = array_shift($data);
                ///XOA 1 ROW
                //array_shift($data);

                // echo "<pre>";
                //        print_r($positions);
                // print_r($data);
                // die();
                // print_r($temp_phong);
                // die();

                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    $nhanvien_string_id = $row[0];
                    if (!is_numeric($nhanvien_string_id)) {
                        continue;
                    }
                    $nhanvien_name = $row[1];

                    $nhan_vien = $this->employee_model->where(array('string_id' => $nhanvien_string_id))->as_object()->get();
                    //print_r($nh)
                    if (empty($nhan_vien)) {
                        $employee = array(
                            'string_id' => $nhanvien_string_id,
                            'name' => $nhanvien_name
                        );
                        $id = $this->employee_model->insert($employee);

                        $nhan_vien = $this->employee_model->where(array('id' => $id))->as_object()->get();
                        $analytics['new_nhanvien'][] = $employee;
                    }

                    $date = $row[2];
                    $head = $row[3];
                    $nose = $row[4];
                    $chest = $row[5];
                    $lf = $row[6];
                    $rf = $row[7];
                    $lg = $row[8];
                    $rg = $row[9];

                    if (!is_Date($date)) {
                        continue;
                        $analytics['miss_row'][] = $row;
                    }
                    $analytics['success']++;
                    $data_up = array(
                        'employee_id' => $nhan_vien->id,
                        'area_id' => $area['id'],
                        'factory_id' => $area['factory_id'],
                        'workshop_id' => $area['workshop_id'],
                        'from_file' => $sheet_name,
                        'created_at' => date("Y-m-d H:i:s"),
                        'date' => $date,
                        'value_H' => $head,
                        'value_N' => $nose,
                        'value_C' => $chest,
                        'value_LF' => $lf,
                        'value_RF' => $rf,
                        'value_LG' => $lg,
                        'value_RG' => $rg
                    );
                    $insert[] = $data_up;
                }
                $this->employeeresult_model->insert($insert);
            }
        } elseif ($object_id == 10 || $object_id == 11) {
            $file = APPPATH . "../" . $record->file;
            $objFile = PHPExcel_IOFactory::identify($file);
            $objData = PHPExcel_IOFactory::createReader($objFile);

            //Chỉ đọc dữ liệu
            //$objData->setReadDataOnly(true);
            // Load dữ liệu sang dạng đối tượng
            $objPHPExcel = $objData->load($file);

            //Lấy ra số trang sử dụng phương thức getSheetCount();
            // Lấy Ra tên trang sử dụng getSheetNames();
            //Chọn trang cần truy xuất
            $count_sheet = $objPHPExcel->getSheetCount();
            for ($k = 0; $k < $count_sheet; $k++) {
                $sheet_name = "sheet_" . $k  . "_" . $file;
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
                $fisrt = 9;
                for ($i = $fisrt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i -  $fisrt][$j] = $cell->getCalculatedValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i -  $fisrt][$j] instanceof PHPExcel_RichText) {
                            $data[$i -  $fisrt][$j] = $data[$i -  $fisrt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i -  $fisrt][$j] > 0) {

                            if (is_numeric($data[$i -  $fisrt][$j])) {
                                $data[$i -  $fisrt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i -  $fisrt][$j]));
                            } else if ($data[$i -  $fisrt][$j] == '26/09/16') {
                                $data[$i -  $fisrt][$j] = '2016-09-26';
                            }
                        }
                    }
                }
                //echo '<pre>';
                //print_r($data);
                //die();

                ///LIST POSTION
                $list_position = array_shift($data);
                ///XOA 1 ROW
                array_shift($data);
                //echo "<pre>";
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
                // print_r($data);
                // die();
                // print_r($temp_phong);
                // die();
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    $date = $row[0];
                    foreach ($positions as $position) {
                        $position_id = $position->id;
                        $value = $row[$position->col];
                        if (is_null($value) || !is_Date($date) || !is_numeric($value)) {
                            continue;
                            $analytics['miss_row'][] = $row;
                        }
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_in_day($position_id, $date);
                        // $data['stt_in_day'] = $max_stt;
                        $data_up = array(
                            'value' => $value,
                            'position_id' => $position_id,
                            'area_id' => $position->area_id,
                            'department_id' => $position->department_id,
                            'target_id' => $position->target_id,
                            'factory_id' => $position->factory_id,
                            'workshop_id' => $position->workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name,
                            'object_id' => $position->object_id,
                            'type_bc' => $position->type_bc,
                            'stt_in_day' => $max_stt
                            //                        'w,orkshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $this->result_model->insert($data_up);

                        $analytics['success']++;
                    }
                    // $phong_id = $find_phong->id;
                }
            }
        }
        $logs = array("status" => 2, 'logs' => json_encode($analytics));
        $this->import_model->update($logs, $record->id);
        if ($refresh) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }
    public function import_all()
    {
        set_time_limit(-1);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        $this->load->model("import_model");
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $records = $this->import_model->where(array('deleted' => 0, 'status' => 1, 'object_id' => $object_id))->get_all();
        foreach ($records as $record) {
            $this->import($record->id, false);
        }

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    public function truncate($id_record)
    {
        $this->load->model("import_model");
        $this->load->model("result_model");
        $record = $this->import_model->where(array("id" => $id_record, 'deleted' => 0, 'status' => 2))->get();
        if (empty($record)) {
            redirect('import', 'refresh');
        }
        // print_r($record);
        $object_id = $record->object_id;
        if ($object_id == 3) {
        } elseif ($object_id == 10 || $object_id == 11) {
            $this->result_model->where("from_file", "like", $record->file)->delete();
        }

        $this->import_model->update(array('status' => 1), $record->id);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    public function vitri()
    {
        // die();
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        $file = APPPATH . '../public/upload/vitri_phong/vitri_all.xlsx';
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

        $count_sheet = $objPHPExcel->getSheetCount();

        $this->load->model("workshop_model");
        $this->load->model("area_model");
        $this->load->model("position_model");
        $this->load->model("department_model");
        $this->load->model("target_model");

        $temp_workshop = array(
            $this->workshop_model->where(array('id' => 5))->as_object()->get(),
            $this->workshop_model->where(array('id' => 6))->as_object()->get(),
            $this->workshop_model->where(array('id' => 7))->as_object()->get(),
            $this->workshop_model->where(array('id' => 8))->as_object()->get(),
            $this->workshop_model->where(array('id' => 9))->as_object()->get(),
            $this->workshop_model->where(array('id' => 10))->as_object()->get(),
            $this->workshop_model->where(array('id' => 11))->as_object()->get()
        );
        $temp_target = array(
            'Active' => 5,
            'Passive' => 3,
            'Rodac' => 4,
            'Contact' => 4,
            'Surface' => 4
        );
        $temp_type_bc = array(
            '2 năm' => 'TwoYear',
            '2 năm / lần' => 'TwoYear',
            'hàng năm' => 'Year',
            'năm' => 'Year',
            'nửa năm' => 'HalfYear',
            'quý' => 'Quarter'
        );
        for ($k = 0; $k < $count_sheet; $k++) {
            $sheet_name = "sheet_" . $k  . "_vitri_all.xlsx";
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
            for ($i = 1; $i <= $Totalrow; $i++) {
                //----Lặp cột
                for ($j = 0; $j < $TotalCol; $j++) {
                    // Tiến hành lấy giá trị của từng ô đổ vào mảng
                    $cell = $sheet->getCellByColumnAndRow($j, $i);

                    $data[$i - 1][$j] = $cell->getValue();
                    ///CHUYEN RICH TEXT
                    if ($data[$i - 1][$j] instanceof PHPExcel_RichText) {
                        $data[$i - 1][$j] = $data[$i - 1][$j]->getPlainText();
                    }
                    ////CHUYEN DATE 
                    if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - 1][$j] > 0) {

                        if (is_numeric($data[$i - 1][$j])) {
                            $data[$i - 1][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - 1][$j]));
                        } else if ($data[$i - 1][$j] == '26/09/16') {
                            $data[$i - 1][$j] = '2016-09-26';
                        }
                    }
                }
            }


            echo "<pre>";
            echo $sheet_name . "<br>";
            print_r($data);
            // die();
            $workshop = $temp_workshop[$k];
            $workshop_id = $workshop->id;
            $frequency_name = $type_bc = $area_name = $target_name = "";
            $temp_area = $temp_phong = array();
            $area_all = $this->area_model->where(array('deleted' => 0, 'workshop_id' => $workshop_id))->as_object()->get_all();

            foreach ($area_all as $row) {
                $temp_area[$row->string_id] = $row;
            }
            $phong_all = $this->department_model->where(array('deleted' => 0))->as_object()->get_all();
            foreach ($phong_all as $phong) {
                $temp_phong[$phong->string_id] = $phong;
            }
            for ($i = 0; $i < count($data); $i++) {
                // $area_string = $data[$i][3];
                if (isset($data[$i][11]) && ($data[$i][11] == "Báo cáo"))
                    continue;
                $position_string_id = trim($data[$i][6]);

                if ($position_string_id == "") {
                    continue;
                }
                $position_name = trim($data[$i][7]);
                $position_name_en =  trim($data[$i][8]);
                if (isset($data[$i][19])) {
                    $position_string_id_old = trim($data[$i][19]);
                } else {
                    $position_string_id_old = '';
                }
                if ($data[$i][9] != "") {
                    $frequency_name =  $data[$i][9];
                }

                if ($data[$i][11] != "") {
                    $type_bc =  strtolower(trim($data[$i][11]));
                    $type_bc = $temp_type_bc[$type_bc];
                }
                if ($data[$i][5] != "") {
                    $target_name = trim($data[$i][5]);
                    $target_id = $temp_target[$target_name];
                }

                if ($data[$i][4] != "") {
                    $area_string_id = trim($data[$i][4]);
                    if (!isset($temp_area[$area_string_id])) {
                        $area_name = "Cấp sạch $area_string_id";
                        $area_name_en = "Grade $area_string_id";
                        //TẠO AREA
                        $area_id = $this->area_model->insert(array(
                            'name' => $area_name,
                            'name_en' => $area_name_en,
                            'workshop_id' => $workshop_id,
                            'factory_id' => $workshop->factory_id,
                            'from_file' => $sheet_name,
                            'string_id' => $area_string_id
                        ));
                        $area = $temp_area[$area_string_id] = $this->area_model->get($area_id);
                    } else {
                        $area =  $temp_area[$area_string_id];
                    }
                }
                if ($data[$i][3] != "") {
                    $phong_name = trim($data[$i][1]);
                    $phong_name_en = trim($data[$i][2]);
                    $phong_string_id = trim($data[$i][3]);
                    if (isset($data[$i][16])) {
                        $phong_string_id_old = trim($data[$i][16]);
                    } else {
                        $phong_string_id_old = '';
                    }
                    ////Tạo Phòng

                    if (!isset($temp_phong[$phong_string_id])) {
                        $data_phong = array(
                            'name' => $phong_name,
                            'name_en' => $phong_name_en,
                            'string_id' => $phong_string_id,
                            'area_id' => $area->id,
                            'type' => $phong_string_id[0],
                            'workshop_id' => $area->workshop_id,
                            'factory_id' => $area->factory_id,
                            'from_file' => $sheet_name,
                            'string_id_old' => $phong_string_id_old
                        );
                        $phong_id = $this->department_model->insert($data_phong);
                        $phong = $temp_phong[$phong_string_id] = $this->department_model->get($phong_id);
                    } else {
                        $phong = $temp_phong[$phong_string_id];
                    }
                }
                if ($phong_string_id[0] == 1) {
                    $object_id = 11;
                } else {
                    $object_id = 10;
                }
                ///Tạo vị trí
                $data_position = array(
                    'name' => $position_name,
                    'name_en' => $position_name_en,
                    'string_id' => $position_string_id,
                    'frequency_name' => $frequency_name,
                    'target_id' => $target_id,
                    'department_id' => $phong->id,
                    'area_id' => $phong->area_id,
                    'workshop_id' => $phong->workshop_id,
                    'factory_id' => $phong->factory_id,
                    'from_file' => $sheet_name,
                    'object_id' => $object_id,
                    'type_bc' => $type_bc,
                    'string_id_old' => $position_string_id_old
                );
                $position_id = $this->position_model->insert($data_position);
                // $area = $temp_area[$area_string];

                // $phong_id = $find_phong->id;
            }
        }
    }

    public function vitri_tieuphan()
    {
        // die();
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        $file = APPPATH . '../public/upload/vitri_phong/vitri_tieuphan.xlsx';
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

        $count_sheet = $objPHPExcel->getSheetCount();

        $this->load->model("workshop_model");
        $this->load->model("area_model");
        $this->load->model("position_model");
        $this->load->model("department_model");
        $this->load->model("target_model");

        $temp_workshop = array(
            $this->workshop_model->where(array('id' => 4))->as_object()->get(),
            $this->workshop_model->where(array('id' => 8))->as_object()->get(),
        );
        $temp_type_bc = array(
            '2 năm' => 'TwoYear',
            '2 năm / lần' => 'TwoYear',
            'hàng năm' => 'Year',
            'năm' => 'Year',
            'nửa năm' => 'HalfYear',
            'quý' => 'Quarter'
        );
        for ($k = 0; $k < $count_sheet; $k++) {
            $sheet_name = "sheet_" . $k  . "_vitri_tieuphan.xlsx";
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
            for ($i = 1; $i <= $Totalrow; $i++) {
                //----Lặp cột
                for ($j = 0; $j < $TotalCol; $j++) {
                    // Tiến hành lấy giá trị của từng ô đổ vào mảng
                    $cell = $sheet->getCellByColumnAndRow($j, $i);

                    $data[$i - 1][$j] = $cell->getValue();
                    ///CHUYEN RICH TEXT
                    if ($data[$i - 1][$j] instanceof PHPExcel_RichText) {
                        $data[$i - 1][$j] = $data[$i - 1][$j]->getPlainText();
                    }
                    ////CHUYEN DATE 
                    if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - 1][$j] > 0) {

                        if (is_numeric($data[$i - 1][$j])) {
                            $data[$i - 1][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - 1][$j]));
                        } else if ($data[$i - 1][$j] == '26/09/16') {
                            $data[$i - 1][$j] = '2016-09-26';
                        }
                    }
                }
            }


            echo "<pre>";
            echo $sheet_name . "<br>";
            // if ($k == 1) {
            //     print_r($data);
            //     die();
            // } else {
            //     continue;
            // }
            $workshop = $temp_workshop[$k];
            $workshop_id = $workshop->id;
            $frequency_name = $type_bc = $area_name = $target_name = "";
            $temp_area = $temp_phong = array();
            $area_all = $this->area_model->where(array('deleted' => 0, 'workshop_id' => $workshop_id))->as_object()->get_all();

            foreach ($area_all as $row) {
                $temp_area[$row->string_id] = $row;
            }
            $phong_all = $this->department_model->where(array('deleted' => 0))->as_object()->get_all();
            foreach ($phong_all as $phong) {
                $temp_phong[$phong->string_id] = $phong;
            }
            for ($i = 0; $i < count($data); $i++) {
                // $area_string = $data[$i][3];
                if (isset($data[$i][10]) && ($data[$i][10] == "Báo cáo"))
                    continue;
                $position_string_id = trim($data[$i][5]);

                if ($position_string_id == "") {
                    continue;
                }
                $position_name = trim($data[$i][6]);
                $position_name_en =  trim($data[$i][7]);
                if (isset($data[$i][17])) {
                    $position_string_id_old = trim($data[$i][17]);
                } else {
                    $position_string_id_old = '';
                }
                if ($data[$i][8] != "") {
                    $frequency_name =  $data[$i][8];
                }

                if ($data[$i][10] != "") {
                    $type_bc =  strtolower(trim($data[$i][10]));
                    $type_bc = $temp_type_bc[$type_bc];
                }
                // if ($data[$i][5] != "") {
                //     $target_name = trim($data[$i][5]);
                //     $target_id = $temp_target[$target_name];
                // }

                if ($data[$i][4] != "") {
                    $area_string_id = trim($data[$i][4]);
                    if (!isset($temp_area[$area_string_id])) {
                        $area_name = "Cấp sạch $area_string_id";
                        $area_name_en = "Grade $area_string_id";
                        //TẠO AREA
                        $area_id = $this->area_model->insert(array(
                            'name' => $area_name,
                            'name_en' => $area_name_en,
                            'workshop_id' => $workshop_id,
                            'factory_id' => $workshop->factory_id,
                            'from_file' => $sheet_name,
                            'string_id' => $area_string_id
                        ));
                        $area = $temp_area[$area_string_id] = $this->area_model->get($area_id);
                    } else {
                        $area =  $temp_area[$area_string_id];
                    }
                }
                if ($data[$i][3] != "") {
                    $phong_name = trim($data[$i][1]);
                    $phong_name_en = trim($data[$i][2]);
                    $phong_string_id = trim($data[$i][3]);
                    if (isset($data[$i][15])) {
                        $phong_string_id_old = trim($data[$i][15]);
                    } else {
                        $phong_string_id_old = '';
                    }
                    ////Tạo Phòng

                    if (!isset($temp_phong[$phong_string_id])) {
                        $data_phong = array(
                            'name' => $phong_name,
                            'name_en' => $phong_name_en,
                            'string_id' => $phong_string_id,
                            'area_id' => $area->id,
                            'type' => $phong_string_id[0],
                            'workshop_id' => $area->workshop_id,
                            'factory_id' => $area->factory_id,
                            'from_file' => $sheet_name,
                            'string_id_old' => $phong_string_id_old
                        );
                        $phong_id = $this->department_model->insert($data_phong);
                        $phong = $temp_phong[$phong_string_id] = $this->department_model->get($phong_id);
                    } else {
                        $phong = $temp_phong[$phong_string_id];
                    }
                }
                if ($phong_string_id[0] == 1) {
                    $object_id = 15;
                } else {
                    $object_id = 14;
                }
                ///Tạo vị trí
                $data_position = array(
                    'name' => $position_name,
                    'name_en' => $position_name_en,
                    'string_id' => $position_string_id,
                    'frequency_name' => $frequency_name,
                    // 'target_id' => $target_id,
                    'department_id' => $phong->id,
                    'area_id' => $phong->area_id,
                    'workshop_id' => $phong->workshop_id,
                    'factory_id' => $phong->factory_id,
                    'from_file' => $sheet_name,
                    'object_id' => $object_id,
                    'type_bc' => $type_bc,
                    'string_id_old' => $position_string_id_old
                );
                $position_id = $this->position_model->insert($data_position);
                // $area = $temp_area[$area_string];

                // $phong_id = $find_phong->id;
            }
        }
    }
    public function vitri_khi()
    {
        // die();
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        $file = APPPATH . '../public/upload/vitri_phong/vitri_khi.xlsx';
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

        $count_sheet = $objPHPExcel->getSheetCount();

        $this->load->model("workshop_model");
        $this->load->model("area_model");
        $this->load->model("position_model");
        $this->load->model("department_model");
        $this->load->model("target_model");

        $temp_workshop = array(
            $this->workshop_model->where(array('id' => 4))->as_object()->get(), //BETA TIEM
            $this->workshop_model->where(array('id' => 5))->as_object()->get(), ///BETA VIEN
            $this->workshop_model->where(array('id' => 8))->as_object()->get(), ///NON BETA TIEM
            $this->workshop_model->where(array('id' => 11))->as_object()->get(), ///NON BETA VIEN

        );
        $temp_type_bc = array(
            '2 năm' => 'TwoYear',
            '2 năm / lần' => 'TwoYear',
            'hàng năm' => 'Year',
            'năm' => 'Year',
            'nửa năm' => 'HalfYear',
            'quý' => 'Quarter'
        );
        for ($k = 0; $k < $count_sheet; $k++) {
            $sheet_name = "sheet_" . $k  . "_vitri_khi.xlsx";
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
            for ($i = 1; $i <= $Totalrow; $i++) {
                //----Lặp cột
                for ($j = 0; $j < $TotalCol; $j++) {
                    // Tiến hành lấy giá trị của từng ô đổ vào mảng
                    $cell = $sheet->getCellByColumnAndRow($j, $i);

                    $data[$i - 1][$j] = $cell->getValue();
                    ///CHUYEN RICH TEXT
                    if ($data[$i - 1][$j] instanceof PHPExcel_RichText) {
                        $data[$i - 1][$j] = $data[$i - 1][$j]->getPlainText();
                    }
                    ////CHUYEN DATE 
                    if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - 1][$j] > 0) {

                        if (is_numeric($data[$i - 1][$j])) {
                            $data[$i - 1][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - 1][$j]));
                        } else if ($data[$i - 1][$j] == '26/09/16') {
                            $data[$i - 1][$j] = '2016-09-26';
                        }
                    }
                }
            }


            echo "<pre>";
            echo $sheet_name . "<br>";
            // if ($k == 1) {
            // print_r($data);
            // die();
            // } else {
            //     continue;
            // }
            $workshop = $temp_workshop[$k];
            $workshop_id = $workshop->id;
            $frequency_name = $type_bc = $area_name = $target_name = "";
            $temp_area = $temp_phong = array();
            $area_all = $this->area_model->where(array('deleted' => 0, 'workshop_id' => $workshop_id))->as_object()->get_all();

            foreach ($area_all as $row) {
                $temp_area[$row->string_id] = $row;
            }
            $phong_all = $this->department_model->where(array('deleted' => 0))->as_object()->get_all();
            foreach ($phong_all as $phong) {
                $temp_phong[$phong->string_id] = $phong;
            }
            for ($i = 0; $i < count($data); $i++) {
                // $area_string = $data[$i][3];
                if (isset($data[$i][10]) && ($data[$i][10] == "Báo cáo"))
                    continue;
                $position_string_id = trim($data[$i][6]);

                if ($position_string_id == "") {
                    continue;
                }
                $position_name = trim($data[$i][7]);
                $position_name_en =  trim($data[$i][8]);
                if (isset($data[$i][17])) {
                    $position_string_id_old = trim($data[$i][17]);
                } else {
                    $position_string_id_old = '';
                }
                if ($data[$i][9] != "") {
                    $frequency_name =  $data[$i][9];
                }

                if ($data[$i][10] != "") {
                    $type_bc =  strtolower(trim($data[$i][10]));
                    $type_bc = $temp_type_bc[$type_bc];
                }
                // if ($data[$i][5] != "") {
                //     $target_name = trim($data[$i][5]);
                //     $target_id = $temp_target[$target_name];
                // }

                if ($data[$i][4] != "") {
                    $area_string_id = trim($data[$i][4]);
                    if (!isset($temp_area[$area_string_id])) {
                        $area_name = "Cấp sạch $area_string_id";
                        $area_name_en = "Grade $area_string_id";
                        //TẠO AREA
                        $area_id = $this->area_model->insert(array(
                            'name' => $area_name,
                            'name_en' => $area_name_en,
                            'workshop_id' => $workshop_id,
                            'factory_id' => $workshop->factory_id,
                            'from_file' => $sheet_name,
                            'string_id' => $area_string_id
                        ));
                        $area = $temp_area[$area_string_id] = $this->area_model->get($area_id);
                    } else {
                        $area =  $temp_area[$area_string_id];
                    }
                }
                if ($data[$i][3] != "") {
                    $phong_name = trim($data[$i][1]);
                    $phong_name_en = trim($data[$i][2]);
                    $phong_string_id = trim($data[$i][3]);
                    if (isset($data[$i][15])) {
                        $phong_string_id_old = trim($data[$i][15]);
                    } else {
                        $phong_string_id_old = '';
                    }
                    ////Tạo Phòng

                    if (!isset($temp_phong[$phong_string_id])) {
                        $data_phong = array(
                            'name' => $phong_name,
                            'name_en' => $phong_name_en,
                            'string_id' => $phong_string_id,
                            'area_id' => $area->id,
                            'type' => $phong_string_id[0],
                            'workshop_id' => $area->workshop_id,
                            'factory_id' => $area->factory_id,
                            'from_file' => $sheet_name,
                            'string_id_old' => $phong_string_id_old
                        );
                        $phong_id = $this->department_model->insert($data_phong);
                        $phong = $temp_phong[$phong_string_id] = $this->department_model->get($phong_id);
                    } else {
                        $phong = $temp_phong[$phong_string_id];
                    }
                }
                if ($position_string_id[0] == "C") {
                    $object_id = 16;
                } else {
                    $object_id = 17;
                }
                ///Tạo vị trí
                $data_position = array(
                    'name' => $position_name,
                    'name_en' => $position_name_en,
                    'string_id' => $position_string_id,
                    'frequency_name' => $frequency_name,
                    // 'target_id' => $target_id,
                    'department_id' => $phong->id,
                    'area_id' => $phong->area_id,
                    'workshop_id' => $phong->workshop_id,
                    'factory_id' => $phong->factory_id,
                    'from_file' => $sheet_name,
                    'object_id' => $object_id,
                    'type_bc' => $type_bc,
                    'string_id_old' => $position_string_id_old
                );
                $position_id = $this->position_model->insert($data_position);
                // $area = $temp_area[$area_string];

                // $phong_id = $find_phong->id;
            }
        }
    }
    public function vitri_nuoc()
    {
        // die();
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        $file = APPPATH . '../public/upload/vitri_phong/vitri_nuoc.xlsx';
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

        $count_sheet = $objPHPExcel->getSheetCount();

        $this->load->model("workshop_model");
        $this->load->model("area_model");
        $this->load->model("position_model");
        $this->load->model("department_model");
        $this->load->model("target_model");

        $temp_workshop = array(
            $this->workshop_model->where(array('id' => 5))->as_object()->get(), ///BETA VIEN
            $this->workshop_model->where(array('id' => 4))->as_object()->get(), //BETA TIEM
            $this->workshop_model->where(array('id' => 4))->as_object()->get(), //BETA TIEM
            $this->workshop_model->where(array('id' => 4))->as_object()->get(), //BETA TIEM
            $this->workshop_model->where(array('id' => 8))->as_object()->get(), ///NON BETA TIEM
            $this->workshop_model->where(array('id' => 8))->as_object()->get(), ///NON BETA TIEM
            $this->workshop_model->where(array('id' => 8))->as_object()->get(), ///NON BETA TIEM
            $this->workshop_model->where(array('id' => 11))->as_object()->get(), ///NON BETA VIEN
            $this->workshop_model->where(array('id' => 10))->as_object()->get(), ///NON BETA QC

        );
        $temp_type_bc = array(
            '2 năm' => 'TwoYear',
            '2 năm / lần' => 'TwoYear',
            'hàng năm' => 'Year',
            'năm' => 'Year',
            'nửa năm' => 'HalfYear',
            'quý' => 'Quarter',
            'tháng' => 'Month'
        );
        for ($k = 0; $k < $count_sheet; $k++) {
            $sheet_name = "sheet_" . $k  . "_vitri_nuoc.xlsx";
            $sheet = $objPHPExcel->setActiveSheetIndex($k);

            //Lấy ra số dòng cuối cùng
            $Totalrow = $sheet->getHighestRow();
            //Lấy ra tên cột cuối cùng
            $LastColumn = $sheet->getHighestColumn();
            //Chuyển đổi tên cột đó về vị trí thứ, VD: C là 3,D là 4
            $TotalCol = PHPExcel_Cell::columnIndexFromString($LastColumn);

            //Tạo mảng chứa dữ liệu
            $data = [];
            $cell = $sheet->getCellByColumnAndRow(10, 1);

            $object_id = $cell->getValue();
            // print_r($object_id);
            // die();
            //Tiến hành lặp qua từng ô dữ liệu
            //----Lặp dòng, Vì dòng đầu là tiêu đề cột nên chúng ta sẽ lặp giá trị từ dòng 2
            for ($i = 1; $i <= $Totalrow; $i++) {
                //----Lặp cột
                for ($j = 0; $j < $TotalCol; $j++) {
                    // Tiến hành lấy giá trị của từng ô đổ vào mảng
                    $cell = $sheet->getCellByColumnAndRow($j, $i);

                    $data[$i - 1][$j] = $cell->getValue();
                    ///CHUYEN RICH TEXT
                    if ($data[$i - 1][$j] instanceof PHPExcel_RichText) {
                        $data[$i - 1][$j] = $data[$i - 1][$j]->getPlainText();
                    }
                    ////CHUYEN DATE 
                    if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - 1][$j] > 0) {

                        if (is_numeric($data[$i - 1][$j])) {
                            $data[$i - 1][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - 1][$j]));
                        } else if ($data[$i - 1][$j] == '26/09/16') {
                            $data[$i - 1][$j] = '2016-09-26';
                        }
                    }
                }
            }


            echo "<pre>";
            echo $sheet_name . "<br>";
            // if ($k == 1) {
            // print_r($data);
            // die();
            // } else {
            //     continue;
            // }
            $workshop = $temp_workshop[$k];
            $workshop_id = $workshop->id;
            $frequency_name = $type_bc = $area_name = $target_name =  $system_id = "";
            $temp_area = $temp_phong = array();
            // $area_all = $this->area_model->where(array('deleted' => 0, 'workshop_id' => $workshop_id))->as_object()->get_all();

            // foreach ($area_all as $row) {
            //     $temp_area[$row->string_id] = $row;
            // }
            $phong_all = $this->department_model->where(array('deleted' => 0))->as_object()->get_all();
            foreach ($phong_all as $phong) {
                $temp_phong[$phong->string_id] = $phong;
            }
            for ($i = 0; $i < count($data); $i++) {
                // $area_string = $data[$i][3];
                if (isset($data[$i][9]) && ($data[$i][9] == "Báo cáo"))
                    continue;
                $position_string_id = trim($data[$i][1]);

                if ($position_string_id == "") {
                    continue;
                }
                $position_name = trim($data[$i][3]);
                $position_name_en =  trim($data[$i][4]);
                if (isset($data[$i][13])) {
                    $position_string_id_old = trim($data[$i][13]);
                } else {
                    $position_string_id_old = '';
                }
                if ($data[$i][7] != "") {
                    $frequency_name =  $data[$i][7];
                }

                if ($data[$i][9] != "") {
                    $type_bc =  strtolower(trim($data[$i][9]));
                    $type_bc = $temp_type_bc[$type_bc];
                }
                // if ($data[$i][5] != "") {
                //     $target_name = trim($data[$i][5]);
                //     $target_id = $temp_target[$target_name];
                // }

                if ($data[$i][12] != "") {
                    $system_id = $data[$i][12];
                }
                if ($data[$i][2] != "") {
                    $phong_name = "";
                    $phong_name_en = "";
                    $phong_string_id = trim($data[$i][2]);
                    // if (isset($data[$i][15])) {
                    //     $phong_string_id_old = trim($data[$i][15]);
                    // } else {
                    //     $phong_string_id_old = '';
                    // }
                    ////Tạo Phòng

                    if (!isset($temp_phong[$phong_string_id])) {
                        $data_phong = array(
                            'name' => $phong_name,
                            'name_en' => $phong_name_en,
                            'string_id' => $phong_string_id,
                            // 'area_id' => $area->id,
                            // 'type' => $phong_string_id[0],
                            'workshop_id' => $workshop_id,
                            'factory_id' => 1,
                            'from_file' => $sheet_name,
                            // 'string_id_old' => $phong_string_id_old
                        );
                        $phong_id = $this->department_model->insert($data_phong);
                        $phong = $temp_phong[$phong_string_id] = $this->department_model->get($phong_id);
                    } else {
                        $phong = $temp_phong[$phong_string_id];
                    }
                }
                // if ($position_string_id[0] == "C") {
                //     $object_id = 16;
                // } else {
                //     $object_id = 17;
                // }
                ///Tạo vị trí
                $data_position = array(
                    'name' => $position_name,
                    'name_en' => $position_name_en,
                    'string_id' => $position_string_id,
                    'frequency_name' => $frequency_name,
                    'system_id' => $system_id,
                    'department_id' => $phong->id,
                    'workshop_id' => $workshop_id,
                    'factory_id' => 1,
                    'from_file' => $sheet_name,
                    'object_id' => $object_id,
                    'type_bc' => $type_bc,
                    'string_id_old' => $position_string_id_old
                );
                $position_id = $this->position_model->insert($data_position);
                // $area = $temp_area[$area_string];

                // $phong_id = $find_phong->id;
            }
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
        echo "<pre>";
        print_r($data);
        die();
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
                    'factory_id' => $area['factory_id'],
                    'object_id' => 3,
                    'type_bc' => "Year"
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
                    'factory_id' => $area['factory_id'],
                    'object_id' => 3,
                    'type_bc' => "Year"
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
                    'factory_id' => $area['factory_id'],
                    'object_id' => 3,
                    'type_bc' => "Year"
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
                    'factory_id' => $area['factory_id'],
                    'object_id' => 3,
                    'type_bc' => "Year"
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
                    'factory_id' => $area['factory_id'],
                    'object_id' => 3,
                    'type_bc' => "Year"
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
                    'factory_id' => $area['factory_id'],
                    'object_id' => 3,
                    'type_bc' => "Year"
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
                    'factory_id' => $area['factory_id'],
                    'object_id' => 3,
                    'type_bc' => "Year"

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
        $dir = APPPATH . '../public/upload/data';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $insert = array();


        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);
        // print_r($sortedarray1);
        // die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
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
                print_r($positions);
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
                        $object_id = $position->object_id;
                        $type_bc = $position->type_bc;
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
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_in_day($position_id, $date);
                        // $data['stt_in_day'] = $max_stt;
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
                            'from_file' => $sheet_name,
                            'object_id' => $object_id,
                            'type_bc' => $type_bc,
                            'stt_in_day' => $max_stt
                            //                        'w,orkshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $this->result_model->insert($data_up);
                    }
                    // $phong_id = $find_phong->id;
                }
            }
        }
    }

    public function result2()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/data_2';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $insert = array();


        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);
        // print_r($sortedarray1);
        // die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
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
                $row_stt = 10;
                for ($i = $row_stt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - $row_stt][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - $row_stt][$j] instanceof PHPExcel_RichText) {
                            $data[$i - $row_stt][$j] = $data[$i - $row_stt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $row_stt][$j] > 0) {

                            if (is_numeric($data[$i - $row_stt][$j])) {
                                $data[$i - $row_stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $row_stt][$j]));
                            } else if ($data[$i - $row_stt][$j] == '26/09/16') {
                                $data[$i - $row_stt][$j] = '2016-09-26';
                            }
                        }
                    }
                }
                // echo "<pre>";
                // print_r($data);
                // die();
                ///LIST POSTION
                $list_position = array_shift($data);
                ///XOA 1 ROW
                // array_shift($data);
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
                print_r($positions);
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
                        $object_id = $position->object_id;
                        $type_bc = $position->type_bc;
                        $date = $row[7];
                        // if ($date == '2020-03-21') {
                        //     print_r($i);
                        //     print($data);
                        //     die();
                        // }
                        $value = $row[$position->col];
                        if (is_null($value) || !is_Date($date) || !is_numeric($value)) {
                            continue;
                        }
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_in_day($position_id, $date);
                        // $data['stt_in_day'] = $max_stt;
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
                            'from_file' => $sheet_name,
                            'object_id' => $object_id,
                            'type_bc' => $type_bc,
                            'stt_in_day' => $max_stt
                            //                        'w,orkshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $this->result_model->insert($data_up);
                    }
                    // $phong_id = $find_phong->id;
                }
            }
        }
    }

    public function result3()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/data_3';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $insert = array();


        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);
        // print_r($sortedarray1);
        // die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
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
                $row_stt = 1;
                for ($i = $row_stt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - $row_stt][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - $row_stt][$j] instanceof PHPExcel_RichText) {
                            $data[$i - $row_stt][$j] = $data[$i - $row_stt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $row_stt][$j] > 0) {

                            if (is_numeric($data[$i - $row_stt][$j])) {
                                $data[$i - $row_stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $row_stt][$j]));
                            } else if ($data[$i - $row_stt][$j] == '26/09/16') {
                                $data[$i - $row_stt][$j] = '2016-09-26';
                            }
                        }
                    }
                }
                // echo "<pre>";
                // print_r($data);
                // die();
                ///LIST POSTION
                $list_position = array_shift($data);
                ///XOA 1 ROW
                // array_shift($data);
                $this->load->model("position_model");
                echo "<pre>";
                $positions = array();
                $list_old = array();
                for ($i = 0; $i < count($list_position); $i++) {
                    $position = $list_position[$i];
                    if ($position == "") {
                        continue;
                    }
                    $find_vitri_old = $this->position_model->where(array('string_id_old' => $position))->as_object()->get_all();
                    if (count($find_vitri_old) > 0) {
                        $find_vitri_old = array_map(function ($item) use ($i) {
                            $item->col = $i;
                            return $item;
                        }, $find_vitri_old);
                        $list_old = array_merge($list_old, $find_vitri_old);
                    }
                    $find_vitri = $this->position_model->where(array('string_id' => $position))->as_object()->get();
                    //            print_r($find_phong);
                    if (empty($find_vitri)) {
                        continue;
                    }
                    ///Tìm ngược lại list old
                    $find_vi_tri_old = array_values(array_filter($list_old, function ($item) use ($find_vitri) {
                        return $item->string_id = $find_vitri->string_id;
                    }));
                    if (isset($find_vi_tri_old[0]))
                        $find_vitri->col_old = $find_vi_tri_old[0]->col;
                    $find_vitri->col = $i;
                    array_push($positions, $find_vitri);
                }
                // print_r($positions);
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
                        $object_id = $position->object_id;
                        $type_bc = $position->type_bc;
                        $date = $row[0];
                        // if ($date == '2020-03-21') {
                        //     print_r($i);
                        //     print($data);
                        //     die();
                        // }
                        // print_r($row);
                        //     die();
                        // if (isset($position->col_old)) {

                        //     echo  $row[$position->col_old] . "<br>";
                        // }
                        if (strlen($row[$position->col]) > 0) {
                            $value = $row[$position->col];
                        } elseif (isset($position->col_old)) {
                            $value = $row[$position->col_old];
                        } else {
                            continue;
                        }
                        if (is_null($value) || !is_Date($date) || !is_numeric($value)) {
                            continue;
                        }
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_in_day($position_id, $date);
                        // $data['stt_in_day'] = $max_stt;
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
                            'from_file' => $sheet_name,
                            'object_id' => $object_id,
                            'type_bc' => $type_bc,
                            'stt_in_day' => $max_stt
                            //                        'w,orkshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $this->result_model->insert($data_up);
                    }
                    // $phong_id = $find_phong->id;
                }
            }
        }
    }
    public function result_tieuphan_dong()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/data_tieuphan_dong';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $this->load->model("target_model");
        $insert = array();


        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);
        // print_r($sortedarray1);
        // die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
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
                $row_stt = 1;
                for ($i = $row_stt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - $row_stt][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - $row_stt][$j] instanceof PHPExcel_RichText) {
                            $data[$i - $row_stt][$j] = $data[$i - $row_stt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $row_stt][$j] > 0) {

                            if (is_numeric($data[$i - $row_stt][$j])) {
                                $data[$i - $row_stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $row_stt][$j]));
                            } else if ($data[$i - $row_stt][$j] == '26/09/16') {
                                $data[$i - $row_stt][$j] = '2016-09-26';
                            }
                        }
                    }
                }
                // $temp_target = array(
                //     $this->target_model->where(array('id' => 14))->as_object()->get(), ///5
                //     $this->target_model->where(array('id' => 16))->as_object()->get(), ///0.5
                // );
                $list_target = array_shift($data);
                echo "<pre>";
                print_r($list_target);
                $targets = array();
                for ($i = 0; $i < count($list_target); $i++) {
                    $target_name = $list_target[$i];
                    if ($target_name == "") {
                        continue;
                    }
                    if (strpos($target_name, "0.5") !== FALSE || strpos($target_name, "0/5") !== FALSE) {
                        $targets[$i] = 16;
                    } elseif (strpos($target_name, "5") !== FALSE) {
                        $targets[$i] = 14;
                    } else {
                        continue;
                    }
                }

                echo "<pre>";
                print_r($targets);
                // die();
                ///LIST POSTION
                $list_position = array_shift($data);

                ///XOA 1 ROW
                // array_shift($data);
                $this->load->model("position_model");
                echo "<pre>";
                $positions = array();
                $list_old = array();
                for ($i = 0; $i < count($list_position); $i++) {
                    $position = $list_position[$i];
                    if ($position == "" || !isset($targets[$i])) {
                        continue;
                    }
                    // $find_vitri_old = $this->position_model->where(array('string_id_old' => $position))->as_object()->get_all();
                    // if (count($find_vitri_old) > 0) {
                    //     $find_vitri_old = array_map(function ($item) use ($i) {
                    //         $item->col = $i;
                    //         return $item;
                    //     }, $find_vitri_old);
                    //     $list_old = array_merge($list_old, $find_vitri_old);
                    // }
                    $find_vitri = $this->position_model->where(array('string_id' => $position))->as_object()->get();
                    //            print_r($find_phong);
                    if (empty($find_vitri)) {
                        continue;
                    }
                    ///Tìm ngược lại list old
                    // $find_vi_tri_old = array_values(array_filter($list_old, function ($item) use ($find_vitri) {
                    //     return $item->string_id = $find_vitri->string_id;
                    // }));
                    // if (isset($find_vi_tri_old[0]))
                    //     $find_vitri->col_old = $find_vi_tri_old[0]->col;
                    $find_vitri->col = $i;
                    $find_vitri->target_id = $targets[$i];

                    array_push($positions, $find_vitri);
                }
                print_r($positions);
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
                        $object_id = $position->object_id;
                        $type_bc = $position->type_bc;
                        $date = $row[0];
                        // if ($date == '2020-03-21') {
                        //     print_r($i);
                        //     print($data);
                        //     die();
                        // }
                        // print_r($row);
                        //     die();
                        // if (isset($position->col_old)) {

                        //     echo  $row[$position->col_old] . "<br>";
                        // }
                        if (strlen($row[$position->col]) > 0) {
                            $value = $row[$position->col];
                        } elseif (isset($position->col_old)) {
                            $value = $row[$position->col_old];
                        } else {
                            continue;
                        }
                        if (is_null($value) || !is_Date($date) || !is_numeric($value)) {
                            continue;
                        }
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_have_target_in_day($position_id, $date, $target_id);
                        // $data['stt_in_day'] = $max_stt;
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
                            'from_file' => $sheet_name,
                            'object_id' => $object_id,
                            'type_bc' => $type_bc,
                            'stt_in_day' => $max_stt
                            //                        'w,orkshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $this->result_model->insert($data_up);
                    }
                    // $phong_id = $find_phong->id;
                }
            }
        }
    }
    public function result_tieuphan_tinh()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/data_tieuphan_tinh';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $this->load->model("target_model");
        $insert = array();


        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);
        // print_r($sortedarray1);
        // die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
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
                $row_stt = 12;
                for ($i = $row_stt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - $row_stt][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - $row_stt][$j] instanceof PHPExcel_RichText) {
                            $data[$i - $row_stt][$j] = $data[$i - $row_stt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $row_stt][$j] > 0) {

                            if (is_numeric($data[$i - $row_stt][$j])) {
                                $data[$i - $row_stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $row_stt][$j]));
                            } else if ($data[$i - $row_stt][$j] == '26/09/16') {
                                $data[$i - $row_stt][$j] = '2016-09-26';
                            }
                        }
                    }
                }

                echo "<pre>";
                // print_r($data);
                // die();
                // $temp_target = array(
                //     $this->target_model->where(array('id' => 14))->as_object()->get(), ///5
                //     $this->target_model->where(array('id' => 16))->as_object()->get(), ///0.5
                // );
                $list_target = array_shift($data);
                echo "<pre>";
                echo $sheet_name . "<br>";
                print_r($list_target);
                $targets = array();
                for ($i = 0; $i < count($list_target); $i++) {
                    $target_name = $list_target[$i];
                    if ($target_name == "") {
                        continue;
                    }
                    if (strpos($target_name, "0.5") !== FALSE || strpos($target_name, "0/5") !== FALSE) {
                        $targets[$i] = 17;
                    } elseif (strpos($target_name, "5") !== FALSE) {
                        $targets[$i] = 15;
                    } else {
                        continue;
                    }
                }

                echo "<pre>";
                print_r($targets);
                // die();
                ///LIST POSTION
                $list_position = array_shift($data);

                ///XOA 1 ROW
                array_shift($data);
                $this->load->model("position_model");
                echo "<pre>";
                $positions = array();
                $list_old = array();
                for ($i = 0; $i < count($list_position); $i++) {
                    $position = $list_position[$i];
                    if ($position == "" || !isset($targets[$i])) {
                        continue;
                    }
                    // $find_vitri_old = $this->position_model->where(array('string_id_old' => $position))->as_object()->get_all();
                    // if (count($find_vitri_old) > 0) {
                    //     $find_vitri_old = array_map(function ($item) use ($i) {
                    //         $item->col = $i;
                    //         return $item;
                    //     }, $find_vitri_old);
                    //     $list_old = array_merge($list_old, $find_vitri_old);
                    // }
                    $find_vitri = $this->position_model->where(array('string_id' => $position))->as_object()->get();
                    //            print_r($find_phong);
                    if (empty($find_vitri)) {
                        continue;
                    }
                    ///Tìm ngược lại list old
                    // $find_vi_tri_old = array_values(array_filter($list_old, function ($item) use ($find_vitri) {
                    //     return $item->string_id = $find_vitri->string_id;
                    // }));
                    // if (isset($find_vi_tri_old[0]))
                    //     $find_vitri->col_old = $find_vi_tri_old[0]->col;
                    $find_vitri->col = $i;
                    $find_vitri->target_id = $targets[$i];

                    array_push($positions, $find_vitri);
                }
                print_r($positions);
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
                        $object_id = $position->object_id;
                        $type_bc = $position->type_bc;
                        $date = $row[1];
                        // if ($date == '2020-03-21') {
                        //     print_r($i);
                        //     print($data);
                        //     die();
                        // }
                        // print_r($row);
                        //     die();
                        // if (isset($position->col_old)) {

                        //     echo  $row[$position->col_old] . "<br>";
                        // }
                        if (strlen($row[$position->col]) > 0) {
                            $value = $row[$position->col];
                        } elseif (isset($position->col_old)) {
                            $value = $row[$position->col_old];
                        } else {
                            continue;
                        }
                        if (is_null($value) || !is_Date($date) || !is_numeric($value)) {
                            continue;
                        }
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_have_target_in_day($position_id, $date, $target_id);
                        // $data['stt_in_day'] = $max_stt;
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
                            'from_file' => $sheet_name,
                            'object_id' => $object_id,
                            'type_bc' => $type_bc,
                            'stt_in_day' => $max_stt
                            //                        'w,orkshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $this->result_model->insert($data_up);
                    }
                    // $phong_id = $find_phong->id;
                }
            }
        }
    }

    public function result_tieuphan_thietbi_tinh()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/data_tieuphan_thietbi_tinh';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $this->load->model("target_model");
        $insert = array();


        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);
        // print_r($sortedarray1);
        // die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
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
                $row_stt = 13;
                for ($i = $row_stt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - $row_stt][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - $row_stt][$j] instanceof PHPExcel_RichText) {
                            $data[$i - $row_stt][$j] = $data[$i - $row_stt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $row_stt][$j] > 0) {

                            if (is_numeric($data[$i - $row_stt][$j])) {
                                $data[$i - $row_stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $row_stt][$j]));
                            } else if ($data[$i - $row_stt][$j] == '26/09/16') {
                                $data[$i - $row_stt][$j] = '2016-09-26';
                            }
                        }
                    }
                }

                echo "<pre>";
                // print_r($data);
                // die();
                // $temp_target = array(
                //     $this->target_model->where(array('id' => 14))->as_object()->get(), ///5
                //     $this->target_model->where(array('id' => 16))->as_object()->get(), ///0.5
                // );
                $list_target = array_shift($data);
                echo "<pre>";
                echo $sheet_name . "<br>";
                print_r($list_target);
                $targets = array();
                for ($i = 0; $i < count($list_target); $i++) {
                    $target_name = $list_target[$i];
                    if ($target_name == "") {
                        continue;
                    }
                    if (strpos($target_name, "0.5") !== FALSE || strpos($target_name, "0/5") !== FALSE) {
                        $targets[$i] = 17;
                    } elseif (strpos($target_name, "5") !== FALSE) {
                        $targets[$i] = 15;
                    } else {
                        continue;
                    }
                }

                echo "<pre>";
                print_r($targets);
                // die();
                ///LIST POSTION
                $list_position = array_shift($data);

                ///XOA 1 ROW
                // array_shift($data);
                $this->load->model("position_model");
                echo "<pre>";
                $positions = array();
                $list_old = array();
                for ($i = 0; $i < count($list_position); $i++) {
                    $position = $list_position[$i];
                    if ($position == "" || !isset($targets[$i])) {
                        continue;
                    }
                    // $find_vitri_old = $this->position_model->where(array('string_id_old' => $position))->as_object()->get_all();
                    // if (count($find_vitri_old) > 0) {
                    //     $find_vitri_old = array_map(function ($item) use ($i) {
                    //         $item->col = $i;
                    //         return $item;
                    //     }, $find_vitri_old);
                    //     $list_old = array_merge($list_old, $find_vitri_old);
                    // }
                    $find_vitri = $this->position_model->where(array('string_id' => $position))->as_object()->get();
                    //            print_r($find_phong);
                    if (empty($find_vitri)) {
                        continue;
                    }
                    ///Tìm ngược lại list old
                    // $find_vi_tri_old = array_values(array_filter($list_old, function ($item) use ($find_vitri) {
                    //     return $item->string_id = $find_vitri->string_id;
                    // }));
                    // if (isset($find_vi_tri_old[0]))
                    //     $find_vitri->col_old = $find_vi_tri_old[0]->col;
                    $find_vitri->col = $i;
                    $find_vitri->target_id = $targets[$i];

                    array_push($positions, $find_vitri);
                }
                print_r($positions);
                // print_r($data);
                // die();
                // print_r($temp_phong);
                // die();
                $stt_date = 13;
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    foreach ($positions as $position) {
                        $position_id = $position->id;
                        $area_id = $position->area_id;
                        $department_id = $position->department_id;
                        $target_id = $position->target_id;
                        $factory_id = $position->factory_id;
                        $workshop_id = $position->workshop_id;
                        $object_id = $position->object_id;
                        $type_bc = $position->type_bc;
                        $date = $row[$stt_date];
                        // if ($date == '2020-03-21') {
                        //     print_r($i);
                        //     print($data);
                        //     die();
                        // }
                        // print_r($row);
                        //     die();
                        // if (isset($position->col_old)) {

                        //     echo  $row[$position->col_old] . "<br>";
                        // }
                        if (strlen($row[$position->col]) > 0) {
                            $value = $row[$position->col];
                        } elseif (isset($position->col_old)) {
                            $value = $row[$position->col_old];
                        } else {
                            continue;
                        }
                        if (is_null($value) || !is_Date($date) || !is_numeric($value)) {
                            continue;
                        }
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_have_target_in_day($position_id, $date, $target_id);
                        // $data['stt_in_day'] = $max_stt;
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
                            'from_file' => $sheet_name,
                            'object_id' => $object_id,
                            'type_bc' => $type_bc,
                            'stt_in_day' => $max_stt
                            //                        'w,orkshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $this->result_model->insert($data_up);
                    }
                    // $phong_id = $find_phong->id;
                }
            }
        }
    }
    public function result_tieuphan_thietbi_dong()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/data_tieuphan_thietbi_dong';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $this->load->model("target_model");
        $insert = array();


        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);
        // print_r($sortedarray1);
        // die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
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
                $row_stt = 1;
                for ($i = $row_stt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - $row_stt][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - $row_stt][$j] instanceof PHPExcel_RichText) {
                            $data[$i - $row_stt][$j] = $data[$i - $row_stt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $row_stt][$j] > 0) {

                            if (is_numeric($data[$i - $row_stt][$j])) {
                                $data[$i - $row_stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $row_stt][$j]));
                            } else if ($data[$i - $row_stt][$j] == '26/09/16') {
                                $data[$i - $row_stt][$j] = '2016-09-26';
                            }
                        }
                    }
                }

                echo "<pre>";
                // print_r($data);
                // die();
                // $temp_target = array(
                //     $this->target_model->where(array('id' => 14))->as_object()->get(), ///5
                //     $this->target_model->where(array('id' => 16))->as_object()->get(), ///0.5
                // );
                $list_target = array_shift($data);
                echo "<pre>";
                echo $sheet_name . "<br>";
                print_r($list_target);
                $targets = array();
                for ($i = 0; $i < count($list_target); $i++) {
                    $target_name = $list_target[$i];
                    if ($target_name == "") {
                        continue;
                    }
                    if (strpos($target_name, "0.5") !== FALSE || strpos($target_name, "0/5") !== FALSE) {
                        $targets[$i] = 17;
                    } elseif (strpos($target_name, "5") !== FALSE) {
                        $targets[$i] = 15;
                    } else {
                        continue;
                    }
                }

                echo "<pre>";
                print_r($targets);
                // die();
                ///LIST POSTION
                $list_position = array_shift($data);

                ///XOA 1 ROW
                // array_shift($data);
                $this->load->model("position_model");
                echo "<pre>";
                $positions = array();
                $list_old = array();
                for ($i = 0; $i < count($list_position); $i++) {
                    $position = $list_position[$i];
                    if ($position == "" || !isset($targets[$i])) {
                        continue;
                    }
                    // $find_vitri_old = $this->position_model->where(array('string_id_old' => $position))->as_object()->get_all();
                    // if (count($find_vitri_old) > 0) {
                    //     $find_vitri_old = array_map(function ($item) use ($i) {
                    //         $item->col = $i;
                    //         return $item;
                    //     }, $find_vitri_old);
                    //     $list_old = array_merge($list_old, $find_vitri_old);
                    // }
                    $find_vitri = $this->position_model->where(array('string_id' => $position))->as_object()->get();
                    //            print_r($find_phong);
                    if (empty($find_vitri)) {
                        continue;
                    }
                    ///Tìm ngược lại list old
                    // $find_vi_tri_old = array_values(array_filter($list_old, function ($item) use ($find_vitri) {
                    //     return $item->string_id = $find_vitri->string_id;
                    // }));
                    // if (isset($find_vi_tri_old[0]))
                    //     $find_vitri->col_old = $find_vi_tri_old[0]->col;
                    $find_vitri->col = $i;
                    $find_vitri->target_id = $targets[$i];

                    array_push($positions, $find_vitri);
                }
                print_r($positions);
                // print_r($data);
                // die();
                // print_r($temp_phong);
                // die();
                $stt_date = 8;
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    foreach ($positions as $position) {
                        $position_id = $position->id;
                        $area_id = $position->area_id;
                        $department_id = $position->department_id;
                        $target_id = $position->target_id;
                        $factory_id = $position->factory_id;
                        $workshop_id = $position->workshop_id;
                        $object_id = $position->object_id;
                        $type_bc = $position->type_bc;
                        $date = $row[$stt_date];
                        // if ($date == '2020-03-21') {
                        //     print_r($i);
                        //     print($data);
                        //     die();
                        // }
                        // print_r($row);
                        //     die();
                        // if (isset($position->col_old)) {

                        //     echo  $row[$position->col_old] . "<br>";
                        // }
                        if (strlen($row[$position->col]) > 0) {
                            $value = $row[$position->col];
                        } elseif (isset($position->col_old)) {
                            $value = $row[$position->col_old];
                        } else {
                            continue;
                        }
                        if (is_null($value) || !is_Date($date) || !is_numeric($value)) {
                            continue;
                        }
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_have_target_in_day($position_id, $date, $target_id);
                        // $data['stt_in_day'] = $max_stt;
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
                            'from_file' => $sheet_name,
                            'object_id' => $object_id,
                            'type_bc' => $type_bc,
                            'stt_in_day' => $max_stt
                            //                        'w,orkshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $this->result_model->insert($data_up);
                    }
                    // $phong_id = $find_phong->id;
                }
            }
        }
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
        $this->load->model("employeeresult_model");
        $this->load->model("employee_model");
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
                // echo "<pre>";
                // print_r($data);
                // die();
                $nhanvien_string_id = $sheet->getCellByColumnAndRow(5, 6)->getValue();
                $area_string = $sheet->getCellByColumnAndRow(2, 7)->getValue();
                // echo $nhanvien_string_id . "<br>" . $area_string;

                if (!isset($temp_area[$area_string])) {
                    continue;
                }
                $area = $temp_area[$area_string];
                $nhan_vien = $this->employee_model->where(array('string_id' => $nhanvien_string_id))->as_object()->get();

                // echo "<pre>";
                // print_r($nhan_vien);
                if (empty($nhan_vien)) {
                    continue;
                }

                // $position_H = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_H"))->as_object()->get();
                // $position_N = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_N"))->as_object()->get();
                // $position_C = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_C"))->as_object()->get();
                // $position_LF = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_LF"))->as_object()->get();
                // $position_RF = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_RF"))->as_object()->get();
                // $position_LG = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_LG"))->as_object()->get();
                // $position_RG = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_RG"))->as_object()->get();

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
                    $data_up = array(
                        'employee_id' => $nhan_vien->id,
                        'area_id' => $area['area_id'],
                        'factory_id' => $area['factory_id'],
                        'workshop_id' => $area['workshop_id'],
                        'from_file' => $sheet_name,
                        'created_at' => date("Y-m-d H:i:s"),
                        'date' => $date,
                        'value_H' => $head,
                        'value_N' => $nose,
                        'value_C' => $chest,
                        'value_LF' => $lf,
                        'value_RF' => $rf,
                        'value_LG' => $lg,
                        'value_RG' => $rg
                    );
                    $insert[] = $data_up;
                }
            }
        }
        print_r($insert);
        $this->employeeresult_model->insert($insert);
    }
    public function result_khi()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/data_khi';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $this->load->model("target_model");
        $insert = array();


        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);
        // print_r($sortedarray1);
        // die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
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
                $title = $sheet->getTitle();
                // echo "<br>";
                // print_r($title . "<br>");
                // die();
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
                $row_stt = 1;
                for ($i = $row_stt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - $row_stt][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - $row_stt][$j] instanceof PHPExcel_RichText) {
                            $data[$i - $row_stt][$j] = $data[$i - $row_stt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $row_stt][$j] > 0) {

                            if (is_numeric($data[$i - $row_stt][$j])) {
                                $data[$i - $row_stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $row_stt][$j]));
                            } else if ($data[$i - $row_stt][$j] == '26/09/16') {
                                $data[$i - $row_stt][$j] = '2016-09-26';
                            }
                        }
                    }
                }

                // echo "<pre>";
                // print_r($data);
                // die();
                // $temp_target = array(
                //     $this->target_model->where(array('id' => 14))->as_object()->get(), ///5
                //     $this->target_model->where(array('id' => 16))->as_object()->get(), ///0.5
                // );
                // die();
                $explode = explode("-", $title);
                $target_id = $explode[0];
                $target = $this->target_model->get($target_id);
                echo "<br>$target_id<br>";
                ///LIST POSTION
                $list_position = array_shift($data);

                ///XOA 1 ROW
                // array_shift($data);
                $this->load->model("position_model");
                echo "<pre>";
                $positions = array();
                $list_old = array();
                for ($i = 0; $i < count($list_position); $i++) {
                    $position = $list_position[$i];
                    if ($position == "") {
                        continue;
                    }
                    // $find_vitri_old = $this->position_model->where(array('string_id_old' => $position))->as_object()->get_all();
                    // if (count($find_vitri_old) > 0) {
                    //     $find_vitri_old = array_map(function ($item) use ($i) {
                    //         $item->col = $i;
                    //         return $item;
                    //     }, $find_vitri_old);
                    //     $list_old = array_merge($list_old, $find_vitri_old);
                    // }
                    $find_vitri = $this->position_model->where(array('string_id' => $position))->as_object()->get();
                    //            print_r($find_phong);
                    if (empty($find_vitri)) {
                        continue;
                    }
                    ///Tìm ngược lại list old
                    // $find_vi_tri_old = array_values(array_filter($list_old, function ($item) use ($find_vitri) {
                    //     return $item->string_id = $find_vitri->string_id;
                    // }));
                    // if (isset($find_vi_tri_old[0]))
                    //     $find_vitri->col_old = $find_vi_tri_old[0]->col;
                    $find_vitri->col = $i;
                    $find_vitri->target_id = $target_id;

                    array_push($positions, $find_vitri);
                }
                // print_r($positions);
                // print_r($data);
                // die();
                // print_r($temp_phong);
                // die();
                $stt_date = 1;
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    foreach ($positions as $position) {
                        $position_id = $position->id;
                        $area_id = $position->area_id;
                        $department_id = $position->department_id;
                        $target_id = $position->target_id;
                        $factory_id = $position->factory_id;
                        $workshop_id = $position->workshop_id;
                        $object_id = $position->object_id;
                        $type_bc = $position->type_bc;
                        $date = $row[$stt_date];
                        // if ($date == '2020-03-21') {
                        //     print_r($i);
                        //     print($data);
                        //     die();
                        // }
                        // print_r($row);
                        //     die();
                        // if (isset($position->col_old)) {

                        //     echo  $row[$position->col_old] . "<br>";
                        // }
                        // if ($target_id == 5) {
                        //     print_r($date . " - " . $row[$position->col] . " - " . var_dump(strlen($row[$position->col]) > 0)   . "<br>");
                        // }
                        // continue;
                        if (strlen($row[$position->col]) > 0) {
                            $value = $row[$position->col];
                        } elseif (isset($position->col_old)) {
                            $value = $row[$position->col_old];
                        } else {
                            continue;
                        }
                        if (is_null($value) || !is_Date($date) || (!is_numeric($value) && $target->type_data == "float")) {
                            continue;
                        }
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_have_target_in_day($position_id, $date, $target_id);
                        // $data['stt_in_day'] = $max_stt;

                        $data_up = array(
                            'position_id' => $position_id,
                            'area_id' => $area_id,
                            'department_id' => $department_id,
                            'target_id' => $target_id,
                            'factory_id' => $factory_id,
                            'workshop_id' => $workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name,
                            'object_id' => $object_id,
                            'type_bc' => $type_bc,
                            'stt_in_day' => $max_stt
                            //                        'w,orkshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        if ($target->type_data == "float") {
                            $data_up['value'] = $value;
                        } else {
                            $data_up['value_text'] = $value;
                        }
                        $this->result_model->insert($data_up);
                    }
                    // $phong_id = $find_phong->id;
                }
            }
        }
    }
    public function result_nuoc()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/data_nuoc';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $this->load->model("target_model");
        $this->load->model("position_model");
        $insert = array();


        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);
        // print_r($sortedarray1);
        // die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
            //Tiến hành xác thực file
            print_r($file);
            $objFile = PHPExcel_IOFactory::identify($file);
            $objData = PHPExcel_IOFactory::createReader($objFile);
            // die();
            //Chỉ đọc dữ liệu
            // $objData->setReadDataOnly(true);
            // Load dữ liệu sang dạng đối tượng
            $objPHPExcel = $objData->load($file);

            //Lấy ra số trang sử dụng phương thức getSheetCount();
            // Lấy Ra tên trang sử dụng getSheetNames();
            //Chọn trang cần truy xuất
            $count_sheet = $objPHPExcel->getSheetCount();
            // print_r($count_sheet);
            // die();
            for ($k = 0; $k < $count_sheet; $k++) {
                $sheet_name = "sheet_" . $k  . "_" . $file_name;
                $sheet = $objPHPExcel->setActiveSheetIndex($k);
                $title = $sheet->getTitle();
                // echo "<br>";
                // print_r($title . "<br>");
                // die();
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
                $row_stt = 7;
                for ($i = $row_stt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - $row_stt][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - $row_stt][$j] instanceof PHPExcel_RichText) {
                            $data[$i - $row_stt][$j] = $data[$i - $row_stt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $row_stt][$j] > 0) {

                            if (is_numeric($data[$i - $row_stt][$j])) {
                                $data[$i - $row_stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $row_stt][$j]));
                            } else if ($data[$i - $row_stt][$j] == '26/09/16') {
                                $data[$i - $row_stt][$j] = '2016-09-26';
                            }
                        }
                    }
                }

                // echo "<pre>";
                // print_r($data);
                // die();
                // $temp_target = array(
                //     $this->target_model->where(array('id' => 14))->as_object()->get(), ///5
                //     $this->target_model->where(array('id' => 16))->as_object()->get(), ///0.5
                // );
                // die();
                // $explode = explode("-", $title);
                // $target_id = $explode[0];
                // $target = $this->target_model->get($target_id);
                // echo "<br>$target_id<br>";
                ///LIST POSTION
                $list_target = array_shift($data);

                ///XOA 1 ROW
                // array_shift($data);
                echo "<pre>";
                $targets = array();
                // $list_old = array();
                for ($i = 0; $i < count($list_target); $i++) {
                    $row = $list_target[$i];
                    if ($row == "") {
                        continue;
                    }

                    $explode = explode("-", $row);
                    $target_id = $explode[0];
                    if (!is_numeric($target_id)) {
                        continue;
                    }
                    // $find_vitri_old = $this->position_model->where(array('string_id_old' => $position))->as_object()->get_all();
                    // if (count($find_vitri_old) > 0) {
                    //     $find_vitri_old = array_map(function ($item) use ($i) {
                    //         $item->col = $i;
                    //         return $item;
                    //     }, $find_vitri_old);
                    //     $list_old = array_merge($list_old, $find_vitri_old);
                    // }
                    $find = $this->target_model->get($target_id);
                    //            print_r($find_phong);
                    if (empty($find)) {
                        continue;
                    }
                    ///Tìm ngược lại list old
                    // $find_vi_tri_old = array_values(array_filter($list_old, function ($item) use ($find_vitri) {
                    //     return $item->string_id = $find_vitri->string_id;
                    // }));
                    // if (isset($find_vi_tri_old[0]))
                    //     $find_vitri->col_old = $find_vi_tri_old[0]->col;
                    $find->col = $i;

                    array_push($targets, $find);
                }
                // print_r($targets);
                // print_r($data);
                // die();
                // print_r($temp_phong);
                // die();
                $stt_date = 1;
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    $position_string_id = $row[1];
                    $date = $row[2];
                    if (!is_Date($date)) {
                        continue;
                    }
                    if ($position_string_id == "") {
                        continue;
                    }
                    $position = $this->position_model->where(array('string_id' => $position_string_id))->get();
                    if (empty($position)) {
                        continue;
                    }
                    foreach ($targets as $target) {
                        $position_id = $position->id;
                        $system_id = $position->system_id;
                        $department_id = $position->department_id;
                        $target_id = $target->id;
                        $factory_id = $position->factory_id;
                        $workshop_id = $position->workshop_id;
                        $object_id = $position->object_id;
                        $type_bc = $position->type_bc;
                        if (strlen($row[$target->col]) > 0) {
                            $value = $row[$target->col];
                        } else {
                            continue;
                        }
                        if (is_null($value) || !is_Date($date) || (!is_numeric($value) && $target->type_data == "float")) {
                            continue;
                        }
                        if ($value == "Đạt") {
                            $value = 1;
                        } elseif ($value == "Không Đạt") {
                            $value = 0;
                        }
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_have_target_in_day($position_id, $date, $target_id);
                        // $data['stt_in_day'] = $max_stt;

                        $data_up = array(
                            'position_id' => $position_id,
                            'system_id' => $system_id,
                            'department_id' => $department_id,
                            'target_id' => $target_id,
                            'factory_id' => $factory_id,
                            'workshop_id' => $workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name,
                            'object_id' => $object_id,
                            'type_bc' => $type_bc,
                            'stt_in_day' => $max_stt
                        );
                        if ($target->type_data == "float" || $target->type_data == "boolean") {
                            $data_up['value'] = $value;
                        } else {
                            $data_up['value_text'] = $value;
                        }
                        $this->result_model->insert($data_up);
                    }
                    // $phong_id = $find_phong->id;
                }
            }
        }
    }
    public function phong_nm2()
    {
        // die();
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        $file = APPPATH . '../public/upload/vitri2/room.xlsx';
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

        $count_sheet = $objPHPExcel->getSheetCount();

        $this->load->model("workshop_model");
        $this->load->model("area_model");
        $this->load->model("position_model");
        $this->load->model("department_model");
        $this->load->model("target_model");

        $temp_workshop = array(
            $this->workshop_model->where(array('id' => 5))->as_object()->get(), ///BETA VIEN
            $this->workshop_model->where(array('id' => 4))->as_object()->get(), //BETA TIEM
            $this->workshop_model->where(array('id' => 4))->as_object()->get(), //BETA TIEM
            $this->workshop_model->where(array('id' => 4))->as_object()->get(), //BETA TIEM
            $this->workshop_model->where(array('id' => 8))->as_object()->get(), ///NON BETA TIEM
            $this->workshop_model->where(array('id' => 8))->as_object()->get(), ///NON BETA TIEM
            $this->workshop_model->where(array('id' => 8))->as_object()->get(), ///NON BETA TIEM
            $this->workshop_model->where(array('id' => 11))->as_object()->get(), ///NON BETA VIEN
            $this->workshop_model->where(array('id' => 10))->as_object()->get(), ///NON BETA QC
        );
        $temp_type_bc = array(
            '2 năm' => 'TwoYear',
            '2 năm / lần' => 'TwoYear',
            'hàng năm' => 'Year',
            'năm' => 'Year',
            'nửa năm' => 'HalfYear',
            'quý' => 'Quarter',
            'tháng' => 'Month'
        );
        for ($k = 0; $k < $count_sheet; $k++) {
            $sheet_name = "sheet_" . $k  . "_room.xlsx";
            $sheet = $objPHPExcel->setActiveSheetIndex($k);

            $title = $sheet->getTitle();
            //Lấy ra số dòng cuối cùng
            $Totalrow = $sheet->getHighestRow();
            //Lấy ra tên cột cuối cùng
            $LastColumn = $sheet->getHighestColumn();
            //Chuyển đổi tên cột đó về vị trí thứ, VD: C là 3,D là 4
            $TotalCol = PHPExcel_Cell::columnIndexFromString($LastColumn);

            //Tạo mảng chứa dữ liệu
            $data = [];
            $cell = $sheet->getCellByColumnAndRow(10, 1);

            $object_id = $cell->getValue();
            // print_r($object_id);
            // die();
            //Tiến hành lặp qua từng ô dữ liệu
            //----Lặp dòng, Vì dòng đầu là tiêu đề cột nên chúng ta sẽ lặp giá trị từ dòng 2
            $stt = 6;
            for ($i = $stt; $i <= $Totalrow; $i++) {
                //----Lặp cột
                for ($j = 0; $j < $TotalCol; $j++) {
                    // Tiến hành lấy giá trị của từng ô đổ vào mảng
                    $cell = $sheet->getCellByColumnAndRow($j, $i);

                    $data[$i - $stt][$j] = $cell->getValue();
                    ///CHUYEN RICH TEXT
                    if ($data[$i - $stt][$j] instanceof PHPExcel_RichText) {
                        $data[$i - $stt][$j] = $data[$i - $stt][$j]->getPlainText();
                    }
                    ////CHUYEN DATE 
                    if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $stt][$j] > 0) {

                        if (is_numeric($data[$i - $stt][$j])) {
                            $data[$i - $stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $stt][$j]));
                        } else if ($data[$i - $stt][$j] == '26/09/16') {
                            $data[$i - $stt][$j] = '2016-09-26';
                        }
                    }
                }
            }


            echo "<pre>";
            echo $sheet_name . "<br>";
            // if ($k == 1) {
            // print_r($data);
            // die();
            // } else {
            //     continue;
            // }

            // $workshop = $title;
            $workshop_id = $title;
            $frequency_name = $type_bc = $area_name = $target_name =  $system_id = "";
            $temp_area = $temp_phong = array();
            // $area_all = $this->area_model->where(array('deleted' => 0, 'workshop_id' => $workshop_id))->as_object()->get_all();

            // foreach ($area_all as $row) {
            //     $temp_area[$row->string_id] = $row;
            // }
            $phong_all = $this->department_model->where(array('deleted' => 0))->as_object()->get_all();
            foreach ($phong_all as $phong) {
                $temp_phong[$phong->string_id] = $phong;
            }
            for ($i = 0; $i < count($data); $i++) {
                if ($data[$i][9] != "") {
                    $area_string_id = trim($data[$i][9]);
                    if (!isset($temp_area[$area_string_id])) {
                        $area_name = "Cấp sạch $area_string_id";
                        $area_name_en = "Grade $area_string_id";
                        //TẠO AREA
                        $area_id = $this->area_model->insert(array(
                            'name' => $area_name,
                            'name_en' => $area_name_en,
                            'workshop_id' => $workshop_id,
                            'factory_id' => 2,
                            'from_file' => $sheet_name,
                            'string_id' => $area_string_id
                        ));
                        $area = $temp_area[$area_string_id] = $this->area_model->get($area_id);
                    } else {
                        $area =  $temp_area[$area_string_id];
                    }
                }
                if ($data[$i][2] != "") {

                    $skuList = preg_split('/\r\n|\r|\n/', trim($data[$i][1]));
                    $phong_name = $skuList[0];
                    $phong_name_en = isset($skuList[1]) ? $skuList[1] : "";
                    // print_r($skuList);
                    // die();
                    $phong_string_id = trim($data[$i][2]);
                    ////Tạo Phòng

                    if (!isset($temp_phong[$phong_string_id])) {
                        $data_phong = array(
                            'name' => $phong_name,
                            'name_en' => $phong_name_en,
                            'string_id' => $phong_string_id,
                            'area_id' => $area->id,
                            'type' => 1,
                            'workshop_id' => $area->workshop_id,
                            'factory_id' => $area->factory_id,
                            'from_file' => $sheet_name
                        );
                        $phong_id = $this->department_model->insert($data_phong);
                        $phong = $temp_phong[$phong_string_id] = $this->department_model->get($phong_id);
                    } else {
                        $phong = $temp_phong[$phong_string_id];
                    }
                }
            }
        }
    }
    public function thietbi_nm2()
    {
        // die();
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        $file = APPPATH . '../public/upload/vitri2/Equipment.xlsx';
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

        $count_sheet = $objPHPExcel->getSheetCount();

        $this->load->model("workshop_model");
        $this->load->model("area_model");
        $this->load->model("position_model");
        $this->load->model("department_model");
        $this->load->model("target_model");

        $temp_workshop = array(
            $this->workshop_model->where(array('id' => 5))->as_object()->get(), ///BETA VIEN
            $this->workshop_model->where(array('id' => 4))->as_object()->get(), //BETA TIEM
            $this->workshop_model->where(array('id' => 4))->as_object()->get(), //BETA TIEM
            $this->workshop_model->where(array('id' => 4))->as_object()->get(), //BETA TIEM
            $this->workshop_model->where(array('id' => 8))->as_object()->get(), ///NON BETA TIEM
            $this->workshop_model->where(array('id' => 8))->as_object()->get(), ///NON BETA TIEM
            $this->workshop_model->where(array('id' => 8))->as_object()->get(), ///NON BETA TIEM
            $this->workshop_model->where(array('id' => 11))->as_object()->get(), ///NON BETA VIEN
            $this->workshop_model->where(array('id' => 10))->as_object()->get(), ///NON BETA QC
        );
        $temp_type_bc = array(
            '2 năm' => 'TwoYear',
            '2 năm / lần' => 'TwoYear',
            'hàng năm' => 'Year',
            'năm' => 'Year',
            'nửa năm' => 'HalfYear',
            'quý' => 'Quarter',
            'tháng' => 'Month'
        );
        for ($k = 0; $k < $count_sheet; $k++) {
            $sheet_name = "sheet_" . $k  . "_Equipment.xlsx";
            $sheet = $objPHPExcel->setActiveSheetIndex($k);

            $title = $sheet->getTitle();
            //Lấy ra số dòng cuối cùng
            $Totalrow = $sheet->getHighestRow();
            //Lấy ra tên cột cuối cùng
            $LastColumn = $sheet->getHighestColumn();
            //Chuyển đổi tên cột đó về vị trí thứ, VD: C là 3,D là 4
            $TotalCol = PHPExcel_Cell::columnIndexFromString($LastColumn);

            //Tạo mảng chứa dữ liệu
            $data = [];
            $cell = $sheet->getCellByColumnAndRow(10, 1);

            $object_id = $cell->getValue();
            // print_r($object_id);
            // die();
            //Tiến hành lặp qua từng ô dữ liệu
            //----Lặp dòng, Vì dòng đầu là tiêu đề cột nên chúng ta sẽ lặp giá trị từ dòng 2
            $stt = 6;
            for ($i = $stt; $i <= $Totalrow; $i++) {
                //----Lặp cột
                for ($j = 0; $j < $TotalCol; $j++) {
                    // Tiến hành lấy giá trị của từng ô đổ vào mảng
                    $cell = $sheet->getCellByColumnAndRow($j, $i);

                    $data[$i - $stt][$j] = $cell->getValue();
                    ///CHUYEN RICH TEXT
                    if ($data[$i - $stt][$j] instanceof PHPExcel_RichText) {
                        $data[$i - $stt][$j] = $data[$i - $stt][$j]->getPlainText();
                    }
                    ////CHUYEN DATE 
                    if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $stt][$j] > 0) {

                        if (is_numeric($data[$i - $stt][$j])) {
                            $data[$i - $stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $stt][$j]));
                        } else if ($data[$i - $stt][$j] == '26/09/16') {
                            $data[$i - $stt][$j] = '2016-09-26';
                        }
                    }
                }
            }


            echo "<pre>";
            echo $sheet_name . "<br>";
            // if ($k == 1) {
            // print_r($data);
            // die();
            // } else {
            //     continue;
            // }

            // $workshop = $title;
            $workshop_id = $title;
            $frequency_name = $type_bc = $area_name = $target_name =  $system_id = "";
            $temp_area = $temp_phong = array();
            // $area_all = $this->area_model->where(array('deleted' => 0, 'workshop_id' => $workshop_id))->as_object()->get_all();

            // foreach ($area_all as $row) {
            //     $temp_area[$row->string_id] = $row;
            // }
            $phong_all = $this->department_model->where(array('deleted' => 0))->as_object()->get_all();
            foreach ($phong_all as $phong) {
                $temp_phong[$phong->string_id] = $phong;
            }
            for ($i = 0; $i < count($data); $i++) {
                if ($data[$i][9] != "") {
                    $phong_string_id = $data[$i][9];
                    if (isset($temp_phong[$phong_string_id]))
                        $phong = $temp_phong[$phong_string_id];
                    else
                        continue;
                }
                if ($data[$i][2] != "") {
                    $skuList = preg_split('/\r\n|\r|\n/', trim($data[$i][1]));
                    $thietbi_name = $skuList[0];
                    $thietbi_name_en = isset($skuList[1]) ? $skuList[1] : "";
                    // print_r($skuList);
                    // die();
                    $thietbi_string_id = trim($data[$i][2]);
                    ////Tạo Phòng

                    if (!isset($temp_phong[$thietbi_string_id])) {
                        $data_thietbi = array(
                            'name' => $thietbi_name,
                            'name_en' => $thietbi_name_en,
                            'string_id' => $thietbi_string_id,
                            'area_id' => $phong->area_id,
                            'type' => 2,
                            'workshop_id' => $phong->workshop_id,
                            'factory_id' => $phong->factory_id,
                            'from_file' => $sheet_name
                        );
                        $thiet_id = $this->department_model->insert($data_thietbi);
                    }
                }
            }
        }
        die();
    }
    public function vitri_nuoc_nm2()
    {
        // die();
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        $file = APPPATH . '../public/upload/vitri2/Vi_tri_nuoc_NH2.xlsx';
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

        $count_sheet = $objPHPExcel->getSheetCount();

        $this->load->model("workshop_model");
        $this->load->model("area_model");
        $this->load->model("position_model");
        $this->load->model("department_model");
        $this->load->model("target_model");
        $temp_workshop = array(
            'QC' => 12,
            'Non-beta' => 13,
            'Kho dễ cháy' => 14,
            'Kho nguyen liệu' => 15
        );
        $temp_system = array(
            'Hệ thống hơi nước tinh khiết' => 1,
            'Hệ thống nước pha tiêm' => 2,
            'Nước vao tank' => 3,
            'Nước nguồn' => 4,
            'Nước sau khử khoáng' => 5,
            'Nước tinh khiết' => 6,
            'Nước tinh khiết hệ thống tạo và phân phối' => 7,
            'Nước sinh hoạt' => 8,
            'Nước sau cột lọc thô' => 9,
            'Nước sau cột cation - anion' => 10,
            'Nước sau cột lọc RO' => 11,
            'Nước sau côt làm mềm nước' => 13,
        );
        $temp_type_bc = array(
            '2 năm' => 'TwoYear',
            '2 năm / lần' => 'TwoYear',
            '2 năm / lần' => 'TwoYear',
            'hàng năm' => 'Year',
            'năm' => 'Year',
            'nửa năm' => 'HalfYear',
            'quý' => 'Quarter',
            'tháng' => 'Month',
            '2 lần/ tháng' => 'HalfYear'
        );
        for ($k = 0; $k < $count_sheet; $k++) {
            $sheet_name = "sheet_" . $k  . "_Vi_tri_nuoc_NH2.xlsx";
            $sheet = $objPHPExcel->setActiveSheetIndex($k);

            $title = $sheet->getTitle();
            //Lấy ra số dòng cuối cùng
            $Totalrow = $sheet->getHighestRow();
            //Lấy ra tên cột cuối cùng
            $LastColumn = $sheet->getHighestColumn();
            //Chuyển đổi tên cột đó về vị trí thứ, VD: C là 3,D là 4
            $TotalCol = PHPExcel_Cell::columnIndexFromString($LastColumn);

            //Tạo mảng chứa dữ liệu
            $data = [];
            $cell = $sheet->getCellByColumnAndRow(10, 1);

            $object_id = $cell->getValue();
            // print_r($object_id);
            // die();
            //Tiến hành lặp qua từng ô dữ liệu
            //----Lặp dòng, Vì dòng đầu là tiêu đề cột nên chúng ta sẽ lặp giá trị từ dòng 2
            $stt = 4;
            for ($i = $stt; $i <= $Totalrow; $i++) {
                //----Lặp cột
                for ($j = 0; $j < $TotalCol; $j++) {
                    // Tiến hành lấy giá trị của từng ô đổ vào mảng
                    $cell = $sheet->getCellByColumnAndRow($j, $i);

                    $data[$i - $stt][$j] = $cell->getValue();
                    ///CHUYEN RICH TEXT
                    if ($data[$i - $stt][$j] instanceof PHPExcel_RichText) {
                        $data[$i - $stt][$j] = $data[$i - $stt][$j]->getPlainText();
                    }
                    ////CHUYEN DATE 
                    if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $stt][$j] > 0) {

                        if (is_numeric($data[$i - $stt][$j])) {
                            $data[$i - $stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $stt][$j]));
                        } else if ($data[$i - $stt][$j] == '26/09/16') {
                            $data[$i - $stt][$j] = '2016-09-26';
                        }
                    }
                }
            }


            echo "<pre>";
            echo $sheet_name . "<br>";
            // if ($k == 1) {
            // print_r($data);
            // die();
            // } else {
            //     continue;
            // }

            // $workshop = $title;
            $workshop_id = $title;
            $frequency_name = $type_bc = $area_name = $target_name =  $system_id = "";
            $temp_area = $temp_phong = array();
            for ($i = 0; $i < count($data); $i++) {
                if ($data[$i][6] != "") {
                    $position_string_id = trim($data[$i][6]);
                    $position_name = trim($data[$i][8]);
                    $position_name_en = trim($data[$i][9]);
                    $frequency_name = trim($data[$i][10]);
                    $frequency_name_en = trim($data[$i][11]);
                    $department_string_id = trim($data[$i][7]);
                    $workshop_id = trim($data[$i][7]);
                    $department = $this->department_model->where("string_id", $department_string_id)->get();
                    $type_bc = $temp_type_bc[trim($data[$i][12])];
                    $workshop_id = $temp_workshop[trim($data[$i][2])];
                    $system_id = $temp_system[trim($data[$i][1])];
                    $data_position = array(
                        'name' => $position_name,
                        'name_en' => $position_name_en,
                        'string_id' => $position_string_id,
                        'frequency_name' => $frequency_name,
                        'frequency_name_en' => $frequency_name_en,
                        'department_id' => isset($department->id) ? $department->id : null,
                        'system_id' => $system_id,
                        'workshop_id' => $workshop_id,
                        'factory_id' => 2,
                        'from_file' => $sheet_name,
                        'object_id' => 20,
                        'type_bc' => $type_bc
                    );
                    $position = $this->position_model->insert($data_position);
                }
            }
        }
    }

    public function vitri_khi_visinh_nm2()
    {
        // die();
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        $file = APPPATH . '../public/upload/vitri2/Vi_tri_khi_visinh_NM2.xlsx';
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

        $count_sheet = $objPHPExcel->getSheetCount();

        $this->load->model("workshop_model");
        $this->load->model("area_model");
        $this->load->model("position_model");
        $this->load->model("department_model");
        $this->load->model("target_model");

        $temp_type_bc = array(
            '2 năm' => 'TwoYear',
            '2 năm / lần' => 'TwoYear',
            'hàng năm' => 'Year',
            'năm' => 'Year',
            'nửa năm' => 'HalfYear',
            'quý' => 'Quarter',
            'tháng' => 'Month'
        );
        $temp_target = array(
            'Active' => 5,
            'Passive' => 3,
            'Rodac' => 4,
            'Contact' => 4,
            'Surface' => 4
        );
        for ($k = 0; $k < $count_sheet; $k++) {
            $sheet_name = "sheet_" . $k  . "_Vi_tri_khi_visinh_NM2.xlsx";
            $sheet = $objPHPExcel->setActiveSheetIndex($k);

            $title = $sheet->getTitle();
            //Lấy ra số dòng cuối cùng
            $Totalrow = $sheet->getHighestRow();
            //Lấy ra tên cột cuối cùng
            $LastColumn = $sheet->getHighestColumn();
            //Chuyển đổi tên cột đó về vị trí thứ, VD: C là 3,D là 4
            $TotalCol = PHPExcel_Cell::columnIndexFromString($LastColumn);

            //Tạo mảng chứa dữ liệu
            $data = [];
            $cell = $sheet->getCellByColumnAndRow(10, 1);

            $object_id = $cell->getValue();
            // print_r($object_id);
            // die();
            //Tiến hành lặp qua từng ô dữ liệu
            //----Lặp dòng, Vì dòng đầu là tiêu đề cột nên chúng ta sẽ lặp giá trị từ dòng 2
            $stt = 3;
            for ($i = $stt; $i <= $Totalrow; $i++) {
                //----Lặp cột
                for ($j = 0; $j < $TotalCol; $j++) {
                    // Tiến hành lấy giá trị của từng ô đổ vào mảng
                    $cell = $sheet->getCellByColumnAndRow($j, $i);

                    $data[$i - $stt][$j] = $cell->getValue();
                    ///CHUYEN RICH TEXT
                    if ($data[$i - $stt][$j] instanceof PHPExcel_RichText) {
                        $data[$i - $stt][$j] = $data[$i - $stt][$j]->getPlainText();
                    }
                    ////CHUYEN DATE 
                    if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $stt][$j] > 0) {

                        if (is_numeric($data[$i - $stt][$j])) {
                            $data[$i - $stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $stt][$j]));
                        } else if ($data[$i - $stt][$j] == '26/09/16') {
                            $data[$i - $stt][$j] = '2016-09-26';
                        }
                    }
                }
            }


            echo "<pre>";
            echo $sheet_name . "<br>";
            // if ($k == 1) {
            // print_r($data);
            // die();
            // } else {
            //     continue;
            // }

            // $workshop = $title;
            $workshop_id = $title;
            $frequency_name = $type_bc = $area_name = $target_name =  $system_id = "";
            $temp_area = $temp_phong = array();
            $target_id = null;
            for ($i = 0; $i < count($data); $i += 2) {
                // echo $i . "<br>";
                // continue;
                if ($data[$i][3] != "") {
                    $position_string_id = trim($data[$i][3]);
                    $position_name = trim($data[$i][2]);
                    $position_name_en = trim($data[$i + 1][2]);
                    $frequency_name = trim($data[$i][4]);
                    $frequency_name_en = trim($data[$i + 1][4]);
                    if ($data[$i][1] != "") {
                        $department_string_id = trim($data[$i][1]);
                        $department = $this->department_model->where("string_id", $department_string_id)->get();
                    }
                    if (isset($data[$i][6]) && $data[$i][6] != "") {
                        $target_id = $temp_target[trim($data[$i][6])];
                    }
                    $type_bc = $temp_type_bc[trim($data[$i][5])];
                    if ($k == 0) {
                        $object_id = 16;
                    } elseif ($position_string_id[0] == "E") {
                        $object_id = 10;
                    } else {
                        $object_id = 11;
                    }
                    echo $position_string_id;
                    echo "<br>";
                    $data_position = array(
                        'name' => $position_name,
                        'name_en' => $position_name_en,
                        'string_id' => $position_string_id,
                        'frequency_name' => $frequency_name,
                        'frequency_name_en' => $frequency_name_en,
                        'target_id' => $target_id,
                        'department_id' =>  $department->id,
                        'area_id' => $department->area_id,
                        'workshop_id' => $department->workshop_id,
                        'factory_id' => $department->factory_id,
                        'from_file' => $sheet_name,
                        'object_id' => $object_id,
                        'type_bc' => $type_bc
                    );
                    $position = $this->position_model->insert($data_position);
                }
            }
        }
    }

    public function result_nuoc_nm2()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/vitri2/data_nuoc';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $this->load->model("target_model");
        $this->load->model("position_model");
        $insert = array();


        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);
        // print_r($sortedarray1);
        // die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
            //Tiến hành xác thực file
            print_r($file);
            $objFile = PHPExcel_IOFactory::identify($file);
            $objData = PHPExcel_IOFactory::createReader($objFile);
            // die();
            //Chỉ đọc dữ liệu
            // $objData->setReadDataOnly(true);
            // Load dữ liệu sang dạng đối tượng
            $objPHPExcel = $objData->load($file);

            //Lấy ra số trang sử dụng phương thức getSheetCount();
            // Lấy Ra tên trang sử dụng getSheetNames();
            //Chọn trang cần truy xuất
            $count_sheet = $objPHPExcel->getSheetCount();
            // print_r($count_sheet);
            // die();
            for ($k = 0; $k < $count_sheet; $k++) {
                $sheet_name = "sheet_" . $k  . "_" . $file_name;
                $sheet = $objPHPExcel->setActiveSheetIndex($k);
                $title = $sheet->getTitle();
                // echo "<br>";
                // print_r($title . "<br>");
                // die();
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
                $row_stt = 7;
                for ($i = $row_stt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - $row_stt][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - $row_stt][$j] instanceof PHPExcel_RichText) {
                            $data[$i - $row_stt][$j] = $data[$i - $row_stt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $row_stt][$j] > 0) {

                            if (is_numeric($data[$i - $row_stt][$j])) {
                                $data[$i - $row_stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $row_stt][$j]));
                            } else if ($data[$i - $row_stt][$j] == '26/09/16') {
                                $data[$i - $row_stt][$j] = '2016-09-26';
                            }
                        }
                    }
                }

                // echo "<pre>";
                // print_r($data);
                // die();
                // $temp_target = array(
                //     $this->target_model->where(array('id' => 14))->as_object()->get(), ///5
                //     $this->target_model->where(array('id' => 16))->as_object()->get(), ///0.5
                // );
                // die();
                // $explode = explode("-", $title);
                // $target_id = $explode[0];
                // $target = $this->target_model->get($target_id);
                // echo "<br>$target_id<br>";
                ///LIST POSTION
                $list_target = array_shift($data);

                ///XOA 1 ROW
                // array_shift($data);
                echo "<pre>";
                $targets = array();
                // $list_old = array();
                for ($i = 0; $i < count($list_target); $i++) {
                    $row = $list_target[$i];
                    if ($row == "") {
                        continue;
                    }

                    $explode = explode("-", $row);
                    $target_id = $explode[0];
                    if (!is_numeric($target_id)) {
                        continue;
                    }
                    // $find_vitri_old = $this->position_model->where(array('string_id_old' => $position))->as_object()->get_all();
                    // if (count($find_vitri_old) > 0) {
                    //     $find_vitri_old = array_map(function ($item) use ($i) {
                    //         $item->col = $i;
                    //         return $item;
                    //     }, $find_vitri_old);
                    //     $list_old = array_merge($list_old, $find_vitri_old);
                    // }
                    $find = $this->target_model->get($target_id);
                    //            print_r($find_phong);
                    if (empty($find)) {
                        continue;
                    }
                    ///Tìm ngược lại list old
                    // $find_vi_tri_old = array_values(array_filter($list_old, function ($item) use ($find_vitri) {
                    //     return $item->string_id = $find_vitri->string_id;
                    // }));
                    // if (isset($find_vi_tri_old[0]))
                    //     $find_vitri->col_old = $find_vi_tri_old[0]->col;
                    $find->col = $i;

                    array_push($targets, $find);
                }
                // print_r($targets);
                // print_r($data);
                // die();
                // print_r($temp_phong);
                // die();
                $stt_date = 1;
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    $position_string_id = $row[1];
                    $date = $row[2];
                    if (!is_Date($date)) {
                        continue;
                    }
                    if ($position_string_id == "") {
                        continue;
                    }
                    $position = $this->position_model->where(array('string_id' => $position_string_id))->get();
                    if (empty($position)) {
                        continue;
                    }
                    foreach ($targets as $target) {
                        $position_id = $position->id;
                        $system_id = $position->system_id;
                        $department_id = $position->department_id;
                        $target_id = $target->id;
                        $factory_id = $position->factory_id;
                        $workshop_id = $position->workshop_id;
                        $object_id = $position->object_id;
                        $type_bc = $position->type_bc;
                        if (strlen($row[$target->col]) > 0) {
                            $value = $row[$target->col];
                        } else {
                            continue;
                        }
                        if (is_null($value) || !is_Date($date) || (!is_numeric($value) && $target->type_data == "float")) {
                            continue;
                        }
                        if ($value == "Đạt") {
                            $value = 1;
                        } elseif ($value == "Không Đạt") {
                            $value = 0;
                        }
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_have_target_in_day($position_id, $date, $target_id);
                        // $data['stt_in_day'] = $max_stt;

                        $data_up = array(
                            'position_id' => $position_id,
                            'system_id' => $system_id,
                            'department_id' => $department_id,
                            'target_id' => $target_id,
                            'factory_id' => $factory_id,
                            'workshop_id' => $workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name,
                            'object_id' => $object_id,
                            'type_bc' => $type_bc,
                            'stt_in_day' => $max_stt
                        );
                        if ($target->type_data == "float" || $target->type_data == "boolean") {
                            $data_up['value'] = $value;
                        } else {
                            $data_up['value_text'] = $value;
                        }
                        $this->result_model->insert($data_up);
                    }
                    // $phong_id = $find_phong->id;
                }
            }
        }
    }
    public function result_khi_nm2()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/vitri2/data_khi';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $this->load->model("target_model");
        $this->load->model("position_model");
        $insert = array();


        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);
        // print_r($sortedarray1);
        // die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
            //Tiến hành xác thực file
            print_r($file);
            $objFile = PHPExcel_IOFactory::identify($file);
            $objData = PHPExcel_IOFactory::createReader($objFile);
            // die();
            //Chỉ đọc dữ liệu
            // $objData->setReadDataOnly(true);
            // Load dữ liệu sang dạng đối tượng
            $objPHPExcel = $objData->load($file);

            //Lấy ra số trang sử dụng phương thức getSheetCount();
            // Lấy Ra tên trang sử dụng getSheetNames();
            //Chọn trang cần truy xuất
            $count_sheet = $objPHPExcel->getSheetCount();
            // print_r($count_sheet);
            // die();
            for ($k = 0; $k < $count_sheet; $k++) {
                $sheet_name = "sheet_" . $k  . "_" . $file_name;
                $sheet = $objPHPExcel->setActiveSheetIndex($k);
                $title = $sheet->getTitle();
                // echo "<br>";
                // print_r($title . "<br>");
                // die();
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
                $row_stt = 7;
                for ($i = $row_stt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - $row_stt][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - $row_stt][$j] instanceof PHPExcel_RichText) {
                            $data[$i - $row_stt][$j] = $data[$i - $row_stt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $row_stt][$j] > 0) {

                            if (is_numeric($data[$i - $row_stt][$j])) {
                                $data[$i - $row_stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $row_stt][$j]));
                            } else if ($data[$i - $row_stt][$j] == '26/09/16') {
                                $data[$i - $row_stt][$j] = '2016-09-26';
                            }
                        }
                    }
                }

                // echo "<pre>";
                // print_r($data);
                // die();
                // $temp_target = array(
                //     $this->target_model->where(array('id' => 14))->as_object()->get(), ///5
                //     $this->target_model->where(array('id' => 16))->as_object()->get(), ///0.5
                // );
                // die();
                // $explode = explode("-", $title);
                // $target_id = $explode[0];
                // $target = $this->target_model->get($target_id);
                // echo "<br>$target_id<br>";
                ///LIST POSTION
                $list_target = array_shift($data);

                ///XOA 1 ROW
                // array_shift($data);
                echo "<pre>";
                $targets = array();
                // $list_old = array();
                for ($i = 0; $i < count($list_target); $i++) {
                    $row = $list_target[$i];
                    if ($row == "") {
                        continue;
                    }

                    $explode = explode("-", $row);
                    $target_id = $explode[0];
                    if (!is_numeric($target_id)) {
                        continue;
                    }
                    // $find_vitri_old = $this->position_model->where(array('string_id_old' => $position))->as_object()->get_all();
                    // if (count($find_vitri_old) > 0) {
                    //     $find_vitri_old = array_map(function ($item) use ($i) {
                    //         $item->col = $i;
                    //         return $item;
                    //     }, $find_vitri_old);
                    //     $list_old = array_merge($list_old, $find_vitri_old);
                    // }
                    $find = $this->target_model->get($target_id);
                    //            print_r($find_phong);
                    if (empty($find)) {
                        continue;
                    }
                    ///Tìm ngược lại list old
                    // $find_vi_tri_old = array_values(array_filter($list_old, function ($item) use ($find_vitri) {
                    //     return $item->string_id = $find_vitri->string_id;
                    // }));
                    // if (isset($find_vi_tri_old[0]))
                    //     $find_vitri->col_old = $find_vi_tri_old[0]->col;
                    $find->col = $i;

                    array_push($targets, $find);
                }
                // print_r($targets);
                // print_r($data);
                // die();
                // print_r($temp_phong);
                // die();
                $stt_date = 1;
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    $position_string_id = $row[3];
                    $date = $row[4];
                    if (!is_Date($date)) {
                        continue;
                    }
                    if ($position_string_id == "") {
                        continue;
                    }
                    $position = $this->position_model->where(array('string_id' => $position_string_id))->get();
                    if (empty($position)) {
                        continue;
                    }
                    $position_id = $position->id;
                    $area_id = $position->area_id;
                    $department_id = $position->department_id;
                    $factory_id = $position->factory_id;
                    $workshop_id = $position->workshop_id;
                    $object_id = $position->object_id;
                    $type_bc = $position->type_bc;
                    foreach ($targets as $target) {
                        $target_id = $target->id;
                        if (strlen($row[$target->col]) > 0) {
                            $value = $row[$target->col];
                        } else {
                            continue;
                        }
                        if (is_null($value) || !is_Date($date) || (!is_numeric($value) && $target->type_data == "float")) {
                            continue;
                        }
                        if ($value == "Đạt") {
                            $value = 1;
                        } elseif ($value == "Không Đạt") {
                            $value = 0;
                        }
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_have_target_in_day($position_id, $date, $target_id);
                        // $data['stt_in_day'] = $max_stt;

                        $data_up = array(
                            'position_id' => $position_id,
                            'area_id' => $area_id,
                            'department_id' => $department_id,
                            'target_id' => $target_id,
                            'factory_id' => $factory_id,
                            'workshop_id' => $workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name,
                            'object_id' => $object_id,
                            'type_bc' => $type_bc,
                            'stt_in_day' => $max_stt
                        );
                        if ($target->type_data == "float" || $target->type_data == "boolean") {
                            $data_up['value'] = $value;
                        } else {
                            $data_up['value_text'] = $value;
                        }
                        $this->result_model->insert($data_up);
                    }
                    // $phong_id = $find_phong->id;
                }
            }
        }
    }
    public function result_visinh_nm2()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/vitri2/data_visinh';

        echo "<pre>";
        echo $dir;
        $this->load->model("result_model");
        $this->load->model("target_model");
        $this->load->model("position_model");
        $insert = array();


        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);
        // print_r($sortedarray1);
        // die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
            //Tiến hành xác thực file
            print_r($file);
            $objFile = PHPExcel_IOFactory::identify($file);
            $objData = PHPExcel_IOFactory::createReader($objFile);
            // die();
            //Chỉ đọc dữ liệu
            // $objData->setReadDataOnly(true);
            // Load dữ liệu sang dạng đối tượng
            $objPHPExcel = $objData->load($file);

            //Lấy ra số trang sử dụng phương thức getSheetCount();
            // Lấy Ra tên trang sử dụng getSheetNames();
            //Chọn trang cần truy xuất
            $count_sheet = $objPHPExcel->getSheetCount();
            // print_r($count_sheet);
            // die();
            for ($k = 0; $k < $count_sheet; $k++) {
                $sheet_name = "sheet_" . $k  . "_" . $file_name;
                $sheet = $objPHPExcel->setActiveSheetIndex($k);
                $title = $sheet->getTitle();
                // echo "<br>";
                // print_r($title . "<br>");
                // die();
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
                $row_stt = 7;
                for ($i = $row_stt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - $row_stt][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - $row_stt][$j] instanceof PHPExcel_RichText) {
                            $data[$i - $row_stt][$j] = $data[$i - $row_stt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $row_stt][$j] > 0) {

                            if (is_numeric($data[$i - $row_stt][$j])) {
                                $data[$i - $row_stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $row_stt][$j]));
                            } else if ($data[$i - $row_stt][$j] == '26/09/16') {
                                $data[$i - $row_stt][$j] = '2016-09-26';
                            }
                        }
                    }
                }

                // echo "<pre>";
                // print_r($data);
                // die();
                // $temp_target = array(
                //     $this->target_model->where(array('id' => 14))->as_object()->get(), ///5
                //     $this->target_model->where(array('id' => 16))->as_object()->get(), ///0.5
                // );
                // die();
                // $explode = explode("-", $title);
                // $target_id = $explode[0];
                // $target = $this->target_model->get($target_id);
                // echo "<br>$target_id<br>";
                ///LIST POSTION
                $list_target = array_shift($data);

                ///XOA 1 ROW
                // array_shift($data);
                echo "<pre>";
                $targets = array();
                // $list_old = array();
                for ($i = 0; $i < count($list_target); $i++) {
                    $row = $list_target[$i];
                    if ($row == "") {
                        continue;
                    }

                    $explode = explode("-", $row);
                    $explode = explode("_", $explode[0]);
                    $target_id = $explode[0];
                    if (!is_numeric($target_id)) {
                        continue;
                    }
                    // $find_vitri_old = $this->position_model->where(array('string_id_old' => $position))->as_object()->get_all();
                    // if (count($find_vitri_old) > 0) {
                    //     $find_vitri_old = array_map(function ($item) use ($i) {
                    //         $item->col = $i;
                    //         return $item;
                    //     }, $find_vitri_old);
                    //     $list_old = array_merge($list_old, $find_vitri_old);
                    // }
                    $find = $this->target_model->get($target_id);
                    //            print_r($find_phong);
                    if (empty($find)) {
                        continue;
                    }
                    ///Tìm ngược lại list old
                    // $find_vi_tri_old = array_values(array_filter($list_old, function ($item) use ($find_vitri) {
                    //     return $item->string_id = $find_vitri->string_id;
                    // }));
                    // if (isset($find_vi_tri_old[0]))
                    //     $find_vitri->col_old = $find_vi_tri_old[0]->col;
                    $find->col = $i;

                    array_push($targets, $find);
                }
                // print_r($targets);
                // print_r($data);
                // die();
                // print_r($temp_phong);
                // die();
                $stt_date = 1;
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    $position_string_id = $row[4];

                    // $position_string_id2 = str_replace("_", "-", $position_string_id);
                    $date = $row[5];

                    if (!is_Date($date)) {
                        continue;
                    }
                    if ($position_string_id == "") {
                        continue;
                    }
                    $position = $this->position_model->where(array('string_id' => $position_string_id))->get();
                    // if ($k == 2) {
                    //     var_dump($date);
                    //     echo "<br>";
                    //     var_dump($position_string_id);
                    //     echo "<br>";
                    //     print_r($position);
                    //     echo "<br>";
                    // }
                    if (empty($position)) {
                        continue;
                    }
                    $position_id = $position->id;
                    $area_id = $position->area_id;
                    $department_id = $position->department_id;
                    $factory_id = $position->factory_id;
                    $workshop_id = $position->workshop_id;
                    $object_id = $position->object_id;
                    $type_bc = $position->type_bc;
                    foreach ($targets as $target) {
                        $target_id = $target->id;
                        if (strlen($row[$target->col]) > 0) {
                            $value = $row[$target->col];
                        } else {
                            continue;
                        }
                        if (is_null($value) || !is_Date($date) || (!is_numeric($value) && $target->type_data == "float")) {
                            continue;
                        }
                        if ($value == "Đạt") {
                            $value = 1;
                        } elseif ($value == "Không Đạt") {
                            $value = 0;
                        }
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_have_target_in_day($position_id, $date, $target_id);
                        // $data['stt_in_day'] = $max_stt;

                        $data_up = array(
                            'position_id' => $position_id,
                            'area_id' => $area_id,
                            'department_id' => $department_id,
                            'target_id' => $target_id,
                            'factory_id' => $factory_id,
                            'workshop_id' => $workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name,
                            'object_id' => $object_id,
                            'type_bc' => $type_bc,
                            'stt_in_day' => $max_stt
                        );
                        if ($target->type_data == "float" || $target->type_data == "boolean") {
                            $data_up['value'] = $value;
                        } else {
                            $data_up['value_text'] = $value;
                        }
                        $this->result_model->insert($data_up);
                    }
                    // $phong_id = $find_phong->id;
                }
            }
        }
    }
    public function job()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/job/change_pass';

        echo "<pre>";
        echo $dir;
        $this->load->model("job_model");
        $this->load->model("job_schedule_model");
        $insert = array();


        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);
        // print_r($sortedarray1);
        // die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
            //Tiến hành xác thực file
            print_r($file);
            $objFile = PHPExcel_IOFactory::identify($file);
            $objData = PHPExcel_IOFactory::createReader($objFile);
            // die();
            //Chỉ đọc dữ liệu
            // $objData->setReadDataOnly(true);
            // Load dữ liệu sang dạng đối tượng
            $objPHPExcel = $objData->load($file);

            //Lấy ra số trang sử dụng phương thức getSheetCount();
            // Lấy Ra tên trang sử dụng getSheetNames();
            //Chọn trang cần truy xuất
            $count_sheet = $objPHPExcel->getSheetCount();
            // print_r($count_sheet);
            // die();
            for ($k = 0; $k < $count_sheet; $k++) {
                $sheet_name = "sheet_" . $k  . "_" . $file_name;
                $sheet = $objPHPExcel->setActiveSheetIndex($k);
                $title = $sheet->getTitle();
                // echo "<br>";
                // print_r($title . "<br>");
                // die();
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
                $row_stt = 2;
                for ($i = $row_stt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i - $row_stt][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i - $row_stt][$j] instanceof PHPExcel_RichText) {
                            $data[$i - $row_stt][$j] = $data[$i - $row_stt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i - $row_stt][$j] > 0) {

                            if (is_numeric($data[$i - $row_stt][$j])) {
                                $data[$i - $row_stt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - $row_stt][$j]));
                            } else if ($data[$i - $row_stt][$j] == '26/09/16') {
                                $data[$i - $row_stt][$j] = '2016-09-26';
                            }
                        }
                    }
                }

                // echo "<pre>";
                // print_r($data);
                // die();
                $stt_date = 1;
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    $string_id = $row[1];
                    $frequency = $row[0];
                    // $position_string_id2 = str_replace("_", "-", $position_string_id);
                    $name = $row[2];
                    $username = $row[3];
                    $date = $row[4];
                    $mail = $row[5];

                    $before_send = $row[6];
                    $hour_send = "06:00:00";
                    if (!is_Date($date)) {
                        continue;
                    }
                    if ($name == "" || $date == "" || $mail == "" || $string_id == "") {
                        continue;
                    }
                    $time_send = $date . " $hour_send";
                    // if($time_send)
                    $data_up = array(
                        'start_date' => $date,
                        'before_send' => $before_send,
                        'mail' => $mail,
                        'start_date' => $date,
                        'equipment_id' => $string_id,
                        'time_send' => $time_send,
                        'equipment_name' => $name,
                        'frequency' => $frequency,
                        'hour_send' => $hour_send,
                        'username' => $username,
                        'job_id' => 1,
                    );
                    $id = $this->job_schedule_model->insert($data_up);
                    $this->job_schedule_model->update_next_send($id);
                    // $phong_id = $find_phong->id;
                }
            }
        }
    }
    function listFolderFiles($dir)
    {
        $ffs = scandir($dir);
        // print_r($ffs);
        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        // prevent empty ordered elements
        if (count($ffs) < 1)
            return array();

        $files = array();
        foreach ($ffs as $ff) {
            if (is_dir($dir . '/' . $ff)) {
                $subfile = $this->listFolderFiles($dir . '/' . $ff);
                $files = array_merge($files, $subfile);
            } else {
                $files[] = $dir . '/' .  $ff;
            }
        }
        return $files;
    }



    public function data_nhanvien()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/data/nhanvien';

        echo "<pre>";
        echo $dir;
        $this->load->model("employeeresult_model");
        $this->load->model("employee_model");
        $this->load->model("area_model");

        //echo '<pre>';
        //print_r($area);
        die();
        $insert = array();

        $sortedarray1 = $this->listFolderFiles($dir);
        $sortedarray1 = array_values($sortedarray1);

        //echo "<pre>";
        //print_r($sortedarray1);
        //die();
        $analytics = array(
            'new_nhanvien' => array(),
            'miss_row' => array(),
            'success' => 0
        );
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $file_name;
            $name_file = explode("/", $file);
            $name_file = end($name_file);
            $area_id = explode("_", $name_file);
            $area_id = $area_id[0];
            //echo '<pre>';
            //print_r($area_id);
            //die();
            //$area_id =
            $area = $this->area_model->where(array('id' => $area_id))->as_array()->get();
            //Tiến hành xác thực file
            $objFile = PHPExcel_IOFactory::identify($file);
            $objData = PHPExcel_IOFactory::createReader($objFile);

            //Chỉ đọc dữ liệu
            //$objData->setReadDataOnly(true);
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
                $fisrt = 8;
                for ($i = $fisrt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i -  $fisrt][$j] = $cell->getCalculatedValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i -  $fisrt][$j] instanceof PHPExcel_RichText) {
                            $data[$i -  $fisrt][$j] = $data[$i -  $fisrt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i -  $fisrt][$j] > 0) {

                            if (is_numeric($data[$i -  $fisrt][$j])) {
                                $data[$i -  $fisrt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i -  $fisrt][$j]));
                            } else if ($data[$i -  $fisrt][$j] == '26/09/16') {
                                $data[$i -  $fisrt][$j] = '2016-09-26';
                            }
                        }
                    }
                }
                //echo "<pre>";
                //print_r($data);
                //die();
                //$nhanvien_string_id = $sheet->getCellByColumnAndRow(5, 6)->getValue();
                //$area_string = $sheet->getCellByColumnAndRow(2, 7)->getValue();
                // echo $nhanvien_string_id . "<br>" . $area_string;

                //if (!isset($temp_area[$area_string])) {
                //    continue;
                //}
                //$area = $temp_area[$area_string];
                //$nhan_vien = $this->employee_model->where(array('string_id' => $nhanvien_string_id))->as_object()->get();

                //// echo "<pre>";
                //// print_r($nhan_vien);
                //if (empty($nhan_vien)) {
                //    continue;
                //}

                // $position_H = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_H"))->as_object()->get();
                // $position_N = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_N"))->as_object()->get();
                // $position_C = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_C"))->as_object()->get();
                // $position_LF = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_LF"))->as_object()->get();
                // $position_RF = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_RF"))->as_object()->get();
                // $position_LG = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_LG"))->as_object()->get();
                // $position_RG = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_RG"))->as_object()->get();

                // die();
                ///LIST POSTION
                //$list_position = array_shift($data);
                ///XOA 1 ROW
                //array_shift($data);

                // echo "<pre>";
                //        print_r($positions);
                // print_r($data);
                // die();
                // print_r($temp_phong);
                // die();
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    $nhanvien_string_id = $row[0];
                    if (!is_numeric($nhanvien_string_id)) {
                        continue;
                    }
                    $nhanvien_name = $row[1];

                    $nhan_vien = $this->employee_model->where(array('string_id' => $nhanvien_string_id))->as_object()->get();
                    //print_r($nh)
                    if (empty($nhan_vien)) {
                        $employee = array(
                            'string_id' => $nhanvien_string_id,
                            'name' => $nhanvien_name
                        );
                        $id = $this->employee_model->insert($employee);

                        $nhan_vien = $this->employee_model->where(array('id' => $id))->as_object()->get();
                        $analytics['new_nhanvien'][] = $employee;
                    }

                    $date = $row[2];
                    $head = $row[3];
                    $nose = $row[4];
                    $chest = $row[5];
                    $lf = $row[6];
                    $rf = $row[7];
                    $lg = $row[8];
                    $rg = $row[9];

                    if (!is_Date($date)) {
                        continue;
                        $analytics['new_nhanvien'][] = $row;
                    }
                    $analytics['success']++;
                    $data_up = array(
                        'employee_id' => $nhan_vien->id,
                        'area_id' => $area['id'],
                        'factory_id' => $area['factory_id'],
                        'workshop_id' => $area['workshop_id'],
                        'from_file' => $sheet_name,
                        'created_at' => date("Y-m-d H:i:s"),
                        'date' => $date,
                        'value_H' => $head,
                        'value_N' => $nose,
                        'value_C' => $chest,
                        'value_LF' => $lf,
                        'value_RF' => $rf,
                        'value_LG' => $lg,
                        'value_RG' => $rg
                    );
                    $insert[] = $data_up;
                }
            }
        }
        print_r($analytics);
        $this->employeeresult_model->insert($insert);
    }

    public function data_tieuphan()
    {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
        //        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/data/tieuphan/';

        echo "<pre>";
        echo $dir;

        $this->load->model("result_model");
        $this->load->model("position_model");
        $this->load->model("target_model");
        // $area = $this->area_model->where(array('id' => 76))->as_array()->get();

        //echo '<pre>';
        //print_r($area);
        //die();
        $insert = array();
        $sortedarray1 = array_values(array_diff(scandir($dir), array('..', '.')));
        //echo "<pre>";
        //print_r($sortedarray1);
        //die();
        foreach ($sortedarray1 as $file_name) {
            //            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $dir . $file_name;

            //Tiến hành xác thực file
            $objFile = PHPExcel_IOFactory::identify($file);
            $objData = PHPExcel_IOFactory::createReader($objFile);

            //Chỉ đọc dữ liệu
            //$objData->setReadDataOnly(true);
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
                $fisrt = 9;
                for ($i = $fisrt; $i <= $Totalrow; $i++) {
                    //----Lặp cột
                    for ($j = 0; $j < $TotalCol; $j++) {
                        // Tiến hành lấy giá trị của từng ô đổ vào mảng
                        $cell = $sheet->getCellByColumnAndRow($j, $i);

                        $data[$i -  $fisrt][$j] = $cell->getValue();
                        ///CHUYEN RICH TEXT
                        if ($data[$i -  $fisrt][$j] instanceof PHPExcel_RichText) {
                            $data[$i -  $fisrt][$j] = $data[$i -  $fisrt][$j]->getPlainText();
                        }
                        ////CHUYEN DATE 
                        if (PHPExcel_Shared_Date::isDateTime($cell) && $data[$i -  $fisrt][$j] > 0) {

                            if (is_numeric($data[$i -  $fisrt][$j])) {
                                $data[$i -  $fisrt][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i -  $fisrt][$j]));
                            } else if ($data[$i -  $fisrt][$j] == '26/09/16') {
                                $data[$i -  $fisrt][$j] = '2016-09-26';
                            }
                        }
                    }
                }
                // echo "<pre>";
                // print_r($data);
                // die();
                //$nhanvien_string_id = $sheet->getCellByColumnAndRow(5, 6)->getValue();
                //$area_string = $sheet->getCellByColumnAndRow(2, 7)->getValue();
                // echo $nhanvien_string_id . "<br>" . $area_string;

                //if (!isset($temp_area[$area_string])) {
                //    continue;
                //}
                //$area = $temp_area[$area_string];
                //$nhan_vien = $this->employee_model->where(array('string_id' => $nhanvien_string_id))->as_object()->get();

                //// echo "<pre>";
                //// print_r($nhan_vien);
                //if (empty($nhan_vien)) {
                //    continue;
                //}

                // $position_H = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_H"))->as_object()->get();
                // $position_N = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_N"))->as_object()->get();
                // $position_C = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_C"))->as_object()->get();
                // $position_LF = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_LF"))->as_object()->get();
                // $position_RF = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_RF"))->as_object()->get();
                // $position_LG = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_LG"))->as_object()->get();
                // $position_RG = $this->position_model->where(array('string_id' => "NV_" . $nhanvien_string_id . "_" . $area_string . "_RG"))->as_object()->get();

                // die();
                ///LIST POSTION
                //$list_position = array_shift($data);
                ///XOA 1 ROW
                //array_shift($data);

                // echo "<pre>";
                //        print_r($positions);
                // print_r($data);
                // die();
                // print_r($temp_phong);
                // die();
                $analytics = array(
                    'miss_row' => array(),
                    'success' => 0
                );
                $targets = array(
                    (object) array(
                        'id' => 17,
                        'name' => 'Trạng thái tĩnh',
                        'name_en' => "At rest condition",
                        'col' => 4,
                        'type_data' => "float",
                    ),
                    (object) array(
                        'id' => 16,
                        'name' => 'Trạng thái động',
                        'name_en' => "At operation  condition",
                        'has_data' => 1,
                        'col' => 5,
                        'type_data' => "float",
                    ), (object) array(
                        'id' => 15,
                        'name' => 'Trạng thái tĩnh',
                        'name_en' => "At rest condition",
                        'has_data' => 1,
                        'col' => 6,
                        'type_data' => "float",
                    ),
                    (object) array(
                        'id' => 14,
                        'name' => 'Trạng thái động',
                        'name_en' => "At operation  condition",
                        'has_data' => 1,
                        'col' => 7,
                        'type_data' => "float",
                    ),
                );
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    $p_string_id = $row[2];
                    // $nhanvien_name = $row[1];
                    if ($p_string_id == "") {
                        continue;
                    }
                    $position = $this->position_model->where(array('string_id' => $p_string_id))->as_object()->get();
                    //print_r($nh)
                    if (empty($position)) {
                        $analytics['miss_row'][] = $p_string_id;
                    }

                    $date = $row[3];
                    foreach ($targets as $target) {
                        if (strlen($row[$target->col]) > 0) {
                            $value = $row[$target->col];
                        } else {
                            continue;
                            $analytics['new_nhanvien'][] = $row;
                        }
                        if (is_null($value) || !is_Date($date) || !is_numeric($value)) {
                            continue;
                            $analytics['new_nhanvien'][] = $row;
                        }
                        $date = date("Y-m-d", strtotime($date));
                        $max_stt = $this->result_model->max_stt_have_target_in_day($position->id, $date, $target->id);
                        // $data['stt_in_day'] = $max_stt;
                        $data_up = array(
                            'value' => $value,
                            'position_id' => $position->id,
                            'area_id' => $position->area_id,
                            'department_id' => $position->department_id,
                            'target_id' => $target->id,
                            'factory_id' => $position->factory_id,
                            'workshop_id' => $position->workshop_id,
                            'date' => $date,
                            'create_at' => date("Y-m-d"),
                            'from_file' => $sheet_name,
                            'object_id' => $position->object_id,
                            'type_bc' => $position->type_bc,
                            'stt_in_day' => $max_stt
                            //                        'w,orkshop_id' => $area['workshop_id'],
                            //                        'factory_id' => $area['factory_id']
                        );
                        $analytics['success']++;
                        $this->result_model->insert($data_up);
                    }
                }
            }
        }
        print_r($analytics);
    }
}
