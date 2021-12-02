<?php
// upload file form POST process (from uploadFile.php, modal window)
if(!defined("ROOT")){
	$code = basename( dirname(__FILE__, 3) );
	require preg_replace('/\/'.$code.'\/.*$/', '/'.$code.'/php/first_include.php', __FILE__);
}

/// increase memory size to allow heavy image manipulations (rotating large image and generating sized-down copies)
ini_set('memory_limit','512M');

// upload file form process
if(isset($_POST['uploadFileSubmit'])){
	$path = urldecode($_POST['path']);
	$replace = urldecode($_POST['replace']);
	$upload_result = upload_file($path, $replace);
	echo $upload_result;
	//header("location: manage_contents.php?upload_result=".urlencode($upload_result));
	exit;
}
