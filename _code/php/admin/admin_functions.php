<?php

/*********** DISPLAY FUNCTIONS (FUNCTIONS THAT OUTPUT HTML MARKUP) ***************/

// display file
function display_file_admin($path, $file_name){
	
	global $types;
	
	$ext = file_extension($file_name);
	
	// various ways to display file depending on extension
	// 1. resizable types (jpg, png, gif)
	if( preg_match($types['resizable_types'], $ext) ){
		$item = $path.'/_M/'.$file_name;
		// url link to file
		if(substr($item, 0, 1) != '/'){
			$file_link = '/'.$item;
		}else{
			$file_link = $item;
		}
		
		$display_file = '<a href="'.str_replace('/_S/', '/_L/', $file_link).'" title="view image in a new window" target="_blank"><img src="'.$file_link.'?rand='.rand(111,999).'" id="'.$file_name.'"></a>';
		
	}else{
		// if not an image, the file is in the _XL directory (no various sizes)
		$item = $path.'/_XL/'.$file_name;
		// url link to file
		$file_link = '/'.$item;
		
		if( preg_match($types['audio_types'], $ext) ){ // audio, show <audio>
			if($ext == '.mp3' || $ext == '.mpg'){
				$media_type = 'mpeg';
			}elseif($ext == '.m4a'){
				$media_type = 'mp4';
			}elseif($ext == '.oga'){
					$media_type = 'ogg';
			}else{
				$media_type = substr($ext, 1);
			}
			$display_file = PHP_EOL.'<audio controls style="width:100%; border:1px solid #ccc;">
			<source src="/'.$item.'" type="audio/'.$media_type.'">
			Sorry, your browser doesn\'t support HTML5 audio.
			</audio>'.PHP_EOL;

		}elseif( preg_match($types['video_types'], $ext) ){ // text video files
			if($ext == '.m4v'){
				$media_type = 'mp4';
			}elseif($ext == '.ogv'){
				$media_type = 'ogg';
			}else{
				$media_type = substr($ext, 1);
			}
			$display_file = PHP_EOL.'<video controls style="width:100%; border:1px solid #ccc;">
			<source src="/'.$item.'" type="video/'.$media_type.'">
			Sorry, your browser doesn\'t support HTML5 video.
			</video>'.PHP_EOL;

		
		}elseif($ext == '.txt'){ // txt
			$display_file = '<div class="txt admin">'.nl2br( strip_tags( file_get_contents(ROOT.$item) ) ).'</div>';
		
		}elseif($ext == '.html'){ // html
			$display_file = '<div class="html admin">'.strip_tags( file_get_contents(ROOT.$item) ).'</div>';

		}elseif($ext == '.emb'){ // embeded media
			$display_file = '<div class="html admin">'.file_get_contents(ROOT.$item).'</div>';
		
		}else{
			$display_file = '<a href="'.str_replace('/_S/', '/_XL/', $file_link).'" title="view file in a new window" target="_blank"><img src="/_code/images/'.substr($ext,1).'.png" id="'.$file_name.'"></a>';
		}
	}
	if( !isset($display_file) || empty($display_file) ){
		$display_file = '<p class="error">Cannot display '.$path.$file_name.'</p>';
	}
	return $display_file;
}

// display one article
function display_article_panier($article, $panier_id, $context = ''){
	$output = '';
	// $article is either array of article data OR article ID
	if( is_array($article) ){
		$a = $article;
	}elseif( is_numeric($article) ){
		$a = get_item('articles', $article);
	}else{
		return '<p class="error">wrong $article data: '.$article.'</p>';
	}
	// context is the div target whose html content is to be updated
	if($context !== 'paniersAjaxTarget' && $context !== 'ventesPaniersAjaxTarget'){
		return '<p class="error">wrong context (target): '.$context.'</p>';
	}

	$statut_vals = get_table('statut');
	$statut_options = '<select name="'.$a['id'].'[statut_id]" class="statut_id">';
	foreach($statut_vals as $sv){
		if($sv['nom'] == 'vendu'){
			$selected = ' selected';
		}else{
			$selected = '';
		}
		$statut_options .= '<option value="'.$sv['id'].'"'.$selected.'>'.$sv['nom'].'</option>';
	}
	$statut_options .= '</select>';

	$output .= '<div class="particle" data-articleid="'.$a['id'].'">';
	$output .= $a['titre'].'<div class="paActions">';
	$output .= $statut_options;

	$output .= '<a href="javascript:;" class="remove" title="supprimer cet article du panier"></a> <input type="number" step="any" min="0" name="'.$a['id'].'[poids]" class="weight aPoids" placeholder="0,000" value="'.$a['poids'].'">kg&nbsp;&nbsp;&nbsp;&nbsp;
	€<input type="number" step="any" name="'.$a['id'].'[prix]" class="currency aPrix" placeholder="0,00" value="'.$a['prix'].'">';
	$output .= '</div><a href="javascript:;" class="undo lowkey" title="annuler la suppression de cet article">Remettre l\'article</a>';

	$output .= '<div class="clearBoth"></div>
	</div>';

	return $output;
}

