<?php
class Home_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getHome()
    {
        $this->db->select('COUNT(id) as usuarios_fb');
        $query = $this->db->get('userFB');
        return $query->result();
    }
}