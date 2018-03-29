<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/JWT/JWT.php';

use Restserver\Libraries\REST_Controller;
use Restserver\Libraries\JWT\JWT;

class Campana extends REST_Controller
{
    protected $headers;
    function __construct()
    {
        parent::__construct();
        $this->load->model('campana_model', 'campana');
        $this->load->model('imagen_model', 'imagen');
        date_default_timezone_set('America/Santiago');

        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            header( 'Access-Control-Allow-Credentials: true' );
            header( 'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS' );
            header( 'Access-Control-Allow-Headers: ACCEPT, ORIGIN, X-REQUESTED-WITH, CONTENT-TYPE, AUTHORIZATION' );
            header( 'Access-Control-Max-Age: 86400' );
            header( 'Content-Length: 0' );
            header( 'Content-Type: text/plain' );
            die();
        }

        $this->load->library('uploadhelper');


        $this->headers = apache_request_headers();
    }

    public function campanas_get()
    {
        $result = array();

        $result['codigo']   = '1';
        $result['mensaje']  = 'campanas';
        $campanas = $this->campana->getCampanas();
        $result['data']  = $campanas;
        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function campanas_visible_get()
    {
        $result = array();

        $result['codigo']   = '1';
        $result['mensaje']  = 'campanas';

        $user_id  = $this->get('user_id');

        $campanas = $this->campana->getCampanasVisible();

        $data = array();

        foreach($campanas as $campana){

            if(empty($this->campana->getLike($user_id,$campana->id))){
                $campana->me_gusta = false;
            }else {
                $campana->me_gusta = true;
            }
            array_push($data,$campana);
            
            
        }



        $result['data']  = $data;
        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function campana_get()
    {
        $auth = JWT::isAuth($this->headers, $this->auth_model);
        if($auth == 1 || $auth == 2)
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = $auth == 1 ? 'No se encuentra el headers de autorización.' : 'La session ha caducado.';
            $code = $auth == 1 ? REST_Controller::HTTP_PAYMENT_REQUIRED : REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($result, $code);
            return;
        }
        $result = array();
        $result['token']   = $auth;

        $id  = $this->get('id');

        if(!empty($id))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'campana';
            $campana = $this->campana->getCampana($id);
            $result['data']  = $campana;
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        
    }

    public function campana_actualizar_post()
    {

        $auth = JWT::isAuth($this->headers, $this->auth_model);
        if($auth == 1 || $auth == 2)
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = $auth == 1 ? 'No se encuentra el headers de autorización.' : 'La session ha caducado.';
            $code = $auth == 1 ? REST_Controller::HTTP_PAYMENT_REQUIRED : REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($result, $code);
            return;
        }

        $result = array();
        $result['token']   = $auth;

        $id             = $this->post('id');
        $titulo  = $this->post('titulo');
        $descripcion  = $this->post('descripcion');
        $video_url    = $this->post('video_url');
        $foto_url    = $this->post('foto_url');
        $pdf_url    = $this->post('pdf_url');


        $fecha_inicio = $this->post('fecha_inicio');
        $fecha_fin = $this->post('fecha_fin');

        //TODO:descomentar en desarrollo o QA
        // $fecha_inicio =  date('m-d-Y',strtotime($fecha_inicio));
        // $fecha_fin    =date('m-d-Y',strtotime($fecha_fin));

        $fecha_inicio =  date('d-m-Y',strtotime($fecha_inicio));
        $fecha_fin    =date('d-m-Y',strtotime($fecha_fin));


        $visiblea_vecino    = $this->post('visiblea_vecino');

        if(empty($foto_url))
        {
           
            $files = $_FILES;
            if (!empty($_FILES['userfile']['name'])) {
               
                $domain_folder_destination = 'campana';
                $foto_url2 =  $this->uploadhelper->uploadImageAndGetUrl($domain_folder_destination,$files,0);
              

                 if($this->campana->updateCampana($titulo,$descripcion,$video_url,$foto_url2,$pdf_url,$fecha_inicio,$fecha_fin,$visiblea_vecino,$id))
                    {
                        $result['codigo']   = '1';
                        //primera vez que se guarda la foto se debe devolver la $foto_url
                        $result['foto_url'] = $foto_url2;
                        $result['mensaje']  = 'Campaña actualizada exitosamente.';
                    }else{
                        $result['codigo']   = '-1';
                        $result['mensaje']  = 'La campaña no pudo ser actualizada.';
                    }
            }
        }else
        {
             if($this->campana->updateCampana($titulo,$descripcion,$video_url,$foto_url,$pdf_url,$fecha_inicio,$fecha_fin,$visiblea_vecino,$id))
                    {
                        $result['codigo']   = '1';
                        $result['mensaje']  = 'Campaña actualizada exitosamente.';
                    }else{
                        $result['codigo']   = '-1';
                        $result['mensaje']  = 'La campaña no pudo ser actualizada.';
                    }
                    
        }

        $this->set_response($result, REST_Controller::HTTP_OK);
    }

  


    public function campana_eliminar_post()
    {
        $auth = JWT::isAuth($this->headers, $this->auth_model);
        if($auth == 1 || $auth == 2)
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = $auth == 1 ? 'No se encuentra el headers de autorización.' : 'La session ha caducado.';
            $code = $auth == 1 ? REST_Controller::HTTP_PAYMENT_REQUIRED : REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($result, $code);
            return;
        }
        $result = array();
        $result['token']   = $auth;

        $id  = $this->post('id');

        if($this->campana->deleteCampana($id))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'Campaña eliminada';
        }
        else{
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Campaña no existe';
        }

        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function campana_post()
    {
        $auth = JWT::isAuth($this->headers, $this->auth_model);
        if($auth == 1 || $auth == 2)
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = $auth == 1 ? 'No se encuentra el headers de autorización.' : 'La session ha caducado.';
            $code = $auth == 1 ? REST_Controller::HTTP_PAYMENT_REQUIRED : REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($result, $code);
            return;
        }
        $result = array();
        $result['token']   = $auth;

        $titulo  = $this->post('titulo');
        $descripcion  = $this->post('descripcion');
        $video_url    = $this->post('video_url');
        $pdf_url    = $this->post('pdf_url');
        


        $fecha_inicio = $this->post('fecha_inicio');
        $fecha_fin = $this->post('fecha_fin');
        
        //descomentar en QA
        // $fecha_inicio =  date('m-d-Y',strtotime($fecha_inicio));
        // $fecha_fin    =date('m-d-Y',strtotime($fecha_fin));
         $fecha_inicio =  date('d-m-Y',strtotime($fecha_inicio));
         $fecha_fin    =date('d-m-Y',strtotime($fecha_fin));



        $visiblea_vecino    = $this->post('visiblea_vecino');


        if ($titulo != '' || $descripcion != '' || $video_url != ''  || $pdf_url != '' || $fecha_inicio != '' || $fecha_fin != '' || $visiblea_vecino !='') {

            $files = $_FILES;
            if (!empty($_FILES['userfile']['name'])) {

                $domain_folder_destination = 'campana';
                $foto_url =  $this->uploadhelper->uploadImageAndGetUrl($domain_folder_destination,$files,0);
              
                $campana_id  = $this->campana->crearCampana($titulo,$descripcion,$video_url,$foto_url,$pdf_url,$fecha_inicio,$fecha_fin,$visiblea_vecino);
                if(!empty($campana_id))
                {
                    $result['codigo']   = '1';
                    $result['mensaje']  = 'campaña creada exitosamente.';
                }
            }
            else
            {
                $result['codigo']   = '-1';
                $result['mensaje']  = 'Todos los datos son obligatorios.';
            }

            $this->set_response($result, REST_Controller::HTTP_OK);
        }
    }
    
    

    private function get_image($i, $files)
    {
        $_FILES['userfile']['name']= $files['userfile']['name'][$i];
        $_FILES['userfile']['type']= $files['userfile']['type'][$i];
        $_FILES['userfile']['tmp_name']= $files['userfile']['tmp_name'][$i];
        $_FILES['userfile']['error']= $files['userfile']['error'][$i];
        $_FILES['userfile']['size']= $files['userfile']['size'][$i];    

        


        $this->upload->initialize($this->set_upload_options());
        $this->upload->do_upload();

        $upload_doc = $this->upload->do_upload()? $this->upload->data() : false;

        $imagen = '';

        if ($upload_doc != false) 
        {
            $imagen = $upload_doc['full_path'];
            $this->load->library('s3');
            $original = $this->s3->putObject($this->s3->inputFile($upload_doc['full_path']), 'iddurazno', 'campana/' . $upload_doc['file_name']);

            $imagen = $original == true ? 'https://s3.amazonaws.com/iddurazno/campana/' . $upload_doc['file_name'] : '';
        }
        @unlink($upload_doc['full_path']);

        return $imagen;
    }

    private function set_upload_options()
    {   
        if (!is_dir(FCPATH . 'assets/uploads/files/')) {
            mkdir(FCPATH . 'assets/uploads/files/', 0777, TRUE);
        }

        //upload an image options
        $config = array();
        $config['upload_path']   = FCPATH . 'assets/uploads/files/';
        $config['allowed_types'] = 'jpg|png';
        $config['max_size']      = '10240';
        $config['encrypt_name']  = true;

        return $config;
    }


    public function campana_visibilidad_post()
    {
        $auth = JWT::isAuth($this->headers, $this->auth_model);
        if($auth == 1 || $auth == 2)
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = $auth == 1 ? 'No se encuentra el headers de autorización.' : 'La session ha caducado.';
            $code = $auth == 1 ? REST_Controller::HTTP_PAYMENT_REQUIRED : REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($result, $code);
            return;
        }
        $result = array();
        $result['token']   = $auth;

        $id = $this->post('id');
        $visiblea_vecino = $this->post('visiblea_vecino');

        if($id != '')
        {
            $id_campana  = $this->campana->visibilidad($id, $visiblea_vecino);

            if(!empty($id_campana))
            {
                $result['codigo']   = '1';
                $result['mensaje']  = $visiblea_vecino == 1 ? 'La campana está visible.' : 'La campaña esta oculta';

                $this->set_response($result, REST_Controller::HTTP_OK);
            }
        }
    }

    public function campana_likes_post()
    {
        $result = array();
        $id_usuario_app  = $this->post('id_usuario_app');
        $id_campana    = $this->post('id_campana');

        //si ya existe existe se elimina el like y si no se crea un like asociado a un usuario
        $messageResult = $this->campana->likeCampana($id_campana,$id_usuario_app);

        //mensaje personalizado segun fue like o dislike
        if($messageResult != '' )
        {
            $result['codigo']   = '1';
            $result['mensaje']  = $messageResult;
        }
        else{
            $result['codigo']   = '-1';
            $result['mensaje']  = 'No se pudieron guardar los datos.';
        }

        $this->set_response($result, REST_Controller::HTTP_OK);
    }

     public function campana_likes_get()
    {
        $auth = JWT::isAuth($this->headers, $this->auth_model);
        if($auth == 1 || $auth == 2)
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = $auth == 1 ? 'No se encuentra el headers de autorización.' : 'La session ha caducado.';
            $code = $auth == 1 ? REST_Controller::HTTP_PAYMENT_REQUIRED : REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($result, $code);
            return;
        }
        $result = array();
        $result['token']   = $auth;

        $id_campana  = $this->get('id_campana');

        if(!empty($id_campana))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'campaña likes';
            $likes = $this->campana->getLikes($id_campana);
            $result['data']  = $likes;
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        
    }
    /*** seccion comentarios ***/

    public function campana_comentarios_get()
    {
        $result = array();

        $id_campana = $this->get('id_campana');
        $result['codigo']   = '1';
        $result['mensaje']  = 'comentarios campaña';
        $comentarios = $this->campana->getComentarios($id_campana);
        $result['data']  = $comentarios;
        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function campana_comentario_get()
    {
        $auth = JWT::isAuth($this->headers, $this->auth_model);
        if($auth == 1 || $auth == 2)
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = $auth == 1 ? 'No se encuentra el headers de autorización.' : 'La session ha caducado.';
            $code = $auth == 1 ? REST_Controller::HTTP_PAYMENT_REQUIRED : REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($result, $code);
            return;
        }
        $result = array();
        $result['token']   = $auth;

        $id  = $this->get('id');

        if(!empty($id))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'campaña comentario';
            $comentario = $this->campana->getComentario($id);
            $result['data']  = $comentario;
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        
    }



    public function campana_comentario_eliminar_post()
    {
        $auth = JWT::isAuth($this->headers, $this->auth_model);
        if($auth == 1 || $auth == 2)
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = $auth == 1 ? 'No se encuentra el headers de autorización.' : 'La session ha caducado.';
            $code = $auth == 1 ? REST_Controller::HTTP_PAYMENT_REQUIRED : REST_Controller::HTTP_UNAUTHORIZED;
            $this->set_response($result, $code);
            return;
        }
        $result = array();
        $result['token']   = $auth;

        $id  = $this->post('id');

        if($this->campana->deleteComentario($id))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'Comentario eliminado';
        }
        else{
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Comentario no existe';
        }

        $this->set_response($result, REST_Controller::HTTP_OK);
    }


      public function campana_comentario_actualizar_post()
        {
            $auth = JWT::isAuth($this->headers, $this->auth_model);
            if($auth == 1 || $auth == 2)
            {
                $result['codigo']   = '-1';
                $result['mensaje']  = $auth == 1 ? 'No se encuentra el headers de autorización.' : 'La session ha caducado.';
                $code = $auth == 1 ? REST_Controller::HTTP_PAYMENT_REQUIRED : REST_Controller::HTTP_UNAUTHORIZED;
                $this->set_response($result, $code);
                return;
            }

            $result = array();
            $result['token']   = $auth;

            $id      = $this->post('id');
            $descripcion  = $this->post('descripcion');
          
            if($this->campana->updateComentario($descripcion, $id))
            {
                $result['codigo']   = '1';
                $result['mensaje']  = 'Comentario actualizado exitosamente.';
            }else{
                $result['codigo']   = '-1';
                $result['mensaje']  = 'El comentario no pudo ser actualizado.';
            }

            $this->set_response($result, REST_Controller::HTTP_OK);
        }

        public function campana_comentario_post()
        {
            $result = array();
            $descripcion  = $this->post('descripcion');
            $id_campana    = $this->post('id_campana');
            $id_usuario_app    = $this->post('id_usuario_app');
          

            if ($descripcion != '' && $id_campana != '' && $id_usuario_app != '') {

                $id  = $this->campana->crearComentario($descripcion,$id_campana,$id_usuario_app);
                if(!empty($id))
                {
                    $result['codigo']   = '1';
                    $result['mensaje']  = 'Comentario creado exitosamente.';
                }
            }
            else
            {
                $result['codigo']   = '-1';
                $result['mensaje']  = 'Todos los datos son obligatorios.';
            }

            $this->set_response($result, REST_Controller::HTTP_OK);
        }
}