<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Groupobject_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'Groups_objects';
        $this->primary_key = 'id';
        parent::__construct();
    }
}