// display one panier
function display_panier($id, $context = ''){
	$paniers = array();
	$paniers[] = get_item('paniers', $id);

	if($context == 'ventesPaniersAjaxTarget'){
		$output = display_paniers($paniers);
	}elseif($context == 'paniersAjaxTarget'){
		$output = display_paniers_en_cours($paniers);
	}
	return '1|'.$output;
}

// display open paniers (in paniersModal.php)
function display_paniers_en_cours($paniers){
	if( !$paniers ){
		return false;
	}
	$output = $last = '';
	$i = 0;
	$p_count = count($paniers);
	$statut_vals = get_table('statut');

	foreach($paniers as $p){
		$i++;
		if($i == $p_count){
			$last = ' last'; // css class that will change the last drop-down ul
		}

		if( $articles = get_panier_articles($p['id']) ){
			
			$a_count = count($articles);
			$poids_total = $total = 0;
			$articles_output = '<div class="articlesCont">';
			
			foreach($articles as $a){
				
				$articles_output .= display_article_panier($a, $p['id'], 'paniersAjaxTarget');

				$poids_total += $a['poids'];
				$total += $a['prix'];
			}

			$articles_output .= '</div>';

			$total = number_format($total,2);

			if($a_count > 1){
				$count_output = '&nbsp;&nbsp;&nbsp;'.$a_count.' articles, '. str_replace('.', ',', $poids_total).' kg<br>';
			}else{
				$count_output = '';
			}

			if($p['paiement_id'] == '2'){
				$checked = ' checked';
			}else{
				$checked = '';
			}

			if( empty($p['notes']) ){
				$node = '&nbsp';
				$note_display = 'none';
				$note_button = '<a href="javascript:;" class="note addNote left">note...</a>';
			}else{
				$node = $p['notes'];
				$note_display = 'block';
				$note_button = '<a href="javascript:;" class="note addNote left" style="display:none;">note...</a>';
			}

			// output starts
			$output .= '<div class="pCont" data-panierid="'.$p['id'].'" data-paniernom="'.$p['nom'].'">';
			
			$output .= '<div class="title"><b class="n">'.$p['nom'].'</b>';
			$output .= $count_output;
			$output .= '</div>';

			$output .= '<form name="'.$p['id'].'" action="" method="get">
			<input type="hidden" name="id" value="'.$p['id'].'">
			<input type="hidden" name="savePanierSubmitted" value="savePanierSubmitted">';

			$output .= $articles_output;

			// show warning if total != somme des articles
			if($total !== $p['total']){
				$show_warn = ' style="visibility:visible;"';
			}else{
				$show_warn = '';
			}

			if($total <= 0 && ( empty($p['total']) || $p['total'] <= 0) ){
				$disabled = ' disabled';
			}else{
				$disabled = '';
			}
			
			$output .= '<p class="n" style="text-align:right;">
			<span style="white-space:nowrap;"><input type="checkbox" name="paiement_id" value="2"'.$checked.'> <label for="paiement_id">paiement par chèque</label></span> &nbsp;&nbsp;&nbsp;&nbsp;';
			$output .= '<span style="white-space:nowrap;">Total €<input type="number" step="any" name="total" id="'.$p['id'].'total" class="currency prixVentePanier" placeholder="0,00" value="'.$p['total'].'"></span><a href="javascript:;" class="warning" title="le total n\'est pas égal à la somme des articles"'.$show_warn.'></a>
			</p>';

			$output .= '<div>
			'.$note_button;
			$output .= '<div class="moreOptions"><a href="javascript:;" class="dots">• • •</a><ul class="statutActions'.$last.'">';
			
			foreach($statut_vals as $st){
				if($st['nom'] !== 'disponible' && $st['nom'] !== 'vendu'){
					$output .= '<li><a href="javascript:;" data-statut="'.$st['id'].'">'.$st['nom'].'</a></li>';
				}
			}
			$output .= '</ul></div>';
			
			$output .= '<a href="javascript:;" class="button vente right ventePanierSubmit'.$disabled.'">Enregistrer la vente</a>';
			
			$output .= '<div class="clearBoth"></div>
			<div class="tAreaResizer" style="display:'.$note_display.';">'.$node.'<textarea class="notes" name="notes">'.$p['notes'].'</textarea></div>
			</div>';
			
			$output .= '</form>
			</div>';
			
		}else{
			$output .= '<div class="pCont" data-panierid="'.$p['id'].'" data-paniernom="'.$p['nom'].'">
			<div class="title"><b>'.$p['nom'].'</b></div>
			<span class="lowkey">- panier vide -</span> <a href="javascript:;" class="button remove right deletePanier">supprimer</a>';
			$output .= '<div class="clearBoth"></div>
			</div>';
		}
	}
	return $output;
}

