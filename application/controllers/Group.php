<?php

class Group extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->data['is_admin'] = $this->ion_auth->is_admin();
        $this->data['userdata'] = $this->session->userdata();
        $this->data['template'] = "admin";
        $this->data['title'] = "Admin";
        $this->group = array("admin");
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

        return true;
    }

    public function index()
    { /////// trang ca nhan
        load_datatable($this->data);
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    public function get($params)
    {
        $id = $params[0];
        $this->load->model("group_model");
        $json_data = $this->group_model->where(array('id' => $id))->as_object()->get();
        echo json_encode($json_data);
    }

    public function add()
    { /////// trang ca nhan
        if (isset($_POST['dangtin'])) {
            $data = $_POST;
            $data['user_id'] = $this->session->userdata('user_id');
            $this->load->model("group_model");
            $data_up = $this->group_model->create_object($data);
            $id = $this->group_model->insert($data_up);

            /// Log audit trail
            $text =   "USER '" . $this->session->userdata('username') . "' added a new group";
            $this->group_model->trail($id, "insert", null, $data_up, null, $text);
            redirect('group', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function edit($param)
    { /////// trang ca nhan
        $id = $param[0];
        if (isset($_POST['dangtin'])) {
            $this->load->model("group_model");
            $data = $_POST;
            $data_up = $this->group_model->create_object($data);

            /// Log audit trail
            $data_prev = $this->group_model->where('id', $id)->as_array()->get();
            $text =   "USER '" . $this->session->userdata('username') . "' edited group '" . $data['name'] . "'";
            $this->group_model->trail($id, "update", null, $data_up, $data_prev, $text);


            $this->group_model->update($data_up, $id);


            redirect('group', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {
            $this->load->model("group_model");
            $tin = $this->group_model->where(array('id' => $id))->as_object()->get();
            $this->data['tin'] = $tin;
            //            load_chossen($this->data);
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function remove($params)
    { /////// trang ca nhan
        $this->load->model("group_model");
        $id = $params[0];
        $this->group_model->update(array("deleted" => 1), $id);

        /// Log audit trail
        $text =   "USER '" . $this->session->userdata('username') . "' removed a group ";
        $this->user_model->trail($id, "delete", null, null, null, $text);

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    public function permission()
    {
        if ($this->input->post() && $this->input->post('cancel'))
            redirect('/group', 'refresh');

        $group_id  =   $this->uri->segment(3);

        if (!$group_id) {
            $this->session->set_flashdata('message', "No group ID passed");
            redirect("/group", 'refresh');
        }

        if ($this->input->post() && $this->input->post('save')) {
            $data = $this->input->post();
            foreach ($data as $k => $v) {
                if (substr($k, 0, 5) == 'perm_') {
                    $permission_id  =   str_replace("perm_", "", $k);

                    if ($v == "X")
                        $this->ion_auth_acl->remove_permission_from_group($group_id, $permission_id);
                    else
                        $this->ion_auth_acl->add_permission_to_group($group_id, $permission_id, $v);
                }
            }
            ///OBJECT
            $this->load->model("groupobject_model");
            $this->load->model("group_model");
            $array = $this->groupobject_model->where('group_id', $group_id)->as_array()->get_all();
            $group_old = array_map(function ($item) {
                return $item['group_id'];
            }, $array);
            $group_new = isset($data['objects']) ? $data['objects'] : array();
            $array_delete = array_diff($group_old, $group_new);
            $array_add = array_diff($group_new, $group_old);
            //$data_update = array('add_object' => $array_add, 'remove_object' => $array_delete);
            foreach ($array_add as $row) {
                $array = array(
                    'group_id' => $group_id,
                    'object_id' => $row
                );
                $this->groupobject_model->insert($array);
            }

            foreach ($array_delete as $row) {
                $array = array(
                    'group_id' => $group_id,
                    'object_id' => $row
                );
                $this->groupobject_model->where($array)->delete();
            }

            /// Log audit trail
            $text =   "USER '" . $this->session->userdata('username') . "' edit permissions group ";
            $this->group_model->trail($group_id, "delete", null, $data_update, array(), $text);

            ///END
            redirect('/group', 'refresh');
        }



        $this->load->model("group_model");
        $this->load->model("object_model");
        $tin = $this->group_model->where(array('id' => $group_id))->with_objects()->as_object()->get();
        $tin->objects = array_keys((array) $tin->objects);
        $this->data['objects']  = $this->object_model->where(array("deleted" => 0))->as_array()->get_all();
        $this->data['permissions']            =   $this->ion_auth_acl->permissions('full', 'perm_key');
        $this->data['group_permissions']      =   $this->ion_auth_acl->get_group_permissions($group_id);
        $this->data['tin'] = $tin;
        load_chossen($this->data);
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }
    public function table()
    {
        $this->load->model("group_model");
        $limit = $this->input->post('length');
        $start = $this->input->post('start');
        $page = ($start / $limit) + 1;
        $where = $this->group_model;

        $totalData = $where->count_rows();
        $totalFiltered = $totalData;

        if (empty($this->input->post('search')['value'])) {
            //            $max_page = ceil($totalFiltered / $limit);

            $where = $this->group_model->where(array("deleted" => 0));
        } else {
            $search = $this->input->post('search')['value'];
            $sWhere = "deleted = 0";
            $where = $this->group_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
            $totalFiltered = $where->count_rows();
            $where = $this->group_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
        }

        $posts = $where->order_by("id", "DESC")->with_image()->paginate($limit, NULL, $page);
        //        echo "<pre>";
        //        print_r($posts);
        //        die();
        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $nestedData['id'] = $post->id;
                $nestedData['name'] = $post->name;
                $nestedData['description'] = $post->description;
                $nestedData['action'] = '<a href="' . base_url() . 'group/edit/' . $post->id . '" class="btn btn-warning btn-sm mr-2" title="edit">'
                    . '<i class="fas fa-pencil-alt">'
                    . '</i>'
                    . '</a>'
                    . '<a href="' . base_url() . 'group/permission/' . $post->id . '" class="btn btn-primary btn-xs mr-2" title="permission">'
                    . '<i class="fas fa-key">'
                    . '</i>'
                    . '</a>';
                if ($post->id != 1) {
                    $nestedData['action'] .=  '<a href="' . base_url() . 'group/remove/' . $post->id . '" class="btn btn-danger btn-xs" data-type="confirm" title="remove">'
                        . '<i class="far fa-trash-alt">'
                        . '</i>'
                        . '</a>';
                }
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
