<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Objecttarget_model extends MY_Model {

    public function __construct() {
        $this->table = 'pmp_object_target';
        $this->primary_key = 'id';
        parent::__construct();
    }

}