// display sold paniers (in ventes.php)
function display_paniers($paniers){
	if( empty($paniers) ){
		return '<p class="lowkey"> - Aucune vente - </p>';
	}
	$output = $last = '';
	$i = 0;
	$p_count = count($paniers);
	$paiement_vals = get_table('paiement');
	$statut_vals = get_table('statut');

	foreach($paniers as $p){
		$i++;
		if($i == $p_count){
			$last = ' last'; // css class that will change the last drop-down ul
		}

		if( $articles = get_panier_articles($p['id']) ){

			$a_count = count($articles);
			$poids_total = $total = 0;
			$articles_output = '<div class="articlesCont">';
			
			foreach($articles as $a){

				$articles_output .= display_article_panier($a, $p['id'], 'ventesPaniersAjaxTarget');

				$poids_total += $a['poids'];
				$total += $a['prix'];
			}

			$articles_output .= '</div>';

			$total = number_format($total,2);

			if($a_count > 1){
				$article_header = '<div class="title"><b class="n">'.$p['nom'].'</b>';
				$article_header .= '&nbsp;&nbsp;&nbsp;'.$a_count.' articles, '. str_replace('.', ',', $poids_total).' kg<br>';
				$article_header .= '</div>';
			}else{
				$article_header = '';
			}

			if( empty($p['notes']) ){
				$node = '&nbsp';
				$note_display = 'none';
				$note_button = '<a href="javascript:;" class="note addNote left">note...</a>';
			}else{
				$node = $p['notes'];
				$note_display = 'block';
				$note_button = '<a href="javascript:;" class="note addNote left" style="display:none;">note...</a>';
			}

			// show warning if total != somme des articles
			if($total !== $p['total']){
				$show_warn = ' style="visibility:visible;"';
			}else{
				$show_warn = '';
			}

			$paiement_options = 'paiement:<select name="paiement_id">';
			foreach($paiement_vals as $pv){
				if($pv['id'] == $p['paiement_id']){
					$selected = ' selected';
				}else{
					$selected = '';
				}
				$paiement_options .= '<option value="'.$pv['id'].'"'.$selected.'>'.$pv['nom'].'</option>';
			}
			$paiement_options .= '</select>';


			// output starts
			$output .= '<div class="pCont" data-panierid="'.$p['id'].'" data-paniernom="'.$p['nom'].'">
			<form name="'.$p['id'].'" action="" method="get">
			<input type="hidden" name="id" value="'.$p['id'].'">
			<input type="hidden" name="savePanierSubmitted" value="savePanierSubmitted">';
			
			$output .= $article_header;

			$output .= $articles_output;
			
			$output .= '<p class="n" style="text-align:right;">';

			$output .= $paiement_options;

			$output .= '&nbsp;&nbsp;&nbsp;&nbsp;';
			$output .= '<span style="white-space:nowrap;">Total €<input type="number" step="any" name="total" id="'.$p['id'].'total" class="currency prixVentePanier" placeholder="0,00" value="'.$p['total'].'"></span><a href="javascript:;" class="warning" title="le total n\'est pas égal à la somme des articles"'.$show_warn.'></a>
			</p>';

			$output .= '<div>'.$note_button.'
			<div class="changes"><button type="submit" class="right save savePanierChanges">Enregistrer</button> <button type="reset" class="reset right">Annuler</button>
			<div class="clearBoth"></div>
			</div>
			<div class="clearBoth"></div>
			</div>';

			$output .= '</form>';
			$output .= '<div class="tAreaResizer" style="display:'.$note_display.'">'.$node.'<textarea class="notes" name="notes">'.$p['notes'].'</textarea></div>';

			$output .= '</div>';
			
		
		}else{ // pas d'articles dans ce panier, propose to delete empty panier
			
			$output .= '<div class="pCont" data-panierid="'.$p['id'].'" data-paniernom="'.$p['nom'].'">
			<div class="title"><b>'.$p['nom'].'</b></div>
			<span class="lowkey">- panier vide -</span> <a href="javascript:;" class="button remove right deletePanier">supprimer</a>';
			$output .= '<div class="clearBoth"></div>
			</div>';
		}
	}
	return $output;
}

// refresh ventes in caisse.php
function refresh_ventes($date){
	$ventes = get_ventes_total($date);
	return $ventes;
}
/*********** 3: ACTIVE FUNCTIONS (FUNCTIONS THAT CHANGE THE Content) ***************/

