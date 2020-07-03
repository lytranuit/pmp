<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class File_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_file';
        $this->primary_key = 'id';
        $this->before_create[] = 'create_date';
        parent::__construct();
    }

    protected function create_date($data)
    {
        $data['date'] = date("Y-m-d H:i:s");
        return $data;
    }
}
