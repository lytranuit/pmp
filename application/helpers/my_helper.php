<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
if (!function_exists('sendMessage')) {

    function sendMessage($chatID, $messaggio) {
        $token = "606461497:AAH68TUT1mB3adaIxlud48-r-7fi2vADkRU";
        $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chatID;
        $url = $url . "&text=" . urlencode($messaggio);
        $ch = curl_init();
        $optArray = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch, $optArray);
        $result = curl_exec($ch);
        curl_close($ch);
    }

}
if (!function_exists("input_params")) {

    function input_params($params) {
        $type = $params['type'];
        $selector = $params['selector'];

        $params['date_from'] = "";
        $params['date_to'] = "";
        $params['date_from_prev'] = "";
        $params['date_to_prev'] = "";
        if ($type == "Year") {
            $params['date_from'] = $selector . "-01-01";
            $params['date_to'] = $selector . "-12-31";
            $params['date_from_prev'] = ($selector - 1) . "-01-01";
            $params['date_to_prev'] = ($selector - 1) . "-12-31";
        } else if ($type == "HalfYear") {
            $spilt = explode("-", $selector);

            $year = $spilt[0];
            if ($spilt[1] == 1) {
                $params['date_from'] = date("Y-m-d", strtotime($year . "-01-01"));
                $params['date_to'] = date("Y-m-t", strtotime($year . "-06-01"));
                $params['date_from_prev'] = date("Y-m-01", strtotime("-6 month", strtotime($params['date_from'])));
                $params['date_to_prev'] = date("Y-m-t", strtotime("-6 month", strtotime($params['date_from'])));
            } else {
                $params['date_from'] = date("Y-m-d", strtotime($year . "-07-01"));
                $params['date_to'] = date("Y-m-t", strtotime($year . "-12-01"));
                $params['date_from_prev'] = date("Y-m-01", strtotime("-6 month", strtotime($params['date_from'])));
                $params['date_to_prev'] = date("Y-m-t", strtotime("-6 month", strtotime($params['date_from'])));
            }
        } else if ($type == "Quarter") {
            $spilt = explode("-", $selector);

            $year = $spilt[0];
            if ($spilt[1] == 1) {
                $params['date_from'] = date("Y-m-d", strtotime($year . "-01-01"));
                $params['date_to'] = date("Y-m-t", strtotime($year . "-03-01"));
                $params['date_from_prev'] = date("Y-m-01", strtotime("-3 month", strtotime($params['date_from'])));
                $params['date_to_prev'] = date("Y-m-t", strtotime("-3 month", strtotime($params['date_from'])));
            } else if ($spilt[1] == 2) {
                $params['date_from'] = date("Y-m-d", strtotime($year . "-04-01"));
                $params['date_to'] = date("Y-m-t", strtotime($year . "-06-01"));
                $params['date_from_prev'] = date("Y-m-01", strtotime("-3 month", strtotime($params['date_from'])));
                $params['date_to_prev'] = date("Y-m-t", strtotime("-3 month", strtotime($params['date_from'])));
            } else if ($spilt[1] == 3) {
                $params['date_from'] = date("Y-m-d", strtotime($year . "-07-01"));
                $params['date_to'] = date("Y-m-t", strtotime($year . "-09-01"));
                $params['date_from_prev'] = date("Y-m-01", strtotime("-3 month", strtotime($params['date_from'])));
                $params['date_to_prev'] = date("Y-m-t", strtotime("-3 month", strtotime($params['date_from'])));
            } else {
                $params['date_from'] = date("Y-m-d", strtotime($year . "-10-01"));
                $params['date_to'] = date("Y-m-t", strtotime($year . "-12-01"));
                $params['date_from_prev'] = date("Y-m-01", strtotime("-3 month", strtotime($params['date_from'])));
                $params['date_to_prev'] = date("Y-m-t", strtotime("-3 month", strtotime($params['date_from'])));
            }
        } else if ($type == "Month") {
            $params['date_from'] = date("Y-m-d", strtotime($selector . "-01"));
            $params['date_to'] = date("Y-m-t", strtotime($selector . "-01"));
            $params['date_from_prev'] = date("Y-m-01", strtotime("-1 month", strtotime($params['date_from'])));
            $params['date_to_prev'] = date("Y-m-t", strtotime("-1 month", strtotime($params['date_from'])));
        } else {
            $daterange = $params['daterange'];
            $list_date = explode(" - ", $daterange);
            $params['date_from'] = date("Y-m-d", strtotime($list_date[0]));
            $params['date_to'] = date("Y-m-d", strtotime($list_date[1]));
            $params['date_from_prev'] = "";
            $params['date_to_prev'] = "";
        }
        return $params;
    }

}
if (!function_exists('getRandomColor')) {

    function getRandomColor() {
        $letters = '0123456789ABCDEF';
        $color = '#';
        //        echo rand(0, 16) . "<br>";
        //        echo rand(0, 16);
        //        die();
        for ($i = 0; $i < 6; $i++) {
            $color .= $letters[rand(0, 15)];
        }
        return $color;
    }

}
if (!function_exists('is_Date')) {

    function is_Date($str) {
        $str = str_replace('/', '-', $str);
        $stamp = strtotime($str);
        if (is_numeric($stamp)) {
            $month = date('m', $stamp);
            $day = date('d', $stamp);
            $year = date('Y', $stamp);
            return checkdate($month, $day, $year);
        }
        return false;
    }

}
if (!function_exists('config_item')) {

    function config_item($str) {
        $CI = &get_instance();
        $item = $CI->config->item($str);
        return $item;
    }

}
if (!function_exists('sluggable')) {

    function sluggable($str) {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/[^a-z0-9-\s]/', '', $str);
        $str = preg_replace('/([\s]+)/', '-', $str);
        return $str;
    }

}
if (!function_exists('get_url_seo')) {

    function get_url_seo($func, $param = array()) {
        $url = $func;
        $CI = &get_instance();
        $CI->load->model('page_model');
        $CI->load->helper('url');
        $page = $CI->page_model->where(array("link" => $func, "deleted" => 0))->as_array()->get_all();
        if (count($page)) {
            $url = $page[0]['seo_url'] != "" ? $page[0]['seo_url'] : $url;
            $pos = 0;
            foreach ($param as $row) {
                if (strpos($url, "(:num)", $pos) !== FALSE) {
                    $pos = strpos($url, "(:num)", $pos);
                    $length = 6;
                } elseif (strpos($url, "(.*)", $pos)) {
                    $pos = strpos($url, "(.*)", $pos);
                    $length = 4;
                } else {
                    break;
                }
                $url = substr_replace($url, $row, $pos, $length);
            }
        }
        return base_url() . $url;
    }

}