// SAVE PANIER CHANGES
function save_panier_changes($post){
	$result = '';
	$poids_total = 0;
	// remove unwanted posts
	foreach($post as $key => $value){
		if($key == 'savePanierSubmitted'){
			unset($post[$key]);

		// go through each article
		}elseif( is_array($value) ){
			$article_id = $key;
			$article_update = array();
			foreach($value as $k => $v){
				$article_update[$k] = $v;
				if($k == 'poids' && $article_update['statut_id'] !== '1'){ // ignore removed articles
					$poids_total += $v; // in passing, calculate new poids total
				}
			}

			if( isset($article_update['statut_id']) && $article_update['statut_id'] == '1'){
				$article_update['paniers_id'] = ''; // NULL IN SQL DB
				$article_update['date_vente'] = ''; // NULL
				$article_update['visible'] = '1';
			}

			$article_result = update_table('articles', $article_id, $article_update);
			
			// if error, add article_result to main function result
			if( substr($article_result, 0, 2) !== '1|' ){
				$result .= $article_result;
			}
		}
	}

	if( !isset($post['paiement_id']) || empty($post['paiement_id']) ){
		$paiement_id = '1';
	}else{
		$paiement_id = $post['paiement_id'];
	}
	$panier_update = array('paiement_id'=>$paiement_id, 'total'=>$post['total'], 'poids'=>$poids_total);
	// update panier
	$result .= update_table('paniers', $post['id'], $panier_update);

	return $result;
}

/* delete file, all its size versions
*/
function delete_file($delete_file){

	global $types; 
	$message = $error = '';
	$file_name = basename($delete_file);
	$ext = file_extension($file_name);
	
	// delete files
	if( file_exists(ROOT.$delete_file) ){
		if( preg_match($types['resizable_types'], $ext) ){ // resizable (images) files
			// get all sizes for deletion
			$xl_file = str_replace('/_S/', '/_XL/', $delete_file);
			$m_file = str_replace('/_S/', '/_M/', $delete_file);
			$l_file = str_replace('/_S/', '/_L/', $delete_file);
			
			if( unlink(ROOT.$delete_file) ){
				$message .= '1|Le fichier a été éffacé.';
				// delete all sizes
				unlink(ROOT.$xl_file);
				unlink(ROOT.$m_file);
				unlink(ROOT.$l_file);
			}else{
				$message .= '0|ERROR: The file could not be deleted.';
			}
			
		}else{ // not an image... no sizes.
			if( unlink(ROOT.$delete_file) ){
				$message .= '1|Le fichier a été éffacé.';
			}else{
				$message .= '0|ERREUR: Le fichier n\'a pas pu être éffacé.';
			}
		}
		
	}else{
		$message .= '0|ERROR: File does not exist: '.$delete_file;
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


/* upload file
*/
function upload_file($path, $replace=''){
	// initialize upload results
	$upload_message = $resize_result = $menu_update_result = '';
	global $types;

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
		$upload_message .= '0|Ce type de fichier n\'est pas autorisé: '.$ext.'<br>Le fichier n\'a pas été mis en ligne.';
	
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
					$upload_message .= '2|Impossible d\'effacer '.$replace;
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
					
					// could read image orientation...
					if($image_orientation !== false){

						// fix image orientation (and return true) or return error message
						$fix_orientation = fix_image_orientation($root_upload_dest, $image_orientation);
						if( $fix_orientation != true ){
							$upload_message .= $fix_orientation;
						}
					
					// cannot read image orientation. (commented out because somehow the message always display for jpg uploads?...)
					}/*else{
						
						$upload_message .= '2|Could not read image orientation for file: '.filename(basename($upload_dest), 'decode');
					}*/
				}
				
				// update width and height now! Or else resizing will be off...
				list($w, $h) = getimagesize($root_upload_dest);

				$resize_result .= resize_all($root_upload_dest, $w, $h);
				if(substr($resize_result, 0, 1) === '0'){
					$upload_message .= '0|'.$resize_result;
				}
			}

			$new_file_name = basename($upload_dest);
			//unlink(ROOT.$upload_dest); // get rid of original file in _XL dir (usually very big)
			$upload_message .= '1|Fichier mis en ligne: '.filename($new_file_name, 'decode');
			
		}else{
			$upload_message .= '0|Erreur: Assurez-vous que le poids du fichier ne dépasse pas '.MAX_UPLOAD_SIZE.'!';
		}
	}

	$upload_results = $upload_message;

	return $upload_results;
}


/* resize image to multiple sizes */
function resize_all($upload_dest, $w, $h){
	
	global $sizes; 

	$resize_result = '';
	
	// resize image to various sizes as specified by $sizes array
	foreach($sizes as $key => $val){
		
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

	global $types;

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