<?php

class Ajax extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    //    function login() {
    //        if (isset($_POST['identity']) && isset($_POST['password'])) {
    //            $this->load->model("user_model");
    //            // check to see if the user is logging in
    //            if ($this->user_model->login($this->input->post('identity'), $this->input->post('password'))) {
    //                echo json_encode(array('success' => 1, 'username' => $this->session->userdata('identity')));
    //            } else {
    //                echo json_encode(array('success' => 0, 'code' => 501, 'msg' => lang('alert_501')));
    //            }
    //        } else {
    //            echo json_encode(array('success' => 0, 'code' => 501, 'msg' => lang('alert_501')));
    //        }
    //    }
    function bestsale()
    {
        $this->load->model('saleorder_model');
        $data = $this->saleorder_model->best_sale();
        echo json_encode($data);
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
    function datachart()
    {
        $time_type = $this->input->get('time_type') or "Day";

        $this->load->model('saleorder_model');
        $data = $this->saleorder_model->amount_sale_group_by_time($time_type);
        echo json_encode($data);
    }

    function getopencart()
    {

        $this->load->model('productopen_model');

        $items = $this->productopen_model->where(array('deleted' => 0))->as_array()->get_all();

        echo json_encode($items);
    }

    function getcart()
    {
        $cart = json_decode($this->input->post('data'), true);
        $items = array(
            'details' => array(),
            'count_product' => 0,
            'amount_product' => 0
        );
        $this->load->model('product_model');
        if (count($cart) > 0) {
            //            echo "<pre>";
            //            print_r($cart);
            //            die();
            foreach ($cart as $key => $item) {
                $data = array();
                if (!isset($item['id']) || !isset($item['qty'])) {
                    continue;
                }
                $qty = $item['qty'];
                $id = $item['id'];

                $product = $this->product_model->where(array('id' => $id))->with_hinhanh()->as_array()->get();

                $data['id'] = $product['id'];
                $data['image_url'] = isset($product['hinhanh']->src) ? $product['hinhanh']->src : "";
                $data['code'] = $product['code'];
                $data['name'] = $product['name'];
                $data['price'] = $product['price'];

                $data['qty'] = $qty;
                $data['amount_product'] = $qty * $product['price'];


                $items['count_product'] += $qty;
                $items['amount_product'] += $qty * $product['price'];

                $items['details'][] = $data;
            }
        }
        echo json_encode($items);
    }

    function order()
    {
        $cart = json_decode($this->input->post('data'), true);
        $items = array(
            'details' => array(),
            'count_product' => 0,
            'amount_product' => 0
        );
        if (count($cart) > 0) {
            //            echo "<pre>";
            //            print_r($cart);
            //            die();
            $this->load->model('product_model');
            $this->load->model('saleorder_model');
            $this->load->model('saleorderline_model');
            foreach ($cart as $key => $item) {
                $data = array();
                if (!isset($item['id']) || !isset($item['qty'])) {
                    continue;
                }
                $qty = $item['qty'];
                $id = $item['id'];

                $product = $this->product_model->where(array('id' => $id))->with_hinhanh()->as_array()->get();

                $data['id'] = $product['id'];
                $data['image_url'] = isset($product['hinhanh']->src) ? $product['hinhanh']->src : "";
                $data['code'] = $product['code'];
                $data['name'] = $product['name'];
                $data['price'] = $product['price'];

                $data['qty'] = $qty;
                $data['amount_product'] = $qty * $product['price'];


                $items['count_product'] += $qty;
                $items['amount_product'] += $qty * $product['price'];

                $items['details'][] = $data;
            }
            /*
             * 
             */
            $messsage = "";
            $array = array(
                'order_date' => date("Y-m-d H:i:s"),
                'customer_phone' => $this->input->post('phone'),
                'notes' => $this->input->post('note'),
                'amount' => $items['amount_product'],
                'total_amount' => $items['amount_product'],
            );
            $messsage .= $this->input->post('note') . "\n" . $this->input->post('phone') . "\n";


            $order_id = $this->saleorder_model->insert($array);
            foreach ($items['details'] as $row) {
                $data_up = array(
                    'order_id' => $order_id,
                    'product_id' => $row['id'],
                    'image_url' => $row['image_url'],
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'quantity' => $row['qty'],
                    'price' => $row['price'],
                    'amount' => $row['qty'] * $row['price']
                );
                $this->saleorderline_model->insert($data_up);
                $messsage .= "- " . $row['name'] . " x " . $row['qty'] . "\n";
            }
            $messsage .= "Tổng: " . number_format($items['amount_product'], 0, ",", ".") . " đ\n";

            $this->load->helper('cookie');
            delete_cookie("CART");
            //            sendMessage(-313318123, $messsage);
            echo 1;
        } else {
            echo "Lỗi đặt hàng!";
        }
    }

    function cart()
    {

        $userdata = $this->session->userdata();
        //        print_r($userdata);
        $user_id = $userdata['user_id'];
        $user_name = $userdata['name'];
        //        print_r($userdata);
        $table_id = $this->input->post('table_id');
        $cart = json_decode($this->input->post('data'), true);
        $items = array(
            'details' => array(),
            'count_product' => 0,
            'amount_product' => 0
        );
        if (count($cart) > 0) {
            //            echo "<pre>";
            //            print_r($cart);
            //            die();
            $this->load->model('product_model');
            $this->load->model('saleorder_model');
            $this->load->model('saleorderline_model');
            foreach ($cart as $key => $item) {
                $data = array();
                if (!isset($item['id']) || !isset($item['qty'])) {
                    continue;
                }
                $qty = $item['qty'];
                $id = $item['id'];

                $product = $this->product_model->where(array('id' => $id))->with_hinhanh()->as_array()->get();

                $data['id'] = $product['id'];
                $data['image_url'] = isset($product['hinhanh']->src) ? $product['hinhanh']->src : "";
                $data['code'] = $product['code'];
                $data['name'] = $product['name'];
                $data['price'] = $product['price'];

                $data['qty'] = $qty;
                $data['amount_product'] = $qty * $product['price'];


                $items['count_product'] += $qty;
                $items['amount_product'] += $qty * $product['price'];

                $items['details'][] = $data;
            }
            /*
             * 
             */
            $messsage = "";
            $array = array(
                'user_id' => $user_id,
                'table_id' => $table_id,
                'user_name' => $user_name,
                'order_date' => date("Y-m-d H:i:s"),
                'notes' => $this->input->post('note'),
                'amount' => $items['amount_product'],
                'total_amount' => $items['amount_product'],
                'create_at' => date("Y-m-d H:i:s"),
                'status' => 4
            );
            $messsage .= $this->input->post('note') . "\n";


            $order_id = $this->saleorder_model->insert($array);
            foreach ($items['details'] as $row) {
                $data_up = array(
                    'order_id' => $order_id,
                    'product_id' => $row['id'],
                    'image_url' => $row['image_url'],
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'quantity' => $row['qty'],
                    'price' => $row['price'],
                    'amount' => $row['qty'] * $row['price']
                );
                $this->saleorderline_model->insert($data_up);
                $messsage .= "- " . $row['name'] . " x " . $row['qty'] . "\n";
            }
            $messsage .= "Tổng: " . number_format($items['amount_product'], 0, ",", ".") . " đ\n";

            //            sendMessage(-313318123, $messsage);
            echo $order_id;
        } else {
            echo "Lỗi thanh toán!";
        }
    }

    function saveordercategory()
    {
        $this->load->model("category_model");
        $data = json_decode($this->input->post('data'), true);
        foreach ($data as $key => $row) {
            if (isset($row['id'])) {
                $id = $row['id'];
                $parent_id = isset($row['parent_id']) && $row['parent_id'] != "" ? $row['parent_id'] : 0;
                $is_home = isset($row['is_home']) ? $row['is_home'] : 1;
                $is_menu = isset($row['is_menu']) ? $row['is_menu'] : 1;
                $array = array(
                    'parent_id' => $parent_id,
                    'sort' => $key,
                    'is_home' => $is_home,
                    'is_menu' => $is_menu
                );
                print_r($array);
                $this->category_model->update($array, $id);
            }
        }
    }

    function savecategory()
    {
        $this->load->model("category_model");
        $data = json_decode($this->input->post('data'), true);
        $id = $data['id'];
        $data_up = $this->category_model->create_object($data);
        $this->category_model->update($data_up, $id);
    }

    function editpage()
    {
        $id = $this->input->get('id');
        $link = $this->input->get('link');
        $seo = $this->input->get('seo');
        $template = $this->input->get('template');
        $page = $this->input->get('page');
        $param = $this->input->get('param');
        $array = array(
            'seo_url' => $seo,
            'template' => $template,
            'link' => $link,
            'page' => $page,
            'param' => $param
        );
        $this->page_model->update($array, $id);
    }

    function addpage()
    {
        $link = $this->input->get('link');
        $seo = $this->input->get('seo');
        $template = $this->input->get('template');
        $page = $this->input->get('page');
        $param = $this->input->get('param');
        $array = array(
            'seo_url' => $seo,
            'template' => $template,
            'link' => $link,
            'page' => $page,
            'param' => $param
        );
        $this->page_model->insert($array);
    }

    function removepage()
    {
        $id = $this->input->get('id');
        $this->page_model->update(array("deleted" => 1), $id);
    }

    function rowpage()
    {
        //$dirmodule = APPPATH . 'modules/';
        $dir = APPPATH . 'controllers/';
        $this->load->library('directoryinfo');
        $arr = $this->directoryinfo->readDirectory($dir, array("Auth.php", "Ajax.php"));
        $arr = array($arr);
        // $sortedarray2 = $this->directoryinfo->readDirectory($dirmodule, true);
        // $arr = array_merge(array($sortedarray1), $sortedarray2);
        //        echo "<pre>";
        //        print_r($arr);
        //        die();
        $dataselect = array();
        foreach ($arr as $key => $row) {
            $module = mb_strtolower($key, 'UTF-8');
            foreach ($row as $key1 => $row1) {
                $class = mb_strtolower($key1, 'UTF-8');
                foreach ($row1 as $row2) {
                    $method = mb_strtolower($row2, 'UTF-8');
                    if ($module) {
                        $page = $module . "/" . $class . "/" . $method;
                    } else {
                        $page = $class . "/" . $method;
                    }
                    $dataselect[$page] = $page;
                }
            }
        }
        $arr_page = $this->page_model->where(array("deleted" => 0))->as_array()->get_all();
        $page_ava = array_map(function ($item) {
            return $item['link'];
        }, $arr_page);
        $this->data['page_ava'] = $page_ava;
        $this->data['link'] = $dataselect;
        echo $this->blade->view()->make('ajax/ajaxpage', $this->data)->render();
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
