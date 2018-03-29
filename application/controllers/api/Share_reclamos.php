<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/JWT/JWT.php';

use Restserver\Libraries\REST_Controller;
use Restserver\Libraries\JWT\JWT;


class share_reclamos extends REST_Controller
{
	public function share_reclamo_get()
	{
  		$templatefile = 'test.html';
		$page = file_get_contents($templatefile);
		$page = str_replace('{Page_Title}', $pagetitle, $page);
		$page = str_replace('{Site_Name}', $sitename, $page);
		echo $page;
	}
}


