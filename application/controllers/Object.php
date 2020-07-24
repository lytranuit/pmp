<?php

class Object extends MY_Controller
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
            base_url() . 'public/lib/jquery-ui/jquery-ui.js',
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

    public function add()
    { /////// trang ca nhan
        if (isset($_POST['dangtin'])) {
            $data = $_POST;
            $this->load->model("object_model");
            $data_up = $this->object_model->create_object($data);
            $id = $this->object_model->insert($data_up);

            /// Log audit trail
            $text =   "USER '" . $this->session->userdata('username') . "' added a new object";
            $this->object_model->trail($id, "insert", null, $data_up, null, $text);
            redirect('object', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {

            $this->load->model("target_model");
            $this->data['targets'] = $this->target_model->where(array('deleted' => 0))->with_parent()->as_object()->get_all();
            // load_chossen($this->data);
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function edit($param)
    { /////// trang ca nhan
        $id = $param[0];
        if (isset($_POST['dangtin'])) {
            $this->load->model("object_model");
            $data = $_POST;
            $data_up = $this->object_model->create_object($data);
            $this->object_model->update($data_up, $id);
            $this->load->model("objecttarget_model");
            // $this->objecttarget_model->where(array('object_id' => $id))->delete();
            // echo "<pre>";
            // print_r($data['targets']);
            // print_r($data['parents']);
            // die();
            if (isset($data['targets']) && isset($data['parents'])) {
                foreach ($data['targets'] as $key => $row) {
                    $parent_id = isset($data['parents'][$key]) ? $data['parents'][$key] : 0;
                    $array = array(
                        'order' => $key,
                        'parent_id' => $parent_id
                    );
                    $this->objecttarget_model->update($array, $row);
                }
            }


            /// Log audit trail
            $data_prev = $this->object_model->where('id', $id)->as_array()->get();
            $text =   "USER '" . $this->session->userdata('username') . "' edited a object";
            $this->object_model->trail($id, "update", null, $data_up, $data_prev, $text);

            redirect('object', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {
            $this->load->model("object_model");
            $tin = $this->object_model->where(array('id' => $id))->with_targets()->as_object()->get();

            $object_frequency = (array) $tin->targets;
            $tin->targets = array_keys($object_frequency);
            $this->data['tin'] = $tin;

            // $this->load->model("area_model");
            // $this->data['areas'] = $this->area_model->where(array('deleted' => 0))->as_object()->get_all();

            $this->load->model("objecttarget_model");
            $this->load->model("target_model");

            $this->data['targets'] = $this->target_model->where(array('deleted' => 0))->as_array()->get_all();
            $object_target = $this->objecttarget_model->where("object_id", $id)->order_by("order", "ASC")->with_target()->as_array()->get_all();

            // echo "<pre>";
            // print_r($object_target);
            // die();
            load_chossen($this->data);
            load_sort_nest($this->data);
            $this->data['html_nestable'] = $this->html_nestable((array) $object_target, 'parent_id', 0);

            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function remove($params)
    { /////// trang ca nhan
        $this->load->model("object_model");
        $id = $params[0];
        $this->object_model->update(array("deleted" => 1), $id);

        /// Log audit trail
        $text =   "USER '" . $this->session->userdata('username') . "' removed a user ";
        $this->object_model->trail($id, "delete", null, null, null, $text);

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    public function add_target()
    {
        $data = $_POST;
        $this->load->model("objecttarget_model");
        $this->objecttarget_model->insert($data);
        echo 1;
    }
    public function remove_target()
    {
        $data = $_POST;
        $id = $data['id'];

        $this->load->model("objecttarget_model");
        $this->objecttarget_model->where("id", $id)->delete();
        echo 1;
    }
    private function html_nestable($array, $column, $parent, $controller = '')
    {
        // echo "<pre>";
        // print_r($array);
        // die();
        $html = "";
        $return = array_filter((array) $array, function ($item) use ($column, $parent) {
            return $item[$column] == $parent;
        });
        ///Bebin Tag
        if ($parent == 0) {
            $id_nestable = "id='nestable'";
        } else {
            $id_nestable = "";
        }
        $html .= '<ol class="dd-list" ' . $id_nestable . '>';
        ///Content
        foreach ($return as $row) {
            $sub_html = "";
            // if ($controller == "menu_header" || $controller == "menu_slide") {
            //     if ($row['type'] == 1) {
            //         $sub_html = "<span class='text-info mr-1'>[Link='" . $row['link'] . "']</span>";
            //     } elseif ($row['type'] == 2) {
            //         $sub_html = "<span class='text-success mr-1'>[Category='" . $row['category']->name_vi . "']</span>";
            //     } elseif ($row['type'] == 3) {
            //         $sub_html = "<span class='text-warning mr-1'>[Topic='" . $row['category']->name_vi . "']</span>";
            //     } elseif ($row['type'] == 4) {
            //         $sub_html = "<span class='text-primary mr-1'>[Khuyến mãi]</span>";
            //     } elseif ($row['type'] == 5) {
            //         $sub_html = "<span class='text-primary mr-1'>[Bài viết]</span>";
            //     }
            // }
            $html .= '<li class="dd-item" id="menuItem_' . $row['id'] . '" data-id="' . $row['id'] . '" data-target="' . $row['target_id'] . '">
                            <div class="dd-handle">
                             ' . $sub_html . '
                                <div>' . $row['target_id'] . " - " . $row['target']['name'] . '</div>
                                <div class="dd-nodrag btn-group ml-auto">
                                    <button class="btn btn-sm btn-outline-light dd-item-delete">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>';
            $html .= $this->html_nestable((array) $array, $column, $row['id'], $controller);
            $html .= '</li>';
        }
        ///End Tag
        $html .= '</ol>';

        return $html;
    }
    public function table()
    {
        $this->load->model("object_model");
        $limit = $this->input->post('length');
        $start = $this->input->post('start');
        $page = ($start / $limit) + 1;
        $where = $this->object_model;

        $totalData = $where->count_rows();
        $totalFiltered = $totalData;

        if (empty($this->input->post('search')['value'])) {
            //            $max_page = ceil($totalFiltered / $limit);

            $where = $this->object_model->where(array("deleted" => 0));
        } else {
            $search = $this->input->post('search')['value'];
            $sWhere = "deleted = 0 and (name like '%" . $search . "%')";
            $where = $this->object_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
            $totalFiltered = $where->count_rows();
            $where = $this->object_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
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
                $nestedData['action'] = '<a href="' . base_url() . 'object/edit/' . $post->id . '" class="btn btn-warning btn-sm mr-2" title="edit">'
                    . '<i class="fas fa-pencil-alt">'
                    . '</i>'
                    . '</a>'
                    . '<a href="' . base_url() . 'object/remove/' . $post->id . '" class="btn btn-danger btn-sm" data-type="confirm" title="remove">'
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