if (!function_exists('get_option')) {

    function get_option($key) {
        $value = "";
        $CI = &get_instance();
        $CI->load->model('option_model');
        $option = $CI->option_model->where(array("key" => $key))->as_object()->get();
        if (!empty($option)) {
            $value = $option->value;
        }
        return $value;
    }

}
if (!function_exists('get_url_page')) {

    function get_url_page($id) {
        $url = "";
        $CI = &get_instance();
        $CI->load->model('pageweb_model');
        $CI->load->helper('url');
        $page = $CI->pageweb_model->where(array("id" => $id))->as_array()->get_all();
        if (count($page)) {
            $url = "page/$id-" . sluggable($page[0]['title']) . ".html";
        }
        return base_url() . $url;
    }

}

if (!function_exists('get_url_product')) {

    function get_url_product($id, $title) {
        $url = "product/$id-" . sluggable($title) . ".html";
        return base_url() . $url;
    }

}

if (!function_exists('get_url_category')) {

    function get_url_category($id, $title) {
        $url = "category/$id-" . sluggable($title) . ".html";
        return base_url() . $url;
    }

}
if (!function_exists('language_current')) {

    function language_current() {
        $CI = &get_instance();
        $language_current = $CI->config->item('language');
        if (isset($_SESSION['language_current'])) {
            $language_current = $_SESSION['language_current'];
        }
        return $language_current;
    }

}

