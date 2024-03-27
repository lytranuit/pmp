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
        // echo language_current();
        // die();
        // $this->session->keep_flashdata('message');
        if ($this->input->post('identity') != "" && $this->input->post('password') != "") {
            // check to see if the user is logging in
            // check for "remember me"
            $remember = (bool) $this->input->post('remember');
            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                /// Log audit trail
                $text =   "USER '" . $this->session->userdata('username') . "' login successfully!";
                $this->user_model->trail(1, "insert", null, null, null, $text);

                redirect('/dashboard/view', 'refresh');
            } else {
                // if the login was un-successful
                // redirect them back to the login page
                $text =   $this->input->post('identity') . " login failed!";
                $this->user_model->trail(1, "insert", null, null, null, $text);

                $_SESSION['message'] = $this->ion_auth->errors();

                // $this->data['message'] = $this->ion_auth->errors();
                // echo $this->blade->view()->make('page/login', $this->data)->render();
                redirect('index/login', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries

                // $this->session->keep_flashdata('message');
            }
        } else {
            // the user is not logging in so display the login page
            // set the flash data error message if there is one
            // print_r($this->session->flashdata('message'));
            // var_dump($_SESSION);
            // exit;
            $message = '';
            if (isset($_SESSION['message'])) {
                $message = $_SESSION['message'];
                unset($_SESSION['message']);
            }
            $this->data['message'] = $message;;

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
        // unset cookies
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time() - 1000);
                setcookie($name, '', time() - 1000, '/');
            }
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function success()
    {
        echo json_encode(1);
    }
}
