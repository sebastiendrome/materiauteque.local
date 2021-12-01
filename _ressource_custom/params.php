<?php
// CUSTOM time zone
date_default_timezone_set('Europe/Paris');

// set var to generaly know we're pulling this file's parameters
$resource_custom = true;

// CUSTOM vars...
define("NAME", 'Bis Usus');
define("TITLE", 'Matériauthèque de Die');

// will show or hide functions/buttons if following are visible or not
$public_site_visible = 0;
$caisse_visible = 1;
$ventes_visible = 1;
$articles_visible = 0;
$categories_visible = 0;

// Errors handling and database connection parameters
if( strstr(SITE,'.local') ){ 	// this local server DB
	// errors
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	$display_debug = TRUE;
	$log_errors = TRUE;
	
	// CUSTOM database
	$db_host = "127.0.0.1";
	$db_user = "root";
	$db_pass = '';
	$db_name = 'bisusus';
}else{	// this remote server
	// errors
	ini_set('display_errors', 0);
	$display_debug = FALSE;
	$log_errors = TRUE;
	define("SEND_ERRORS_TO", AUTHOR_REF);
	
	// CUSTOM database
	$db_host = "localhost";
	// $db_port = 3306;
	$db_user = "fixw2867_bisusus";
	$db_pass = "matériauthèque";
	$db_name = 'fixw2867_bisusus';
}

// CUSTOM admin credentials
$admin_username = 'd033e22ae348aeb5660fc2140aec35850c4da997';
$admin_password = '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8';
