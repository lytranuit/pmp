<?php

class Report extends MY_Controller
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

    public function index()
    { /////// trang ca nhan
        load_datatable($this->data);
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    public function table()
    {

        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $this->load->model("report_model");
        $limit = $this->input->post('length');
        $start = $this->input->post('start');
        $page = ($start / $limit) + 1;
        $where = $this->report_model->where(array("deleted" => 0, 'object_id' => $object_id));
        $totalData = $where->count_rows();
        $totalFiltered = $totalData;
        $where = $this->report_model->where(array("deleted" => 0, 'object_id' => $object_id));
        $posts = $where->order_by("id", "DESC")->with_object()->with_workshop()->with_user()->paginate($limit, NULL, $page);
        //        echo "<pre>";
        //        print_r($posts);
        //        die();
        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $list_url = explode(",", $post->name);
                $list_url = array_map(function ($item) {
                    $url = base_url() . "public/export/" . urlencode($item);
                    return '<a href="' . $url . '">' . $item . '</a>';
                }, $list_url);

                $nestedData['name'] = implode(",", $list_url);
                if ($this->ion_auth->is_admin()) {
                    $nestedData['id'] = '<a href="' . base_url() . "export/export/$post->id" . '" target="_blank">' . $post->id . '</a>';
                } else {
                    $nestedData['id'] = $post->id;
                }
                $nestedData['object_name'] = isset($post->object->name) ? $post->object->name : "";
                $nestedData['workshop_name'] = isset($post->workshop->name) ? $post->workshop->name . "<i class='d-block'>" . $post->workshop->name_en . "</i>" : "";
                $nestedData['date'] = $post->date;
                $nestedData['type'] = $post->type;
                $nestedData['selector'] = $post->selector;
                $status =  '<div class="spinner-border" style="width: 1rem;height: 1rem;"></div> Loading...';
                if ($post->status == 2) {
                    $status = '<div class="spinner-border" style="width: 1rem;height: 1rem;"></div> Processing...';
                } elseif ($post->status == 3) {
                    $status = '<span class="text-success" style="font-size:20px;"><i class="fas fa-check"></i></span>';
                }
                $nestedData['status'] = $status;
                $nestedData['user_name'] = isset($post->user->last_name) ? $post->user->last_name : "";
                //                $nestedData['action'] = '<a href="' . base_url() . 'result/remove/' . $post->id . '" class="btn btn-danger btn-sm" data-type="confirm" title="remove">'
                //                        . '<i class="far fa-trash-alt">'
                //                        . '</i>'
                //                        . '</a>';
                $nestedData['action'] = '';
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
