<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User_model extends MY_Model {

    public function __construct() {
        $this->table = 'users';
        $this->primary_key = 'id';
        $this->has_many_pivot['groups'] = array(
            'foreign_model' => 'Group_model',
            'pivot_table' => 'users_groups',
            'local_key' => 'id',
            'pivot_local_key' => 'user_id', /* this is the related key in the pivot table to the local key
              this is an optional key, but if your column name inside the pivot table
              doesn't respect the format of "singularlocaltable_primarykey", then you must set it. In the next title
              you will see how a pivot table should be set, if you want to  skip these keys */
            'pivot_foreign_key' => 'group_id', /* this is also optional, the same as above, but for foreign table's keys */
            'foreign_key' => 'id',
            'get_relate' => TRUE /* another optional setting, which is explained below */
        );
        $this->has_many['paids'] = array('foreign_model' => 'DebtPaid_model', 'foreign_table' => 'tbl_debt_paid', 'foreign_key' => 'user_id', 'local_key' => 'id');

        parent::__construct();
    }

    function create_object($data) {
        $array = array(
            'username', 'address', 'phone', 'active', 'last_name', 'password', 'debt'
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

    /**
     * logged_in
     *
     * @return bool
     * @author Mathew
     * */
    public function logged_in() {
        return (bool) $this->session->userdata('identity');
    }

    /**
     * login
     *
     * @return bool
     * @author Mathew
     * */
    public function login($identity, $password) {
        if (empty($identity) || empty($password)) {
            return FALSE;
        }
        $query = $this->db->select('username,id,password,active,customer_id,role')
                ->where('username', $identity)
                ->limit(1)
                ->get("user");
        if ($query->num_rows() === 1) {
            $user = $query->row();
            if (md5($password) == $user->password) {
                if ($user->active == 0) {
                    return FALSE;
                }

                $this->set_session($user);
                return TRUE;
            }
        }
    }

    /**
     * set_session
     *
     * @return bool
     * @author jrmadsen67
     * */
    public function set_session($user) {


        $session_data = array(
            'identity' => $user->username,
            'username' => $user->username,
            'role' => $user->role,
            'customer_id' => $user->customer_id,
            'user_id' => $user->id, //everyone likes to overwrite id so we'll use user_id
        );
//        print_r($permission);
//        die();
        $this->session->set_userdata($session_data);
        return TRUE;
    }

    public function set_permission($role) {
        $query = $this->db->from("role_permission")
                        ->join("permission", "role_permission.id_permission = permission.id")
                        ->where('id_role', $role)->get();
        $permission = $query->result_array();
        $permission = array_map(function($item) {
            return $item['function'];
        }, $permission);
        $this->session->set_userdata('permission', $permission);
        return TRUE;
    }

    public function role_permission($id_permission) {
        $query = $this->db->from("role_permission")
                        ->where('id_permission', $id_permission)->group_by("id_role")->get();
        $permission = $query->result_array();
        $permission = array_map(function($item) {
            return $item['id_role'];
        }, $permission);
        return $permission;
    }

    /**
     * logout
     *
     * @return void
     * @author Mathew
     * */
    public function logout() {
        $identity = $this->config->item('identity', 'ion_auth');

        if (substr(CI_VERSION, 0, 1) == '2') {
            $this->session->unset_userdata(array('identity' => '', 'id' => ''));
        } else {
            $this->session->unset_userdata(array('identity', 'id'));
        }
        // Destroy the session
        $this->session->sess_destroy();

        //Recreate the session
        if (substr(CI_VERSION, 0, 1) == '2') {
            $this->session->sess_create();
        } else {
            if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
                session_start();
            }
            $this->session->sess_regenerate(TRUE);
        }
        return TRUE;
    }

}
