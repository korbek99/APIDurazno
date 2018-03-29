<?php
    // class PHPFatalError {

    // public function setHandler() {
    //         register_shutdown_function('handleShutdown');
    //     }

    // }

    // function handleShutdown() {
    //     if (($error = error_get_last())) {
    //         ob_start();
    //             echo "<pre>";
    //         var_dump($error);
    //             echo "</pre>";
    //         $message = ob_get_clean();
    //         sendEmail($message);
    //         ob_start();
    //         echo '{"status":"error","message":"Internal application error!"}';
    //         ob_flush();
    //         exit();
    //     }
    // }



class ExceptionHook 
{
  public function SetExceptionHandler()
  {
     set_exception_handler(array($this, 'HandleExceptions'));
  }
   
  public function HandleExceptions($exception)
  {
	
  	$msg ='Exception of type \''.get_class($exception).'\' occurred with Message: '.$exception->getMessage().' in File '.$exception->getFile().' at Line '.$exception->getLine();
		
        $msg .="\r\n Backtrace \r\n";
	    $msg .=$exception->getTraceAsString();
	
        log_message('error', $msg, TRUE);

        die('{"status":"error","message":"Internal application error!"}');
	
        		
	   //mail('dev-mail@example.com', 'An Exception Occurred', $msg, 'From: test@example.com');	
	
}

}

