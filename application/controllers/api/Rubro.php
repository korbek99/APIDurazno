<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/JWT/JWT.php';

use Restserver\Libraries\REST_Controller;
use Restserver\Libraries\JWT\JWT;

class Rubro extends REST_Controller
{
    protected $headers;
    function __construct()
    {
        parent::__construct();
        $this->load->model('rubro_model', 'rubro');
        $this->load->model('cuota_model', 'cuota');
        $this->load->model('imagen_model', 'imagen');

        $this->load->library('uploadhelper');


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

        $this->headers = apache_request_headers();
    }

    public function rubros_get()
    {
        $result = array();

    

        $result['codigo']   = '1';
        $result['mensaje']  = 'rubro';
        $rubros = $this->rubro->getRubros();
        foreach ($rubros as &$valor) {
            $valor->_cuotas = $this->cuota->getCuotasByRubro($valor->id);   
        }
        $result['data']  = $rubros;
        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function rubro_get()
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
            $result['mensaje']  = 'rubro';
            $noticias = $this->rubro->getRubro($id);
            $result['data']  = $noticias;
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        
    }

    public function rubro_actualizar_post()
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
        $nombre         = $this->post('nombre');
        $icono          = $this->post('icono');
        $year           = $this->post('year');


        $files = $_FILES;
        if (!empty($_FILES['userfile']['name'])) {
            $domain_folder_destination = 'icono';
            $icono = $this->uploadhelper->uploadImageAndGetUrl($domain_folder_destination,$files,0); 
        }


      
        if($this->rubro->updateRubro($nombre, $icono, $year, $id))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'Rubro actualizado exitosamente.';
        }else{
            $result['codigo']   = '-1';
            $result['mensaje']  = 'El rubro no pudo ser actualizado.';
        }

        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function rubro_eliminar_post()
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

        if($this->rubro->deleteRubro($id))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'Rubro eliminado';
        }
        else{
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Rubro no existe';
        }

        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function rubro_post()
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

        $nombre  = $this->post('nombre');
        $year    = $this->post('year');

        if ($nombre != '' && $year != '') {
            $files = $_FILES;
            $this->load->library('upload');

            if (!empty($_FILES['userfile']['name'])) {
                $cpt = count($_FILES['userfile']['name']);
                $img = $this->get_image(0, $files);
                $rubro_id  = $this->rubro->crearRubro($nombre, $img, $year);
                if(!empty($rubro_id))
                {
                    $result['codigo']   = '1';
                    $result['mensaje']  = 'Rubro creado exitosamente.';
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
            $original = $this->s3->putObject($this->s3->inputFile($upload_doc['full_path']), 'iddurazno', 'icono/' . $upload_doc['file_name']);
            $imagen = $original == true ? 'https://s3.amazonaws.com/iddurazno/icono/' . $upload_doc['file_name'] : '';
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
        $config['max_size']      = '1000000';
        $config['encrypt_name']  = true;

        return $config;
    }

}