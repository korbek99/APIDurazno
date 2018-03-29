<?php
class Rubro_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function getRubros()
    {
        
        $this->db->select('rubro.id, rubro.nombre, icono, year, COUNT(cuota.id) cuotas');
        $this->db->from('rubro');
        $this->db->join('cuota', 'cuota.rubro_id = rubro.id', 'left');
        $this->db->group_by('rubro.id');
        $this->db->order_by('id', 'DESC');
        
        $query = $this->db->get();

        return $query->result();
    }

    public function getRubro($id)
    {
        $this->db->select('id, nombre, icono, year');
        $this->db->where('id', $id);
        $query = $this->db->get('rubro');

        return $query->row();
    }

    public function deleteRubro($id)
    {
        $this->db->where('id', $id);

        $this->db->delete('rubro');

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public function crearRubro($nombre, $icono, $year)
    {
        $data = array(
            'nombre'    => $nombre,
            'icono'     => $icono,
            'year'      => $year

        );

        $this->db->insert('rubro', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }
        else {
            return false;
        }
    }

    public function updateRubro($nombre, $icono, $year, $id)
    {
        $data = array(
            'nombre'    => $nombre,
            'icono'     => $icono,
            'year'      => $year

        );
        $this->db->where('id', $id);
        $this->db->update('rubro', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }
}