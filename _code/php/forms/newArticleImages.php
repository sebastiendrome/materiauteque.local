
<?php
if( !defined("ROOT") ){
	require('../../../_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

if( isset($_GET['article_id']) ){
	$article_id = urldecode($_GET['article_id']);
}
if( !isset($article_id) ){
	echo '<p class="error">Pas d\'article id!</p>';
	exit();
}else{
	$path = 'uploads/'.$article_id.'/';
	$images_array = get_article_images($article_id, '_S');
}
if( isset($_GET['context']) ){
	$context = $_GET['context'];
}else{
	$context = '';
}
?>
<div class="modal"><a class="closeBut hideModal" href="javascript:;">&times;</a>
<form enctype="multipart/form-data" name="uploadFileForm" id="uploadFileForm" action="/_code/php/admin/upload_file.php" method="post">
	<table>
	<tr>
		<td>Images:<td>
			<?php 
			$path = 'uploads/'.$article_id;
			echo '<a class="button submit left" id="chooseFileLink">Ajouter une image</a>
			<div class="progress">
				<div class="bar"></div>
			</div>
			<br>
			<span class="hideUp">(Poids maximum de l\'image: '.MAX_UPLOAD_SIZE.')</span>
			<input type="hidden" name="path" value="'.$path.'">
			<input type="hidden" name="replace" value="">
			<input type="hidden" name="context" value="'.$context.'">
			<input type="hidden" name="MAX_FILE_SIZE" value="'.MAX_UPLOAD_BYTES.'">
			<input type="hidden" name="contextNewFile" value="contextNewFile">
			<div style="height:1px; margin:10px 0; overflow:hidden;">
			<input type="file" name="file" id="fileUpload" style="opacity:0;"><br>
			<button type="submit" name="uploadFileSubmit" id="uploadFileSubmit" style="opacity:0;">Choisir une image</button></div>';
			if(!empty($images_array)){
				if( isset($_GET['upload_result']) ){
					$rand = '?v='.rand(1,999);
				}else{
					$rand = '';
				}
				foreach($images_array as $i){
					$medium_image = str_replace('/_S/','/_M/', $i);
					$large_image = str_replace('/_S/','/_L/', $i);

					echo '<div class="editImageDiv">
					<!--<a href="javascript:;" class="button change showModal" rel="newFile?path='.urlencode($path).'&replace='.urlencode($i).'">modifier</a>-->
					<a href="javascript:;" class="button remove cancel showModal" rel="deleteFile?file='.urlencode($i).'">supprimer</a>
					<a href="/'.$large_image.$rand.'" target="_blank"><img src="/'.$medium_image.$rand.'"></a></div>';
				}
			}
			?>
	</tr>
	</table>
</form>
</div>
