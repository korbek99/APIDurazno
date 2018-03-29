<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Imagen extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('imagen_model', 'imagen');
        date_default_timezone_set('America/Santiago');
    }

}