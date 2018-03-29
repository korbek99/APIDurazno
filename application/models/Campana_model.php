<?php
class Campana_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function getCampanas()
    {
        $this->db->select('id, video_url,titulo,descripcion,foto_url,pdf_url,fecha_inicio,fecha_fin,visiblea_vecino,(SELECT count(id) FROM campana_likes where id_campana = campana.id) likes,(SELECT count(id) FROM campana_comentario where id_campana = campana.id) cantidad_comentarios');
        $this->db->order_by('id', 'DESC');

        $query = $this->db->get('campana');

        return $query->result();
    }

    public function getCampanasVisible()
    {
        $this->db->select('id, video_url,titulo,descripcion,foto_url,pdf_url,fecha_inicio,fecha_fin,visiblea_vecino,(SELECT count(id) FROM campana_likes where id_campana = campana.id) likes,(SELECT count(id) FROM campana_comentario where id_campana = campana.id) cantidad_comentarios');
        $this->db->where('visiblea_vecino',true);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get('campana');

        return $query->result();
    }


    public function getLike($user_id,$id_campana)
    {
        $this->db->select('id,id_campana,id_usuario_app');
        $this->db->where('id_campana',$id_campana);
        $this->db->where('id_usuario_app',$user_id);
        $query = $this->db->get('campana_likes');

        return $query->result();
    }


    public function getCampana($id)
    {
        $this->db->select('id,titulo, descripcion,video_url,foto_url,pdf_url,fecha_inicio,fecha_fin,visiblea_vecino');
        $this->db->where('id', $id);
        $query = $this->db->get('campana');

        return $query->row();
    }

    public function deleteCampana($id)
    {
        $this->db->where('id', $id);

        $this->db->delete('campana');

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public function crearCampana($titulo,$descripcion,$video_url,$foto_url,$pdf_url,$fecha_inicio,$fecha_fin,$visiblea_vecino)
    {
    

        $data = array(
            'titulo'    => $titulo,
            'descripcion'    => $descripcion,
            'video_url'     => $video_url,
            'foto_url'      => $foto_url,
            'pdf_url'      => $pdf_url,
            'fecha_inicio'      => $fecha_inicio,
            'fecha_fin'      => $fecha_fin,
            'visiblea_vecino'      => $visiblea_vecino
        );

        $this->db->insert('campana', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }
        else {
            return false;
        }
    }

    public function updateCampana($titulo,$descripcion,$video_url,$foto_url,$pdf_url,$fecha_inicio,$fecha_fin,$visiblea_vecino,$id)
    {
        $data = array(
            'titulo'    => $titulo,
            'descripcion'    => $descripcion,
            'video_url'     => $video_url,
            'foto_url'      => $foto_url,
            'pdf_url'      => $pdf_url,
            'fecha_inicio'      => $fecha_inicio,
            'fecha_fin'      => $fecha_fin,
            'visiblea_vecino'      => $visiblea_vecino
        );

        $this->db->where('id', $id);
        $this->db->update('campana', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public function visibilidad($id, $visiblea_vecino)
    {
        
        $data = array(
            'visiblea_vecino'    => $visiblea_vecino == 1 ? true : false
        );
        
        $this->db->where('id', $id);

        $this->db->update('campana', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->affected_rows();
        }
        else {
            return false;
        }
    }

    /*** likes and dislikes para la campaña ***/
        public function likeCampana($id_campana,$id_usuario_app)
        {

            $query = null; 

            $data = array(
                'id_campana'    => $id_campana,
                'id_usuario_app'     => $id_usuario_app
            );

            //revisar si ya existe un like para esta campaña/usuario app
            $query = $this->db->get_where('campana_likes',$data);
            $count = $query->num_rows();
            

            $mensaje = '';
            //like campaña
            if ($count === 0) {

                $this->db->insert('campana_likes', $data);

                if ($this->db->affected_rows() > 0) {
                    $mensaje= 'Like creado exitosamente';
                }

            }//dislike campaña
            else 
            {   
                $this->db->where('id_campana', $id_campana,'id_usuario_app',$id_usuario_app);
                $this->db->delete('campana_likes', $data);

                 if ($this->db->affected_rows() > 0) {
                    $mensaje=  'dislike creado exitosamente';
                }
            }

            return $mensaje;
        }

        public function getLikes($id_campana)
        {
            
            $this->db->select('COUNT(id) likes');
            $this->db->where('id_campana', $id_campana);
            $query = $this->db->get('campana_likes');

            return $query->row();
        
        }


     /**** COMENTARIOS de los USUARIOS APP PARA LA CAMPAÑA ****/

        public function getComentarios($campana_id)
        {
            $this->db->select('campana_comentario.id, campana_comentario.descripcion,userFB.email,userFB.first_name,userFB.last_name');
            $this->db->from('campana_comentario');
            $this->db->join('userFB', 'userFB.id = campana_comentario.id_usuario_app');
            $this->db->where('campana_comentario.id_campana', $campana_id);

            $query = $this->db->get();

            return $query->result();
        }

        public function crearComentario($descripcion,$id_campana,$id_usuario_app)
        {
        

            $data = array(
                'descripcion'    => $descripcion,
                'id_campana'     => $id_campana,
                'id_usuario_app'      => $id_usuario_app
            );

            $this->db->insert('campana_comentario', $data);

            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            }
            else {
                return false;
            }
        }

        public function deleteComentario($id)
        {
            $this->db->where('id', $id);

            $this->db->delete('campana_comentario');

            if ($this->db->affected_rows() > 0) {
                return true;
            }
            else {
                return false;
            }
        }

        public function getComentario($id)
        {

            $this->db->select('campana_comentario.id, campana_comentario.descripcion,userFB.email,userFB.first_name,userFB.last_name');
            $this->db->from('campana_comentario');
            $this->db->join('userFB', 'userFB.id = campana_comentario.id_usuario_app');
            $this->db->where('campana_comentario.id', $id);

            $query = $this->db->get();
            return $query->row();
        }


        public function updateComentario($descripcion,$id)
        {
            $data = array(
                'descripcion'    => $descripcion
            );

            $this->db->where('id', $id);
            $this->db->update('campana_comentario', $data);

            if ($this->db->affected_rows() > 0) {
                return true;
            }
            else {
                return false;
            }
        }




}