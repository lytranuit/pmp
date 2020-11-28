<?php

use Philo\Blade\Blade;

class MY_Admin extends CI_Controller
{

    protected $data = array();

    function __construct()
    {
        parent::__construct();
        $this->load->library(array('widget', 'ion_auth','ion_auth_acl','form_validation'));
        // $this->load->model("page_model");
        //        $this->load->model("user_model");
        //        echo language_current();
        ////// set langue
        $this->config->set_item('language', language_current());
        $this->lang->load(array('home'));
        ////
        $this->data['widget'] = $this->widget;
        $this->data['project_name'] = $this->config->item("project_name");
        $this->data['stylesheet_tag'] = array();
        $this->data['javascript_tag'] = array();

        ////////////////////////////////
        $views = APPPATH . "views/";
        $cache = APPPATH . "cache/";
        $this->blade = new Blade($views, $cache);
        $module = $this->router->fetch_module();
        $class = $this->router->fetch_class(); // class = controller
        $method = $this->router->fetch_method();
        //        echo $module;
        //        echo $class;
        //        die();
        $link = $module == "" ? $class . "/" . $method : $module . "/" . $class . "/" . $method;
        // $page = $this->page_model->where(array("deleted" => 0, 'link' => $link))->as_array()->get_all();
        // if (count($page)) {
        //     $this->data['content'] = $class . "." . $method;
        //     $this->data['template'] = $page[0]['template'];
        //     $this->data['title'] = $page[0]['page'];
        // } else { //////// Default
        $this->data['content'] = $class . "." . $method;
        $this->data['template'] = "template";
        $this->data['title'] = "";
        // }

        $this->data['host'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        //        print_r($this->data['template']);
    }

    ////////////
}
