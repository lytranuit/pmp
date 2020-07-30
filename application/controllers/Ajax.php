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

    ////////////
}
