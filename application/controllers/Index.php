<?php

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class Index extends MY_Controller {

    function __construct() {
        parent::__construct();
        ////////////////////////////////
        ////////////
        $this->data['is_login'] = $this->ion_auth->logged_in();
        $this->data['userdata'] = $this->session->userdata();
        $version = $this->config->item("version");
        $this->data['stylesheet_tag'] = array(
            base_url() . 'public/assets/bootstrap.css',
            base_url() . 'public/assets/style.css?v=' . $version,
            base_url() . "public/admin/vendor/fonts/fontawesome/css/fontawesome-all.css"
        );
        $this->data['javascript_tag'] = array(
            base_url() . 'public/scripts/assets/jquery.min.js',
            base_url() . 'public/lib/bootstrap/js/bootstrap.bundle.min.js',
            base_url() . "public/assets/scripts/jquery.cookies.2.2.0.min.js",
            base_url() . "public/assets/core.min.js",
            base_url() . "public/lib/easing/easing.min.js"
        );

        $this->load->model("option_model");
        $print_bill = $this->option_model->where(array('key' => 'print_bill'))->as_object()->get();
        $this->data['is_print_bill'] = !$print_bill || $print_bill->value == '1' ? 1 : 0;
    }

    public function _remap($method, $params = array()) {
        if (!method_exists($this, $method)) {
            show_404();
        }
        $this->$method($params);
    }

    public function listall() {
        //echo __DIR__;
        $dirmodule = APPPATH . 'modules/';
        $dir = APPPATH . 'controllers/';
        $this->load->library('directoryinfo');
        $sortedarray1 = $this->directoryinfo->readDirectory($dir, true);
        $sortedarray2 = $this->directoryinfo->readDirectory($dirmodule, true);
        $arr = array_merge(array($sortedarray1), $sortedarray2);
    }

    public function page_404() {
        echo $this->blade->view()->make('page/404-page', $this->data)->render();
    }

    public function delete_img() {
        $this->load->model("hinhanh_model");
        $hinh = $this->hinhanh_model->hinhanh_sudung();
        $this->hinhanh_model->delete_img_not($hinh[0]['id']);
        echo "<pre>";
        print_r($hinh);
        die();
    }

    function printbillnormal() {
        try {
            // Enter the share name for your USB printer here
            //            $connector = null;
            $connector = new WindowsPrintConnector("Receipt Printer");

            /* Print a "Hello world" receipt" */
            $printer = new Printer($connector);
            $printer->text("Hello World!\n");
            $printer->cut();

            /* Close printer */
            $printer->close();
        } catch (Exception $e) {
            echo "Couldn't print to this printer: " . $e->getMessage() . "\n";
        }
    }

    function printbill() {
        $id = $this->input->get('id');
        $this->load->model("saleorder_model");
        $tin = $this->saleorder_model->where(array('id' => $id))->with_details()->as_object()->get();
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
        $this->data['cart'] = $tin;
        $pdf->allow_charset_conversion = true;  // Set by default to TRUE
        $pdf->charset_in = 'UTF-8';
        //   $pdf->SetDirectionality('rtl');
        $pdf->autoLangToFont = true;
        $html = $this->blade->view()->make('pdf/bill', $this->data)->render();

        // render the view into HTML
        $pdf->WriteHTML($html);
        // write the HTML into the PDF
        $output = 'itemreport' . date('Y_m_d_H_i_s') . '_.pdf';
        $pdf->Output("$output", 'I');
        // save to file because we can exit();
        // - See more at: http://webeasystep.com/blog/view_article/codeigniter_tutorial_pdf_to_create_your_reports#sthash.QFCyVGLu.dpuf
    }

    public function index() {
        redirect("dashboard", "refresh");
        //        
        //        //////////
        //        $this->load->model("category_model");
        //        $this->load->model("product_model");
        //        $this->data['category'] = $this->category_model->where(array("deleted" => 0, 'is_home' => 1, 'active' => 1, 'parent_id' => 0))->order_by('sort', "ASC")->as_array()->get_all();
        //        foreach ($this->data['category'] as &$row23) {
        //            $row23['products'] = $this->product_model->get_product_by_category($row23['id']);
        //        }
        ////        echo "<pre>";
        ////        print_r($this->data['category']);
        ////        die();
        //
        //        $version = $this->config->item("version");
        //        array_push($this->data['javascript_tag'], base_url() . "public/js/main.js?v=" . $version);
        //        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    public function test1() {
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

    public function test2() {
        set_time_limit(-1);
        require_once APPPATH . 'third_party/PHPEXCEL/PHPExcel.php';
        //Đường dẫn file
//        $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
        $dir = APPPATH . '../public/upload/data/';
        echo $dir;
        $this->load->model("result_model");
        $insert = array();
        $sortedarray1 = array_values(array_diff(scandir($dir), array('..', '.')));
        foreach ($sortedarray1 as $file) {
//            $file = APPPATH . '../public/upload/data_visinh/1.xlsx';
            $file = $dir . $file;
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
                        if (PHPExcel_Shared_Date::isDateTime($cell)) {
                            $data[$i - 11][$j] = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($data[$i - 11][$j]));
                        }
                    }
                }
//                echo "<pre>";
//                print_r($data);
//                die();
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
//        print_r($data);
//        die();
                // print_r($temp_phong);
                // die();
                for ($i = 0; $i < count($data); $i++) {
                    $row = $data[$i];
                    foreach ($positions as $position) {
                        $position_id = $position->id;
                        $area_id = $position->area_id;
                        $department_id = $position->department_id;
                        $target_id = $position->target_id;
                        $date = $row[1];
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
                            'date' => $date,
                            'create_at' => date("Y-m-d")
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

    public function service() {
        if (!$this->ion_auth->is_admin()) {
            redirect("index/login", "refresh");
            return;
        }
        $this->load->model("category_model");
        $this->load->model("product_model");
        $this->load->model("table_model");
        $this->data['table'] = $this->table_model->where(array("deleted" => 0))->as_array()->get_all();
        $this->data['category'] = $this->category_model->where(array("deleted" => 0, 'is_home' => 1, 'active' => 1, 'parent_id' => 0))->order_by('sort', "ASC")->as_array()->get_all();
        foreach ($this->data['category'] as &$row23) {
            $row23['products'] = $this->product_model->get_product_by_category($row23['id']);
        }
        //        echo "<pre>";
        //        print_r($this->data['category']);
        //        die();

        $version = $this->config->item("version");
        array_push($this->data['javascript_tag'], base_url() . "public/js/service.js?v=" . $version);
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    function login() {
        $this->data['title'] = lang('login');
        if ($this->input->post('identity') != "" && $this->input->post('password') != "") {
            // check to see if the user is logging in
            // check for "remember me"
            $remember = (bool) $this->input->post('remember');
            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                redirect('/dashboard', 'refresh');
            } else {
                // if the login was un-successful
                // redirect them back to the login page
                $this->session->set_flashdata('message', lang('alert_501'));
                redirect('index/login', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
            }
        } else {
            // the user is not logging in so display the login page
            // set the flash data error message if there is one
            $this->data['message'] = $this->session->flashdata('message');
            echo $this->blade->view()->make('page/login', $this->data)->render();
        }
    }

    function cart() {
        $this->data['cart'] = sync_cart();
        //        $this->data['stylesheet_tag'] = array();
        array_push($this->data['stylesheet_tag'], base_url() . "public/assets/checkout.css");

        //        echo "<pre>";
        //        print_r($this->data['']);
        //        die();
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    function checkout() {
        $this->data['cart'] = sync_cart();
        $this->load->model("user_model");
        //        $this->data['stylesheet_tag'] = array();
        array_push($this->data['stylesheet_tag'], base_url() . "public/assets/checkout.css");

        //        echo "<pre>";
        //        print_r($this->data['userdata']);
        //        die();
        if (!isset($this->data['userdata']['user_id'])) {
            $this->data['userdata']['user_id'] = 0;
            $this->data['userdata']['name'] = "";
            $this->data['userdata']['email'] = "";
            $this->data['userdata']['address'] = "";
            $this->data['userdata']['phone'] = "";
        } else {
            $user_id = $this->data['userdata']['user_id'];
            $this->data['userdata'] = $this->user_model->where(array("id" => $user_id))->as_array()->get();
            $this->data['userdata']['user_id'] = $user_id;
            $this->data['userdata']['name'] = $this->data['userdata']['last_name'];
        }
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    function complete() {
        $cart = sync_cart();
        if (isset($_POST) && count($_POST) && count($cart['details'])) {
            $this->load->model("saleorder_model");
            $this->load->model("saleorderline_model");
            $array = array(
                'order_date' => date("Y-m-d H:i:s"),
                'customer_name' => $_POST['name'],
                'customer_phone' => $_POST['phone'],
                'customer_email' => $_POST['email'],
                'customer_address' => $_POST['address'],
                'user_id' => $_POST['user_id'],
                'notes' => $_POST['notes'],
                'amount' => $cart['amount_product'],
                'total_amount' => $cart['amount_product']
            );
            $order_id = $this->saleorder_model->insert($array);
            foreach ($cart['details'] as $row) {
                $data_up = array(
                    'order_id' => $order_id,
                    'product_id' => $row['product_id'],
                    'image_url' => $row['image_url'],
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'quantity' => $row['qty'],
                    'price' => $row['price'],
                    'amount' => $row['qty'] * $row['price']
                );
                $this->saleorderline_model->insert($data_up);
            }
            /*
             * NEW DEBT
             */
            if ($_POST['user_id'] > 0) {
                $user_id = $_POST['user_id'];
                $this->load->model("debtpaid_model");
                $this->load->model("user_model");
                $data['date'] = time();
                $data['user_id'] = $user_id;
                $data['paid_amount'] = 0 - $cart['amount_product'];
                $data['note'] = "Đơn hàng #$order_id";
                $data_up = $this->debtpaid_model->create_object($data);
                $this->debtpaid_model->insert($data_up);
                $tin = $this->user_model->where(array('id' => $user_id))->with_paids()->as_object()->get();
                $total_paid_amount = 0;
                if ($tin->paids) {
                    foreach ($tin->paids as $row) {
                        $total_paid_amount += $row->paid_amount;
                    }
                }
                $data['debt'] = $total_paid_amount;
                $data_up = $this->user_model->create_object($data);
                $this->user_model->update($data_up, $user_id);
            }
            /////////////////
            $this->data['cart'] = $cart;
            $this->load->helper('cookie');
            delete_cookie("CART");
            array_push($this->data['stylesheet_tag'], base_url() . "public/assets/checkout.css");
            echo $this->blade->view()->make('page/page', $this->data)->render();
        } else {
            redirect("index", 'refresh');
        }
    }

    // log the user out
    function logout() {

        $this->data['title'] = "Logout";

        // log the user out
        $logout = $this->ion_auth->logout();

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function success() {
        echo json_encode(1);
    }

}