if (!function_exists('short_language_current')) {

    function short_language_current() {
        $CI = &get_instance();
        $language_current = $CI->config->item('language');
        $arr_lang = $CI->config->item('language_list');
        if (isset($_SESSION['language_current'])) {
            $language_current = $_SESSION['language_current'];
        }

        return $arr_lang[$language_current];
    }

}

if (!function_exists('pick_language')) {

    function pick_language($data, $struct = 'name_') {
        $CI = &get_instance();
        $short_lang = short_language_current();
        $data = (array) $data;
        if (isset($data[$struct . $short_lang]) && $data[$struct . $short_lang] != "") {
            return $struct . $short_lang;
        } else {
            return $struct . 'vi';
        }
    }

}

if (!function_exists('strtofloat')) {

    function strtofloat($str) {
        $str = str_replace(".", "", $str); // replace dots (thousand seps) with blancs 
        $str = str_replace(",", ".", $str); // replace ',' with '.'
        if (preg_match("#([0-9\.]+)#", $str, $match)) { // search for number that may contain '.' 
            return floatval($match[0]);
        } else {
            return floatval($str); // take some last chances with floatval 
        }
    }

}
if (!function_exists('split_string')) {

    function split_string($str, $length) {
        $str = strip_tags($str);
        if (strlen($str) > $length) {
            $str = mb_substr($str, 0, $length) . "...";
        }
        return $str;
    }

}
if (!function_exists('is_permission')) {

    function is_permission($func) {
        $array_permission = $_SESSION['permission'];
        $role = $_SESSION['role'];
        if ($role == 1 || in_array($func, $array_permission)) {
            return true;
        } else {
            return false;
        }
    }

}

if (!function_exists('has_permission')) {

    function has_permission($func) {
        $CI = &get_instance();
        $CI->load->model('permission_model');
        $permission = $CI->permission_model->where(array("function" => $func, 'deleted' => 0))->as_array()->get_all();
        if (count($permission)) {
            return true;
        } else {
            return false;
        }
    }

}

if (!function_exists('nestable')) {

    function nestable($array, $column, $parent) {
        $return = array_filter($array, function ($item) use ($column, $parent) {
            return $item[$column] == $parent;
        });
        foreach ($return as &$row) {
            $row['child'] = nestable($array, $column, $row['id']);
        }

        return $return;
    }

}

