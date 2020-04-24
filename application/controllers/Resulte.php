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
        if ($object_id != "3") {
            redirect("result", "refresh");
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
            $data = $_POST;
            $this->load->model("employeeResult_model");
            $data_up = $this->employeeResult_model->create_object($data);
            $this->employeeResult_model->update($data_up, $id);
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
        $this->employeeResult_model->update(array("deleted" => 1), $id);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function table()
    {
        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $this->load->model("employeeResult_model");
        $this->load->model("employee_model");
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
            $search = $this->input->post('search')['value'];
            $sWhere = "deleted = 0 and employee_id IN (SELECT id from pmp_employee where deleted = 0 and (name like '%" . $search . "%' OR string_id like '%" . $search . "%'))";
            if ($daterange != "") {
                $list_date = explode(" - ", $daterange);
                $date_from = date("Y-m-d", strtotime($list_date[0]));
                $date_to = date("Y-m-d", strtotime($list_date[1]));
                $sWhere .= " AND date BETWEEN '$date_from' AND '$date_to";
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
                $nestedData['employee_string_id'] = isset($post->employee->string_id) ? $post->employee->string_id : "";
                $nestedData['employee_name'] = isset($post->employee->name) ? $post->employee->name : "";
                $nestedData['area_name'] = isset($post->area->name) ? $post->area->name : "";
                $nestedData['date'] = $post->date;
                $nestedData['value_H'] = $post->value_H;
                $nestedData['value_N'] = $post->value_N;
                $nestedData['value_C'] = $post->value_C;
                $nestedData['value_LF'] = $post->value_LF;
                $nestedData['value_RF'] = $post->value_RF;
                $nestedData['value_LG'] = $post->value_LG;
                $nestedData['value_RG'] = $post->value_RG;
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
