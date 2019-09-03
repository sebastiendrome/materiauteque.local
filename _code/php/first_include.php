<?php
/******** TO DO ********
 * view more than 1 image per article
 * order images for articles (move up/down)
 * Form: vente process and logic
 * pagination des résultats (admin et public)
 */
session_start();
date_default_timezone_set('Europe/Paris');

// set version, to load fresh css and js
$version = 14;

// initialize site 
define("SITE", $_SERVER['HTTP_HOST'].'/');
// Protocol: http vs https
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
define("PROTOCOL", $protocol);
// document root (beware of inconsistent trailing slash depending on environment, hence the use of realpth)
define("ROOT", realpath($_SERVER['DOCUMENT_ROOT']).'/');
// reference to site author...
define("AUTHOR_REF", 'sebdedie@gmail.com');

// php root and error reporting, local vs. remote
if( strstr(SITE,'.local') ){ 					// local server
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	$display_debug = TRUE;
	$log_errors = TRUE;
	$db_host = "127.0.0.1";
	$db_user = "root";
	$db_pass = '';
	$db_name = 'materiauteque';
}else{ 											// remote server
	ini_set('display_errors', 0);
	$display_debug = FALSE;
	$log_errors = TRUE;
	$db_host = "mysql.materiauteque.org";
	$db_user = "materiautequedb";
	$db_pass = "dKy-UzMo!@qlJk";
	$db_name = 'materiauteque';
	define("SEND_ERRORS_TO", AUTHOR_REF);
}

define("LOG_ERRORS", $log_errors);
define("DISPLAY_DEBUG", $display_debug);
define("DB_HOST", $db_host);
define("DB_USER", $db_user);
define("DB_PASS", $db_pass);
define("DB_NAME", $db_name);


// error handler
require(ROOT.'_code/php/errors.php');



// FILE TYPES
$types = array();
// ALL
$types['supported_types'] = '/^\.(jpe?g?|png|gif|s?html?|txt|mp3|m4a|oga?g?|wav|mp4|m4v|webm|ogv|pdf|docx?|msword|odt)$/i';
// TEXT
$types['text_types'] = '/^\.(s?html?|txt)$/i';
// audio
$types['audio_types'] = '/^\.(mp3|m4a|oga?g?|wav)$/i';
// video
$types['video_types'] = '/^\.(mp4|m4v|webm|ogv)$/i';
// resizable
$types['resizable_types'] = '/^\.(jpe?g?|png|gif)$/i';
// only available for download
$types['download'] = '/^\.(pdf|docx?|msword|odt)$/i';
// register $types as a $_POST var, so it is accessible within functions scope (like a constant).
$_POST['types'] = $types;

// FILE SIZES:
$sizes = array();
//$sizes['L'] = array("width"=>800, "height"=>667);
$sizes['L'] = array("width"=>650, "height"=>542);
$sizes['M'] = array("width"=>300, "height"=>250);
$sizes['S'] = array("width"=>80, "height"=>70);
// register $sizes as a $_POST var, so it is accessible within functions scope.
$_POST['sizes'] = $sizes;

// image size 
$size = "_M";
if(isset($_COOKIE['wW'])){
	if($_COOKIE['wW'] > 1370 ){
		$size = "_L";
	}elseif($_COOKIE['wW'] < 340){
		$size = "_S";
	}
}
define("SIZE", $size);


// connect to database
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die("No DB Connection");
mysqli_set_charset( $db, 'utf8');


// require common functions
require(ROOT.'_code/php/functions.php');
require(ROOT.'_code/php/db_functions.php');


// admin credentials
$admin_username = 'd033e22ae348aeb5660fc2140aec35850c4da997';
$admin_password = '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8';
$master_username = 'd756b59530a2ad4b4d1bc0468f89631c2bbdb03a';
$master_password = 'dfc46fa4321fecc8de64ed31087e25c2c9a1b76d';


// max upload size (after including functions, for 
$max_upload_size = ini_get('upload_max_filesize');
$max_upload_bytes = return_bytes($max_upload_size);
define("MAX_UPLOAD_SIZE", $max_upload_size);
define("MAX_UPLOAD_BYTES", $max_upload_bytes);

