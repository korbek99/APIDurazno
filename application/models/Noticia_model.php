<?php
class Noticia_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getNoticias($destacada)
    {
        $this->db->select('id, titulo, imagen, descripcion, imgYoutube, urlYoutube, destacada');
        if(strtolower($destacada) === "true") $this->db->where('destacada', $destacada);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get('noticia');

        return $query->result();
    }

    public function getNoticia($id)
    {

      
        $this->db->select('id, titulo, imagen, imgYoutube, descripcion, urlYoutube,destacada');
       // $this->db->select("destacada::text as destacada",  FALSE );
        $this->db->where('id', $id);
        $query = $this->db->get('noticia');

        return $query->row();
    }

    public function getImagenesNoticias($id)
    {
        $this->db->select('id, url, noticia_id');
        $this->db->where('noticia_id', $id);
        $query = $this->db->get('foto');

        return $query->result();
    }

    public function deleteNoticia($id)
    {
        $this->db->where('id', $id);

        $this->db->delete('noticia');

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public function deleteImagenes($imagenes, $id)
    {
        $this->db->where('noticia_id', $id);
        $this->db->where_in('id',$imagenes);
        //$this->db->where_not_in('id', $imagenes);

        $this->db->delete('foto');

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public function crearNoticia($titulo, $descripcion, $urlYoutube, $imgYoutube, $imagen, $destacada)
    {
        $data = array(
            'urlYoutube'    => $urlYoutube,
            'imgYoutube'    => $imgYoutube,
            'titulo'        => $titulo,
            'descripcion'   => $descripcion,
            'imagen'        => $imagen,
            'destacada'     => $destacada

        );

        $this->db->insert('noticia', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }
        else {
            return false;
        }
    }

    public function updateNoticia($id, $titulo, $descripcion, $urlYoutube, $imgYoutube, $imagen, $destacada)
    {
        $data = array(
            'urlYoutube'    => $urlYoutube,
            'imgYoutube'    => $imgYoutube,
            'titulo'        => $titulo,
            'descripcion'   => $descripcion,
            'imagen'        => $imagen,
            'destacada'     => $destacada

        );
        $this->db->where('id', $id);
        $this->db->update('noticia', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->affected_rows();
        }
        else {
            return false;
        }
    }

    public function noticiaDestacada($id, $tipo)
    {
        $data = array(
            'destacada'    => $tipo == 1 ? true : false
        );
        
        $this->db->where('id', $id);

        $this->db->update('noticia', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->affected_rows();
        }
        else {
            return false;
        }
    }
}