<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/JWT/JWT.php';


use Restserver\Libraries\REST_Controller;
use Restserver\Libraries\JWT\JWT;


class Configuracion extends REST_Controller
{
    protected $headers;
    function __construct()
    {
        parent::__construct();
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



     
    public function configuracion_app_get()
    {
       
        $result = array();
        
        $result['codigo']   = '1';
        $result['mensaje']  = 'parametros de configuración';


        $data = array(
            'urlTerminosYCondiciones' =>$this->config->item('url_terminos_y_condiciones')
        );

        $result['data']  = $data;
       

        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function reset_password_uri_scheme_app_get()
    {
        
        $token_reset_at = $this->get('token_reset_at');
        $token = $this->get('token');
        $user_id= $this->get('user_id');

        //parametros para abrir la aplicacion ios/android
       $url = $this->config->item('uri_launch_mobile_app')."user_id=".$user_id."&token=".$token."&token_reset_at=".$token_reset_at;

        header("Location:".$url);
       
    }
      
       


}