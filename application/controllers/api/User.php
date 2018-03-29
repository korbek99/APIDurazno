<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/JWT/JWT.php';

use Restserver\Libraries\REST_Controller;
use Restserver\Libraries\JWT\JWT;
/*
Clase que almacena los usuarios de facebook y adminsitradores.

*/
class User extends REST_Controller {

    protected $headers;
    function __construct()
    {
        parent::__construct();
        $this->load->model('user_model', 'usermodel');
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
        
        $this->load->library('email');
        $this->load->library('uploadhelper');

        $this->load->helper('date');

        $this->headers = apache_request_headers();
    }

    //Obtenemos los usuarios de fb
    public function user_fb_get()
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

        $users = $this->usermodel->getUserFB();
        if (!empty($users)) {
            $result['codigo']   = '1';
            $result['mensaje']  = 'usuario_fb';
            $result['data']  = $users;
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        else{
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Error al consultar los usuarios';
        }
    }

    //Obtenemos los usuarios administradores
    public function users_get()
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

        $users = $this->usermodel->getUsers();
        if (!empty($users)) {
            $result['codigo']   = '1';
            $result['mensaje']  = 'usuarios';
            $result['data']  = $users;
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        else{
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Error al consultar los usuarios';
        }
    }

    public function user_app_from_user_fb_get()
    {
       
        $result = array();
        $id_fbk  = $this->get('id');

        if(!empty($id_fbk))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'usuario app';
            $user_app = $this->usermodel->getUserApp($id_fbk);
            $result['data']  = $user_app;
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        
    }



    //Obtenemos el usuario por el id, si no viene el id retorna el usuario desde el token.
    public function user_get()
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

