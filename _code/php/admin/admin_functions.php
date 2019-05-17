<?php
/*********** 1: UTILITY FUNCTIONS (USED WITHIN OTHER FUNCTIONS) ***************/

/* COPY DIRECTORY AND ITS CONTENTS */
function copyr($source, $dest){
    if (is_file($source)) {// Simple copy for a file
        return copy($source, $dest);
    }
    if (!is_dir($dest)) {// Make destination directory
        mkdir($dest,0777);
    }
    $dir = dir($source);// Loop through the folder
    while (false !== $entry = $dir->read()) {
        if ($entry == '.' || $entry == '..') {// Skip pointers
            continue;
        }
        if ($dest !== "$source/$entry") {// Deep copy directories
            copyr("$source/$entry", "$dest/$entry");
        }
    }
    $dir->close();// Clean up
    return true;
}
/* FUNCTION TO REMOVE DIRECTORY AND ITS CONTENTS */
function rmdirr($dirname){
    if (!file_exists($dirname)){// Sanity check
        return false;
    }
    if (is_file($dirname)){// Simple delete for a file
        return unlink($dirname);
    }
    $dir = dir($dirname);// Loop through the folder
    while (false !== $entry = $dir->read()){
        if ($entry == '.' || $entry == '..'){// Skip pointers
            continue;
        }
        rmdirr("$dirname/$entry");// Recurse
    }
    $dir->close();// Clean up
    return rmdir($dirname);
}

/* human file size */
function FileSizeConvert($bytes){
	$bytes = floatval($bytes);
		$arBytes = array(
			0 => array(
				"UNIT" => "TB",
				"VALUE" => pow(1024, 4)
			),
			1 => array(
				"UNIT" => "GB",
				"VALUE" => pow(1024, 3)
			),
			2 => array(
				"UNIT" => "MB",
				"VALUE" => pow(1024, 2)
			),
			3 => array(
				"UNIT" => "KB",
				"VALUE" => 1024
			),
			4 => array(
				"UNIT" => "B",
				"VALUE" => 1
			),
		);

	foreach($arBytes as $arItem){
		if($bytes >= $arItem["VALUE"]){
			$result = $bytes / $arItem["VALUE"];
			$result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
			break;
		}
	}
	return $result;
}




/*********** 2: DISPLAY FUNCTIONS (FUNCTIONS THAT OUTPUT HTML MARKUP) ***************/



/*********** 3: ACTIVE FUNCTIONS (FUNCTIONS THAT CHANGE THE CONTENT) ***************/



/* delete file, all its size versions
*/
function delete_file($delete_file){
	$message = $error = '';
	$file_name = basename($delete_file);
	$ext = file_extension($file_name);
	
	// delete files
	if( file_exists(ROOT.$delete_file) ){
		if( preg_match($_POST['types']['resizable_types'], $ext) ){ // resizable (images) files
			// get all sizes for deletion
			$xl_file = str_replace('/_S/', '/_XL/', $delete_file);
			$m_file = str_replace('/_S/', '/_M/', $delete_file);
			$l_file = str_replace('/_S/', '/_L/', $delete_file);
			
			if( unlink(ROOT.$delete_file) ){
				$message .= '<p class="success">Le fichier a été éffacé.</p>';
				// delete all sizes
				unlink(ROOT.$xl_file);
				unlink(ROOT.$m_file);
				unlink(ROOT.$l_file);
			}else{
				$message .= '<p class="error">ERROR: The file could not be deleted.</p>';
			}
			
		}else{ // not an image... no sizes.
			if( unlink(ROOT.$delete_file) ){
				$message .= '<p class="success">Le fichier a été éffacé.</p>';
			}else{
				$message .= '<p class="error">ERREUR: Le fichier n\'a pas pu être éffacé.</p>';
			}
		}
		
	}else{
		$message .= '<p class="error">ERROR: File does not exist: '.$delete_file.'</p>';
	}
	return $message;
}

/******************************* UPLOAD / RESIZE FILE *******************************************/

/* straight-up upload file function, used in later function. 
Requires a FORM-submitted file input named "file"
*/
function up_file($upload_dest){
	// increase memory size to allow heavy image manipulations (rotating large image and generating sized-down copies)
	ini_set('memory_limit','512M');
	if( move_uploaded_file($_FILES['file']['tmp_name'], $upload_dest) ) {
		// if file is a jpg, fix orientation if possible
		$ext = file_extension($upload_dest);
		if($ext == '.jpg'){
			if( $orientation = get_image_orientation($upload_dest) ){
				$result = fix_image_orientation($upload_dest, $orientation);
				// $result could be empty (success) or string 'error message'. 
				// This is NOT returned by this function, which just returns true or false.
			}
		}
		return true;
	}else{
		return false;
	}
}


