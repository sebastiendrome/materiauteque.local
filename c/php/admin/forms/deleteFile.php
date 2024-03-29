<?php
if(!defined("ROOT")){
	$code = basename( dirname(__FILE__, 4) );
	require preg_replace('/\/'.$code.'\/.*$/', '/'.$code.'/php/first_include.php', __FILE__);
}

// for creating sub-sections, we need the parent section:
if(isset($_GET['file']) && !empty($_GET['file']) ){
	$file = urldecode($_GET['file']);
	//echo '$file: '.$file.'<br>';
	$ext = file_extension($file);
	// get file_name and path ready for function display_file_admin
	$file_name = basename($file);
	$path = preg_replace('/\/(_XL|_S|_M|_L)\/'.preg_quote($file_name).'$/', '', $file);
	//echo '$path: '.$path.'<br>';
	$display_file = display_file_admin(REL.$path, $file_name);
	
}else{
	exit;
}

?>
<div class="modal" id="deleteFileContainer">
	<a href="javascript:;" class="closeBut hideModal">&times;</a>
	<h3 class="first">Êtes vous sûr.e de vouloir supprimer cette image?</h3>
	<?php echo $display_file; ?>
	<p><?php echo filename($path, 'decode').'/'.filename($file_name, 'decode'); ?></p>
	<form name="deleteFileForm" action="<?php echo REL; ?>c/php/admin/delete_file.php" method="post">
		<input type="hidden" name="deleteFile" value="<?php echo urlencode($file); ?>">
	<a class="button hideModal left">Non</a> <button type="submit" name="deleteFileSubmit" class="cancel right">Supprimer</button>
</form>	
</div>

