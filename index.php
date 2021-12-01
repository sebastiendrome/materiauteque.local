<?php
require 'c/php/first_include.php';
if($public_site_visible == false){
	header("Location: c/admin/");
}else{
	require 'c/php/index.php';
}