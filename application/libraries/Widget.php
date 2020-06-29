<?php

use Philo\Blade\Blade;

class Widget
{

    private $data = array();
    protected $CI;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->lang->load(array('home'));
        // $this->data['ion_auth'] =  $this->CI->ion_auth;
        //        $this->CI->load->model("user_model");
        //        $this->data['is_login'] = $this->CI->user_model->logged_in();
        //        $this->data['userdata'] = $this->CI->session->userdata();
        ////////////////////////////////
        $views = APPPATH . "views/";
        $cache = APPPATH . "cache/";
        $this->blade = new Blade($views, $cache);
    }

    public function header()
    {
        $this->data['is_login'] = $this->CI->ion_auth->logged_in();
        $this->data['is_admin'] = $this->CI->ion_auth->is_admin();
        $this->data['userdata'] = $this->CI->session->userdata();

        $this->CI->load->model("object_model");
        $this->data['objects'] = $this->CI->object_model->where(array("deleted" => 0))->as_array()->get_all();
        echo $this->blade->view()->make('widget/header', $this->data)->render();
    }

    public function footer()
    {
        $this->CI->load->model("pageweb_model");
        $this->data['all_page'] = $this->CI->pageweb_model->where(array("deleted" => 0, 'active' => 1))->as_array()->get_all();
        $this->CI->load->model("option_model");
        $this->data['options'] = $this->CI->option_model->get_options_in(array("dia_chi", 'mo_ta', 'email', 'hot_line', 'fan_page', 'phone'));
        echo $this->blade->view()->make('widget/footer', $this->data)->render();
    }

    public function nav_menu_mobile()
    {
        $this->CI->load->model("category_model");
        $all_category = $this->CI->category_model->where(array("deleted" => 0, 'is_menu' => 1, 'active' => 1))->order_by('sort', "ASC")->with_hinhanh()->as_array()->get_all();

        $cate_level1 = array_values(array_filter($all_category, function ($item) {
            return $item['parent_id'] == 0;
        }));
        foreach ($cate_level1 as &$cate) {
            $cate_id = $cate['id'];
            $child = array_values(array_filter($all_category, function ($item) use ($cate_id) {
                return $item['parent_id'] == $cate_id;
            }));
            $cate['child'] = $child;
        }
        $this->data['cate_level1'] = $cate_level1;
        echo $this->blade->view()->make('widget/nav_menu_mobile', $this->data)->render();
    }

    public function nav_menu()
    {
        $this->CI->load->model("category_model");
        $all_category = $this->CI->category_model->where(array("deleted" => 0, 'is_menu' => 1, 'active' => 1))->order_by('sort', "ASC")->with_hinhanh()->as_array()->get_all();

        $cate_level1 = array_values(array_filter($all_category, function ($item) {
            return $item['parent_id'] == 0;
        }));
        foreach ($cate_level1 as &$cate) {
            $cate_id = $cate['id'];
            $child = array_values(array_filter($all_category, function ($item) use ($cate_id) {
                return $item['parent_id'] == $cate_id;
            }));
            $cate['child'] = $child;
        }
        $this->data['cate_level1'] = $cate_level1;
        echo $this->blade->view()->make('widget/nav_menu', $this->data)->render();
    }

    public function index_slider()
    {

        $this->CI->load->model("slider_model");
        $this->data['all_slider'] = $this->CI->slider_model->where(array("deleted" => 0, 'active' => 1))->order_by('date', "DESC")->with_hinhanh()->as_object()->get_all();

        echo $this->blade->view()->make('widget/index_slider', $this->data)->render();
    }

    function index_banner()
    {
        $this->CI->load->model("banner_model");
        $this->data['banner'] = $this->CI->banner_model->where(array('deleted' => 0))->with_hinhanh()->as_object()->get_all();
        echo $this->blade->view()->make('widget/index_banner', $this->data)->render();
    }

    function index_product()
    {
        $this->CI->load->model("product_model");
        $this->CI->load->model("category_model");
        $all_category = $this->CI->category_model->where(array("deleted" => 0, 'is_home' => 1, 'active' => 1, 'parent_id' => 0))->order_by('sort', "ASC")->as_array()->get_all();
        foreach ($all_category as &$row) {
            $row['product'] = $this->CI->product_model->get_product_by_category($row['id'], "", NULL, NULL, 8);
        }
        $this->data['category'] = $all_category;
        echo $this->blade->view()->make('widget/index_product', $this->data)->render();
    }

    function index_lookbook()
    {
        $this->CI->load->model("lookbook_model");
        $this->data['all_obj'] = $this->CI->lookbook_model->where(array("deleted" => 0, 'active' => 1))->order_by('date', "DESC")->with_hinhanh()->as_object()->get_all();

        echo $this->blade->view()->make('widget/index_lookbook', $this->data)->render();
    }

    public function index_contact()
    {
        echo $this->blade->view()->make('widget/index_contact', $this->data)->render();
    }

    function seen()
    {
        echo $this->blade->view()->make('widget/widget_seen', $this->data)->render();
    }

    function page()
    {

        $this->CI->load->model("pageweb_model");
        $this->data['all_page'] = $this->CI->pageweb_model->where(array("deleted" => 0, 'active' => 1))->as_array()->get_all();

        echo $this->blade->view()->make('widget/widget_page', $this->data)->render();
    }

    function product_hot()
    {
        $this->CI->load->model("product_model");
        $this->data['product'] = $this->CI->product_model->get_product_by_category(29);
        echo $this->blade->view()->make('widget/widget_product_hot', $this->data)->render();
    }
}
