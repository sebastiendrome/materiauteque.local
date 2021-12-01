<?php
/* 
required to be defined on top of each page individualy:
$title, $description
*/
if( !isset($title) ){
	if( strstr($_SERVER['REQUEST_URI'], '/admin/') ){
		$title = 'ADMIN: '.NAME;
	}else{
		$title = NAME.' '.TITLE;
	}
}
if( !isset($description) ){
	$description = '';
}
/*
optional: 
$social_url, $social_image
*/
if( !isset($social_url) || empty($social_url) ){
	$social_url = PROTOCOL.SITE.REL.substr($_SERVER['REQUEST_URI'],1); // http(s)://example.com/path/to/dir/
}
/*
if( isset($home_image) && !isset($social_image) ){
	$social_image = PROTOCOL.SITE.REL.$home_image;
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
<link href="<?php echo REL; ?>_code/css/common.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">


<!-- load responsive design style sheets -->
<link rel="stylesheet" media="(max-width: 980px)" href="<?php echo REL; ?>_code/css/max-980px.css?v=<?php echo $version; ?>">
<link rel="stylesheet" media="(max-width: 720px)" href="<?php echo REL; ?>_code/css/max-720px.css?v=<?php echo $version; ?>">

<?php
/* 
output styles depending on PHP vars ste in _ressource_custom.params.php 
namely, show or hide articles, caisse, categories/matiere, or ventes buttons
*/
if(!$articles_visible || !$ventes_visible || !$caisse_visible || !$categories_visible){
	echo '<style type="text/css">'.PHP_EOL;
	if(!$articles_visible){
		echo '.artSH{display:none !important;}'.PHP_EOL;
	}
	if(!$ventes_visible){
		echo '.venSH, .vendre{display:none !important;}'.PHP_EOL;
	}
	if(!$caisse_visible){
		echo '.caiSH{display:none !important;}'.PHP_EOL;
	}
	if(!$categories_visible && !isset($_GET['master'])){
		echo '.catSH{display:none !important;}'.PHP_EOL;
	}
	echo '</style>'.PHP_EOL;
}
// output js depedning on PHP vars
require(ROOT.'_code/js/js.php');
?>

</head>

<body>
