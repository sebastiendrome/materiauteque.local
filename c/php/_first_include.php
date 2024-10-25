<?php
/******** TO DO ********
 * * Statistiques
 * * Fermer / ouvrir la caisse
 * * Gérer Catégories & Matières (master only)
 * * Gérer les utilisateurs (master only)
 * 'master' and 'guest' user (guest cannot access Catégories/Matières, Utilisateurs)
 * order images for articles (move up/down)
 */
session_start();

// set version, to load fresh css and js
$version = 1;

// software author
define("AUTHOR_REF", 'sebdedie@gmail.com');
// initialize site 
define("SITE", $_SERVER['HTTP_HOST']);
// both vars above are needed for processing _ressource_custom.php required below

// document root (beware of inconsistent trailing slash depending on environment, hence the use of realpath)
$root = realpath($_SERVER['DOCUMENT_ROOT']).'/magazin';
echo $root.'/_ressource_custom/params.php';
// include custom parameters, or set default values
if( file_exists($root.'/_ressource_custom/params.php') ){
	require $root.'/_ressource_custom/params.php';
}else{
	date_default_timezone_set('Europe/Paris');
	$ressource_custom = false;
	define("NAME", 'Le Nom');
	define("TITLE", 'Ressourcerie');
	$public_site_visible = 1;
	$caisse_visible = 1;
	$ventes_visible = 1;
	$articles_visible = 1;
}

// admin credentials (default, if not specified in ressource_custom.php/params.php)
if(!$ressource_custom || !isset($admin_username) ){
	$admin_username = 'd033e22ae348aeb5660fc2140aec35850c4da997';
	$admin_password = '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8';
}
$master_username = 'd756b59530a2ad4b4d1bc0468f89631c2bbdb03a';
$master_password = 'dfc46fa4321fecc8de64ed31087e25c2c9a1b76d';

/* 
We KNOW that this file is within 'c/php', which may or may not be at web root 
(/c/php, OR /dir/c/php) 
so let's see if there's anything between ROOT and 'c/php',
if yes, it is the directory we have to add to all our relative paths
*/
$rel = preg_replace('/('.preg_quote($root, '/').'|c\/php)/', '', realpath(__DIR__));
/* debug */
//echo '<h1>REL: '.$rel.'</h1>';

define("ROOT", $root.$rel);

// site relative root
define("REL", $rel);

// Protocol: http vs https
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
define("PROTOCOL", $protocol);

// php root and error reporting, local vs. remote
if( !$ressource_custom && strstr(SITE,'.local') ){ 	// default local server, no custom params
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	$display_debug = TRUE;
	$log_errors = TRUE;
	$db_host = "127.0.0.1";
	$db_user = "root";
	$db_pass = '';
	$db_name = 'materiauteque';
}elseif( !$ressource_custom || !isset($db_host) ){ // no custom remote server: Abort.
	echo '<p style="color:red;">ERROR: No DB connection data!...</p>';
	exit;
}

define("LOG_ERRORS", $log_errors);
define("DISPLAY_DEBUG", $display_debug);
define("DB_HOST", $db_host);
define("DB_USER", $db_user);
define("DB_PASS", $db_pass);
define("DB_NAME", $db_name);

// error handler
require ROOT.'c/php/errors.php';

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

// FILE SIZES:
$sizes = array();
//$sizes['L'] = array("width"=>800, "height"=>667);
$sizes['L'] = array("width"=>650, "height"=>542);
$sizes['M'] = array("width"=>300, "height"=>250);
$sizes['S'] = array("width"=>80, "height"=>70);

// images default size 
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
require ROOT.'c/php/functions.php';
require ROOT.'c/php/db_functions.php';

// require admin functions if within admin directory
if( strstr($_SERVER['REQUEST_URI'], '/admin/') ){
	// get paniers en cours (as array) and count them
	if( $paniers = get_table('paniers', 'statut_id=1', 'date DESC') ){
		$paniers_count = count($paniers);
	}else{
		$paniers_count = '0';
	}
	// get statut table, paiement table as arrays
	/*
	$statut_array = get_table('statut');
	$paiement_array = get_table('paiement');
	*/
	require ROOT.'c/php/admin/not_logged_in.php';
	require ROOT.'c/php/admin/admin_functions.php';

	// max upload size (after including functions) 
	$max_upload_size = ini_get('upload_max_filesize');
	$max_upload_bytes = return_bytes($max_upload_size);
	define("MAX_UPLOAD_SIZE", $max_upload_size);
	define("MAX_UPLOAD_BYTES", $max_upload_bytes);
}
