<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Productcategory_model extends MY_Model {

    public function __construct() {
        $this->table = 'product_category';
        $this->primary_key = 'id';
        parent::__construct();
    }

}
