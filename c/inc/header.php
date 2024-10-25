<?php
// custom image (logo) to display above login form
if( file_exists(realpath($_SERVER['DOCUMENT_ROOT']).'/_ressource_custom/logo.png') ){
	$custom_image = '<img src="/_ressource_custom/logo.png" style="max-width:100%;" alt="'.NAME.' '.TITLE.'">';
}else{
	$custom_image = '';
}
?>
<div id="header">
	<h1 id="mainHeader"><a href="/" title="<?php echo NAME.' '.TITLE; ?>"><?php echo NAME; ?><br>
	<?php echo $custom_image; ?></a></h1>
	<?php require(ROOT.'/c/inc/search.php'); ?>
	<div class="clearBoth"></div>
</div>