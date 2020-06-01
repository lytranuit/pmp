<?php

class Resulte extends MY_Controller
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
        $object_array = array(3);
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
            $this->load->model("employeeResult_model");
            $data['created_at'] = date("Y-m-d H:i:s");
            $data['user_id'] = $this->session->userdata('user_id');
            $data_up = $this->employeeResult_model->create_object($data);
            $id = $this->employeeResult_model->insert($data_up);

            /// Log audit trail
            $text =   "USER '" . $this->session->userdata('username') . "' added a new record($id) to the table 'pmp_employee_result'";
            $this->employeeResult_model->trail($id, "insert", null, $data_up, null, $text);
            redirect('resulte', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {

            load_chossen($this->data);
            $this->load->model("employee_model");
            $this->data['employee'] = $this->employee_model->where(array('deleted' => 0))->as_object()->get_all();
            $this->load->model("factory_model");
            $this->data['factory'] = $this->factory_model->where(array('deleted' => 0))->as_object()->get_all();
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function edit($params)
    { /////// trang ca nhan
        $id = $params[0];
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        if (isset($_POST['dangtin'])) {
            $this->load->model("employeeResult_model");
            $data = $_POST;
            $prev_data = $this->employeeResult_model->as_array()->get($id);
            $data_up = $this->employeeResult_model->create_object($data);
            $status = $this->employeeResult_model->update($data_up, $id);
            /// Log audit trail
            $text =   "USER '" . $this->session->userdata('username') . "' edited record($id) to the table 'pmp_employee_result'";
            $this->employeeResult_model->trail($status, "update", null, $data_up, $prev_data, $text);
            redirect('resulte', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
        } else {
            $this->load->model("employeeResult_model");
            $tin = $this->employeeResult_model->where(array('id' => $id))->as_object()->get();
            $this->data['tin'] = $tin;

            load_chossen($this->data);

            $this->load->model("employee_model");
            $this->data['employee'] = $this->employee_model->where(array('deleted' => 0))->as_object()->get_all();

            $this->load->model("factory_model");
            $this->data['factory'] = $this->factory_model->where(array('deleted' => 0))->as_object()->get_all();

            $this->load->model("workshop_model");
            $this->data['workshop'] = $this->workshop_model->where(array('deleted' => 0, 'factory_id' => $tin->factory_id))->as_object()->get_all();

            $this->load->model("area_model");
            $this->data['area'] = $this->area_model->where(array('deleted' => 0, 'workshop_id' => $tin->workshop_id))->as_object()->get_all();
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    public function remove($params)
    { /////// trang ca nhan
        $this->load->model("employeeResult_model");
        $id = $params[0];
        $status = $this->employeeResult_model->update(array("deleted" => 1), $id);

        /// Log audit trail
        $text =   "USER '" . $this->session->userdata('username') . "' removed record($id) to the table 'pmp_employee_result'";
        $this->result_model->trail($status, "delete", null, null, $id, $text);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function table()
    {
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $this->load->model("employeeResult_model");
        $this->load->model("employee_model");
        $this->load->model("limit_model");
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
                $where = $this->employeeResult_model->where(array("deleted" => 0))->where("date", ">=", $date_from)->where("date", "<=", $date_to);
                $totalFiltered = $where->count_rows();
                $where = $this->employeeResult_model->where(array("deleted" => 0))->where("date", ">=", $date_from)->where("date", "<=", $date_to);
            } else {
                $where = $this->employeeResult_model->where(array("deleted" => 0));
                $totalFiltered = $where->count_rows();
                $where = $this->employeeResult_model->where(array("deleted" => 0));
            }
        } else {
            $daterange = $this->input->post('daterange');

            $search = $this->input->post('search')['value'];
            $sWhere = "deleted = 0 and employee_id IN (SELECT id from pmp_employee where deleted = 0 and (name like '%" . $search . "%' OR string_id like '%" . $search . "%'))";
            if ($daterange != "") {
                $list_date = explode(" - ", $daterange);
                $date_from = date("Y-m-d", strtotime($list_date[0]));
                $date_to = date("Y-m-d", strtotime($list_date[1]));
                $sWhere .= " AND date BETWEEN '$date_from' AND '$date_to'";
            }
            $where = $this->employeeResult_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
            $totalFiltered = $where->count_rows();
            $where = $this->employeeResult_model->where($sWhere, NULL, NULL, FALSE, FALSE, TRUE);
        }

        $posts = $where->order_by("id", "DESC")->with_area()->with_employee()->paginate($limit, NULL, $page);
        //        echo "<pre>";
        //        print_r($posts);
        //        die();
        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $limit = $this->limit_model->where(array("area_id" => $post->area_id, 'target_id' => 6))->where("day_effect", "<=", $post->date)->order_by("day_effect", "DESC")->limit(1)->as_object()->get();

                $nestedData['employee_string_id'] = isset($post->employee->string_id) ? $post->employee->string_id : "";
                $nestedData['employee_name'] = isset($post->employee->name) ? $post->employee->name : "";
                $nestedData['area_name'] = isset($post->area->name) ? $post->area->name : "";
                $nestedData['date'] = $post->date;

                $nestedData['value_H'] = "<div class='text-center'>$post->value_H</div>";
                if (!empty($limit) && $post->value_H > $limit->alert_limit && $post->value_H < $limit->action_limit) {
                    $nestedData['value_H'] = "<div class='bg-warning text-white text-center'>$post->value_H</div>";
                } elseif (!empty($limit) && $post->value_H > $limit->action_limit) {
                    $nestedData['value_H'] = "<div class='bg-danger text-white text-center'>$post->value_H</div>";
                }

                $nestedData['value_C'] = "<div class='text-center'>$post->value_C</div>";
                if (!empty($limit) && $post->value_C > $limit->alert_limit && $post->value_C < $limit->action_limit) {
                    $nestedData['value_C'] = "<div class='bg-warning text-white text-center'>$post->value_C</div>";
                } elseif (!empty($limit) && $post->value_C > $limit->action_limit) {
                    $nestedData['value_C'] = "<div class='bg-danger text-white text-center'>$post->value_C</div>";
                }
                $nestedData['value_N'] = "<div class='text-center'>$post->value_N</div>";
                if (!empty($limit) && $post->value_N > $limit->alert_limit && $post->value_N < $limit->action_limit) {
                    $nestedData['value_N'] = "<div class='bg-warning text-white text-center'>$post->value_N</div>";
                } elseif (!empty($limit) && $post->value_N > $limit->action_limit) {
                    $nestedData['value_N'] = "<div class='bg-danger text-white text-center'>$post->value_N</div>";
                }
                $nestedData['value_LF'] = "<div class='text-center'>$post->value_LF</div>";
                if (!empty($limit) && $post->value_LF > $limit->alert_limit && $post->value_LF < $limit->action_limit) {
                    $nestedData['value_LF'] = "<div class='bg-warning text-white text-center'>$post->value_LF</div>";
                } elseif (!empty($limit) && $post->value_LF > $limit->action_limit) {
                    $nestedData['value_LF'] = "<div class='bg-danger text-white text-center'>$post->value_LF</div>";
                }
                $nestedData['value_RF'] = "<div class='text-center'>$post->value_RF</div>";
                if (!empty($limit) && $post->value_RF > $limit->alert_limit && $post->value_RF < $limit->action_limit) {
                    $nestedData['value_RF'] = "<div class='bg-warning text-white text-center'>$post->value_RF</div>";
                } elseif (!empty($limit) && $post->value_RF > $limit->action_limit) {
                    $nestedData['value_RF'] = "<div class='bg-danger text-white text-center'>$post->value_RF</div>";
                }
                $nestedData['value_LG'] = "<div class='text-center'>$post->value_LG</div>";
                if (!empty($limit) && $post->value_LG > $limit->alert_limit && $post->value_LG < $limit->action_limit) {
                    $nestedData['value_LG'] = "<div class='bg-warning text-white text-center'>$post->value_LG</div>";
                } elseif (!empty($limit) && $post->value_LG > $limit->action_limit) {
                    $nestedData['value_LG'] = "<div class='bg-danger text-white text-center'>$post->value_LG</div>";
                }
                $nestedData['value_RG'] = "<div class='text-center'>$post->value_RG</div>";
                if (!empty($limit) && $post->value_RG > $limit->alert_limit && $post->value_RG < $limit->action_limit) {
                    $nestedData['value_RG'] = "<div class='bg-warning text-white text-center'>$post->value_RG</div>";
                } elseif (!empty($limit) && $post->value_RG > $limit->action_limit) {
                    $nestedData['value_RG'] = "<div class='bg-danger text-white text-center'>$post->value_RG</div>";
                }

                $nestedData['note'] = $post->note;
                $nestedData['action'] = '<a href="' . base_url() . 'resulte/edit/' . $post->id . '" class="btn btn-warning btn-sm mr-2" title="edit">'
                    . '<i class="fas fa-pencil-alt">'
                    . '</i>'
                    . '</a>'
                    . '<a href="' . base_url() . 'resulte/remove/' . $post->id . '" class="btn btn-danger btn-sm" data-type="confirm" title="remove">'
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
