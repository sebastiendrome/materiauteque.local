<?php
require '_code/php/first_include.php';
if($public_site_visible == false){
	header("Location: _code/admin/");
}else{
	require '_code/php/index.php';
}