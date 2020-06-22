<?php

class Limit extends MY_Controller
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
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $this->data['object_id'] = $object_id;
        load_datatable($this->data);
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    public function add()
    { /////// trang ca nhan
        if (isset($_POST['dangtin'])) {
            $data = $_POST;
            $this->load->model("limit_model");
            $data_up = $this->limit_model->create_object($data);
            $id = $this->limit_model->insert($data_up);

            /// Log audit trail
            $text =   "USER '" . $this->session->userdata('username') . "' added a new record($id) to the table 'pmp_limit'";
            $this->limit_model->trail($id, "insert", null, $data_up, null, $text);
            redirect('limit', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {

            $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
            $this->load->model("factory_model");
            $this->data['factory'] = $this->factory_model->where(array('deleted' => 0))->as_object()->get_all();

            $this->load->model("workshop_model");
            $factory_id = isset($this->data['factory'][0]->id) ? $this->data['factory'][0]->id : 0;
            $this->data['workshop'] = $this->workshop_model->where(array('deleted' => 0, 'factory_id' => $factory_id))->as_object()->get_all();
            if ($object_id <= 17) {
                $this->load->model("area_model");
                $workshop_id = isset($this->data['workshop'][0]->id) ? $this->data['workshop'][0]->id : 0;
                $this->data['area'] = $this->area_model->where(array('deleted' => 0, 'workshop_id' => $workshop_id))->as_object()->get_all();
            } else {
                $this->load->model("system_model");
                $this->data['system'] = $this->system_model->where(array('deleted' => 0))->as_object()->get_all();
            }
            $this->load->model("objecttarget_model");

            $object_target = $this->objecttarget_model->where("object_id", $object_id)->order_by("order", "ASC")->with_target()->as_array()->get_all();
            $this->data['html_nestable_target'] = $this->html_nestable_target((array) $object_target, 'parent_id', 0);

            $this->data['object_id'] = $object_id;
            // print_r($object);
            // die();
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function edit($param)
    { /////// trang ca nhan
        $id = $param[0];
        if (isset($_POST['dangtin'])) {
            $this->load->model("limit_model");
            $prev_data = $this->limit_model->as_array()->get($id);
            $data = $_POST;
            $data_up = $this->limit_model->create_object($data);
            $status = $this->limit_model->update($data_up, $id);

            /// Log audit trail
            $text =   "USER '" . $this->session->userdata('username') . "' edited record($id) to the table 'pmp_limit'";
            $this->limit_model->trail($status, "update", null, $data_up, $prev_data, $text);
            redirect('limit', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {

            $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
            $this->load->model("limit_model");
            $tin = $this->limit_model->where(array('id' => $id))->as_object()->get();
            $this->data['tin'] = $tin;

            $this->load->model("factory_model");
            $this->data['factory'] = $this->factory_model->where(array('deleted' => 0))->as_object()->get_all();

            $this->load->model("workshop_model");
            $this->data['workshop'] = $this->workshop_model->where(array('deleted' => 0, 'factory_id' => $tin->factory_id))->as_object()->get_all();
            if ($object_id <= 17) {
                $this->load->model("area_model");
                $workshop_id = isset($this->data['workshop'][0]->id) ? $this->data['workshop'][0]->id : 0;
                $this->data['area'] = $this->area_model->where(array('deleted' => 0, 'workshop_id' => $workshop_id))->as_object()->get_all();
            } else {
                $this->load->model("system_model");
                $this->data['system'] = $this->system_model->where(array('deleted' => 0))->as_object()->get_all();
            }

            $this->load->model("objecttarget_model");

            $object_target = $this->objecttarget_model->where("object_id", $object_id)->order_by("order", "ASC")->with_target()->as_array()->get_all();
            $this->data['html_nestable_target'] = $this->html_nestable_target((array) $object_target, 'parent_id', 0);

            $this->data['object_id'] = $object_id;
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function remove($params)
    { /////// trang ca nhan
        $this->load->model("limit_model");
        $id = $params[0];
        $status = $this->limit_model->update(array("deleted" => 1), $id);
        /// Log audit trail
        $text =   "USER '" . $this->session->userdata('username') . "' removed record($id) to the table 'pmp_limit'";
        $this->limit_model->trail($status, "delete", null, null, $id, $text);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    private function html_nestable_target($array, $column, $parent, $deep = 0)
    {
        // echo "<pre>";
        // print_r($array);
        // die();
        $html = "";
        $return = array_filter((array) $array, function ($item) use ($column, $parent) {
            return $item[$column] == $parent;
        });
        ///Bebin Tag
        ///Content
        foreach ($return as $row) {
            $is_disabled = !$row['target']->has_data ? "disabled" : "";
            $sub_html = "";
            if ($deep > 0) {
                for ($i = 0; $i < $deep; $i++) {
                    $sub_html .= "-";
                }
            }
            $html .= '<option value="' . $row['target_id'] . '" ' . $is_disabled . ' data-type="' . $row['target']->type_data . '">' . $sub_html . " " . $row['target']->name . '</option>';
            $html .= $this->html_nestable_target((array) $array, $column, $row['id'], $deep + 1);
            // $html .= '</li>';
        }
        ///End Tag

        return $html;
    }
    public function table()
    {
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $this->load->model("object_model");
        $this->load->model("limit_model");
        $this->load->model("objecttarget_model");
        $limit = $this->input->post('length');
        $start = $this->input->post('start');
        $page = ($start / $limit) + 1;
        $object = $this->object_model->where("id", $object_id)->with_targets()->get();
        if (!empty($object->targets)) {
            $list = array_keys((array) $object->targets);
        } else {
            $json_data = array(
                "draw" => intval($this->input->post('draw')),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => array()
            );

            echo json_encode($json_data);
            die();
        }
        $sWhere = "deleted = 0 and target_id IN(" . implode(",", $list) . ")";
        $where = $this->limit_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
        $totalFiltered = $where->count_rows();
        $where = $this->limit_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
        //        echo "<pre>";
        //        print_r($object);
        //        die();
        //        if (empty($this->input->post('search')['value'])) {
        //            //            $max_page = ceil($totalFiltered / $limit);
        //
        //            $where = $this->limit_model->where(array("deleted" => 0));
        //            $totalFiltered = $where->count_rows();
        //            $where = $this->limit_model->where(array("deleted" => 0));
        //        } else {
        //            $search = $this->input->post('search')['value'];
        //            $sWhere = "deleted = 0";
        //            $where = $this->limit_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
        //            $totalFiltered = $where->count_rows();
        //            $where = $this->limit_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
        //        }

        $posts = $where->order_by("id", "DESC")->with_factory()->with_workshop()->with_system()->with_area()->with_target()->paginate($limit, NULL, $page);
        //        echo "<pre>";
        //        print_r($posts);
        //        die();
        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $target = $this->objecttarget_model->where(array("object_id" => $object_id, 'target_id' => $post->target_id))->with_parent(array("with" => array('relation' => 'target')))->get();
                if (!empty($target) && isset($target->parent->target->name)) {
                    $post->target->name .=  " (" . $target->parent->target->name . ")";
                }
                $nestedData['day_effect'] = $post->day_effect;
                $nestedData['target_name'] = isset($post->target->name) ? $post->target->name : "";

                $nestedData['workshop_name'] = isset($post->workshop->name) ? $post->workshop->name : "";
                $nestedData['factory_name'] = isset($post->factory->name) ? $post->factory->name : "";
                if ($object_id <= 17) {
                    $nestedData['area_name'] = isset($post->area->name) ? $post->area->name : "";
                } else {
                    $nestedData['area_name'] = isset($post->system->name) ? $post->system->name : "";
                }
                if ($post->target->type_data == "float") {
                    $nestedData['standard_limit'] = $post->standard_limit;
                    $nestedData['alert_limit'] = $post->alert_limit;
                    $nestedData['action_limit'] = $post->action_limit;
                } else {
                    $nestedData['standard_limit'] = $post->standard_limit_text;
                    $nestedData['alert_limit'] = $post->alert_limit_text;
                    $nestedData['action_limit'] = $post->action_limit_text;
                    if ($post->standard_limit_text != $post->standard_limit_text_en && $post->standard_limit_text_en != "") {
                        $nestedData['standard_limit'] .= "<br><i>$post->standard_limit_text_en</i>";
                    }
                    if ($post->alert_limit_text != $post->alert_limit_text_en && $post->alert_limit_text_en != "") {
                        $nestedData['alert_limit'] .= "<br><i>$post->alert_limit_text_en</i>";
                    }
                    if ($post->action_limit_text != $post->action_limit_text_en && $post->action_limit_text_en != "") {
                        $nestedData['action_limit'] .= "<br><i>$post->action_limit_text_en</i>";
                    }
                }
                $nestedData['action'] = '<a href="' . base_url() . 'limit/edit/' . $post->id . '" class="btn btn-warning btn-sm mr-2" title="edit">'
                    . '<i class="fas fa-pencil-alt">'
                    . '</i>'
                    . '</a>'
                    . '<a href="' . base_url() . 'limit/remove/' . $post->id . '" class="btn btn-danger btn-sm" data-type="confirm" title="remove">'
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
}
