<?php
/* 
required to be defined on top of each page individualy:
$title, $description
*/
if( !isset($title) ){
	$title = 'ADMIN: Matériauthèque de DIE';
}
if( !isset($description) ){
	$description = '';
}
/*
optional: 
$social_url, $social_image
*/
if( !isset($social_url) || empty($social_url) ){
	$social_url = PROTOCOL.SITE.substr($_SERVER['REQUEST_URI'],1); // http(s)://example.com/path/to/dir/
}
/*
if( isset($home_image) && !isset($social_image) ){
	$social_image = PROTOCOL.SITE.$home_image;
}
*/
?>
<!DOCTYPE HTML>
<html lang="fr">
<head>
<?php 
// include google analytics js code if it exists in root directory
$gtag = ROOT.'gtag.js';
if( file_exists($gtag) ){
	include($gtag);
}
?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="<?php echo $description; ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="author" content="Sébastien Brault, sebdedie@gmail.com">

<meta property="og:url"			content="<?php echo $social_url; ?>">
<meta property="og:type"		content="website">
<meta property="og:title"		content="<?php echo $title; ?>">
<meta property="og:description"	content="<?php echo $description; ?>">
<meta property="og:author"		content="Sébastien Brault, sebdedie@gmail.com">
<?php 
if( isset($social_image) && !empty($social_image) ){ ?>
<meta property="og:image"		content="<?php echo $social_image; ?>">
<?php 
}
?>
<title><?php echo $title; ?></title>


<!-- generic css -->
<link href="/_code/css/common.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">


<!-- load responsive design style sheets -->
<link rel="stylesheet" media="(max-width: 980px)" href="/_code/css/max-980px.css?v=<?php echo $version; ?>">
<link rel="stylesheet" media="(max-width: 720px)" href="/_code/css/max-720px.css?v=<?php echo $version; ?>">

</head>

<body>
