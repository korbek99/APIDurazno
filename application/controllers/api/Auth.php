<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/JWT/JWT.php';

use Restserver\Libraries\REST_Controller;
use Restserver\Libraries\JWT\JWT;

class Auth extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model("auth_model");
        date_default_timezone_set('America/Santiago');

         //Codigo para habilitar CROSS
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
    public function login_post()
    {
        $result = array();

        if(!$this->post("email") || !$this->post("password"))
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Todos los datos son necesarios';
            $this->set_response($result, REST_Controller::HTTP_OK);
            return;
        }
        $email = $this->post("email");
        $password = sha1($this->post("password"));
        $user = $this->auth_model->login($email, $password);
        
        if($user !== false)
        {
            //ha hecho login
            $user->iat = time();
            $user->exp = time() + 1000;
            $jwt = JWT::encode($user, JWT::$secret_key);
            $result['codigo']   = '1';
            $result['mensaje']  = 'Token creado con éxito.';
            $result['token'] = $jwt;
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        else
        {   
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Datos invalidos.';
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
    }

}
