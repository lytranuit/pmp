<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Job_schedule_model extends MY_Model
{

    public function __construct()
    {
        $this->table = 'job_schedule';
        $this->primary_key = 'id';
        // $this->after_update[] = "update_after";
        $this->name = "job_schedule";
        parent::__construct();
        $this->has_one['job'] = array('foreign_model' => 'Job_model', 'foreign_table' => 'job', 'foreign_key' => 'id', 'local_key' => 'job_id');
    }

    function create_object($data)
    {
        $array = array(
            'job_id', 'deleted', 'mail', 'start_date', 'time_send', 'before_send', 'hour_send', 'frequency'
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
    function update_next_send($id)
    {
        $schedule = $this->where("id", $id)->get();

        $start_date = $schedule->start_date;
        $hour_send = $schedule->hour_send;
        $before_send = $schedule->before_send;
        $frequency = $schedule->frequency;
        $time_send = $schedule->time_send;
        $time_send_int = strtotime($time_send);
        $now_int = time();
        while ($now_int > $time_send_int) {
            // print_r(date("Y-m-d H:i:s", $time_send_int) . "<br>");
            // print_r(date("Y-m-d H:i:s", $now_int) . "<br>");
            $change_date = date("Y-m-d H:i:s", $time_send_int);
            $time_send_int = strtotime($time_send . " -$before_send day +$frequency day");
            $next_date = date("Y-m-d H:i:s", strtotime($time_send . " +$frequency day"));
            // print_r(date("Y-m-d H:i:s", $time_send_int) . "<br>");

            $time_send = date("Y-m-d H:i:s", $time_send_int);
        }

        $this->update(array("time_send" => date("Y-m-d H:i:s", $time_send_int), 'change_date' => $change_date, 'next_date' => $next_date), $id);
    }
    function get_send()
    {
        $sql = "SELECT * FROM `job_schedule` WHERE time_send <= NOW() and deleted = 0";
        //        echo $sql . "<br>";
        $query = $this->db->query($sql);
        $rows = $query->result_object();
        return $rows;
    }
}
