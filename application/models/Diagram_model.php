<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Diagram_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'pmp_diagram';
        $this->primary_key = 'id';
        parent::__construct();
        $this->has_many_pivot['positions'] = array(
            'foreign_model' => 'Position_model',
            'pivot_table' => 'pmp_diagram_position',
            'local_key' => 'id',
            'pivot_local_key' => 'diagram_id', /* this is the related key in the pivot table to the local key
              this is an optional key, but if your column name inside the pivot table
              doesn't respect the format of "singularlocaltable_primarykey", then you must set it. In the next title
              you will see how a pivot table should be set, if you want to  skip these keys */
            'pivot_foreign_key' => 'position_id', /* this is also optional, the same as above, but for foreign table's keys */
            'foreign_key' => 'id',
            'get_relate' => TRUE /* another optional setting, which is explained below */
        );

        $this->has_many_pivot['images'] = array(
            'foreign_model' => 'File_model',
            'pivot_table' => 'pmp_diagram_image',
            'local_key' => 'id',
            'pivot_local_key' => 'diagram_id', /* this is the related key in the pivot table to the local key
              this is an optional key, but if your column name inside the pivot table
              doesn't respect the format of "singularlocaltable_primarykey", then you must set it. In the next title
              you will see how a pivot table should be set, if you want to  skip these keys */
            'pivot_foreign_key' => 'image_id', /* this is also optional, the same as above, but for foreign table's keys */
            'foreign_key' => 'id',
            'get_relate' => TRUE /* another optional setting, which is explained below */
        );
    }

    function create_object($data)
    {
        $array = array(
            'name', 'name_en', 'deleted'
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
}
