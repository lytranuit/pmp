<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Diagram_image_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_diagram_image';
        $this->primary_key = 'id';
        parent::__construct();

        $this->has_one['diagram'] = array('foreign_model' => 'diagram_model', 'foreign_table' => 'pmp_diagram', 'foreign_key' => 'id', 'local_key' => 'diagram_id');

        $this->has_one['image'] = array('foreign_model' => 'File_model', 'foreign_table' => 'pmp_file', 'foreign_key' => 'id', 'local_key' => 'image_id');
    }
}
