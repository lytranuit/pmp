<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Productopen_model extends MY_Model {

    public function __construct() {
        $this->table = 'product_open';
        $this->primary_key = 'id';
        parent::__construct();
    }

}
