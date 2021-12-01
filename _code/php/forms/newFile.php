<?php
// upload file form
// used inline or loaded via ajax, so check for necessary vars and require files accordingly
if( !defined("ROOT") ){
	require('../../../_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}
if( isset($_GET['path']) ){
	$path = urldecode($_GET['path']);
}
if( isset($_GET['replace']) ){
	$replace = urldecode($_GET['replace']);
}
if( !isset($path) || empty($path) ){
	exit;
}

// uploaded file should replace a previous one?
if( !isset($replace) || empty($replace) ){
	$replace = $replace_filename = '';
}
?>

	<!-- upload file start -->
	<div>
	<form enctype="multipart/form-data" name="uploadFileForm" id="uploadFileForm" action="<?php echo REL; ?>_code/php/admin/upload_file.php" method="post">
	<a class="button submit left" id="chooseFileLink">Choisir une image</a>
	<div class="progress">
		<div class="bar"></div>
	</div>
		<input type="file" name="file" id="fileUpload" style="opacity:0;">
		<input type="hidden" name="path" value="<?php echo $path; ?>">
		<input type="hidden" name="replace" value="<?php echo $replace; ?>">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_UPLOAD_BYTES; ?>">
		<input type="hidden" name="contextNewFile" value="contextNewFile">
		
		<button type="submit" name="uploadFileSubmit" id="uploadFileSubmit" class="right"  style="opacity:0;">Choisir une image</button>
	</form>
	<span class="hideUp">(Poids maximum de l'image: <?php echo MAX_UPLOAD_SIZE; ?>)</span>
	</div>
	<!-- upload file end -->

	<!-- <a class="button hideModal left hideUp">Annuler</a> -->
	