        $id = $this->get('id');
        if($id == '')
        {
            $user = JWT::getDataUser($this->headers, $this->auth_model);
            //print_r($user);
            $userF = $this->usermodel->getUser($user->id);
            $result['codigo']   = '1';
            $result['mensaje']  = 'usuario';
            $result['data']  = $userF;
            $this->set_response($result, REST_Controller::HTTP_OK);
            return;
        }
        else
        {
            $user = $this->usermodel->getUser($id);
            if (!empty($user)) {
                $result['codigo']   = '1';
                $result['mensaje']  = 'usuario';
                $result['data']  = $user;
                $this->set_response($result, REST_Controller::HTTP_OK);
            }
            else{
                $result['codigo']   = '-1';
                $result['mensaje']  = 'Error al consultar los usuarios';
                $this->set_response($result, REST_Controller::HTTP_OK);
            }
        }
        
    }

    public function user_admin_post()
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

        $email  = $this->input->post('email');
        $password  = sha1($this->input->post('password'));
        $first_name  = $this->input->post('first_name');
        $last_name  = $this->input->post('last_name');
        $gender  = $this->input->post('gender');
        $phone  = $this->input->post('phone');
        $image  = $this->input->post('userfile');

        $avatar = '';

        if (empty($this->usermodel->getUserByMail($email))) 
        {
            $upload_doc = $this->do_upload($image);

            if ($upload_doc != false) 
            {
                $avatar = $upload_doc['full_path'];
                $this->load->library('s3');
                $original = $this->s3->putObject($this->s3->inputFile($upload_doc['full_path']), 'iddurazno', 'avatar/' . $upload_doc['file_name']);
                $avatar = $original == true ? 'https://s3.amazonaws.com/iddurazno/avatar/' . $upload_doc['file_name'] : '';
            }
            
            @unlink($upload_doc['full_path']);
            if ($this->usermodel->insertadmin($email, $password, $first_name, 
                                    $last_name, $gender, $phone, $avatar)) 
            {
        
                $result['codigo']   = '1';
                $result['mensaje']  = 'Usuario creado con éxito.';
                  

            }
            else
            {
                $result['codigo']   = '-1';
                $result['mensaje']  = 'No se logro crear el usuario. Intente nuevamente.';
            }
        }
        else
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Ya existe un usuario con ese correo. Intente con otro correo.';

        }
        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function send_email_new_usuario_app($full_name,$email){

        //configurar email
        $config['protocol'] = 'sendmail';
        $config['mailpath'] = '/usr/sbin/sendmail';
        $config['wordwrap'] = TRUE;
        $config['mailtype']= 'html';

         //enviar por correo 
         $this->email->initialize($config);

         $data = array(
            'name'=> $full_name
        );

         $this->email->from('IDD <solutions@ewin.cl>');
         $this->email->to($email);
         $this->email->subject('Bienvenido a APP IDD');
     
         $message = $this->load->view('Mail/nuevacuentausuario.php',$data,TRUE);

         $this->email->message($message);
         $this->email->set_crlf( "\r\n" );
     

         if($this->email->send())
         {
            return true;
         }
         else
         {
             //show_error($this->email->debugger());
             return false;
         }
    }
    
     //crear usuario app login sin facebook
    public function user_post()
    {
     
        $result = array();

        $email  = $this->input->post('email');
        $password  = sha1($this->input->post('password'));
        $full_name  = $this->input->post('full_name');
        $phone  = $this->input->post('phone');
        $image  = $this->input->post('userfile');


        $url = '';

         if (empty($this->usermodel->getUserAppByMail($email))) 
         {
           
            $files = $_FILES;
            if (!empty($_FILES['userfile']['name'])) {
                $domain_folder_destination = 'avatar';
                $url = $this->uploadhelper->uploadImageAndGetUrl($domain_folder_destination,$files,0); 
            }


             if ($this->usermodel->insert($email,$password,$full_name,$phone,$url)) 
             {
                //enviar email de bienvenida
                $this->send_email_new_usuario_app($full_name,$email);
                $result['codigo']   = '1';
                $result['mensaje']  = 'Usuario creado con exito.';
                $result['data'] = $this->usermodel->getUserAppByMail($email);
             }
             else
             {
                 $result['codigo']   = '-1';
                 $result['mensaje']  = 'No se logro crear el usuario. Intente nuevamente.';
             }
         }
         else
         {
             $result['codigo']   = '-1';
             $result['mensaje']  = 'Ya existe un usuario con ese correo. Intente con otro correo.';
         }

         $this->set_response($result, REST_Controller::HTTP_OK);

    }


        //actualizar usuario app login sin facebook
        public function user_put()
        {
         
            $result = array();
    
            $id  = $this->input->post('id');
            $email  = $this->input->post('email');
            $password  = sha1($this->input->post('password'));
            $full_name  = $this->input->post('full_name');
            $phone  = $this->input->post('phone');
            $image  = $this->input->post('userfile');
    
    
            $avatar = '';

            if ($this->usermodel->update($email,$password,$full_name,$phone,$avatar,$id)) 
            {
                $result['codigo']   = '1';
                $result['mensaje']  = 'Usuario actalizado con exito.';
            }
            else
            {
                $result['codigo']   = '-1';
                $result['mensaje']  = 'No se logro actualizar el usuario. Intente nuevamente.';
            }
        
            $this->set_response($result, REST_Controller::HTTP_OK);
        }


    //Actualizar usuario
    public function user_actualizar_post()
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

        $id  = $this->input->post('id');
        $email  = $this->input->post('email');
        $password  = $this->input->post('password');
        $first_name  = $this->input->post('first_name');
        $last_name  = $this->input->post('last_name');
        $gender  = $this->input->post('gender');
        $phone  = $this->input->post('phone');
        $repassword  = $this->input->post('repassword');
        $avatar  = $this->input->post('avatar');
        $image  = $this->input->post('userfile');

        if(!empty($password) && !empty($repassword))
        {
            $password = sha1($this->input->post('password'));
            $repassword  = sha1($this->input->post('repassword'));
            if(!$this->usermodel->cambioClave($id, $password, $repassword))
            {
                $result['codigo']   = '-1';
                $result['mensaje']  = 'La contraseña no coincide.';
                $this->set_response($result, REST_Controller::HTTP_OK);
                return;
            }
        }

        if (!empty($this->usermodel->getUserByMail($email)))
        {
            if(empty($avatar))
            {
                $upload_doc = $this->do_upload($avatar);

                if ($upload_doc != false) 
                {
                    $avatar = $upload_doc['full_path'];
                    $this->load->library('s3');
                    $original = $this->s3->putObject($this->s3->inputFile($upload_doc['full_path']), 'iddurazno', 'avatar/' . $upload_doc['file_name']);
                    $avatar = $original == true ? 'https://s3.amazonaws.com/iddurazno/avatar/' . $upload_doc['file_name'] : '';
                }
                
                @unlink($upload_doc['full_path']);
            }
            
            if ($this->usermodel->updateadmin($first_name, 
                                    $last_name, $gender, $phone, $avatar, $id)) 
            {
                $result['codigo']   = '1';
                $result['mensaje']  = 'Usuario actualizado con éxito.';
                $this->set_response($result, REST_Controller::HTTP_OK);
            }
            else
            {
                $result['codigo']   = '-1';
                $result['mensaje']  = 'No se logro actualizar el usuario. Intente nuevamente.';
            }
        }
        else
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = 'El usuario no existe.';

        }
        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    //Eliminar usuario
    public function user_eliminar_post()
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

        if($this->usermodel->deleteUser($id))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'Usuario eliminado';
        }
        else{
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Usuario no existe';
        }

        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    //Crear usuario FB
    public function user_fb_post()
    {
        $result = array();

        $token  = $this->post('token');
        $url  = $this->post('url');
        $expireDate  = $this->post('expireDate');
        $id_fbk  = $this->post('id_fbk');
        $email  = $this->post('email');
        $phone  = $this->post('phone');
        $first_name  = $this->post('first_name');
        $last_name  = $this->post('last_name');
        $full_name  = $this->post('full_name');
        $public_profile  = $this->post('public_profile');
        $user_birthday  = $this->post('user_birthday');
        //$password  = $this->post('password');
        $user_hometown  = $this->post('user_hometown');
        $user_location  = $this->post('user_location');
        $gender  = $this->post('gender');
        
        $password =''; // sha1($this->input->post('password'));


        $files = $_FILES;
        if (!empty($_FILES['userfile']['name'])) {
            $domain_folder_destination = 'avatar';
            $url = $this->uploadhelper->uploadImageAndGetUrl($domain_folder_destination,$files,0); 
        }

        if ($this->usermodel->insert_fb($token, $url, $expireDate, $id_fbk, $first_name, $last_name, $email, $public_profile, $user_birthday, $user_hometown, $user_location, $gender,$password,$full_name,$phone)) 
        {
            //enviar email de bienvenida
            $this->send_email_new_usuario_app($full_name,$email);

            $result['codigo']   = '1';
            $result['mensaje']  = 'Usuario creado con exito.';
            $result['data'] = $this->usermodel->getUserAppByMail($email);
            $this->set_response($result, REST_Controller::HTTP_OK);

        }
        else
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = 'No se logro crear el usuario. Intente nuevamente.';
        }
        $this->set_response($result, REST_Controller::HTTP_OK);
    }

    public function login_user_app_post()
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

        $user = $this->usermodel->loginUserApp($email, $password);
    
        if (!empty($user)) {
            $result['codigo']   = '1';
            $result['mensaje']  = 'usuario';
            $result['data']  = $user;
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        else{
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Usuario y/o contraseña inválida. Intente nuevamente';
            $this->set_response($result, REST_Controller::HTTP_OK);
        }

    }

    public function login_user_facebook_post()
    {
        $result = array();

        if(!$this->post("id_fbk"))
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Todos los datos son necesarios';
            $this->set_response($result, REST_Controller::HTTP_OK);
            return;
        }

        $id_fbk = $this->post("id_fbk");


        $user = $this->usermodel->loginUserFacebook($id_fbk);
                
        if (!empty($user)) {
            $result['codigo']   = '1';
            $result['mensaje']  = 'usuario';
            $result['data']  = $user;
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        else{
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Usuario no existe';
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
    }



    //reclamos por usuario

    private function getReclamosFromEcheckitApi($email){
        
            $url = $this->config->item('url_echeckit')."v1/reports/?filter[dynamic_attributes][81][value]=".$email."&include=images,pdfs,state";

            return $this->getCURLFromEcheckit($url);
        
    }



    public function reclamos_get(){


        $email = $this->get('email');

        $data = $this->getReclamosFromEcheckitApi($email);

       // print_r("test");
        $reclamos = array();

        //obtener cada reclamo
        foreach ($data['data'] as $reporte) {

          
            
          //print_r($reporte);
          $attribute_reclamo_id = $reporte['id'];
         // print_r("codigo reporte : " . $attribute_reclamo_id);


            $reclamo = '';
            
            $titulo = $reporte['attributes']['dynamic_attributes']['78']['value'];
            $descripcion = $reporte['attributes']['dynamic_attributes']['79']['value'];
            $nombres = $reporte['attributes']['dynamic_attributes']['80']['value'];
            $email = $reporte['attributes']['dynamic_attributes']['81']['value'];
            $telefono = $reporte['attributes']['dynamic_attributes']['82']['value'];
            //$pdf = $reporte['attributes']['pdf'];

            //print_r($pdf);

            //$pdf = $reporte['attributes']['pdf'];
            //$fecha_creacion = $reporte['attributes']['formatted_created_at'];
            $fecha_creacion = $reporte['attributes']['created_at'];
            $fecha_creacion_formateada = date('d-m-y',strtotime($fecha_creacion));

        


            $id_estado_actual = $reporte['attributes']['state_id'];
            $estado_actual = '';


            //$attribute_reclamo_id = $reporte['data']['id'];
            //print_r($attribute_reclamo_id);

          

          
            $id_imagenes = array();
            
            if (isset($reporte['relationships']['images'])) {
                foreach ($reporte['relationships']['images'] as $item_image){

                        for ($i = 0; $i < count($item_image); $i++) {
                        if(isset($item_image[$i]['id'])){  
                            array_push($id_imagenes,$item_image[$i]['id']);

                        } 
                        }    
                }
            }


            
            $imagenes = array();

            $imagen_antes_compartir = '';
            $imagen_despues_compartir = '';


            foreach ($data['included'] as $item_included){

                //obtener estado actual
                if($item_included['type'] == 'states'){

                    if($item_included['id'] == $id_estado_actual){
                        $estado_actual = array(
                            'id'=>$id_estado_actual,
                            'nombre'=> $item_included['attributes']['name'],
                            'color'=> $item_included['attributes']['color']
                        );
                    }
                }

                if($item_included['type'] == 'pdfs'){
                    $atributes_reports = '';
                    //obtiene datos pdf Url segun ID de Reclamo
                    if($item_included['attributes']['report_id'] == $attribute_reclamo_id){
                        $atributes_reports = array(
                            'idReport'=>$attribute_reclamo_id,
                            'pdfUrl'=> $item_included['attributes']['pdf_url'],
                            'pdfHtml'=> $item_included['attributes']['pdf_html']
                        );

                        $pdf = $item_included['attributes']['pdf_url'];
                       // print_r($pdf);
                    }    
                }
   


                if ($item_included['type'] == 'images') {


                   // $id_imagenes_reversed = array_reverse($id_imagenes);
                    foreach($id_imagenes as $id_imagen){
                        if($id_imagen == $item_included['id']){
                            
                            $state_id = $item_included['attributes']['state_id'];
                            $selected = $item_included['attributes']['selected'];

                         
                            $es_despues = '';
                            

                            //imagen antes
                            if ($state_id == 12 && $selected = true){

                                $es_despues = false;
                                if($imagen_antes_compartir ==''){
                                    $imagen_antes_compartir = $item_included['attributes']['url'];
                                }
                            }

                            //imagen despues
                            if ($state_id == 13 && $selected = true){
                                
                                $es_despues = true;
                                if($imagen_despues_compartir ==''){
                                    $imagen_despues_compartir = $item_included['attributes']['url'];
                                }
                            }

                            //imagen antes o despues
                            $imagen = array(
                                'esDespues'=>$es_despues,
                                'url'=>$item_included['attributes']['url']
                            );

                            array_push($imagenes,$imagen);

                        }

                    }
                   
                }


            }



            $imagen_compartir = '';
            if ($estado_actual['nombre'] == 'Resuelto'){
               // echo $imagen_antes_compartir.$imagen_despues_compartir;
                $imagen_compartir = ''; 
            }

            $reclamo = array(
                'titulo'=>$titulo,
                'descripcion'=>$descripcion,
                'nombres'=>$nombres,
                'email'=>$email,
                'telefono'=>$telefono,
                'pdf'=>$pdf,
                'fechaCreacion'=>$fecha_creacion_formateada,
                'estadoActual'=>$estado_actual,
                'imagenes'=>$imagenes,
                'mensajeCompartir'=>'reclamo resuelto'
            );


           array_push($reclamos,$reclamo);
        

       }


       $result['codigo']   = '1';
       $result['mensaje']  = 'reclamos';
       $result['data']  = $reclamos;
       $this->set_response($result, REST_Controller::HTTP_OK);
        

    }


    public function getCURLFromEcheckit($_url){
        
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
           

                $output=curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
                curl_close($ch);

                
                if($httpcode == "200") //ok
                {

                   $data = json_decode($output,true);
                   $result =  $data;  
        
                }else
                {
                   $result['codigo']   = '-1';
                   $result['mensaje']  = 'Ha ocurrido un error. ';
                }
                return $result;
    }
        



    
    public function register_sns_device_post()
    {

        $result = array();

        if(!$this->post("user_id") && !$this->post("endpoint_arn")  && !$this->post("application_arn"))
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Todos los datos son necesarios';
            $this->set_response($result, REST_Controller::HTTP_OK);
            return;
        }

        $user_id = $this->post("user_id");
        
        $endpoint_arn = $this->post("endpoint_arn");
        $application_arn = $this->post("application_arn");
        

        if (empty($this->usermodel->existsEndpointArn($endpoint_arn,$user_id))) 
        {

            $data = $this->usermodel->register_sns_device($user_id,$endpoint_arn,$application_arn);
            
                       
            if (!empty($data)) {
                $result['codigo']   = '1';
                $result['mensaje']  = 'usuario';
                $result['data']  = $data;
                $this->set_response($result, REST_Controller::HTTP_OK);
            }
            else{
                $result['codigo']   = '-1';
                $result['mensaje']  = 'Usuario no existe';
                $this->set_response($result, REST_Controller::HTTP_OK);
            }
        }else {
            $result['codigo']   = '-1';
            $result['mensaje']  = 'Endpoint Arn ya se encuentra registrado';
            $this->set_response($result, REST_Controller::HTTP_OK);
        }

        
       
    }


    public function reset_password_request_post()
    {

        $result = array();

        //configurar email
        $config['protocol'] = 'sendmail';
        $config['mailpath'] = '/usr/sbin/sendmail';
        $config['wordwrap'] = TRUE;
        $config['mailtype']= 'html';
        

        $url_scheme_reset_password = $this->config->item('url_link_reset_password_for_email');


        $email =  $this->post('email');
       
            if($email !='') {

                //validar que exista el usuario
                $user = $this->usermodel->getUserAppByMail($email);
                if(empty($user)){
                
                    $result['codigo']   = '-1';
                    $result['mensaje']  = 'El mail ingresado no está registrado. Ingresa un correo válido.';
                  
                }else{


                    //generar codigo aleatorio de 4 digitos
                    $token =  substr(number_format(time() * rand(),0,'',''),0,4);

                    $this->email->initialize($config);

                    $data = array(
                        'name'=> $user->full_name,
                        'url_scheme_reset_password'=>$url_scheme_reset_password,
                        'user_id'=>$user->id,
                        'token_reset_at'=>date('m-d-Y'),
                        'token'=> $token
                    );

                    //actualizar token en cuenta del usuario
                    // if(!$this->adminmodel->updatePasswordToken($email,$code)){
                        
                    //     $result['codigo']   = '-1';
                    //     $result['mensaje']  = 'Ha ocurrido un error generando el codigo.';

                    // }else{

                        //enviar por correo 
                        $this->email->from('IDD App <solutions@ewin.cl>');
                        $this->email->to($email);
                        $this->email->subject('Recuperación de contraseña');
                    
                        $message = $this->load->view('Mail/codigoContrasena.php',$data,TRUE);

                        $this->email->message($message);
                        $this->email->set_crlf( "\r\n" );
                    

                        if($this->email->send())
                        {
                            $result['codigo']   = '1';
                            $result['mensaje']  = 'Mail enviado exitosamente.';
                        }
                        else
                        {
                            $result['codigo']   = '-1';
                            show_error($this->email->debugger());
                            $result['mensaje']  = 'No se logro enviar el Mail.';
                        }
                    //}
                }

            }
            else
            {
                $result['codigo']   = '-1';
                $result['mensaje']  = 'Todos los datos son obligatorios.';
            }


            $this->set_response($result, REST_Controller::HTTP_OK);
    }


    public function reset_password_post(){
        
        $result = array();

        $user_id =  $this->post('user_id');
        $password =  $this->post('password');
        $token =  $this->post('token');

        if($user_id != '' && $password != '' && $token != ''){

            if ($this->usermodel->updatePassword(sha1($password),$user_id,$token)) 
            {
                $result['codigo']   = '1';
                $result['mensaje']  = 'Usuario actualizado con exito';
            }
            else
            {
                $result['codigo']   = '-1';
                $result['mensaje']  = 'No se logro actualizar la contraseña. Intente nuevamente.';
            }

        }else{

              $result['codigo']   = '-1';
              $result['mensaje']  = 'Todos los datos son obligatorios.';

        }
        
        $this->set_response($result, REST_Controller::HTTP_OK);


    }

    //Metodo utilizado para subir archivos
    //OJO QUE NO BORRA LOS ARCHIVOS QUE GUARDA DE FORMA LOCAL.
    private function do_upload($file) 
    {
        if (!is_dir(FCPATH . 'assets/uploads/files/')) {
            mkdir(FCPATH . 'assets/uploads/files/', 0777, TRUE);
        }

        $config['upload_path'] = FCPATH . 'assets/uploads/files/';
        $config['allowed_types'] = 'jpg|png';
        $config['max_size'] = '10240';
        $config['encrypt_name'] = true;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload()) {
            return false;
        } else {
            $data = $this->upload->data();
            return $data;
        }
    }

    public function inbox_get(){
                
        $result = array();

        $user_id  = $this->get('user_id');

        if(!empty($user_id))
        {
            $result['codigo']   = '1';
            $result['mensaje']  = 'inbox';
            $data = $this->usermodel->getInbox($user_id);
            $result['data']  = $data;
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
    }

    public function inbox_eliminar_post(){

        $result = array();

        //echo "wtf".$this->post('id');

        if(!$this->post("id"))
        {
            $result['codigo']   = '-1';
            $result['mensaje']  = 'todos los datos son obligatorios.';
            $this->set_response($result, REST_Controller::HTTP_OK);
            return;
        }


        $id = $this->post("id"); 


        $data = $this->usermodel->deleteInbox($id);


            
        if (!empty($data)) {
        
                   
            $result['codigo']   = '1';
            $result['mensaje']  = 'Inbox';
            $result['data']  = 'Mensaje eliminado exitosamente';
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
        else{

            $result['codigo']   = '-1';
            $result['mensaje']  = "Mensaje ya no existe";
            $this->set_response($result, REST_Controller::HTTP_OK);
        }
    }
           

  

}
