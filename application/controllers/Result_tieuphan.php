<?php

class Result_tieuphan extends MY_Controller
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

        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $object_array = array(14, 15, 16, 17);
        if (!in_array((int) $object_id, $object_array)) {
            $this->redirect_result($object_id);
        }
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
        return true;
    }

    public function index()
    { /////// trang ca nhan
        load_datatable($this->data);
        load_daterangepicker($this->data);
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    public function add()
    { /////// trang ca nhan
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        if (isset($_POST['dangtin'])) {
            $data = $_POST;
            $this->load->model("result_model");

            $data['user_id'] = $this->session->userdata('user_id');
            $data['created_at'] = date("Y-m-d H:i:s");
            $position_id = $data['position_id'];
            $target_id = $data['target_id'];
            $date = $data['date'];
            $max_stt = $this->result_model->max_stt_have_target_in_day($position_id, $date, $target_id);
            $data['stt_in_day'] = $max_stt;
            $data_up = $this->result_model->create_object($data);
            $id = $this->result_model->insert($data_up);

            /// Log audit trail
            $text =   "USER '" . $this->session->userdata('username') . "' added a new record($id) to the table 'pmp_result'";
            $this->result_model->trail($id, "insert", null, $data_up, null, $text);
            // die();
            redirect('result_tieuphan', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {

            load_chossen($this->data);
            $this->load->model("position_model");
            $this->data['positions'] = $this->position_model->where(array('deleted' => 0, 'object_id' => $object_id))->as_object()->get_all();

            $this->load->model("objecttarget_model");
            $object_target = $this->objecttarget_model->where("object_id", $object_id)->order_by("order", "ASC")->with_target()->as_array()->get_all();
            $this->data['html_nestable_target'] = $this->html_nestable_target((array) $object_target, 'parent_id', 0);

            // echo "<pre>";
            // print_r($list_target);
            // die();
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }
    public function edit($params)
    { /////// trang ca nhan
        $id = $params[0];
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        if (isset($_POST['dangtin'])) {

            $this->load->model("result_model");
            $prev_data = $this->result_model->as_array()->get($id);
            $data = $_POST;
            $position_id = $data['position_id'];
            $target_id = $data['target_id'];
            $date = $data['date'];
            $max_stt = $this->result_model->max_stt_have_target_in_day($position_id, $date, $target_id);
            $data['stt_in_day'] = $max_stt;
            $data_up = $this->result_model->create_object($data);
            $status = $this->result_model->update($data_up, $id);

            /// Log audit trail
            $text =   "USER '" . $this->session->userdata('username') . "' edited record($id) to the table 'pmp_result'";
            $this->result_model->trail($status, "update", null, $data_up, $prev_data, $text);
            redirect('result_tieuphan', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {
            $this->load->model("result_model");
            $tin = $this->result_model->where(array('id' => $id))->as_object()->get();
            $this->data['tin'] = $tin;

            load_chossen($this->data);
            $this->load->model("position_model");
            $this->data['positions'] = $this->position_model->where(array('deleted' => 0, 'object_id' => $object_id))->as_object()->get_all();

            $this->load->model("objecttarget_model");

            $object_target = $this->objecttarget_model->where("object_id", $object_id)->order_by("order", "ASC")->with_target()->as_array()->get_all();
            $this->data['html_nestable_target'] = $this->html_nestable_target((array) $object_target, 'parent_id', 0);

            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }
    public function remove($params)
    { /////// trang ca nhan
        $this->load->model("result_model");
        $id = $params[0];
        $status = $this->result_model->update(array("deleted" => 1), $id);

        /// Log audit trail
        $text =   "USER '" . $this->session->userdata('username') . "' removed record($id) to the table 'pmp_result'";
        $this->result_model->trail($status, "delete", null, null, $id, $text);
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
        $this->load->model("result_model");
        $this->load->model("limit_model");
        $this->load->model("objecttarget_model");
        $limit = $this->input->post('length');
        $start = $this->input->post('start');
        $page = ($start / $limit) + 1;


        if (empty($this->input->post('search')['value'])) {
            //            $max_page = ceil($totalFiltered / $limit);
            $daterange = $this->input->post('daterange');

            if ($daterange != "") {
                $list_date = explode(" - ", $daterange);
                $date_from = date("Y-m-d", strtotime($list_date[0]));
                $date_to = date("Y-m-d", strtotime($list_date[1]));
                $where = $this->result_model->where(array("deleted" => 0, 'object_id' => $object_id))->where("date", ">=", $date_from)->where("date", "<=", $date_to);
                $totalFiltered = $where->count_rows();
                $where = $this->result_model->where(array("deleted" => 0, 'object_id' => $object_id))->where("date", ">=", $date_from)->where("date", "<=", $date_to);
            } else {
                $where = $this->result_model->where(array("deleted" => 0, 'object_id' => $object_id));
                $totalFiltered = $where->count_rows();
                $where = $this->result_model->where(array("deleted" => 0, 'object_id' => $object_id));
            }
        } else {
            $daterange = $this->input->post('daterange');

            $search = $this->input->post('search')['value'];
            $sWhere = "deleted = 0 and object_id = " . $this->db->escape($object_id) . " and position_id IN (SELECT id from pmp_position where deleted = 0 and string_id like '%" . $search . "%')";
            if ($daterange != "") {
                $list_date = explode(" - ", $daterange);
                $date_from = date("Y-m-d", strtotime($list_date[0]));
                $date_to = date("Y-m-d", strtotime($list_date[1]));
                $sWhere .= " AND date BETWEEN '$date_from' AND '$date_to'";
            }
            $where = $this->result_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
            $totalFiltered = $where->count_rows();
            $where = $this->result_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
        }
        $posts = $where->order_by("id", "DESC")->with_department()->with_area()->with_position()->with_target()->paginate($limit, NULL, $page);

        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $limit = $this->limit_model->where(array("area_id" => $post->area_id, 'target_id' => $post->target_id))->where("day_effect", "<=", $post->date)->order_by("day_effect", "DESC")->limit(1)->as_object()->get();
                $target = $this->objecttarget_model->where(array("object_id" => $object_id, 'target_id' => $post->target_id))->with_parent(array("with" => array('relation' => 'target')))->get();
                if (!empty($target) && isset($target->parent->target->name)) {
                    $post->target->name .=  " (" . $target->parent->target->name . ")";
                }
                $nestedData['target_name'] = isset($post->target->name) ? $post->target->name : "";
                $nestedData['position_name'] = isset($post->position->name) ? $post->position->name : "";
                $nestedData['position_string_id'] = isset($post->position->string_id) ? $post->position->string_id : "";
                $nestedData['frequency_name'] = isset($post->position->frequency_name) ? $post->position->frequency_name : "";
                $nestedData['department_name'] = isset($post->department->name) ? $post->department->name : "";
                $nestedData['date'] = $post->date;
                if ($post->target->type_data == "float") {
                    $nestedData['value'] = "<div class='text-center'>$post->value</div>";
                } else {
                    $nestedData['value'] = "<div class='text-center'>$post->value_text</div>";
                }
                if (!empty($limit) && $post->value > $limit->alert_limit && $post->value < $limit->action_limit) {
                    $nestedData['value'] = "<div class='bg-warning text-white text-center'>$post->value</div>";
                } elseif (!empty($limit) && $post->value > $limit->action_limit) {
                    $nestedData['value'] = "<div class='bg-danger text-white text-center'>$post->value</div>";
                }
                $nestedData['note'] = $post->note;
                $nestedData['action'] = '<a href="' . base_url() . 'result_tieuphan/edit/' . $post->id . '" class="btn btn-warning btn-sm mr-2" title="edit">'
                    . '<i class="fas fa-pencil-alt">'
                    . '</i>'
                    . '</a>'
                    . '<a href="' . base_url() . 'result_tieuphan/remove/' . $post->id . '" class="btn btn-danger btn-sm" data-type="confirm" title="remove">'
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
