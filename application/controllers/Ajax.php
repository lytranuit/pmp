<?php

class Ajax extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
    }
    function setlanguage()
    {
        $_SESSION['language_current'] = $_POST['language'];
        echo 1;
    }

    function downloadfile()
    {
        $this->load->model("user_model");
        $this->load->model("hinhanh_model");
        $is_logged_in = $this->user_model->logged_in();
        if (!$is_logged_in) {
            echo json_encode(array("code" => 403, "msg" => lang('alert_403')));
            die();
        }
        $role_user = $this->session->userdata('role');
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $file = $this->hinhanh_model->where(array("id_hinhanh" => $id))->as_array()->get();
            if ($file) {
                $role_download = $file['role_download'];
                if ($role_download == 0 || in_array($role_user, explode(",", $role_download))) {
                    $real_name = $file['real_hinhanh'];
                    $src = FCPATH . $file['src'];
                    header("Cache-Control: public");
                    header("Content-Type: application/force-download");
                    header("Content-Type: application/octet-stream");
                    header("Content-Type: application/download");
                    header("Content-Disposition: attachment; filename=" . $real_name);
                    header("Content-Transfer-Encoding: binary");
                    readfile($src);
                } else {
                    echo json_encode(array("code" => 406, "msg" => lang('alert_406')));
                    die();
                }
            } else {
                echo json_encode(array("code" => 405, "msg" => lang('alert_405')));
                die();
            }
        } else {
            echo json_encode(array("code" => 404, "msg" => lang('alert_404')));
            die();
        }
    }

    /*
     * UPload hình ảnh
     */

    public function uploadimage()
    {
        ini_set('post_max_size', '64M');
        ini_set('upload_max_filesize', '64M');
        $this->load->helper('file');
        $date = date("Y-m-d");
        $upload_path_url = "public/uploads/$date/";
        $dir = FCPATH . "public/uploads/$date/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $config['upload_path'] = $dir;
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['max_size'] = '10000';
        $this->load->library('upload', $config);
        $files = $_FILES;

        $ext = pathinfo($files['file']['name'], PATHINFO_EXTENSION);
        $_FILES['file']['name'] = time() . "." . $ext;
        $real_name = $files['file']['name'];
        if (!$this->upload->do_upload('file')) {
            $errors = $this->upload->display_errors();
            print_r($errors);
        } else {
            $data = $this->upload->data();
            /*
             * Array
              (
              [file_name] => png1.jpg
              [file_type] => image/jpeg
              [file_path] => /home/ipresupu/public_html/uploads/
              [full_path] => /home/ipresupu/public_html/uploads/png1.jpg
              [raw_name] => png1
              [orig_name] => png.jpg
              [client_name] => png.jpg
              [file_ext] => .jpg
              [file_size] => 456.93
              [is_image] => 1
              [image_width] => 1198
              [image_height] => 1166
              [image_type] => jpeg
              [image_size_str] => width="1198" height="1166"
              )

              // to re-size for thumbnail images un-comment and set path here and in json array
              $config = array();
              $config['image_library'] = 'gd2';
              $config['source_image'] = $data['full_path'];
              $config['create_thumb'] = TRUE;
              $config['new_image'] = $data['file_path'] . 'thumbs/';
              $config['maintain_ratio'] = TRUE;
              $config['thumb_marker'] = '';
              $config['width'] = 75;
              $config['height'] = 50;
              $this->load->library('image_lib', $config);
              $this->image_lib->resize();
             */
            ///resize 1

            ////////////
            $data_up = array(
                'name' => $data['file_name'],
                'real_name' => $real_name,
                'src' => $upload_path_url . $data['file_name'],
                'file_type' => $data['file_type'],
                'size' => $data['file_size'] * 1024,
                'type' => 1,
                'user_id' => $this->session->userdata('user_id')
            );
            $this->load->model('file_model');
            $id_image = $this->file_model->insert($data_up);
            $data_up['id'] = $id_image;
            echo json_encode($data_up);
        }
    }
    function uploadchart()
    {
        $data = $this->input->post('image');
        $name = $this->input->post('name');
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('invalid image type');
            }

            $data = base64_decode($data);

            if ($data === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }
        if (!file_exists(APPPATH . '../public/upload/chart')) {
            mkdir(APPPATH . '../public/upload/chart', 0777, true);
        }
        file_put_contents(APPPATH . '../public/upload/chart/' . $name . "." . $type, $data);
        echo 1;
    }
    public function position_tree()
    {
        $is_reload = $this->input->get('is_reload', TRUE);
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));

        if ($is_reload || !$factory = $this->cache->get('factory_cache')) {
            $this->load->model("factory_model");
            $this->load->model("workshop_model");
            $this->load->model("area_model");
            $this->load->model("department_model");
            $this->load->model("position_model");
            $factory = $this->factory_model->where(array("deleted" => 0))->get_all();
            foreach ($factory as &$row) {
                $workshops = $this->workshop_model->where(array("deleted" => 0, 'factory_id' => $row->id))->get_all();
                foreach ($workshops as &$workshop) {
                    $areas = $this->area_model->where(array("deleted" => 0, 'workshop_id' => $workshop->id))->get_all();
                    foreach ($areas as &$area) {
                        $rooms = $this->department_model->where(array("deleted" => 0, 'area_id' => $area->id))->get_all();
                        foreach ($rooms as &$room) {
                            $positions = $this->position_model->where(array("deleted" => 0, 'department_id' => $room->id))->get_all();

                            $room->child = $positions;
                        }
                        $area->child = $rooms;
                    }
                    $workshop->child = $areas;
                }
                $row->child = $workshops;
            }
            // Save into the cache for 5 minutes
            $this->cache->save('factory_cache', $factory, 60);
        }
        $this->data['factory'] = $factory;
        echo $this->blade->view()->make('include/dashboard/position_tree', $this->data)->render();
    }
    ////////////
}