if (!function_exists('array_child_category')) {

    function array_child_category($array, $parent) {
        $return = array_filter($array, function ($item) use ($parent) {
            return $item['parent_id'] == $parent;
        });
        $data = array();
        foreach ($return as $row) {
            $data[] = $row['id'];
            $child = array_child_category($array, $row['id']);
            array_merge($data, $child);
        }

        return $data;
    }

}
if (!function_exists('html_menu')) {

    function html_menu() {
        $CI = &get_instance();
        $CI->load->model('category_model');
        $category = $CI->category_model->where(array("active" => 1, 'deleted' => 0, 'is_menu' => 1))->order_by('sort', "ASC")->as_array()->get_all();
        //        echo "<pre>";
        //        print_r($)
        echo html_menu_lv1($category, 0);
    }

}
if (!function_exists('html_menu_lv1')) {

    function html_menu_lv1($array, $parent) {
        $html = "";
        $return = array_filter($array, function ($item) use ($parent) {
            return $item['parent_id'] == $parent;
        });
        ///Bebin Tag
        $html .= '';
        ///Content
        foreach ($return as $row) {
            //            $id = $row['id'];
            //            $child = array_filter($array, function($item) use($id) {
            //                return $item['parent_id'] == $id;
            //            });
            //            if (count($child) > 0) {
            //                $type = "cm-menu-item-responsive";
            //            } else {
            //                $type = 
            //            }
            $html .= '<li class="item_' . $row['id'] . ' ty-menu__item cm-menu-item-responsive">
                        <a href="' . get_url_category($row['id'], $row['name']) . '" class="ty-menu__item-link">
                            ' . $row['name'] . '
                        </a>';
            $html .= html_menu_lv2($array, $row['id']);
            $html .= '</li>';
        }
        ///End Tag
        $html .= '';

        return $html;
    }

}
if (!function_exists('html_menu_lv2')) {

    function html_menu_lv2($array, $parent) {
        $html = "";
        $return = array_filter($array, function ($item) use ($parent) {
            return $item['parent_id'] == $parent;
        });
        if (count($return)) {

            ///Content
            $has_child = FALSE;
            foreach ($return as $row) {
                $id = $row['id'];
                $child = array_filter($array, function ($item) use ($id) {
                    return $item['parent_id'] == $id;
                });
                if (count($child)) {
                    $has_child = true;
                }
            }

            ///Bebin Tag
            if ($has_child) {
                $html .= '<div class="ty-menu__submenu">
                        <ul class="ty-menu__submenu-items ty-menu__submenu-items-multiple cm-responsive-menu-submenu">';
                foreach ($return as $row) {
                    $has_child = true;
                    $html .= '<li class="ty-top-mine__submenu-col">
                                <div class="ty-menu__submenu-item-header">
                                    <a href="' . get_url_category($row['id'], $row['name']) . '" class="ty-menu__submenu-link">' . $row['name'] . '</a>
                                </div>';
                    $html .= html_menu_lv3($array, $row['id']);
                    $html .= '</li>';
                }
            } else {
                $html .= '<div class="ty-menu__submenu">
                        <ul class="ty-menu__submenu-items ty-menu__submenu-items-simple cm-responsive-menu-submenu">';
                foreach ($return as $row) {
                    $html .= '<li class="ty-menu__submenu-item">
                                <a class="ty-menu__submenu-link" href="' . get_url_category($row['id'], $row['name']) . '">' . $row['name'] . '</a>';
                    $html .= '</li>';
                }
            }

            ///End Tag
            $html .= '</ul>
                    </div>';
        }
        return $html;
    }

}
if (!function_exists('html_menu_lv3')) {

    function html_menu_lv3($array, $parent) {
        $html = "";
        $return = array_filter($array, function ($item) use ($parent) {
            return $item['parent_id'] == $parent;
        });
        if (count($return)) {

            ///Bebin Tag
            $html .= '<div class="ty-menu__submenu">
                        <ul class="ty-menu__submenu-list cm-responsive-menu-submenu">';
            ///Content
            foreach ($return as $row) {
                $html .= '<li class="ty-menu__submenu-item">
                                <a class="ty-menu__submenu-link" href="' . get_url_category($row['id'], $row['name']) . '">' . $row['name'] . '</a>';
                $html .= '</li>';
            }
            ///End Tag
            $html .= '</ul>
                    </div>';
        }
        return $html;
    }

}


if (!function_exists('html_select_category')) {

    function html_select_category($array, $column, $parent) {
        $html = "";
        $return = array_filter($array, function ($item) use ($column, $parent) {
            return $item[$column] == $parent;
        });
        ///Bebin Tag
        //        $html .= '';
        ///Content
        foreach ($return as $row) {
            $html .= '<option value = "' . $row['id'] . '">' . $row['name'] . '</option>';
            $html .= html_select_category($array, $column, $row['id']);
        }
        ///End Tag
        //        $html .= '</ol>';

        return $html;
    }

}

