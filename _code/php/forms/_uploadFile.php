<?php
$path = 'uploads/'.$article_id.'/';
?>
<div class="modal" id="uploadFileContainer">

<a href="javascript:;" class="closeBut">&times;</a>

	<!-- upload file start -->
	<div>
	<form enctype="multipart/form-data" name="uploadFileForm" id="uploadFileForm" action="/_code/php/admin/up_file.php" method="post">
		<input type="hidden" name="path" value="<?php echo $path; ?>">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_UPLOAD_BYTES; ?>">
		<input type="hidden" name="contextUploadFile" value="contextUploadFile">
		<a class="button submit left" id="chooseFileLink">Upload a file</a>
		<input type="file" name="file" id="fileUpload" style="opacity:0;"> 
		<button type="submit" name="uploadFileSubmit" style="opacity:0;" id="uploadFileSubmit">Upload</button>
		<div class="progress">
			<div class="bar"></div>
		</div>
	</form>
	</div>
	<!-- upload file end -->

	<p>Supported File Types: jpg, gif, png. Maximum Upload Size: <?php echo MAX_UPLOAD_SIZE; ?></p>
	<a class="button hideModal left">Cancel</a>

	
</div>

