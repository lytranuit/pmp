<?php
class User extends MY_Controller
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
        $this->load->model("user_model");
        $json_data = $this->user_model->where(array('id' => $id))->as_object()->get();
        echo json_encode($json_data);
    }
    public function checkregister()
    {
        $username = $this->input->get('username');
        $this->load->model("user_model");
        $check = $this->user_model->where(array("username" => $username))->as_array()->get();
        // account_creation_duplicate_identity
        // account_creation_duplicate_email
        if (!empty($check)) {
            echo json_encode(array('success' => 0, 'msg' => lang('account_creation_duplicate_identity')));
            die();
        }

        echo json_encode(array('success' => 1));
    }
    public function add()
    { /////// trang ca nhan
        if (isset($_POST['dangtin'])) {
            $data = $_POST;
            $this->load->model("user_model");
            $data_up = $this->user_model->create_object($data);
            $id = $this->user_model->insert($data_up);

            $result = $this->ion_auth_model->reset_password($_POST['username'], $_POST['newpassword']);
            if (isset($data['groups'])) {
                $this->load->model("usergroup_model");

                foreach ($data['groups'] as $row) {
                    $array = array(
                        'group_id' => $row,
                        'user_id' => $id
                    );
                    $this->usergroup_model->insert($array);
                }
            }

            /// Log audit trail
            $text =   "USER '" . $this->session->userdata('username') . "' added a new user";
            $this->user_model->trail($id, "insert", null, $data_up, null, $text);
            redirect('user', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {
            $this->load->model("group_model");
            $this->data['groups'] = $this->group_model->as_array()->get_all();
            load_chossen($this->data);
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function edit($param)
    { /////// trang ca nhan
        $id = $param[0];
        if (isset($_POST['dangtin'])) {
            $this->load->model("user_model");
            $this->load->model("usergroup_model");
            $data = $_POST;
            $data_up = $this->user_model->create_object($data);
            $this->user_model->update($data_up, $id);

            $array = $this->usergroup_model->where('user_id', $id)->as_array()->get_all();
            $group_old = array_map(function ($item) {
                return $item['group_id'];
            }, $array);
            $group_new = isset($data['groups']) ? $data['groups'] : array();
            $array_delete = array_diff($group_old, $group_new);
            $array_add = array_diff($group_new, $group_old);
            foreach ($array_add as $row) {
                $array = array(
                    'group_id' => $row,
                    'user_id' => $id
                );
                $this->usergroup_model->insert($array);
            }

            foreach ($array_delete as $row) {
                $array = array(
                    'group_id' => $row,
                    'user_id' => $id
                );
                $this->usergroup_model->where($array)->delete();
            }

            /// Log audit trail
            $data_prev = $this->user_model->where('id', $id)->as_array()->get();
            $text =   "USER '" . $this->session->userdata('username') . "' edited user '" . $data['username'] . "'";
            $this->user_model->trail($id, "update", null, $data_up, $data_prev, $text);

            redirect('user', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {
            $this->load->model("user_model");
            $tin = $this->user_model->where(array('id' => $id))->with_groups()->as_object()->get();

            $users_groups = (array) $tin->groups;
            //            echo "<pre>";
            //            print_r($users_groups);
            //            die();
            $tin->groups = array_keys($users_groups);
            $this->load->model("group_model");
            $this->data['groups'] = $this->group_model->as_array()->get_all();
            $this->data['tin'] = $tin;
            // load_editor($this->data);
            load_chossen($this->data);
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function remove($params)
    { /////// trang ca nhan
        $this->load->model("user_model");
        $id = $params[0];
        $this->user_model->update(array("deleted" => 1), $id);

        /// Log audit trail
        $text =   "USER '" . $this->session->userdata('username') . "' removed a user ";
        $this->user_model->trail($id, "delete", null, null, null, $text);

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function table()
    {
        $this->load->model("user_model");
        $limit = $this->input->post('length');
        $start = $this->input->post('start');
        $page = ($start / $limit) + 1;


        if (empty($this->input->post('search')['value'])) {
            //            $max_page = ceil($totalFiltered / $limit);

            $where = $this->user_model->where(array("deleted" => 0));
            $totalData = $where->count_rows();
            $totalFiltered = $totalData;
            $where = $this->user_model->where(array("deleted" => 0));
        } else {
            $search = $this->input->post('search')['value'];
            $sWhere = "deleted = 0 and (username like '%" . $search . "%' OR last_name like '%" . $search . "%')";
            $where = $this->user_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
            $totalFiltered = $where->count_rows();
            $where = $this->user_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
        }

        $posts = $where->order_by("id", "DESC")->with_groups()->paginate($limit, NULL, $page);
        //        echo "<pre>";
        //        print_r($posts);
        //        die();
        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $groups = "";
                foreach ($post->groups as $row) {
                    $groups .= "<p>$row->description</p>";
                }
                $nestedData['username'] = $post->username;
                $nestedData['last_name'] = $post->last_name;
                $nestedData['groups'] = $groups;
                $nestedData['active'] = $post->active ? "Có" : "Không";
                $nestedData['action'] = '<a href="' . base_url() . 'user/edit/' . $post->id . '" class="btn btn-warning btn-xs mr-2" title="edit">'
                    . '<i class="fas fa-pencil-alt">'
                    . '</i>'
                    . '</a>'
                    . '<a href="' . base_url() . 'user/remove/' . $post->id . '" class="btn btn-danger btn-xs" data-type="confirm" title="remove">'
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
