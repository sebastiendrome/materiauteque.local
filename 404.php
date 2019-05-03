<?php
require('_code/php/first_include.php');

header('HTTP/1.0 404 Not Found');
$title = $description = '404: Page not found';
$page = '404';

// social image (for meta property="og:image") is the background image in home page...
if( file_exists( ROOT.'_code/images/home/bg.jpg') ){
	$home_image = 'bg.jpg';
}elseif( file_exists( ROOT.'_code/images/home/bg.gif') ){
	$home_image = 'bg.gif';
}elseif( file_exists( ROOT.'_code/images/home/bg.png') ){
	$home_image = 'bg.png';
}
if( isset($home_image) ){
	$social_image = PROTOCOL.SITE.'_code/images/home/'.$home_image;
}


require(ROOT.'_code/php/doctype.php');

?>

<?php require(ROOT.'_code/inc/header.php'); ?>




<div style="padding:30px; color:#fff; text-shadow:1px 1px 1px #444; text-align:center; font-size:100px;">404:<br>
page not found</div>

<div class="clearBoth"></div>

<?php require(ROOT.'_code/inc/footer.php'); ?>