/* determine if image can be rotated to correct orientation (only for jpg)
*/
function get_image_orientation($path_to_jpg){
	$exif = exif_read_data($path_to_jpg);
	if ( !empty($exif['IFD0']['Orientation']) ) {
		$orientation = $exif['IFD0']['Orientation'];
	}elseif( !empty($exif['Orientation']) ){
		$orientation = $exif['Orientation'];
	}else{
		$orientation = false;
	}
	return $orientation;
}


/* fix image orientation (only for jpg)
*/
function fix_image_orientation($path_to_jpg, $image_orientation){

	$result = '';
	list($w, $h) = getimagesize($path_to_jpg);
	$new = imagecreatetruecolor($w, $h);
						
	if(!$new){
		$result .= '<p class="error">could not imagecreatetruecolor</p>';
	}else{
		$from = imagecreatefromjpeg($path_to_jpg);
		
		if(!$from){
			$result .= '<p class="error">could not imagecreatefromjpeg: '.$path_to_jpg.'</p>';
		}else{
			if( !imagecopyresampled($new, $from, 0, 0, 0, 0, $w, $h, $w, $h) ){
				$result .= '<p class="error">could not imagecopyresampled: '.$path_to_jpg.'</p>';
			}else{
				
				switch($image_orientation) {
					case 3:
						$new = imagerotate($new, 180, 0);
						break;
					case 6:
						$new = imagerotate($new, -90, 0);
						break;
					case 8:
						$new = imagerotate($new, 90, 0);
						break;
				}
				
				imagejpeg($new, $path_to_jpg, 90);
			}
		}
	}
	imagedestroy($new);

	if( empty($result) ){
		return true;
	}else{
		return $result;
	}

}


/* upload file (under manage content) - requires updating menu.txt
uses update_menu_file
*/
function upload_file($path, $replace=''){
	// initialize upload results
	$upload_message = $resize_result = $menu_update_result = '';
	$types = $_POST['types'];

	$file_name = $_FILES['file']['name']; // 'file' must be the name of the file upload input in the sending html FORM!

	// get file extension
	$ext = file_extension($file_name);
	// re-format extension to standard, to avoid meaningless mismatch
	$ext = strtolower($ext);
	if($ext == '.jpeg' || $ext == '.jpe'){
		$ext = '.jpg';
	}
	if($ext == '.oga'){
		$ext = '.ogg';
	}
	// Mac .txt files can use the "plain" file type (for plain text)!...
	if($ext == '.plain'){
		$ext = '.txt';
	}
	// msword file type (can be generated by open office)... and docx can be .doc, to use the doc.png icon...
	if($ext == '.msword' || $ext == '.docx'){
		$ext = '.doc';
	}
	// wav files can have 'x-wav' type
	if($ext == '.x-wav'){
		$ext = '.wav';
	}
	
	// check against extension if file type is supported
	if (!preg_match($types['supported_types'], $ext)){
		$upload_message .= '<p class="error">Ce type de fichier n\'est pas autorisé: '.$ext.'<br>Le fichier n\'a pas été mis en ligne.</p>';
	
	// UPLOAD FILE
	}else{
		
		// format/clean file name (without the extension)
		$file_name_no_ext = file_name_no_ext($file_name);
		$file_name_no_ext = filename($file_name_no_ext, 'encode');
		
		// is it an image? (if yes, it will be resized and uploaded in various sizes/directories)
		if( preg_match($types['resizable_types'], $ext) ){
			$resize = TRUE;
		}else{
			$resize = FALSE;
		}
		
		$path .= '/_XL/'; // append the extra large (original version) size directory to upload path
		
		// if we're uploading a file to replace another one
		if( !empty($replace) ){
			$replace_file_name_no_ext = file_name_no_ext($replace);
			$upload_dest = $path.$replace_file_name_no_ext.$ext;
			// if the original file and its replacement don't have the same extension, delete the original
			$replace_ext = file_extension($replace);
			if( $replace_ext != $ext){
				if( !unlink(ROOT.$replace) ){
					$upload_message .= '<p class="note warning">Impossible d\'effacer '.$replace.'</p>';
				}
			}
		// if we're uploading to add a new file
		}else{
			// let's make sure the file name is unique
			$rand = rand(1,9999);
			$new_file_name = $file_name_no_ext.'-'.$rand.$ext;
			$upload_dest = $path.$new_file_name;
		}

		$root_upload_dest = ROOT.$upload_dest;
		
		// upload
		if( up_file($root_upload_dest) ){
			
			// RESIZE, if file is resizable (image)
			if($resize){
				
				// read exif data and fix image orientation now if necessary! (concerns only jpgs)
				if($ext == '.jpg'){

					// get image orientation from exif metadata, or return false
					$image_orientation = get_image_orientation($root_upload_dest);
					
					// could not read image orientation...
					if($image_orientation !== false){

						// fix image orientation (and return true) or return error message
						$fix_orientation = fix_image_orientation($root_upload_dest, $image_orientation);
						if( $fix_orientation != true ){
							$upload_message .= $fix_orientation;
						}
					
					// cannot read image orientation. (commented out because somehow the message always display for jpg uploads?...)
					}/*else{
						
						$upload_message .= '<p class="note warning">Could not read image orientation for file: '.filename(basename($upload_dest), 'decode').'</p>';
					}*/
				}
				
				// update width and height now! Or else resizing will be off...
				list($w, $h) = getimagesize($root_upload_dest);

				$resize_result .= resize_all($root_upload_dest, $w, $h);
				if(substr($resize_result, 0, 1) === '0'){
					$upload_message .= '<p class="error">'.$resize_result.'</p>';
				}
			}

			$new_file_name = basename($upload_dest);
			$upload_message .= '<p class="success">Fichier mis en ligne: '.filename($new_file_name, 'decode').'</p>';
			
		}else{
			$upload_message .= '<p class="error">Erreur: Assurez-vous que le poids du fichier ne dépasse pas '.MAX_UPLOAD_SIZE.'!</p>';
		}
	}

	$upload_results = $upload_message;

	return $upload_results;
}


