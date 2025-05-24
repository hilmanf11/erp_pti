<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Banshu extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        //PostgreSQL
        $this->pg = $this->load->database('pg', TRUE);
    }

    function index()
    {
        show_404();
    }

    function query($query)
    {
        $query = $this->pg->query($query);
        $records = $query->result_array();
        return $records;
    }

    function read($table, $like = [], $where = [], $limit = "", $orderfield = "", $orderby = "", $groupby = [], $select = "*")
    {
        $this->pg->select($select);
        $this->pg->from($table);
        if ($where != []) {
            $this->pg->where($where);
        }
        if ($like != []) {
            $this->pg->like($like);
        }
        if ($limit != "") {
            $this->pg->limit($limit);
        }
        if ($orderby != "") {
            $this->pg->order_by($orderfield, $orderby);
        }
        if ($groupby != []) {
            $this->pg->group_by($groupby);
        }
        $records = $this->pg->get()->row();
        return $records;
    }

    function reads($table, $like = [], $where = [], $limit = "", $orderfield = "", $orderby = "", $groupby = [], $select = "*")
    {
        $this->pg->select($select);
        $this->pg->from($table);
        if ($where != []) {
            $this->pg->where($where);
        }
        if ($like != []) {
            $this->pg->like($like);
        }
        if ($limit != "") {
            $this->pg->limit($limit);
        }
        if ($orderby != "") {
            $this->pg->order_by($orderfield, $orderby);
        }
        if ($groupby != []) {
            $this->pg->group_by($groupby);
        }
        $records = $this->pg->get()->result_object();
        return $records;
    }
}
