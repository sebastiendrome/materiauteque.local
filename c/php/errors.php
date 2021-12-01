<?php
// handle errors
//Src: http://stackoverflow.com/a/7313887
set_error_handler("errorHandler");
register_shutdown_function("shutdownHandler");

function errorHandler($error_level, $error_message, $error_file, $error_line, $error_context){
	$error = "level: ".$error_level." | msg:".$error_message." | file:".$error_file." | line:".$error_line;
	switch ($error_level){
		case E_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_PARSE:
			log_custom_error($error, "fatal");
			break;
		case E_USER_ERROR:
		case E_RECOVERABLE_ERROR:
			log_custom_error($error, "error");
			break;
		case E_WARNING:
		case E_CORE_WARNING:
		case E_COMPILE_WARNING:
		case E_USER_WARNING:
			log_custom_error($error, "warn");
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
			log_custom_error($error, "info");
			break;
		case E_STRICT:
			log_custom_error($error, "debug");
			break;
		case E_DEPRECATED:
		//do nothing
			break;
		default:
			log_custom_error($error, "warn");
	}
}

function shutdownHandler(){ //will be called when php script ends.
	$lasterror = error_get_last();
	switch ($lasterror['type']){
		case E_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_USER_ERROR:
		case E_RECOVERABLE_ERROR:
		case E_CORE_WARNING:
		case E_COMPILE_WARNING:
		case E_PARSE:
		$error = "[SHUTDOWN] level:".$lasterror['type']." | msg:".$lasterror['message']." | file:".$lasterror['file']." | line:".$lasterror['line'];
		log_custom_error($error, "fatal");
	}
}

function log_db_errors($error, $query){
	if( defined('DB_ERRORLOG') ){
		$error = $error;
		$error .= '|Query: '.$query;
		log_custom_error( $error, 'FATAL' );
	}
}

function log_custom_error($error, $errlvl){

	$headers  = 'MIME-Version: 1.0'."\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
	$headers .= 'From: '.str_replace(array('.','/','http:','https:'), '', SITE).' <admin@'.$_SERVER['HTTP_HOST'].'>'."\r\n";

	if( defined('SITE') ){
		$message = '<p>----------------------------------<br>An error has occurred on '.SITE.':</p>';
	}else{
		$message = '<p>----------------------------------</p>';
	}

	$message .= '<p>Error at '.date('Y-m-d H:i:s').': ';
	$message .= 'URI: '.$_SERVER['REQUEST_URI'].': ';
	$error = explode('|', $error);
	foreach($error as $e){
		$message .= $e.'<br>';
	}
	$message .= 'Severity: '.$errlvl.'</p>';

	if( defined('SEND_ERRORS_TO') ){
		mail(SEND_ERRORS_TO , 'Error on '.$_SERVER['HTTP_HOST'], $message, $headers);
	}

	if(DISPLAY_DEBUG){
		echo $message;
	}

	if(LOG_ERRORS){

		$logfile = ROOT.'_ressource_custom/hGtDjkpPWSXk.php';
		$message = strip_tags( $message, '<p><br>' );
		$message = str_replace( array( '<p>', '</p>', '<br />', '<br>' ), PHP_EOL, $message );
		// add HTTP_REF (added aug 4 2014)
		if( isset($_SERVER['HTTP_REFERER']) ){
			$message .= 'http_ref: '.$_SERVER['HTTP_REFERER'].PHP_EOL;
		}
		// add session tracking (added june 18 2014)
		if( isset($_SESSION) && !empty($_SESSION) ){
			foreach($_SESSION as $k => $v){
				if( is_array($v) ){
					foreach($v as $val){
						if( is_array($val) ){
							$v = implode(',', $val);
						}else{
							$v = $val;
						}
					}
				}
				$message .= $k.' Session: '.$v.PHP_EOL;
			}
		}

		if( isset($_COOKIE) && !empty($_COOKIE) ){
			foreach($_COOKIE as $k => $v){
				if( !preg_match('/^(fb|_ga)/', $k) ){ // skip facebook and google cookies
					if( is_array($v) ){
						$v = implode(',', $v);
					}
					$message .= $k.' Cookie: '.$v.PHP_EOL;
				}
			}
		}
		$handle = fopen($logfile, 'a') or die('Cannot open file:  '.$logfile);
		fwrite($handle, $message);
		fclose($handle);
	}
}

