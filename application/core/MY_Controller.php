<?php

use Philo\Blade\Blade;

class MY_Controller extends CI_Controller
{

    protected $data = array();

    function __construct()
    {
        parent::__construct();
        $this->load->library(array('widget', 'ion_auth'));
        $this->load->model("page_model");
        //        $this->load->model("user_model");
        //        echo language_current();
        ////// set langue
        $this->config->set_item('language', language_current());
        $this->lang->load(array('home'));
        ////
        $this->data['widget'] = $this->widget;
        $this->data['ion_auth'] = $this->ion_auth;
        $this->data['project_name'] = $this->config->item("project_name");
        $this->data['stylesheet_tag'] = array();
        $this->data['javascript_tag'] = array(

            base_url() . "public/assets/jquery.cookies.2.2.0.min.js",
        );

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
        $page = $this->page_model->where(array("deleted" => 0, 'link' => $link))->as_array()->get_all();
        if (count($page)) {
            $this->data['content'] = $class . "." . $method;
            $this->data['template'] = $page[0]['template'];
            $this->data['title'] = $page[0]['page'];
        } else { //////// Default
            $this->data['content'] = $class . "." . $method;
            $this->data['template'] = "template";
            $this->data['title'] = "";
        }

        $object_id = isset($_COOKIE['SELECT_ID']) ? $_COOKIE['SELECT_ID'] : 3;
        $this->data['object_id'] = $object_id;
        $this->data['host'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        //        print_r($this->data['template']);
    }

    public function redirect_result($object_id)
    {
        if ($object_id == 3) {
            redirect("resulte", "refresh");
        } elseif ($object_id == 14 || $object_id == 15 || $object_id == 16 || $object_id == 17) {
            redirect("result_tieuphan", "refresh");
        } elseif ($object_id == 18 || $object_id == 19 || $object_id == 20) {
            redirect("result_nuoc", "refresh");
        } else {
            redirect("result", "refresh");
        }
    }
    ////////////
}
