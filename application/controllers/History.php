<?php

class History extends MY_Controller
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
        $this->load->model("history_model");
        $limit = $this->input->post('length');
        $start = $this->input->post('start');
        $page = ($start / $limit) + 1;
        if (empty($this->input->post('search')['value'])) {

            $where = $this->history_model;
            $totalData = $where->count_rows();
            $totalFiltered = $totalData;
            $where = $this->history_model;
        } else {
            $search = $this->input->post('search')['value'];
            $sWhere = "name LIKE '%$search%' OR description LIKE '%$search%'";

            $where = $this->history_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
            $totalFiltered = $where->count_rows();
            $where = $this->history_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
        }

        $posts = $where->order_by("id", "DESC")->paginate($limit, NULL, $page);
        //        echo "<pre>";
        //        print_r($posts);
        //        die();
        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $nestedData['created_at'] = $post->created_at;
                $nestedData['description'] = $post->description;
                $nestedData['name'] = $post->name;
                $nestedData['old_values'] = $post->old_values;
                $nestedData['new_values'] = $post->new_values;
                $nestedData['action'] = '';
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
