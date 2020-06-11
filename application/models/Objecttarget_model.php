<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Objecttarget_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_object_target';
        $this->primary_key = 'id';
        parent::__construct();

        $this->has_one['object'] = array('foreign_model' => 'Object_model', 'foreign_table' => 'pmp_object', 'foreign_key' => 'id', 'local_key' => 'object_id');

        $this->has_one['target'] = array('foreign_model' => 'Target_model', 'foreign_table' => 'pmp_target', 'foreign_key' => 'id', 'local_key' => 'target_id');
        $this->has_one['parent'] = array('foreign_model' => 'Objecttarget_model', 'foreign_table' => 'pmp_object_target', 'foreign_key' => 'id', 'local_key' => 'parent_id');
    }
}
