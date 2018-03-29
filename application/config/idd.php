<?php defined('BASEPATH') OR exit('No direct script access allowed');


/*
|--------------------------------------------------------------------------
| Url Api Echeckit 
|--------------------------------------------------------------------------
|
| Endpoint para conectar con la api de echeckit con ambiente desarrollo/Produccion
|
*/

//desarrollo
//$config['url_echeckit'] = 'http://50.16.161.152/efinding-staging/api/';
  
//produccion
$config['url_echeckit'] = 'http://50.16.161.152/productos/api/';


/*
|--------------------------------------------------------------------------
| Token echeckit
|--------------------------------------------------------------------------
|
| token de acceso para conectar con la api de echeckit
|
*/

$config['echeckit_token'] = '5e14db9e991dd77358fa8d14736273aff5395b8634da12e1c340b7919d6c56d7';



/*
|--------------------------------------------------------------------------
| Url Terminos y condiciones
|--------------------------------------------------------------------------
|
| url pdf de terminos y condiciones entregada por idd
|
*/

$config['url_terminos_y_condiciones'] = 'https://www.datospersonales.gub.uy/wps/wcm/connect/urcdp/57e2264e-370f-411d-933c-63aca968c88b/Clausula_de_Consentimiento_para_Organismos_Publicos.pdf?MOD=AJPERES&CONVERT_TO=url&CACHEID=57e2264e-370f-411d-933c-63aca968c88b';


/*
|--------------------------------------------------------------------------
| Uri Launch App 
|--------------------------------------------------------------------------
|
| uri scheme para levantar app movil en ios y android cuando se 
| ej: cuando se quiere levantar app desde gmail con un link (recuperar contraseña)
|
*/

$config['uri_launch_mobile_app'] = 'com.ewin.intendenciadurazno://compose?';



/*
|--------------------------------------------------------------------------
| Uri Launch App 
|--------------------------------------------------------------------------
|
| uri scheme para levantar app movil en ios y android cuando se 
| ej: cuando se quiere levantar app desde gmail con un link (recuperar contraseña)
|
*/


//desarrollo
//$config['url_link_reset_password_for_email'] = 'http://ec2-54-88-114-83.compute-1.amazonaws.com/idd/index.php/api/configuracion/reset_password_uri_scheme_app';

//produccion
$config['url_link_reset_password_for_email'] = 'http://190.0.158.2:8083/api/index.php/api/configuracion/reset_password_uri_scheme_app';
        

        






