<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/JWT/JWT.php';

use Restserver\Libraries\REST_Controller;
use Restserver\Libraries\JWT\JWT;

class Cuota extends REST_Controller
{
    protected $headers;
    function __construct()
    {
        parent::__construct();
        $this->load->model('cuota_model', 'cuota');
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

        $this->headers = apache_request_headers();
    }

    public function cuotas_get()
    {
        $result = array();

        $result['codigo']   = '1';
        $result['mensaje']  = 'cuotas';
        $cuotas = $this->cuota->getCuotas();
        $result['data']  = $cuotas;
        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function cuota_get()
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
            $cuota = $this->cuota->getCuota($id);
            $result['data']  = $cuota;
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        
    }

    public function cuota_actualizar_post()
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
        $nombre  = $this->post('nombre');
        $mes    = $this->post('mes');
        $dia    = $this->post('dia');
        $monto    = $this->post('monto');
        $rubro_id    = $this->post('rubro_id');

        if($this->cuota->updateCuota($nombre, $mes, $dia, $monto, $rubro_id, $id))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'Cuota actualizada exitosamente.';
        }else{
            $result['codigo']   = '-1';
            $result['mensaje']  = 'La cuota no pudo ser actualizada.';
        }

        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function cuota_eliminar_post()
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

        if($this->cuota->deleteCuota($id))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'Cuota eliminada';
        }
        else{
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Cuota no existe';
        }

        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function cuota_post()
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
        $mes    = $this->post('mes');
        $dia    = $this->post('dia');
        $monto    = $this->post('monto');
        $rubro_id    = $this->post('rubro_id');

        if ($nombre != '' && $mes != '' && $dia != '' && $monto != '' && $rubro_id != '') {

            $cuota_id  = $this->cuota->crearCuota($nombre, $mes, $dia, $monto, $rubro_id);
            if(!empty($cuota_id))
            {
                $result['codigo']   = '1';
                $result['mensaje']  = 'Cuota creado exitosamente.';
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