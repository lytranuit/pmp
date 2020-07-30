<?php

class Diagram extends MY_Controller
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

    public function getbyparent($params)
    {
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;

        $id = isset($params[0]) ? $params[0] : null;
        if (!is_numeric($id)) {
            echo json_encode(array());
            die();
        }
        $this->load->model("diagram_model");
        $data = $this->diagram_model->where(array("deleted" => 0, 'workshop_id' => $id))->as_object()->get_all();
        echo json_encode($data);
    }
    public function add()
    { /////// trang ca nhan
        if (isset($_POST['dangtin'])) {
            $data = $_POST;
            $this->load->model("diagram_model");
            $this->load->model("diagram_position_model");
            $this->load->model("diagram_image_model");
            $data_up = $this->diagram_model->create_object($data);
            $id = $this->diagram_model->insert($data_up);

            $this->diagram_position_model->where(array('diagram_id' => $id))->delete();
            if (isset($data['positions']) && isset($data['positions'])) {
                foreach ($data['positions'] as $key => $row) {
                    $array = array(
                        'diagram_id' => $id,
                        'position_id' => $row
                    );
                    $this->diagram_position_model->insert($array);
                }
            }
            /*
             * Image_other
             */
            $this->diagram_image_model->where(array('diagram_id' => $id))->delete();
            if (isset($data['images'])) {
                foreach ($data['images'] as $row) {
                    $array = array(
                        'diagram_id' => $id,
                        'image_id' => $row
                    );
                    $this->diagram_image_model->insert($array);
                }
                // die();
            }


            /// Log audit trail
            $this->diagram_model->trail($id, "insert", null, $data_up, null, null);

            redirect('diagram', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {

            load_chossen($this->data);
            load_datatable($this->data);
            $this->load->model("position_model");
            $this->data['positions'] = $this->position_model->where(array('deleted' => 0))->as_object()->get_all();
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function edit($param)
    { /////// trang ca nhan
        $id = $param[0];
        if (isset($_POST['dangtin'])) {
            $this->load->model("diagram_model");
            $this->load->model("diagram_position_model");
            $this->load->model("diagram_image_model");
            //old
            $data_prev = $this->diagram_model->where('id', $id)->as_array()->get();

            $data = $_POST;
            $data_up = $this->diagram_model->create_object($data);
            $this->diagram_model->update($data_up, $id);

            $this->diagram_position_model->where(array('diagram_id' => $id))->delete();
            if (isset($data['positions']) && isset($data['positions'])) {
                foreach ($data['positions'] as $key => $row) {
                    $array = array(
                        'diagram_id' => $id,
                        'position_id' => $row
                    );
                    $this->diagram_position_model->insert($array);
                }
            }

            /*
             * Image_other
             */
            $this->diagram_image_model->where(array('diagram_id' => $id))->delete();
            if (isset($data['images'])) {
                foreach ($data['images'] as $row) {
                    $array = array(
                        'diagram_id' => $id,
                        'image_id' => $row
                    );
                    $this->diagram_image_model->insert($array);
                }
                // die();
            }
            // die();

            /// Log audit trail
            $this->diagram_model->trail($id, "update", null, $data_up, $data_prev, null);
            redirect('diagram', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {
            $this->load->model("diagram_model");
            $tin = $this->diagram_model->where(array('id' => $id))->with_positions()->with_images()->as_object()->get();
            $users_groups = (array) $tin->positions;
            $tin->positions = array_keys($users_groups);
            $tin->images = array_values((array) $tin->images);
            // echo "<pre>";
            // print_r($tin);
            // die();
            $this->data['tin'] = $tin;


            load_chossen($this->data);
            load_datatable($this->data);
            $this->load->model("position_model");
            $this->data['positions'] = $this->position_model->where(array('deleted' => 0))->as_object()->get_all();
            //            load_chossen($this->data);
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function remove($params)
    { /////// trang ca nhan
        $this->load->model("diagram_model");
        $this->load->model("diagram_position_model");
        $this->load->model("diagram_image_model");
        $id = $params[0];
        $this->diagram_model->update(array("deleted" => 1), $id);
        $this->diagram_position_model->where(array("diagram_id" => $id))->delete();
        $this->diagram_image_model->where(array("diagram_id" => $id))->delete();


        /// Log audit trail
        $this->diagram_model->trail($id, "delete", null, null, null, null);

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function table()
    {
        $this->load->model("diagram_model");
        $limit = $this->input->post('length');
        $start = $this->input->post('start');
        $page = ($start / $limit) + 1;
        $where = $this->diagram_model;

        $totalData = $where->count_rows();
        $totalFiltered = $totalData;

        if (empty($this->input->post('search')['value'])) {
            //            $max_page = ceil($totalFiltered / $limit);

            $where = $this->diagram_model->where(array("deleted" => 0));
        } else {
            $search = $this->input->post('search')['value'];
            $sWhere = "deleted = 0 and (name like '%" . $search . "%')";
            $where = $this->diagram_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
            $totalFiltered = $where->count_rows();
            $where = $this->diagram_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
        }

        $posts = $where->order_by("id", "DESC")->paginate($limit, NULL, $page);
        //        echo "<pre>";
        //        print_r($posts);
        //        die();
        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $nestedData['id'] = $post->id;
                $nestedData['name'] = $post->name;
                $nestedData['action'] = '<a href="' . base_url() . 'diagram/edit/' . $post->id . '" class="btn btn-warning btn-sm mr-2" title="edit">'
                    . '<i class="fas fa-pencil-alt">'
                    . '</i>'
                    . '</a>'
                    . '<a href="' . base_url() . 'diagram/remove/' . $post->id . '" class="btn btn-danger btn-sm" data-type="confirm" title="remove">'
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