/* resize image to multiple sizes */
function resize_all($upload_dest, $w, $h){
	
	$resize_result = '';
	
	// resize image to various sizes as specified by $_POST['sizes'] array
	foreach($_POST['sizes'] as $key => $val){
		
		$width = $val['width'];
		$height = $val['height'];
		$resize_dest = str_replace('/_XL', '/_'.$key, $upload_dest);
		
		if($w > $width || $h > $height){
			$resize_result .= resize($upload_dest, $resize_dest, $w, $h, $width, $height);
				
		}else{
			if( !copy($upload_dest, $resize_dest) ){
				$resize_result .= '0|could not copy '.$upload_dest.' to '.$resize_dest.'<br>';
			}
		}
	}
	
	return $resize_result;
}


/* resize image */
function resize($src, $dest, $width_orig, $height_orig, $width, $height){

	$types = $_POST['types'];
	$result = '';

	$ext = file_extension($src); //extract extension
	$ext = str_replace('jpeg', 'jpg', strtolower($ext) ); // format it for later macthing
	
	// make sure file is resizable (match against file extension)
	if ( preg_match($types['resizable_types'], $ext) ){
		
		// if image is bigger than the target width or height, calculate new sizes and resize
		if($width_orig > $width || $height_orig > $height){
			$scale = min($width/$width_orig, $height/$height_orig);
			$width = round($width_orig*$scale);
			$height = round($height_orig*$scale);
			
			// create canvas for image with new sizes
			$new = imagecreatetruecolor($width, $height);
			if(!$new){
				return '0|could not imagecreatetruecolor<br>';
			}
			
			// we can resize jpg, gif and png files.
			if($ext == '.jpg'){ 
				$from = imagecreatefromjpeg($src);
			}elseif($ext == '.gif'){
				imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
				imagealphablending($new, false);
				imagesavealpha($new, true);
				$from = imagecreatefromgif($src); 
			}elseif($ext == '.png'){
				imagealphablending($new, false);
				imagesavealpha($new, true);
				$from = imagecreatefrompng($src);
			}
			
			if(!$from){
				return '0|could not imagecreatefrom: '.$src.'<br>';
			}
			
			if( !imagecopyresampled($new, $from, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig) ){
				return '0|could not imagecopyresampled<br>';
			}
				
			if($ext == '.jpg'){
				imagejpeg($new, $dest, 90);
			}elseif($ext == '.gif'){ 
				imagegif($new, $dest); 
			}elseif($ext == '.png'){
				imagepng($new, $dest);
			}
			imagedestroy($new);
			
		// no need to resize, the original image is too small
		}else{
			return '1|no need to resize.';
		}
	
	// file is not resizable
	}else{
		return '0|file is not resizable.';
	}
	
	return $result;
}


/* echo popup to edit value of field */
function popup_edit($field){
	$output = '';
	switch ($field) {
		case 'title':
			$output = "title";
			break;
		case 'categories_id':
		$output = "i égal 1";
			break;
	}
	return $output;
}

// CSV TO ARRAY
function csv_to_array($csv_file, $delimiter){
	if($handle = fopen($csv_file, "r")){
		$first_line = fgets($handle);
		$fields = explode($delimiter,$first_line);
		while ($d = fgetcsv($handle, 1500,$delimiter)){ // limits line to 1500 chars
			$i=0;
			foreach($fields as $f){
				$array[trim($f)] = str_replace('"','', trim($d[$i]) );
				$i++;
			}
			$csv_data[] = $array;
		}
		fclose($handle);

		return $csv_data;

	}else{
		echo '<p class="error">Sorry, could not find csv file: '.$csv_file.'</p>';
	}
}