<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/JWT/JWT.php';

use Restserver\Libraries\REST_Controller;
use Restserver\Libraries\JWT\JWT;

class Home extends REST_Controller
{
    protected $headers;
    function __construct()
    {
        parent::__construct();
        //header('Access-Control-Expose-Headers: "Authorization"');
        header("Access-Control-Allow-Headers: Authorization, X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
        die();
        }
        $this->load->model('home_model', 'home');
        $this->headers = apache_request_headers();
        date_default_timezone_set('America/Santiago');
    }

    public function home_get()
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
        $result['codigo']   = '1';
        $result['mensaje']  = 'home';
        $home = $this->home->getHome()[0];
        $result['data']  = $home;
        $this->set_response($result, REST_Controller::HTTP_OK);

    }

}