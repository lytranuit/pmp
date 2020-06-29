<?php

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class Index extends MY_Controller
{

    function __construct()
    {
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
    }

    public function _remap($method, $params = array())
    {
        if (!method_exists($this, $method)) {
            show_404();
        }
        $this->$method($params);
    }

    public function listall()
    {
        //echo __DIR__;
        $dirmodule = APPPATH . 'modules/';
        $dir = APPPATH . 'controllers/';
        $this->load->library('directoryinfo');
        $sortedarray1 = $this->directoryinfo->readDirectory($dir, true);
        $sortedarray2 = $this->directoryinfo->readDirectory($dirmodule, true);
        $arr = array_merge(array($sortedarray1), $sortedarray2);
    }

    public function page_404()
    {
        echo $this->blade->view()->make('page/404-page', $this->data)->render();
    }

    public function delete_img()
    {
        $this->load->model("hinhanh_model");
        $hinh = $this->hinhanh_model->hinhanh_sudung();
        $this->hinhanh_model->delete_img_not($hinh[0]['id']);
        echo "<pre>";
        print_r($hinh);
        die();
    }

    function printbillnormal()
    {
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

    function printbill()
    {
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

    public function index()
    {
        redirect("report", "refresh");
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

    function login()
    {
        $this->load->model("user_model");
        $this->data['title'] = lang('login');
        if ($this->input->post('identity') != "" && $this->input->post('password') != "") {
            // check to see if the user is logging in
            // check for "remember me"
            $remember = (bool) $this->input->post('remember');
            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                /// Log audit trail
                $text =   "USER '" . $this->session->userdata('username') . "' login successfully!";
                $this->user_model->trail(1, "insert", null, null, null, $text);

                redirect('/report', 'refresh');
            } else {
                // if the login was un-successful
                // redirect them back to the login page
                $text =   $this->input->post('identity') . " login failed!";
                $this->user_model->trail(1, "insert", null, null, null, $text);

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

    // log the user out
    function logout()
    {

        $this->load->model("user_model");
        $this->data['title'] = "Logout";

        $text =   "USER '" . $this->session->userdata('username') . "' logout!";
        $this->user_model->trail(1, "insert", null, null, null, $text);

        // log the user out
        $logout = $this->ion_auth->logout();

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function success()
    {
        echo json_encode(1);
    }
}
