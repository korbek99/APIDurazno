<?php
class Imagen_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function crearImagen($url, $noticia_id)
    {
        $data = array(
            'url'    => $url,
            'noticia_id'    => $noticia_id

        );

        $this->db->insert('foto', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }
        else {
            return false;
        }
    }
}