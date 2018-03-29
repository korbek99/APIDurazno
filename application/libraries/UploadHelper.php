<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Uploadhelper {
        
        protected $CI;
        protected $bucket;
        protected $base_s3_url;
     

        public function __construct()
        {
               $this->CI =& get_instance();
               $this->bucket = 'iddurazno';
               $this->base_s3_url = 'https://s3.amazonaws.com/';
        }


        public  function uploadImageAndGetUrl($domain_folder,$files,$index){
                
               
                $_FILES['userfile']['name']= $files['userfile']['name'][$index];
                $_FILES['userfile']['type']= $files['userfile']['type'][$index];
                $_FILES['userfile']['tmp_name']= $files['userfile']['tmp_name'][$index];
                $_FILES['userfile']['error']= $files['userfile']['error'][$index];
                $_FILES['userfile']['size']= $files['userfile']['size'][$index];    


                $this->CI->load->library('upload');


                 $this->CI->upload->initialize($this->set_upload_options());

                $imagen ='';


                $upload_doc = $this->CI->upload->do_upload()? $this->CI->upload->data() : false;
                if ($upload_doc == false)
                {       
                        $error = array('error' => $this->CI->upload->display_errors());
                       // print_r($error);
                }
                else
                {
                        $imagen = $upload_doc['full_path'];
                        $this->CI->load->library('s3');
                        $original = $this->CI->s3->putObject($this->CI->s3->inputFile($upload_doc['full_path']), $this->bucket, $domain_folder.'/' . $upload_doc['file_name']);
                        $imagen = $original == true ? $this->base_s3_url.'/'.$this->bucket.'/'.$domain_folder.'/' . $upload_doc['file_name'] : '';
                }
                //elimina el archivo de la carpeta assets/upload/files
                @unlink($upload_doc['full_path']);

                 return $imagen;
        }
        


        public  function uploadImagePathAndGetUrl($domain_folder,$file_path){
                
               
                $url_image=$file_path;

                $unique_new_file_name = md5(uniqid(mt_rand())).".png";
 
                $this->CI->load->library('s3');
                $original = $this->CI->s3->putObject($this->CI->s3->inputFile($url_image), $this->bucket, $domain_folder.'/' . $unique_new_file_name);
                $url_image = $original == true ? $this->base_s3_url.'/'.$this->bucket.'/'.$domain_folder.'/' . $unique_new_file_name : '';
                

                return $url_image;
        }

        public  function uploadImagePathAndGetUrlNews($domain_folder,$file_path){
                
                $url_image=$file_path;
                $unique_new_file_name = md5(uniqid(mt_rand())).".html";
                $this->CI->load->library('s3');
                $original = $this->CI->s3->putObject($this->CI->s3->inputFile($url_image), $this->bucket, $domain_folder.'/' . $unique_new_file_name);
                $url_image = $original == true ? $this->base_s3_url.'/'.$this->bucket.'/'.$domain_folder.'/' . $unique_new_file_name : '';
                return $url_image;
        }
        
        private function set_upload_options()
        {   
                if (!is_dir(FCPATH . 'assets/uploads/files/')) {
                mkdir(FCPATH . 'assets/uploads/files/', 0777, TRUE);
                }

                //upload an image options
                $config = array();
                $config['upload_path']   = FCPATH . 'assets/uploads/files/';
                $config['allowed_types'] = 'jpg|png|jpeg';
                $config['max_size']      = '1000000'; //ajustar en caso que no se suba la imagen
                $config['encrypt_name']  = true;

                return $config;
         }

}