if (!function_exists('html_nestable')) {

    function html_nestable($array, $column, $parent) {
        $html = "";
        $return = array_filter($array, function ($item) use ($column, $parent) {
            return $item[$column] == $parent;
        });
        ///Bebin Tag
        if ($parent == 0) {
            $id_nestable = "id='nestable'";
        } else {
            $id_nestable = "";
        }
        $html .= '<ol class="dd-list" ' . $id_nestable . '>';
        ///Content
        foreach ($return as $row) {
            $is_home = $row['is_home'] ? "checked" : "";
            $is_menu = $row['is_menu'] ? "checked" : "";
            $html .= '<li class="dd-item" id="menuItem_' . $row['id'] . '" data-id="' . $row['id'] . '">
                            <div class="dd-handle"> <span class="drag-indicator"></span>
                                <div>' . $row['name'] . '</div>
                                <div class="dd-nodrag btn-group ml-auto">
                                    <div class="btn btn-sm btn-outline-light">
                                        <span>Home</span>
                                        <div class="switch-button switch-button-xs switch-button-success">
                                            <input type="checkbox" ' . $is_home . ' id="show' . $row['id'] . '" value="1">
                                            <span>
                                                <label for="show' . $row['id'] . '"></label>
                                            </span>
                                        </div>   
                                    </div>
                                    <div class="btn btn-sm btn-outline-light">
                                        <span>Menu</span>
                                        <div class="switch-button switch-button-xs switch-button-success">
                                            <input type="checkbox"  ' . $is_menu . ' id="switch' . $row['id'] . '" value="1">
                                            <span>
                                                <label for="switch' . $row['id'] . '"></label>
                                            </span>
                                        </div>   
                                    </div>
                                    <a class="btn btn-sm btn-outline-light" href="' . base_url() . 'admin/editcategory/' . $row['id'] . '">Edit</a> 
                                    <button class="btn btn-sm btn-outline-light dd-item-delete">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>';
            $html .= html_nestable($array, $column, $row['id']);
            $html .= '</li>';
        }
        ///End Tag
        $html .= '</ol>';

        return $html;
    }

}
if (!function_exists('html_page_footer')) {

    function html_page_footer() {
        $html = "";
        $CI = &get_instance();
        $CI->load->model('pageweb_model');
        $page = $CI->pageweb_model->where(array("active" => 1, 'deleted' => 0))->as_array()->get_all();
        foreach ($page as $row) {
            $html .= '<li class="ty-footer-menu__item"><a href="' . get_url_page($row['id']) . '">' . $row['title'] . '</a></li>';
        }
        return $html;
    }

}
if (!function_exists('breadcrumbs_category')) {

    function breadcrumbs_category($id, $name, $parent) {
        $html = "";
        if ($parent == 0) {
            $html .= '<a href="' . get_url_category($id, $name) . '" class="ty-breadcrumbs__a">' . $name . '</a>';
        } else {
            $CI = &get_instance();
            $CI->load->model('category_model');
            $data_parent = $CI->category_model->where(array("id" => $parent))->as_array()->get();
            $html .= breadcrumbs_category($data_parent['id'], $data_parent['name'], $data_parent['parent_id']);
            $html .= '<span class="ty-breadcrumbs__slash">/</span><a href="' . get_url_category($id, $name) . '" class="ty-breadcrumbs__a">' . $name . '</a>';
        }
        return $html;
    }

}
if (!function_exists('sync_cart')) {

    function sync_cart() {

        $CI = &get_instance();
        $items = array(
            'details' => array(),
            'count_product' => 0,
            'amount_product' => 0,
            'debt' => 0
        );
        $CI->load->helper(array('cookie'));
        $CI->load->model('product_model');
        $CI->load->model('user_model');

        $user_id = $CI->session->userdata('user_id');
        if ($user_id > 0) {
            $user = $CI->user_model->where(array('id' => $user_id))->as_object()->get();
            $items['debt'] = $user->debt;
        }
        $cart = array();
        if (get_cookie("CART") && get_cookie("CART") != "") {
            $cart = json_decode(get_cookie("CART"), true);
        }
        if (count($cart) > 0) {
            //            echo "<pre>";
            //            print_r($cart);
            //            die();
            foreach ($cart as $key => $item) {
                $data = array();
                if (!isset($item['product_id']) || !isset($item['qty'])) {
                    continue;
                }
                $qty = $item['qty'];
                $id = $item['product_id'];

                $color_name = isset($item['color']) ? "-" . $item['color'] : "";
                $product = $CI->product_model->where(array('id' => $id))->with_hinhanh()->with_size()->as_array()->get();

                $data['product_id'] = $product['id'];
                $data['image_url'] = isset($product['hinhanh']->src) ? $product['hinhanh']->src : "";
                $data['code'] = $product['code'];
                $data['name'] = $product['name'] . $color_name;
                $data['price'] = $product['price'];
                $data['size_name'] = $product['size']->name;

                $data['color'] = isset($item['color']) ? $item['color'] : "";
                $data['qty'] = $qty;
                $data['product_parent'] = $product['parent'] > 0 ? $product['parent'] : $product['id'];
                $data['amount_product'] = $qty * $product['price'];


                $items['count_product'] += $qty;
                $items['amount_product'] += $qty * $product['price'];

                $parent = $product['parent'] > 0 ? $product['parent'] : $id;
                $variant = $CI->product_model->where(array('parent' => $parent))->with_size()->as_object()->get_all();
                $parent = $CI->product_model->where(array('id' => $parent))->with_colors()->with_size()->as_object()->get();
                array_unshift($variant, $parent);
                $array_size = array();
                //                echo "<pre>";
                //                print_r($variant);
                //                die();
                foreach ($variant as $key1 => $row) {
                    if (isset($row->size->id) && !isset($array_size[$row->size->id])) {
                        $array_size[$row->size->id] = array('product_id' => $row->id, 'size_id' => $row->size->id, 'size_name' => $row->size->name);
                    }
                }
                $data['sizes'] = $array_size;
                $data['colors'] = $parent->colors;

                $items['details'][] = $data;
            }
            //            echo "<pre>";
            //            print_r($items);
            //            die();
            //            $cookie = array(
            //                'name' => 'CART',
            //                'value' => json_encode($items),
            //                'secure' => TRUE
            //            );
            //            $CI->input->set_cookie($co
        }
        return $items;
    }

}
if (!function_exists('sync_wishlist')) {

    function sync_wishlist() {

        $CI = &get_instance();
        $CI->load->model('product_model');
        $wish_list = array();
        if (get_cookie("WISHLIST") && get_cookie("WISHLIST") != "") {
            $wish_list = json_decode(get_cookie("WISHLIST"), true);
        }
        $items = array();
        if (count($wish_list) > 0) {
            foreach ($wish_list as $key => $id) {
                $data = array();
                $product = $CI->product_model->where(array('id' => $id))->with_hinhanh()->with_size()->as_array()->get();
                $data['product_id'] = $product['id'];
                $data['image_url'] = isset($product['hinhanh']->src) ? $product['hinhanh']->src : "";
                $data['code'] = $product['code'];
                $data['name'] = $product['name'];
                $data['price'] = $product['price'];
                $data['size_name'] = $product['size']->name;

                $items[] = $data;
            }
        }
        return $items;
    }

}
if (!function_exists('is_wishlist')) {

    function is_wishlist($product_id) {
        $wish_list = array();
        if (get_cookie("WISHLIST") && get_cookie("WISHLIST") != "") {
            $wish_list = json_decode(get_cookie("WISHLIST"), true);
        }
        //            echo $product_id;
        //            print_r($wish_list);
        //            var_dump(in_array($product_id, $wish_list));
        //            die();
        return in_array($product_id, $wish_list);
    }

}

if (!function_exists('html_img_second')) {

    function html_img_second($product_id) {

        $db = &DB();
        $where = "WHERE a.product_id = $product_id";
        $sql = "SELECT b.* FROM product_hinhanh as a JOIN tbl_hinhanh as b ON a.hinhanh_id = b.id_hinhanh $where ";
        //        echo $sql . "<br>";
        $query = $db->query($sql);
        $rows = $query->result_array();
        if (empty($rows))
            return "";
        if (count($rows) == 1) {
            $row = $rows[0];
        } else {
            $row = $rows[1];
        }
        $html = "<img src='" . base_url() . $row['src'] . "' alt='" . $row['real_hinhanh'] . "'/>";


        return $html;
    }

}
