<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/JWT/JWT.php';
require APPPATH . '/libraries/aws/aws-autoloader.php';


use Restserver\Libraries\REST_Controller;
use Restserver\Libraries\JWT\JWT;
use Aws\Sns\SnsClient;

class Reclamo extends REST_Controller
{
        protected $headers;

        //protected $CI;
        //protected $bucket;
        //protected $base_s3_url;

    function __construct()
    {
        parent::__construct();
        $this->load->model('imagen_model', 'imagen');
        $this->load->model('user_model', 'usermodel');
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

    public function reclamo_post()
    {


        $result = array();
        $titulo  = $this->post('titulo');
        $descripcion  = $this->post('descripcion');
        $nombres = $this->post('nombres');
        $email    = $this->post('email');
        $telefono    = $this->post('telefono');
        $longitude = $this->post('longitude');
        $latitude  = $this->post('latitude');
        $address  = $this->post('address');

        $urls = '';


        


        if ($titulo == ''){
            
            $result['codigo']   = '-1';
            $result['mensaje']  = 'El título es obligatorio.';
            return $this->set_response($result, REST_Controller::HTTP_OK);

        }

        if ($descripcion == ''){
            
            $result['codigo']   = '-1';
            $result['mensaje']  = 'La descripción es obligatoria.';
            return $this->set_response($result, REST_Controller::HTTP_OK);
            
        }

        if($nombres == ''){
            $nombres = "Anónimo";
        }

                //actualizar telefono si ya existe el fb email
               if (empty($this->usermodel->getUserAppByMail($email))) 
               {
                   $this->usermodel->update_fb($email,$telefono) ;  
               }

                //guardar imagenes del reclamos    
                $files = $_FILES;
                if($files){
                 
                    $urls  = $this->saveImagenesReclamos($files);
                }

            

                      $data = array('data'=>
                      array('type'=>'reports',
                            'attributes'=>array(
                                'dynamic_attributes'=>array(
                                                '78'=>array('value'=>$titulo),
                                                '79'=>array('value'=>$descripcion),
                                                '80'=>array('value'=>$nombres),
                                                '81'=>array('value'=>$email),
                                                '82'=>array('value'=>$telefono)
                                                )
                            ,
                            'finished'=>true,
                            "initial_location_attributes"=>array(
                                'longitude'=>$longitude,
                                'latitude'=>$latitude,
                                'reference'=>$address
                            ),
                            "images_attributes"=> $urls
                            
                                                )
                            )); //end data;

                        //echo json_encode($data,JSON_UNESCAPED_SLASHES);

                        
                $result =  $this->uploadReclamoToApi(json_encode($data,JSON_UNESCAPED_SLASHES));


        $this->set_response($result, REST_Controller::HTTP_OK);
        
    }

    private function uploadReclamoToApi($params){


          $url = $this->config->item('url_echeckit')."v1/reports";
          return $this->postCURL($url, $params);

    }


    


     
    public function resumen_get()
    {
        $result = array();

        $fecha_desde = $this->get('fecha_desde');
        $fecha_hasta = $this->get('fecha_hasta');



        
        $result['codigo']   = '1';
        $result['mensaje']  = 'resumen reclamos';
        $result['data']  = $this->getReclamosResumenFromApi($fecha_desde,$fecha_hasta);
       

        $this->set_response($result, REST_Controller::HTTP_OK);
    }
       

    private function getReclamosResumenFromApi($fecha_desde,$fecha_hasta){
        
         
    
            $url = $this->config->item('url_echeckit')."v1/idd/reports/summaries?start_date=".$fecha_desde."&end_date=".$fecha_hasta;

            return $this->getCURL($url);
        
    }

    public function getCURL($_url){
        
                $result = array();
        
        

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, false); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type:application/vnd.api+json',
                        'Authorization:Bearer '.$this->config->item('echeckit_token')
                ));
                

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
           
                // curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
                // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        
        
                $output=curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
                curl_close($ch);
              //  echo "httpcode: ".$httpcode;

                
                if($httpcode == "200") //ok
                {

                   $data = json_decode($output,true);

                   $result = $data["data"];

                
        
                }else
                {
                   $result['codigo']   = '-1';
                   $result['mensaje']  = 'Ha ocurrido un error. ';
                }
                return $result;
    }
        
           


    public function postCURL($_url, $params){

        $result = array();


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, false); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:application/vnd.api+json',
                'Authorization:Bearer '.$this->config->item('echeckit_token')
        ));

   
        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 


        $output=curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        
        if($httpcode == "201") //ok
        {
           //$result = json_decode($output);
           $result['codigo']   = '1';
           $result['mensaje']  = 'Reclamo creado exitosamente. ';


        }else
        {
           $result['codigo']   = '-1';
           $result['mensaje']  = 'Ha ocurrido un error. '; //$output;
        }
        return $result;
    }

    private function saveImagenesReclamos($files){
           
            $imagenesUrl = array();

            $this->load->library('upload'); 

            if (!empty($_FILES['userfile']['name'])) {
                
                //obtener numero de imagenes
                $cpt = count($_FILES['userfile']['name']);
                
                for($i=0; $i<$cpt; $i++)
                {   
                    $img_url = $this->get_image($i, $files);
                    if($img_url != ''){
                    
                        array_push($imagenesUrl,
                                   array('url'=>$img_url,'comment'=>'TXTE')
                        );
                    }
                }
            }
            return $imagenesUrl;

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
            $original = $this->s3->putObject($this->s3->inputFile($upload_doc['full_path']), 'iddurazno', 'reclamo/' . $upload_doc['file_name']);

            $imagen = $original == true ? 'https://s3.amazonaws.com/iddurazno/reclamo/' . $upload_doc['file_name'] : '';
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

    function createthumbnail($src=null,$newHeight=null,&$dest_x=null) {
        
                $srcimg = imagecreatefromjpeg($src);
        
                $srcsize = getimagesize($src);
               
                $dest_y = $newHeight;

                $dest_x = ($newHeight / $srcsize[1]) * $srcsize[0];
               // $dest_x =  ($newHeight / $srcsize[1]) * $srcsize[0];
                $thumbimg = imagecreatetruecolor($dest_x, $dest_y);
                //$thumbimg = imagecreatetruecolor($dest_x, $dest_y);
                //imagecopyresampled($thumbimg,$srcimg,0,0,0,0,$dest_x,$dest_y, $srcsize[0], $srcsize[1]);
                
                imagecopyresampled($thumbimg,$srcimg,0,0,0,0,$dest_x,$dest_y, $srcsize[0], $srcsize[1]);

                imagedestroy($srcimg);
                
                return $thumbimg;
     }

     private function getImageFromReclamo($imagen_antes,$imagen_despues){
        
                $dest = imagecreatefromjpeg('assets/base_share.jpg');

                $src_w = null;
                $src_h= 335;

                $src = $this->createthumbnail($imagen_antes,$src_h,$src_w);
                
                $dst_x=30;
                $dst_y= 250;
                $src_x=0;
                $src_y=0;
                
                imagecopymerge($dest, $src, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, 100); 
                
                $src2 = $this->createthumbnail($imagen_despues,$src_h,$src_w);
                
                imagecopymerge($dest, $src2, 605,250, $src_x, $src_y, $src_w, $src_h, 100); 
                
                $unique_file_name = md5(uniqid(mt_rand())).".png";
                $file_path = 'assets/uploads/files/'.$unique_file_name;

                imagepng($dest,$file_path);
                
                $domain_folder_destination = 'share_reclamo';
                $image =  $this->uploadhelper->uploadImagePathAndGetUrl($domain_folder_destination,$file_path);
              
                imagedestroy($dest);
                imagedestroy($src);
        
                @unlink($file_path);
                
                return $image;
        
    }


    public function imagen_reclamo_compartir_get(){

                $imagen_antes = $this->get('imagen_antes');
                $imagen_despues = $this->get('imagen_despues');


                $result['codigo']   = '1';
                $result['mensaje']  = 'imagen para compartir en redes sociales';
                $result['data']  = $this->getImageFromReclamo($imagen_antes,$imagen_despues);
        
                $this->set_response($result, REST_Controller::HTTP_OK);
        
        
    }

    public function send_push_notification_post()
    {

        $result = array();

        if(!$this->post("email") || !$this->post("message") || !$this->post("title"))
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = 'all fields are required.';
            $this->set_response($result, REST_Controller::HTTP_OK);
            return;
        }


        $email = $this->post("email"); 
        $message = $this->post("message");
        $title = $this->post("title");


        $data = $this->usermodel->getUserSNSDevices($email);


           
        if (!empty($data)) {

            //recorrer endpoints 
            $snsConfig = [
                'region'  => 'us-west-2',
                'version' => 'latest',
                'credentials' => [
                    'key'    => 'AKIAJLTJVDVD3ECKFSBQ',
                    'secret' => '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',
                ]
            ];
            
            $sns = new SnsClient($snsConfig);
                
            $endpointArn = 'arn:aws:sns:us-west-2:490523474570:endpoint/GCM/idd_android/92823a83-d236-3a50-8134-942847c8be7c';
            

            $user_id = $data[0]->id;

            //guardar el push en el inbox del usuario
            $inbox = $this->usermodel->insertInboxMensaje($user_id,$title,$message);
                  
            if (!empty($data)) {
              
            foreach($data as $user_sns)
            {

                try
                {

                    $array_push = array();

                    if (preg_match('/ios/',$user_sns->endpoint_arn)){


                        $dataSend_ios = json_encode(array(
                            'aps' => array(
                                'alert' => array(
                                    "title" => $title,
                                    "body" 	=> $message
                                    )
                                )
                        ));

                        $array_push = array(
                            'TargetArn' => $user_sns->endpoint_arn,
                            'MessageStructure' => 'json',
                            'Message' => json_encode(array(
                                'default' => $title,
                                'APNS_SANDBOX' 	=> $dataSend_ios,
                                'APNS' 			=> $dataSend_ios
                                ))
                        );
                        
                    }else {
                        $array_push = array(
                            'TargetArn' => $user_sns->endpoint_arn,
                            'MessageStructure' => 'json',
                            'Message' => json_encode(array(
                                'default' => $title,
                                'GCM' => json_encode(array(
                                    'data' => array(
                                        'message' => $message,
                                        'title'=>$title
                                        )
                                    )
                    
                                ))
                                )
                        );

                    }
                        $sns->publish($array_push);
                    }
                    catch (Exception $e)
                    {
                         print($endpointArn . " - Failed: " . $e->getMessage() . "!\n");
                    }
            }
        }

            $result['codigo']   = '1';
            $result['mensaje']  = 'Report SNS';
            $result['data']  = 'Push notification successfully generated.';
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        else{
            $result['codigo']   = '-1';
            $result['mensaje']  = "User doesn't exists or has no devices associated";
            $this->set_response($result, REST_Controller::HTTP_OK);
        }



    }
    public function shares_html_get()
    {
                $str_html= "<!DOCTYPE html>
                <html>
                <head>
                <title>Page Title</title>
                </head>
                <body>
                <h1>This is a Heading</h1>
                <p>This is a paragraph.</p>
                </body>
                </html>";

               $domain_folder_destination = "assets/amaz/";
                $foto_url2 = "";

                $imagen_antes = "https://s3.amazonaws.com/iddurazno/logo/logo_fbshare.png";
                $imagen_despues = "https://upload.wikimedia.org/wikipedia/commons/0/0f/Eiffel_Tower_Vertical.JPG";
                $imagen_facebook = "http://www.tiburonesenlafarmacia.com/wp-content/uploads/2016/02/bot%C3%B3n-facebook.png";
                $imagen_twitter = "";
                $page = file_get_contents('assets/index.html',$str_html);
                $page = str_replace('{variableAntes}', $imagen_antes, $page);
                $page = str_replace('{variableDespues}', $imagen_despues, $page);
                $page = str_replace('{imagefacebook}', $imagen_facebook, $page);
                echo $page;
                $foto_url2 =  $this->uploadhelper->uploadImagePathAndGetUrlNews($domain_folder_destination,$page,0);ç
                echo $foto_url2;
    }





   
}