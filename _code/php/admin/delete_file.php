<?php
// delete file form POST process (from deleteFile.php, modal window)
if( !defined("ROOT") ){
	if(!defined("ROOT")){
	$code = basename( dirname(__FILE__, 3) );
	require preg_replace('/\/'.$code.'\/.*$/', '/'.$code.'/php/first_include.php', __FILE__);
}
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

$referer = preg_replace('/\?.*$/', '', $_SERVER['HTTP_REFERER'] );
$page = basename($referer);
// only authorize coming from these 2 admin pages
/*if($page !== 'manage_contents.php' && $page !== 'manage_structure.php'){
	echo $page.' not authorized';
	exit;
}*/

// DELETE FILE form process
if(isset($_POST['deleteFile']) && !empty($_POST['deleteFile'])){
	$message = delete_file( urldecode($_POST['deleteFile']) );
	header('location: '.$referer.'?message='.urlencode($message));
	exit;
}
