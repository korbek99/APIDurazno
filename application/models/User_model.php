<?php
class User_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function getUserByMail($email)
    {
        $this->db->select('id, first_name, last_name, avatar, email, gender, phone');
        $this->db->where('email', $email);
        $query = $this->db->get('cuenta');
        
        return $query->row();
    }

    public function getUserAppByMail($email)
    {
        $this->db->select('id,id_fbk,first_name,last_name,full_name,email,phone,url');
        $this->db->where('email', $email);
        $query = $this->db->get('userFB');
        
        return $query->row();
    }

    public function getUserFB()
    {
        $this->db->select('first_name, last_name, email, gender');
        $query = $this->db->get('userFB');

        return $query->result();
    }

    public function getUsers()
    {
        $this->db->select('id, first_name, last_name, avatar, email, gender, phone');
        $query = $this->db->get('cuenta');
        
        return $query->result();
    }

    public function getUser($id)
    {
        $this->db->select('id, first_name, last_name, avatar, email, gender, phone');
        $this->db->where('id', $id);
        $query = $this->db->get('cuenta');

        return $query->row();
    }

    public function cambioClave($id, $password, $repassword)
    {
        $this->db->where('id', $id);
        $this->db->where('password', $password);

        $data = array(
            'password'    => $repassword
        );

        $this->db->update('cuenta', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }

    }

    public function deleteUser($id)
    {
        $this->db->where('id', $id);

        $this->db->delete('cuenta');

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public function getUserById($id)
    {
        $this->db->select('id, first_name, last_name, avatar, email, gender, phone');
        $this->db->where('id', $id);
        $query = $this->db->get('cuenta');
        
        return $query->result();
    }

    public function insert($email, $password, $full_name, $phone, $url)
    {
        $data = array(
            'email' => $email,
            'full_name'=>$full_name,
            'phone'=>$phone,
            'password'=>$password,
            'url'=>$url
        );

        $this->db->insert('userFB', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }


    public function update($email,$password,$full_name,$phone,$avatar,$id)
    {
        $data = array(
            'email' => $email,
            'password' => $password,
            'full_name' => $full_name,
            'phone' => $phone,
            'url' => $avatar
        );

        $this->db->where('id', $id);
        $this->db->update('userFB', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public function insertadmin($email, $password, $nombre, $apellido, $genero, $telefono, $avatar)
    {
        $data = array(
            'email' => $email,
            'password' => $password,
            'first_name' => $nombre,
            'last_name' => $apellido,
            'gender' => $genero,
            'phone' => $telefono,
            'avatar' => $avatar
        );

        $this->db->insert('cuenta', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }


  



    public function updateadmin($nombre, $apellido, $genero, $telefono, $avatar, $id)
    {
        $data = array(
            'first_name' => $nombre,
            'last_name' => $apellido,
            'gender' => $genero,
            'phone' => $telefono,
            'avatar' => $avatar
        );

        $this->db->where('id', $id);
        $this->db->update('cuenta', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }


    public function insert_fb($token, $url, $expireDate, $id_fbk, $first_name, $last_name, $email, $public_profile, $user_birthday, $user_hometown, $user_location, $gender,$password,$full_name,$phone)
    {
        $data = array(
            'token' => $token,
            'url' => $url,
            'expireDate' => $expireDate,
            'id_fbk' => $id_fbk,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'public_profile' => $public_profile,
            'user_birthday' => $user_birthday,
            'user_hometown' => $user_hometown,
            'user_location' => $user_location,
            'gender' => $gender,
            'password'=>$password,
            'full_name'=>$full_name,
            'phone'=>$phone
        );

        $this->db->insert('userFB', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }



    //usado: actualizar datos al crear un reclamos
    public function update_fb($email, $telefono)
    {
        $data = array(
            'phone' => $telefono
        );

        $this->db->where('email', $email);
        $this->db->update('userFB', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public function getUserApp($id_fbk)
    {
        $this->db->select('id,id_fbk,first_name, last_name,email,url');
        $this->db->where('id_fbk', $id_fbk);
        $query = $this->db->get('userFB');

        return $query->row();
    }


     public function loginUserApp($email, $password)
    {
        $this->db->select('id,id_fbk,first_name,last_name,full_name,phone,email,url');
        $this->db->where('email', $email);
        $this->db->where('password', $password);
        $query = $this->db->get('userFB');
        return $query->row();
    }


    public function loginUserFacebook($id_fbk)
    {
        $this->db->select('id,id_fbk,first_name,last_name,full_name,phone,email,url');
        $this->db->where('id_fbk', $id_fbk);
        $query = $this->db->get('userFB');
        return $query->row();
    }


     public function register_sns_device($user_id, $endpoint_arn,$application_arn)
     {
        $data = array(
            'id_usuario_app' => $user_id,
            'endpoint_arn' => $endpoint_arn,
            'application_arn' => $application_arn
        );

        $this->db->insert('user_sns_devices', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
     }



  


     public function getUserSNSDevices($email)
     {

        $this->db->select("usr.id,usr.email,usr.full_name,sns.endpoint_arn,sns.application_arn");
        $this->db->where('usr.email', $email);
        $this->db->where('sns.active', true);
        $this->db->join('userFB as usr', 'usr.id = sns.id_usuario_app');
    
        $query = $this->db->get('user_sns_devices as sns');
         
         return $query->result();
     }

  
     public function existsEndpointArn($endpoint_arn,$user_id)
     {

        $this->db->select("sns.id_usuario_app,sns.endpoint_arn,sns.application_arn");
        $this->db->where('sns.endpoint_arn', $endpoint_arn);
        $this->db->where('sns.id_usuario_app', $user_id);

        $query = $this->db->get('user_sns_devices as sns');
         
         return $query->result();
     }

     public function insertInboxMensaje($user_id,$title,$message)
     {
        $data = array(
            'id_usuario_app' => $user_id,
            'title' => $title,
            'message' => $message
        );

        $this->db->insert('user_inbox', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }
     }

     public function getInbox($user_id)
     {

        $this->db->select("id,id_usuario_app as user_id,title,message,to_char(created_at,'dd/MM') as created_at");
        $this->db->where('id_usuario_app', $user_id);
    
        $query = $this->db->get('user_inbox');
         
         return $query->result();
     }

     public function deleteInbox($id)
     {

        $this->db->where('id', $id);
        
        $this->db->delete('user_inbox');

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        else {
            return false;
        }

     }


     public function updatePassword($password, $id,$token)
     {
         $data = array(
             'password' => $password,
         );
 
         $this->db->where('id', $id);
         //$this->db->where('reset_password_token',$token);
         $this->db->update('userFB', $data);
 
         if ($this->db->affected_rows() > 0) {
             return true;
         }
         else {
             return false;
         }
     }






}
