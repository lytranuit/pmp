<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Page_model extends MY_Model {

    public function __construct() {
        $this->table = 'tbl_page';
        $this->primary_key = 'id';
        $this->before_create[] = 'add_page';
        parent::__construct();
    }

    public function add_page($data) {
        $link = explode("/", $data['link']);
        if (count($link) > 1) {
            $method = $link[count($link) - 1];
            $class = $link[count($link) - 2];
            $dir = APPPATH . "views/include/$class/";
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            if (!file_exists($dir . $method . ".blade.php")) {
                file_put_contents($dir . $method . ".blade.php", "");
            }
        }
        return $data;
    }

}
