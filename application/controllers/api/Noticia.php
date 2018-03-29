<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/JWT/JWT.php';

use Restserver\Libraries\REST_Controller;
use Restserver\Libraries\JWT\JWT;

class Noticia extends REST_Controller
{
    protected $headers;
    function __construct()
    {
        parent::__construct();
        $this->load->model('noticia_model', 'noticia');
        $this->load->model('imagen_model', 'imagen');
        date_default_timezone_set('America/Santiago');

        $this->load->library('uploadhelper');
    
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

        $this->headers = apache_request_headers();
    }

    public function noticias_get()
    {
        $destacada = $this->get('destacada');
        if(empty($destacada)) $destacada = false; 
        $result = array();

        $result['codigo']   = '1';
        $result['mensaje']  = 'noticias';
        $noticias = $this->noticia->getNoticias($destacada);
        $result['data']  = $noticias;
        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function noticia_get()
    {
        $result = array();

        $id  = $this->get('id');

        if(!empty($id))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'noticia';
            $noticias = $this->noticia->getNoticia($id);
            $noticias->imagenes = $this->noticia->getImagenesNoticias($id);
            $result['data']  = $noticias;
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        
    }

    public function noticia_eliminar_post()
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

        if($this->noticia->deleteNoticia($id))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'Noticia eliminada';
        }
        else{
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Noticia no existe';
        }

        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function noticia_destacada_post()
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
        $tipo = $this->post('tipo');

        if($id != '')
        {
            $noticia_id  = $this->noticia->noticiaDestacada($id, $tipo);

            if(!empty($noticia_id))
            {
                $result['codigo']   = '1';
                $result['mensaje']  = $tipo == 1 ? 'La noticia está destacada.' : 'La noticia dejó de ser destacada';

                $this->set_response($result, REST_Controller::HTTP_OK);
            }
        }
    }

    public function noticia_post()
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

        $titulo         = $this->post('titulo');
        $descripcion    = $this->post('descripcion');
        $urlYoutube     = $this->post('urlYoutube');
        $destacada      = $this->post('destacada');
        if($destacada == '' || $destacada == null) $destacada = false;

         $imagenYoutube = '';
         if($urlYoutube != ''){
            if (filter_var($urlYoutube, FILTER_VALIDATE_URL)== FALSE){

                   $result['codigo']   = '-1';
                   $result['mensaje']  = 'Debe ingresar una URL de youtube valida.';
                   $this->set_response($result, REST_Controller::HTTP_OK);
                   return;
            }else{
                parse_str(parse_url($urlYoutube, PHP_URL_QUERY), $urlVar);
                $imagenYoutube = 'https://img.youtube.com/vi/'.$urlVar['v'].'/hqdefault.jpg';
            }
         }
      

        if ($titulo != '' && $descripcion != '') {
      


            $files = $_FILES;
            $this->load->library('upload');
            $result['token']   = $auth;
            if (!empty($_FILES['userfile']['name'])) {
                
                $cpt = count($_FILES['userfile']['name']);
               // $img = $this->get_image(0, $files);

                $domain_folder_destination = 'noticias';
                $img = $this->uploadhelper->uploadImageAndGetUrl($domain_folder_destination,$files,0); 
                


                $noticia_id  = $this->noticia->crearNoticia($titulo, $descripcion, $urlYoutube, $imagenYoutube, $img, $destacada);
                if(!empty($noticia_id))
                {   
                    for($i=1; $i<$cpt; $i++)
                    {
                        //$img = $this->get_image($i, $files);
    
                        $domain_folder_destination = 'noticias';
                        $img = $this->uploadhelper->uploadImageAndGetUrl($domain_folder_destination,$files,$i); 
                    
                        $this->imagen->crearImagen($img, $noticia_id);
                    }

                    $result['codigo']   = '1';
                    $result['mensaje']  = 'Noticia creada exitosamente.';
                }else{
                    $result['codigo']   = '1';
                    $result['mensaje']  = 'No se logro crear la noticia. Intente nuevamente';
                }
                
            }
        }
        else
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Todos los datos son obligatorios.';
        }

        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function noticia_actualizar_post()
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
        $titulo         = $this->post('titulo');
        $descripcion    = $this->post('descripcion');
        $urlYoutube     = $this->post('urlYoutube');
        $destacada      = $this->post('destacada');
        //$imagenes       = $this->post('imagenes');
        $imagenes_para_borrar       = $this->post('imagenes_para_borrar');
        $imagen_principal       = $this->post('imagen');




         $imagenYoutube = '';
         if($urlYoutube != ''){
            if (filter_var($urlYoutube, FILTER_VALIDATE_URL)== FALSE){

                   $result['codigo']   = '-1';
                   $result['mensaje']  = 'Debe ingresar una URL de youtube valida.';
                   $this->set_response($result, REST_Controller::HTTP_OK);
                   return;
            }else{
                parse_str(parse_url($urlYoutube, PHP_URL_QUERY), $urlVar);
                $imagenYoutube = 'https://img.youtube.com/vi/'.$urlVar['v'].'/hqdefault.jpg';
            }
         }


        if (!empty($imagenes_para_borrar)) {
            $this->noticia->deleteImagenes($imagenes_para_borrar, $id);
        }

        $files = $_FILES;
        
        //agregar nuevas imagenes
        if (!empty($_FILES['userfile']['name'])) {

            $cpt = count($_FILES['userfile']['name']);
            $starImg = 0;

            //actualizar imagen principal
            if(empty($imagen_principal))
            {

                if (!empty($_FILES['userfile']['name'])) {
                    $domain_folder_destination = 'noticias';
                    $imagen_principal = $this->uploadhelper->uploadImageAndGetUrl($domain_folder_destination,$files,0); 
                }

                $starImg = 1;
            }

            $this->noticia->updateNoticia($id, $titulo, $descripcion, $urlYoutube, $imagenYoutube, $imagen_principal, $destacada);

            for($i=$starImg; $i<$cpt; $i++)
            {
                $domain_folder_destination = 'noticias';
                $img = $this->uploadhelper->uploadImageAndGetUrl($domain_folder_destination,$files,$i); 


                $this->imagen->crearImagen($img, $id);
            }

            $result['codigo']   = '1';
            $result['mensaje']  = 'Noticia actualizada exitosamente.';
            
        }else{
            //actulizar noticia sin agregar nuevas imagenes
            $this->noticia->updateNoticia($id, $titulo, $descripcion, $urlYoutube, $imagenYoutube, $imagen_principal, $destacada);

            $result['codigo']   = '1';
            $result['mensaje']  = 'Noticia actualizada exitosamente.';
        }

        $this->set_response($result, REST_Controller::HTTP_OK);
    }



}