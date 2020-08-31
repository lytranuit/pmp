<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Area_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_area';
        $this->primary_key = 'id';
        // $this->after_update[] = "update_after";
        $this->name = "area";
        parent::__construct();
        $this->has_one['factory'] = array('foreign_model' => 'Factory_model', 'foreign_table' => 'pmp_factory', 'foreign_key' => 'id', 'local_key' => 'factory_id');
        $this->has_one['workshop'] = array('foreign_model' => 'Workshop_model', 'foreign_table' => 'pmp_workshop', 'foreign_key' => 'id', 'local_key' => 'workshop_id');
    }

    function create_object($data)
    {
        $array = array(
            'name', 'name_en', 'deleted', 'factory_id', 'workshop_id'
        );
        $obj = array();
        foreach ($array as $key) {
            if (isset($data[$key])) {
                $obj[$key] = $data[$key];
            } else
                continue;
        }

        return $obj;
    }
    // function update_after()
    // {
    //     $this->db->trans_begin();

    //     $this->db->query("UPDATE pmp_area AS a JOIN pmp_workshop AS b ON a.workshop_id = b.id SET a.factory_id = b.factory_id;");

    //     $this->db->query("UPDATE pmp_department AS a JOIN pmp_area AS b ON a.area_id = b.id SET a.factory_id = b.factory_id ,a.`workshop_id`=b.`workshop_id`;");

    //     $this->db->query("UPDATE pmp_position AS a JOIN pmp_department AS b ON a.department_id = b.id SET a.factory_id = b.factory_id,a.`workshop_id`=b.`workshop_id`,a.area_id = b.area_id;");

    //     $this->db->query("UPDATE pmp_position AS a JOIN pmp_workshop AS b ON a.workshop_id = b.id SET a.factory_id = b.factory_id WHERE object_id IN (18,19,20);");

    //     $this->db->query("UPDATE `pmp_result` AS a JOIN pmp_position AS b ON a.position_id = b.id SET a.factory_id = b.factory_id ,a.`workshop_id`=b.`workshop_id`,a.`area_id`=b.`area_id`,a.`system_id` = b.`system_id`,a.`department_id` = b.`department_id`;");

    //     $this->db->query("UPDATE `pmp_employee_result` AS a JOIN pmp_area AS b ON a.area_id = b.id SET a.factory_id = b.factory_id ,a.`workshop_id`=b.`workshop_id`;");

    //     $this->db->trans_complete();
    // }
}
