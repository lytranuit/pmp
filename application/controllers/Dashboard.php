<?php

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

    public function index() { /////// trang ca nhan
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

}
