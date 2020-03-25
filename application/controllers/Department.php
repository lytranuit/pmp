<?php

class Department extends MY_Controller {

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
        load_datatable($this->data);
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    public function add() { /////// trang ca nhan
        if (isset($_POST['dangtin'])) {
            $data = $_POST;
            $this->load->model("department_model");
            $data_up = $this->department_model->create_object($data);
            $id = $this->department_model->insert($data_up);

            redirect('department', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {

            $this->load->model("area_model");
            $this->data['areas'] = $this->area_model->where(array('deleted' => 0))->as_object()->get_all();
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function edit($param) { /////// trang ca nhan
        $id = $param[0];
        if (isset($_POST['dangtin'])) {
            $this->load->model("department_model");
            $data = $_POST;
            $data_up = $this->department_model->create_object($data);
            $this->department_model->update($data_up, $id);
            redirect('department', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {
            $this->load->model("department_model");
            $tin = $this->department_model->where(array('id' => $id))->as_object()->get();
            $this->data['tin'] = $tin;

            $this->load->model("area_model");
            $this->data['areas'] = $this->area_model->where(array('deleted' => 0))->as_object()->get_all();
//            load_chossen($this->data);
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function remove($params) { /////// trang ca nhan
        $this->load->model("department_model");
        $id = $params[0];
        $this->department_model->update(array("deleted" => 1), $id);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function table() {
        $this->load->model("department_model");
        $limit = $this->input->post('length');
        $start = $this->input->post('start');
        $page = ($start / $limit) + 1;
        $where = $this->department_model;

        $totalData = $where->count_rows();
        $totalFiltered = $totalData;

        if (empty($this->input->post('search')['value'])) {
//            $max_page = ceil($totalFiltered / $limit);

            $where = $this->department_model->where(array("deleted" => 0));
        } else {
            $search = $this->input->post('search')['value'];
            $sWhere = "deleted = 0 and (name like '%" . $search . "%')";
            $where = $this->department_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
            $totalFiltered = $where->count_rows();
            $where = $this->department_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
        }

        $posts = $where->order_by("id", "DESC")->with_area()->paginate($limit, NULL, $page);
//        echo "<pre>";
//        print_r($posts);
//        die();
        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $area = $post->area;
                $nestedData['string_id'] = $post->string_id;
                $nestedData['name'] = $post->name;
                $nestedData['area_name'] = $area->name;
                $nestedData['action'] = '<a href="' . base_url() . 'department/edit/' . $post->id . '" class="btn btn-warning btn-sm mr-2" title="edit">'
                        . '<i class="fas fa-pencil-alt">'
                        . '</i>'
                        . '</a>'
                        . '<a href="' . base_url() . 'department/remove/' . $post->id . '" class="btn btn-danger btn-sm" data-type="confirm" title="remove">'
                        . '<i class="far fa-trash-alt">'
                        . '</i>'
                        . '</a>';

                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw" => intval($this->input->post('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        );

        echo json_encode($json_data);
    }

}
