<?php

class Admin extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->data['is_admin'] = $this->ion_auth->is_admin();
        $this->data['userdata'] = $this->session->userdata();
        $this->data['template'] = "admin";
        $this->data['title'] = "Admin";
        $version = $this->config->item("version");
        $this->data['stylesheet_tag'] = array(
            base_url() . "public/assets/css/main.css?v=" . $version,
            base_url() . "public/admin/vendor/fonts/fontawesome/css/fontawesome-all.css"
        );
        $this->data['javascript_tag'] = array(
            base_url() . 'public/assets/scripts/jquery.min.js',
            base_url() . "public/lib/jquery-validation/jquery.validate.js",
            base_url() . "public/admin/vendor/inputmask/js/jquery.inputmask.bundle.js",
            base_url() . "public/admin/libs/js/moment.js",
            base_url() . "public/assets/scripts/jquery.cookies.2.2.0.min.js",
            base_url() . "public/assets/scripts/main.js?v=" . $version,
            base_url() . "public/assets/scripts/custom.js?v=" . $version
        );
    }

    public function _remap($method, $params = array()) {
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

    private function has_right($method, $params = array()) {

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

    public function index() { /////// trang ca nhan
        $this->load->model("saleorder_model");
        $this->load->model("debt_model");
        $this->load->model("product_model");
        $this->data['count_product'] = $this->product_model->where(array("active" => 1, 'deleted' => 0, 'parent' => 0))->count_rows();
        $this->data['amount_sale'] = $this->saleorder_model->amount_sale();
        $this->data['amount_sale_in_day'] = $this->saleorder_model->amount_sale_in_day();
        $this->data['amount_debt'] = $this->debt_model->amount_debt();
        $this->data['amount_debt_has_order'] = $this->saleorder_model->amount_debt_has_order();
//        $this->data['amount_food'] = 
//        print_r($count_product);
//        die();
        load_datatable($this->data);
        array_push($this->data['javascript_tag'], base_url() . "public/assets/color-hash.js");
        array_push($this->data['javascript_tag'], base_url() . "public/admin/vendor/charts/charts-bundle/Chart.bundle.js");
        echo $this->blade->view()->make('page/page', $this->data)->render();
    }

    public function account() { /////// trang ca nhan
        $id_user = $this->session->userdata('user_id');
        $this->load->model("user_model");
        if (isset($_POST['edit_user'])) {
            $additional_data = array(
                'last_name' => $this->input->post('last_name'),
                'phone' => $this->input->post('phone'),
                'gioitinh' => $this->input->post("gioitinh")
            );
            $this->user_model->update($additional_data, $id_user);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        } else {
            $user = $this->user_model->where(array('id' => $id_user))->as_object()->get();
            $this->data['user'] = $user;
            //echo $this->data['content'];
            echo $this->blade->view()->make('page/page', $this->data)->render();
        }
    }

    function changepass() {
        $id_user = $this->session->userdata('user_id');
        $this->load->model("user_model");
        if (!isset($_POST['password']) || (isset($_POST['password']) && $this->ion_auth->hash_password_db($id_user, $_POST['password']) === FALSE)) {
            echo json_encode(array('code' => 402, "msg" => "Mật khẩu cũ không đúng."));
            die();
        }
        if (!isset($_POST['confirmpassword']) || !isset($_POST['newpassword']) || (isset($_POST['newpassword']) && isset($_POST['confirmpassword']) && $_POST['newpassword'] != $_POST['confirmpassword'])) {
            echo json_encode(array('code' => 403, "msg" => "Xác nhận mật khẩu mới không đúng."));
            die();
        }
        $this->ion_auth->change_password($this->session->userdata('identity'), $this->input->post('password'), $this->input->post('newpassword'));
        echo json_encode(array('code' => 400, "msg" => "Thay đổi mật khẩu thành công."));
        die();
    }


    /*
     * UPload hình ảnh
     */

    public function uploadimage() {
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
            $config = array();
            $config['image_library'] = 'gd2';
            $config['source_image'] = $data['full_path'];
            $config['create_thumb'] = FALSE;
            $config['maintain_ratio'] = FALSE;
            $config['quality'] = "100%";
            $config['width'] = 768;
            $config['height'] = 576;
            $config['new_image'] = $data['file_path'] . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
            $bg_src = $upload_path_url . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
            $dim = (intval($data["image_width"]) / intval($data["image_height"])) - ($config['width'] / $config['height']);
            $config['master_dim'] = ($dim > 0) ? "height" : "width";
            $this->load->library('image_lib');
            $this->image_lib->initialize($config);
            $this->image_lib->resize();
            ///resize 2
            $config = array();
            $config['image_library'] = 'gd2';
            $config['source_image'] = $data['full_path'];
            $config['create_thumb'] = FALSE;
            $config['maintain_ratio'] = FALSE;
            $config['quality'] = "100%";
            $config['width'] = 1200;
            $config['height'] = 450;
            $config['new_image'] = $data['file_path'] . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
            $slider_src = $upload_path_url . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
            $dim = (intval($data["image_width"]) / intval($data["image_height"])) - ($config['width'] / $config['height']);
            $config['master_dim'] = ($dim > 0) ? "height" : "width";
            $this->load->library('image_lib');
            $this->image_lib->initialize($config);
            $this->image_lib->resize();
            ///resize 3
            $config = array();
            $config['image_library'] = 'gd2';
            $config['source_image'] = $data['full_path'];
            $config['create_thumb'] = FALSE;
            $config['maintain_ratio'] = FALSE;
            $config['quality'] = "100%";
            $config['width'] = 125;
            $config['height'] = 100;
            $config['new_image'] = $data['file_path'] . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
            $thumb_src = $upload_path_url . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
            $dim = (intval($data["image_width"]) / intval($data["image_height"])) - ($config['width'] / $config['height']);
            $config['master_dim'] = ($dim > 0) ? "height" : "width";
            $this->load->library('image_lib');
            $this->image_lib->clear();
            $this->image_lib->initialize($config);
            ////////////
            if (!$this->image_lib->resize()) { //Resize image
                echo $this->image_lib->display_errors();
            } else {
                $info = new StdClass;
                $info->name = $data['file_name'];
                $info->size = $data['file_size'] * 1024;
                $info->type = $data['file_type'];
                $info->url = $upload_path_url . $data['file_name'];
                $info->deleteType = 'GET';
                $info->error = null;
                $data_up = array(
                    'ten_hinhanh' => $info->name,
                    'real_hinhanh' => $real_name,
                    'src' => $info->url,
                    'type' => $info->type,
                    'size' => $info->size,
                    'thumb_src' => $thumb_src,
                    'bg_src' => $bg_src,
                    'slider_src' => $slider_src,
                    'id_user' => $this->session->userdata('user_id'),
                    'deleted' => 1,
                    'date' => date("Y-m-d H:i:s")
                );
                $this->load->model('hinhanh_model');
                $id_image = $this->hinhanh_model->insert($data_up);
                echo json_encode(array("link" => base_url() . $info->url));
            }
        }
    }

    public function uploadhinhanh() {
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

        $file_count = count($_FILES['hinhanh']['name']);
        for ($i = 0; $i < $file_count; $i++) {
            $ext = pathinfo($_FILES['hinhanh']['name'][$i], PATHINFO_EXTENSION);
            $_FILES['hinhanh']['name'] = time() . "_" . $i . "." . $ext;
            $_FILES['hinhanh']['type'] = $files['hinhanh']['type'][$i];
            $_FILES['hinhanh']['tmp_name'] = $files['hinhanh']['tmp_name'][$i];
            $_FILES['hinhanh']['error'] = $files['hinhanh']['error'][$i];
            $_FILES['hinhanh']['size'] = $files['hinhanh']['size'][$i];
            $real_name = $files['hinhanh']['name'][$i];
            if (!$this->upload->do_upload('hinhanh')) {
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
                $config = array();
                $config['image_library'] = 'gd2';
                $config['source_image'] = $data['full_path'];
                $config['create_thumb'] = FALSE;
                $config['maintain_ratio'] = FALSE;
                $config['quality'] = "100%";
                $config['width'] = 768;
                $config['height'] = 576;
                $config['new_image'] = $data['file_path'] . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
                $bg_src = $upload_path_url . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
                $dim = (intval($data["image_width"]) / intval($data["image_height"])) - ($config['width'] / $config['height']);
                $config['master_dim'] = ($dim > 0) ? "height" : "width";
                $this->load->library('image_lib');
                $this->image_lib->initialize($config);
                $this->image_lib->resize();
                ///resize 2
                $config = array();
                $config['image_library'] = 'gd2';
                $config['source_image'] = $data['full_path'];
                $config['create_thumb'] = FALSE;
                $config['maintain_ratio'] = FALSE;
                $config['quality'] = "100%";
                $config['width'] = 1200;
                $config['height'] = 450;
                $config['new_image'] = $data['file_path'] . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
                $slider_src = $upload_path_url . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
                $dim = (intval($data["image_width"]) / intval($data["image_height"])) - ($config['width'] / $config['height']);
                $config['master_dim'] = ($dim > 0) ? "height" : "width";
                $this->load->library('image_lib');
                $this->image_lib->initialize($config);
                $this->image_lib->resize();
                ///resize 3
                $config = array();
                $config['image_library'] = 'gd2';
                $config['source_image'] = $data['full_path'];
                $config['create_thumb'] = FALSE;
                $config['maintain_ratio'] = FALSE;
                $config['quality'] = "100%";
                $config['width'] = 125;
                $config['height'] = 100;
                $config['new_image'] = $data['file_path'] . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
                $thumb_src = $upload_path_url . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
                $dim = (intval($data["image_width"]) / intval($data["image_height"])) - ($config['width'] / $config['height']);
                $config['master_dim'] = ($dim > 0) ? "height" : "width";
                $this->load->library('image_lib');
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                ////////////
                if (!$this->image_lib->resize()) { //Resize image
                    echo $this->image_lib->display_errors();
                } else {

                    $info = new StdClass;
                    $info->name = $data['file_name'];
                    $info->size = $data['file_size'] * 1024;
                    $info->type = $data['file_type'];
                    $info->url = $upload_path_url . $data['file_name'];
                    $info->deleteType = 'GET';
                    $info->error = null;
                    $data_up = array(
                        'ten_hinhanh' => $info->name,
                        'real_hinhanh' => $real_name,
                        'src' => $info->url,
                        'type' => $info->type,
                        'size' => $info->size,
                        'thumb_src' => $thumb_src,
                        'bg_src' => $bg_src,
                        'slider_src' => $slider_src,
                        'id_user' => $this->session->userdata('user_id'),
                        'deleted' => 1,
                        'date' => date("Y-m-d H:i:s")
                    );
                    $this->load->model('hinhanh_model');
                    $id_image = $this->hinhanh_model->insert($data_up);
                    if (IS_AJAX) {
                        echo json_encode(array(
                            'initialPreview' => array("<img src = '" . base_url() . "$info->url' class = 'file-preview-image img-fluid'>"),
                            'initialPreviewConfig' => array(array('caption' => $info->name, 'width' => '120px', 'height' => '160px', 'url' => base_url() . '/index/success/' . $id_image, 'key' => $id_image)),
                            'append' => true,
                            'key' => $id_image
                        ));
                    } else {
                        $file_data['upload_data'] = $this->upload->data();
                    }
                }
            }
        }
    }

    public function uploadfile() {
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
        $config['allowed_types'] = '*';
        $config['max_size'] = 20 * 1024;
        $this->load->library('upload', $config);
        $files = $_FILES;

        $file_count = count($_FILES['file_up']['name']);
//        echo "<pre>";
//        print_r($_FILES['file_up']);
//        die();
        for ($i = 0; $i < $file_count; $i++) {

            $ext = pathinfo($_FILES['file_up']['name'][$i], PATHINFO_EXTENSION);
            $_FILES['file_up']['name'] = time() . "_" . $i . "." . $ext;
            $_FILES['file_up']['type'] = $files['file_up']['type'][$i];
            $_FILES['file_up']['tmp_name'] = $files['file_up']['tmp_name'][$i];
            $_FILES['file_up']['error'] = $files['file_up']['error'][$i];
            $_FILES['file_up']['size'] = $files['file_up']['size'][$i];
            $real_name = $files['file_up']['name'][$i];
            if (!$this->upload->do_upload('file_up')) {
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
                ////////////
                ///resize 1
                $config = array();
                $config['image_library'] = 'gd2';
                $config['source_image'] = $data['full_path'];
                $config['create_thumb'] = FALSE;
                $config['maintain_ratio'] = FALSE;
                $config['quality'] = "100%";
                $config['width'] = 768;
                $config['height'] = 576;
                $config['new_image'] = $data['file_path'] . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
                $bg_src = $upload_path_url . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
                $dim = (intval($data["image_width"]) / intval($data["image_height"])) - ($config['width'] / $config['height']);
                $config['master_dim'] = ($dim > 0) ? "height" : "width";
                $this->load->library('image_lib');
                $this->image_lib->initialize($config);
                $this->image_lib->resize();
                ///resize 2
                $config = array();
                $config['image_library'] = 'gd2';
                $config['source_image'] = $data['full_path'];
                $config['create_thumb'] = FALSE;
                $config['maintain_ratio'] = FALSE;
                $config['quality'] = "100%";
                $config['width'] = 1200;
                $config['height'] = 450;
                $config['new_image'] = $data['file_path'] . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
                $slider_src = $upload_path_url . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
                $dim = (intval($data["image_width"]) / intval($data["image_height"])) - ($config['width'] / $config['height']);
                $config['master_dim'] = ($dim > 0) ? "height" : "width";
                $this->load->library('image_lib');
                $this->image_lib->initialize($config);
                $this->image_lib->resize();
                ///resize 3
                $config = array();
                $config['image_library'] = 'gd2';
                $config['source_image'] = $data['full_path'];
                $config['create_thumb'] = FALSE;
                $config['maintain_ratio'] = FALSE;
                $config['quality'] = "100%";
                $config['width'] = 125;
                $config['height'] = 100;
                $config['new_image'] = $data['file_path'] . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
                $thumb_src = $upload_path_url . $config['width'] . "x" . $config['height'] . "_" . $data['file_name'];
                $dim = (intval($data["image_width"]) / intval($data["image_height"])) - ($config['width'] / $config['height']);
                $config['master_dim'] = ($dim > 0) ? "height" : "width";
                $this->load->library('image_lib');
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                ////////////
                if (!$this->image_lib->resize()) { //Resize image
                    echo $this->image_lib->display_errors();
                } else {
                    $info = new StdClass;
                    $info->name = $data['file_name'];
                    $info->size = $data['file_size'] * 1024;
                    $info->type = $data['file_type'];
                    $info->url = $upload_path_url . $data['file_name'];
                    $info->deleteType = 'GET';
                    $info->error = null;
                    $data_up = array(
                        'ten_hinhanh' => $info->name,
                        'real_hinhanh' => $real_name,
                        'src' => $info->url,
                        'type' => $info->type,
                        'size' => $info->size,
                        'thumb_src' => $thumb_src,
                        'bg_src' => $bg_src,
                        'slider_src' => $slider_src,
                        'id_user' => $this->session->userdata('user_id'),
                        'deleted' => 1,
                        'date' => date("Y-m-d H:i:s")
                    );
                    $this->load->model('hinhanh_model');
                    $id_image = $this->hinhanh_model->insert($data_up);
                    if (IS_AJAX) {
                        echo json_encode(array(
                            'initialPreview' => array("<img src = '" . base_url() . "$info->url' class = 'file-preview-image img-fluid'>"),
                            'initialPreviewConfig' => array(
                                array(
                                    'caption' => $info->name, 'width' => '120px', 'height' => '160px',
                                    'url' => base_url() . '/index/success/' . $id_image,
                                    'key' => $id_image
                                )
                            ),
                            'append' => true,
                            'key' => $id_image
                        ));
                    } else {
                        $file_data['upload_data'] = $this->upload->data();
                    }
                }
            }
        }
    }

    public function checkusername() {
        $username = $this->input->get('username');
        $this->load->model("user_model");
        $check = $this->user_model->where(array("username" => $username))->as_array()->get_all();
        if (!$check) {
            echo json_encode(array('success' => 1));
        } else {
            echo json_encode(array('success' => 0, 'msg' => "Tài khoản đã tồn tại!"));
        }
    }

    function changepasswithout() {
        $id_user = $this->input->post('id_user');
        $this->load->model("user_model");
        $this->load->model("ion_auth_model");
        if (!isset($_POST['confirmpassword']) || !isset($_POST['newpassword']) || (isset($_POST['newpassword']) && isset($_POST['confirmpassword']) && $_POST['newpassword'] != $_POST['confirmpassword'])) {
            echo json_encode(array('code' => 403, "msg" => "Xác nhận mật khẩu mới không đúng."));
            die();
        }
        $user = $this->user_model->where(array("id" => $id_user))->as_object()->get();
        $user_name = $user->username;
//        print_r($additional_data);
//        echo $id_user;
//        die();

        $result = $this->ion_auth_model->reset_password($user_name, $_POST['newpassword']);
        if ($result) {
            echo json_encode(array('code' => 400, "msg" => "Thay đổi mật khẩu thành công."));
            die();
        } else {
            echo json_encode(array('code' => 500, "msg" => "Bug."));
            die();
        }
    }

    public function deleteImage($params) {//gets the job done but you might want to add error checking and security
        $this->load->model('hinhanh_model');
        $id = $params[0];
        $file = $this->hinhanh_model->where('id_hinhanh', $id)->as_array()->get();
        $success = 0;
        if (file_exists($file['src'])) {
            $success = unlink($file['src']);
        }
        if (file_exists($file['real_hinhanh'])) {
            $success = unlink($file['real_hinhanh']);
        }
        if (file_exists($file['thumb_src'])) {
            $success = unlink($file['thumb_src']);
        }
        if (file_exists($file['bg_src'])) {
            $success = unlink($file['bg_src']);
        }
        if (file_exists($file['slider_src'])) {
            $success = unlink($file['slider_src']);
        }
        $data = array('deleted' => 1);
        $this->hinhanh_model->update($data, $id);
//        $info = new StdClass;
//        $info->sucess = $success;
//        if (IS_AJAX) {
////I don't think it matters if this is set but good for error checking in the console/firebug
//            echo json_encode(array($info));
//        } else {
////here you will need to decide what you want to show for a successful delete        
//            $file_data['delete_data'] = $file;
////$this->load->view('admin/delete_success', $file_data);
//        }
        echo json_encode(1);
    }


    function _load_language() {
        $translations = array();
        $arrray_lang = $this->config->item("language_list");
        foreach ($arrray_lang as $k => $row) {
            $path = APPPATH . "language/" . $k . "/home_lang.php";
//            echo $path;
            $masterModule = $this->_load_module($path);
            foreach ($masterModule as $lineNumber => $line) {
                // Extract each key and value
                if ($this->_is_lang_key($line)) {
                    $key = $this->_get_lang_key($line);
                    $translations[$key][$k] = $this->_get_lang($line);
                }
            }
        }
        return $translations;
    }

    function _load_module($modulePath) {

        /* TODO: Add error checking for non-existent files? */

        $module = @file($modulePath);

        return $module;
    }

    /**
     * Determine if a line of PHP code contains a translation key
     *
     * @param $line string
     * @return boolean
     */
    function _is_lang_key($line) {
        $line = trim($line);
        if (empty($line) || mb_stripos($line, '$lang[') === FALSE) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Extract translation key from a line of PHP code
     *
     * @param $line string
     * @return string
     */
    function _get_lang_key($line) {
        // Trim forward to the first quote mark
        $line = trim(mb_substr($line, mb_strpos($line, '[') + 1));
        // Trim forward to the second quote mark
        $line = trim(mb_substr($line, 0, mb_strpos($line, ']')));
        return mb_substr($line, 1, mb_strlen($line) - 2);
    }

    /**
     * Extract translation string from a line of PHP code
     *
     * @param $line string
     * @return string
     */
    function _get_lang($line) {

        /* Agricultural solution */
        // Trim forward to the first quote mark
        $line = trim(mb_substr($line, strpos($line, '=') + 1));
        // Trim backward from the semi-colon
        $line = mb_substr($line, 0, mb_strrpos($line, ';'));
        $line = trim($line, '\'"');
        /* TODO - This is no good if the string is a PHP expression e.g. 'Hello, ' + CONST + ' how\'s your world?'
          // Trim any encapsulating quote marks
          $line = trim( $line, '\'"' );
         */

        /* Regex based solution ?
          $pattern = '/[^=]*=\s*[\'"]?(.*)[\'"]?;$/';
          $pattern = '/[^=]*=\s*[\'"]?(.*);$/';
          preg_match($pattern, $line, $matches);
          $line = $matches[ 1 ];

          $pattern = '/^[\'"]?(.*)[\'"]{1}$/';
          preg_match($pattern, $line, $matches);
          if ( count( $matches ) >= 1 ) {
          $line = $matches[ 1 ];
          }
         */

        return $this->_escape_templates($line);
    }

    /**
     * Escape template tags
     *
     * @return string
     */
    function _escape_templates($line) {
        return preg_replace('/{(.*)}/', '\\{$1\\}', $line);
    }

    /**
     * Unescape template tags
     *
     * @return string
     */
    function _unescape_templates($line) {
        return preg_replace('/\\\{(.*)\\\}/', '{$1}', $line);
    }

    /**
     * Check PHP syntax
     *
     * Returns FALSE if no errors found otherwise returns the line number of the
     * error with the error message and bad code in variables passed by reference
     *
     * @param $php string
     * @return int
     */
    function _invalid_php_syntax($php, &$err = '', &$bad_code = '') {

        // Remove opening and closing PHP tags
        $php = str_replace('<?php', '', $php);
        $php = str_replace('?>', '', $php);

        // Evaluate the code
        ob_start();
        eval($php);
        $err = ob_get_contents();
        ob_end_clean();

        if (!empty($err)) {
            if (mb_stripos($err, 'Parse error') == FALSE) {
                return FALSE;
            }
        }
        // Remove any html tags returned in error message
        $err_text = strip_tags($err);

        // Get the line number
        $line = (int) trim(substr($err_text, strripos($err_text, ' ')));

        $php = explode("\n", $php);

        $bad_code = $php[max(0, $line - 1)];

        return $line;
    }

}
