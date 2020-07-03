<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Diagram_position_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_diagram_position';
        $this->primary_key = 'id';
        parent::__construct();

        $this->has_one['diagram'] = array('foreign_model' => 'diagram_model', 'foreign_table' => 'pmp_diagram', 'foreign_key' => 'id', 'local_key' => 'diagram_id');

        $this->has_one['position'] = array('foreign_model' => 'position_model', 'foreign_table' => 'pmp_position', 'foreign_key' => 'id', 'local_key' => 'position_id');
    }
}
