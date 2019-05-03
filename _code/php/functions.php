<?php
/* return human file size to bytes */
function return_bytes($val){
    preg_match('/(?<value>\d+)(?<option>.?)/i', trim($val), $matches);
    $inc = array(
        'g' => 1073741824, // (1024 * 1024 * 1024)
        'm' => 1048576, // (1024 * 1024)
        'k' => 1024
    );

    $value = (int) $matches['value'];
    $key = strtolower(trim($matches['option']));
    if (isset($inc[$key])) {
        $value *= $inc[$key];
    }

    return $value;
}

// scan directory content, return array
function content_array($dir){
    $content_array = array();
    foreach(scandir($dir) as $file){
		// filter out system files
        if(substr($file,0,1) !== '.'){
            $content_array[$dir.'/'.$file] = $file;
        }
    }
	asort($content_array);
    $content_array = array_keys($content_array);
	rsort($content_array);
    return $content_array;
}

// show section content, or subsection content. Data comes from $menu_array, generated from reading 
// file menu.txt via above function
function display_content_array($path, $menu_array = ''){
	
	// initialize output
	$display = '';

	// get current directory (=section or sub-section)
	$dir = basename($path);
	
	// if current directory ($dir) != $path, we're dealing with a sub section, set $parent_dir
	if($dir != $path){
		$parent_dir = str_replace('/'.$dir, '', $path);
		// [experimental] user option to scroll orizontally (or show gallery with prev/next arrows) within sub-sections
		if(SUB_SECTION_SCROLL == 'o'){ // <-- defined in first_include
			$open_extra_container = 'set';
			$display .= PHP_EOL.'<!-- open orizontal scroll container -->'.PHP_EOL.'<div id="oriz">';
		}
	}

	
	// generate menu array from menu.txt file
	if( empty($menu_array) ){
		//echo '<h1>EMPTY MENU ARRAY</h1>';
		$menu_array = menu_file_to_array();
		
		// no parent dir, so attempt to match current directory (=section) to top level of menu_array (=menu_array[key])
		if( !isset($parent_dir) ){
			foreach($menu_array as $k => $v){
				//$display .=  $k.'<br>';
				if( preg_match('/^'.preg_quote(filename($dir, 'decode')).',/', $k) ){
					$parent = $k;
					// and generate sub-array of items accordingly
					$depth_array = $menu_array[$k];
					$split = explode(',', $k); // split two sides of the sub-section name, to get english and german versions
					break;
				}
			}
		// else, attempt to match current directory to sub level of menu_array(=menu_array[key][val])
		}else{ 
			foreach($menu_array as $k => $v){
				//$display .=  $k.'<br>';
				if( preg_match('/^'.preg_quote(filename($parent_dir, 'decode')).',/', $k) ){
					foreach($v as $vk => $vv){
						if( preg_match('/^'.preg_quote(filename($dir, 'decode')).',/', $vk) ){
							$parent = $k.'/'.$vk;
							// and generate sub-sub array of items accordingly
							$depth_array = $menu_array[$k][$vk];
							$split = explode(',', $vk); // split two sides of the sub-section name, to get english and german versions
							break;
						}
					}
				}
			}
			// language dependent title for this sub-section (get german title from $menu_array)
			if(LANG == 'en'){
				$subsection_title = filename(SECTION, 'decode');
			}elseif(LANG == 'de') {
				$subsection_title = trim($split[1]);
			}
			
	        $back_title = '<div class="backTitle">
			<ul><li>
			<a href="javascript:window.history.back();">&larr; '.BACK.'</a> | <u>'.$subsection_title.'</u></li></ul>
	        </div>
			<p class="title">&nbsp;</p>'.PHP_EOL;
			
		}
		
		// now we can recreate menu_array so it is the proper array of items depending on current directory depth.
		$menu_array = $depth_array;
	}
	
	
	// loop through menu_array to display the content
    foreach($menu_array as $key => $val){
        
		// filter out hidden files/folders (whose name starts with underscore)
        if( substr(basename($key),0,1) !== '_' && !empty($key) ){
            
			// open item container
			$display .= '<div class="divItem"><!-- start div item container -->'.PHP_EOL;

			if(isset($back_title)){
				$display .= $back_title;
			}
			
			// is item a file or a folder?
			if(!strstr($key, ',')){ // file
				
				$display_file = display_file($path, $key);
				
				// get text description english or deutsch version depending on LANG (cookie)
				$ext = file_extension($key);
				$txt_filename = preg_replace('/'.preg_quote($ext).'$/', '.txt', $key);
				$text_file = $path.'/'.LANG.'/'.$txt_filename;
				if( file_exists(ROOT.CONTENT.$text_file) ){
					$description = stripslashes( file_get_contents(ROOT.CONTENT.$text_file) );
				}else{
					$description = '';
				}
				
				// display file and description
				$display .= $display_file;
				$display .= '<p class="description">'.$description.'</p>';

				
			}else{ // folder = sub-section. show sub-section name and its first file.
				
				// langage dependent title for this subsection
				$split = explode(',', $key);
				if(LANG == 'en'){
					$sec_name = $split[0];
				}elseif(LANG == 'de'){
					$sec_name = trim($split[1]);
				}
				$sec_dir = filename($split[0], 'encode');
				
				// get the first file in subfolder to represent this subsection.
				// avoid repeating same file through loop, if subsequent passages don't finde a 1st file... 
				if(isset($first_file)){
					unset($first_file);
				}
				foreach($val as $k => $v){
					$first_file = $path.'/'.$sec_dir.'/'.SIZE.'/'.$k;
					break;
				}
				// display sub-section name and file only if a first file has been found
				if( isset($first_file) ){
					
					$display .= '<p class="title"><a href="/'.LANG_LINK.$path.'/'.$sec_dir.'/" class="aMore">'.$sec_name.' | &rarr; '.MORE.'</a></p>';
					
					// if optional 3rd var is TRUE, display file without enclosing <a> tag.
					$display_file = display_file($path.'/'.$sec_dir, $k, TRUE);
					
					$display .= '<a href="/'.LANG_LINK.$path.'/'.$sec_dir.'/" class="imgMore">'.$display_file.'</a>';
					$ext = file_extension($first_file);
					$txt_filename = preg_replace('/'.preg_quote($ext).'$/', '.txt', basename($first_file));
					$text_file = $path.'/'.$sec_dir.'/'.LANG.'/'.$txt_filename;
					if( file_exists(ROOT.CONTENT.$text_file) ){
						$description = stripslashes( file_get_contents(ROOT.CONTENT.$text_file) );
					}else{
						$description = '';
					}
					
					$display .= '<p class="description">'.$description.'</p>';
				}
			}
			
			$display .= '</div><!-- end div item container -->'.PHP_EOL; // close item container
		}
	}

	// [experimental] option to scroll orizontally sub-sections
	if(SUB_SECTION_SCROLL == 'o' && isset($open_extra_container) ){
		$display .= PHP_EOL.'</div><!-- close orizontal scroll container -->'.PHP_EOL;
	}

	return $display;
}

// display file
// if optional var $raw is TRUE, display file without enclosing <a> tag.
function display_file($path, $file_name, $raw = FALSE){
	
	$ext = file_extension($file_name);
	
	// get text description english and deutsch versions
	$txt_filename = preg_replace('/'.preg_quote($ext).'/', '.txt', $file_name);
	$text_file = $path.'/'.LANG.'/'.$txt_filename;
	
	if( file_exists(ROOT.CONTENT.$text_file) ){
		$description = stripslashes( file_get_contents(ROOT.CONTENT.$text_file) );
		$alt_content = substr( str_replace(array('\"', "\'"), array('&#34;', '&#39;'), strip_tags($description) ), 0, 30);
		$alt = ' alt="'.$alt_content.'"';
	}else{
		$description = '';
		$alt = ' alt="'.$file_name.'"';
	}
	
	// various ways to display file depending on extension
	// 1. resizable types (jpg, gif, png)
	if( preg_match($_POST['types']['resizable_types'], $ext) ){ // images
		$item = $path.'/'.SIZE.'/'.$file_name;
		list($w, $h) = getimagesize(ROOT.CONTENT.$item);
		// 'raw' or not: with surrounding zoom <a> link
		if($raw){
			$start_link = $end_link = '';
		}else{
			$start_link = '<a href="/_zoom.php?img='.urlencode($item).'&lang='.LANG.'" class="zoom">';
			$end_link = '</a>';
		}
		
		$display_file = $start_link.'<img src="/'.CONTENT.$item.'"'.$alt.' style="max-width:'.$w.'px">'.$end_link;
		
	}else{
		// if not an image, the file is in the _XL directory (no various sizes)
		$item = $path.'/_XL/'.$file_name;
		
		if( preg_match($_POST['types']['audio_types'], $ext) ){ // audio, show <audio>
			if($ext == '.mp3' || $ext == '.mpg'){
				$media_type = 'mpeg';
			}elseif($ext == '.m4a'){
				$media_type = 'mp4';
			}elseif($ext == '.oga'){
					$media_type = 'ogg';
			}else{
				$media_type = substr($ext, 1);
			}
			$display_file = PHP_EOL.'<audio controls>
			<source src="/'.CONTENT.$item.'" type="audio/'.$media_type.'">
			Sorry, your browser doesn\'t support HTML5 audio.<br>
			<a href="/'.CONTENT.$item.'" title="view audio file in a new window" target="_blank"><img src="/_code/images/'.substr($ext, 1).'.png"><br>
			Download the file.</a>
			</audio>'.PHP_EOL;

		}elseif( preg_match($_POST['types']['video_types'], $ext) ){ // text video files
			if($ext == '.m4v'){
				$media_type = 'mp4';
			}elseif($ext == '.ogv'){
				$media_type = 'ogg';
			}else{
				$media_type = substr($ext, 1);
			}
			$display_file = PHP_EOL.'<video controls>
			<source src="/'.CONTENT.$item.'" type="video/'.$media_type.'">
			Sorry, your browser doesn\'t support HTML5 video.<br>
			<a href="/'.CONTENT.$item.'" title="view video file in a new window" target="_blank"><img src="/_code/images/'.substr($ext, 1).'.png"><br>
			Download the file.</a>
			</video>'.PHP_EOL;

		
		}elseif( preg_match($_POST['types']['text_types'], $ext) ){ // text files (html or txt)
			$contents = file_get_contents(ROOT.CONTENT.$item);
			// detect if there is a language version to link to
			if( preg_match('/\[[a-zA-Z]*\.version\]/', $contents, $matches) ){
				$lang = str_replace(array('.version', '[', ']'), '', $matches[0]);
				$contents = str_replace($matches[0], '<a name="'.$lang.str_replace($ext,'',$file_name).'"></a>', $contents);
				$contents = '<a href="#'.$lang.str_replace($ext,'',$file_name).'" class="langLink">&darr; '.$lang.' version</a>'.$contents;
			}
			if($ext == '.txt'){ // txt
				$display_file = '<div class="txt">'.my_nl2br( strip_tags( $contents, ALLOWED_TAGS ) ).'</div>';
				
			}elseif($ext == '.html'){ // html
				if( preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $contents, $matches) ){
					$display_file = '<div class="txt">'.$matches[1].'</div>';
				}else{
					$display_file = '<div class="txt">'.$contents.'</div>';
				}
			}

		}elseif($ext == '.emb'){ // embeded media
			$contents = file_get_contents(ROOT.CONTENT.$item);
			$display_file = $contents;
			
		}else{
			$display_file = '<a href="/'.CONTENT.$item.'" title="view file in a new window" target="_blank"><img src="/_code/images/'.substr($ext,1).'.png" class="icon"></a>';
		}
	}
	if( !isset($display_file) || empty($display_file) ){
		$display_file = '<p class="error">Cannot display '.$path.$file_name.'</p>';
	}
	return $display_file;
}

// get directory name from bilingual section name ("english name, deutsch Name" => "english_name")
function dir_from_section_name($menu_section) {
	$split = explode(',', $menu_section);
	$dir_name = filename($split[0], 'encode');
	return $dir_name;
}

// check if a folder is empty or not. Returns "true" if it is empty
function is_empty_folder($dir){
	if(is_dir($dir)){
		$dir_contents = glob("$dir/*");
		foreach($dir_contents as $s){
			if(!preg_match('/^\./',basename($s))){
				$filtered[] = $s;
			}
		}
		if (count($filtered) == 0){
			return true;
		}else{
			return false;
		}
	}elseif(is_file($dir)){
		$f_size = filesize($dir);
		if($f_size < 5){
			return true;
		}else{
			return false;
		}
	}
}


/***** STRING and file name MANIPULATIONS *******/

// CUSTOM nl2br
function my_nl2br($content){
	$content = str_replace(array("\r\n","\r","\n"),'<br>',$content);
	return $content;
}
// CUSTOM br2nl
function my_br2nl($content){
	$content = str_replace('<br>', "\n", $content);
	return $content;
}

// ENCODE STRING TO SAFE FILENAME
function filename($string, $de_encode){
	// <>:"/\|?*
	$char = array
	(
		' ', '/', '\\', '(', ')', '[', ']', '{', '}', '|', '<', '>', '*', '#', '%', '&', '$', '@', '+', '!', '?', ',', '.', ';', ':', '"', "'", '‘', '’', '“', '”', '‛', '‟', '′', '″', '©', 'ç', 'à', 'á', 'â', 'ã', 'ä', 'Ä', 'Ö', 'Ü', 'ß', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ĩ', 'ï', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'ü', 'û'
	);
	$rep =  array
	(
		'qZ','zFSz','zBSz','zOPz','zCPz','zOBz','zCBz','zOAz','zCAz','zVLz','zPz','zNz','zSRz','zPDz','zPTz','zAz','zDRz','zATz','zPSz','zEPz','zQz','zCz','zDz','zSCz','zCNz','zQTz','zSQz','zSQDz','zSQUz','zQDz','zQUz','zSQFz','zQFz','zAFz','zDAFz','zCYz','qCCq','qAGq','qAAq','qACq','qATq','qADq','QADQ','QODQ','QUDQ','qSSq','qEGq','qEAq','qECq','qEDq','qIGq','qIAq','qICq','qITq','qIDq','qOGq','qOAq','qOCq','qOTq','qODq','qUGq','qUAq','qUCq','qUDq'
	);
	if($de_encode == 'encode'){
		foreach($char as $key => $value){
			$string = str_replace($value, $rep[$key], $string);
		}
	}elseif($de_encode == 'decode'){
		foreach($rep as $key => $value){
			$string = str_replace($value, $char[$key], $string);
		}
	}
	return $string;
}

// get file name without extension
function file_name_no_ext($file_name){
	if( strstr($file_name, '/') ){
		$file_name = basename($file_name);
	}
	$file_name_no_ext = preg_replace('/\.[^\.]*$/', '', $file_name);
	return $file_name_no_ext;
}

// get file extension from file name (including the dot: ".jpg")
function file_extension($file_name){
	preg_match('/\.[^\.]*$/', $file_name, $matches);
	if( !empty($matches) ){
		return $matches[0];
	}else{
		return false;
	}
}

/* Replace special characters with their equivalents */
function normalize( $data ){
    $invalid_values = array(
    	'/ä|æ|ǽ/' => 'ae',
    	'/ö|œ/' => 'oe',
    	'/ü/' => 'ue',
    	'/Ä/' => 'Ae',
    	'/Ü/' => 'Ue',
    	'/Ö/' => 'Oe',
    	'/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
    	'/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/' => 'a',
    	'/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
    	'/ç|ć|ĉ|ċ|č/' => 'c',
    	'/Ð|Ď|Đ/' => 'D',
    	'/ð|ď|đ/' => 'd',
    	'/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/' => 'E',
    	'/è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',
    	'/Ĝ|Ğ|Ġ|Ģ/' => 'G',
    	'/ĝ|ğ|ġ|ģ/' => 'g',
    	'/Ĥ|Ħ/' => 'H',
    	'/ĥ|ħ/' => 'h',
    	'/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/' => 'I',
    	'/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/' => 'i',
    	'/Ĵ/' => 'J',
    	'/ĵ/' => 'j',
    	'/Ķ/' => 'K',
    	'/ķ/' => 'k',
    	'/Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
    	'/ĺ|ļ|ľ|ŀ|ł/' => 'l',
    	'/Ñ|Ń|Ņ|Ň/' => 'N',
    	'/ñ|ń|ņ|ň|ŉ/' => 'n',
    	'/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
    	'/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/' => 'o',
    	'/Ŕ|Ŗ|Ř/' => 'R',
    	'/ŕ|ŗ|ř/' => 'r',
    	'/Ś|Ŝ|Ş|Š/' => 'S',
    	'/ś|ŝ|ş|š|ſ/' => 's',
    	'/Ţ|Ť|Ŧ/' => 'T',
    	'/ţ|ť|ŧ/' => 't',
    	'/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
    	'/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',
    	'/Ý|Ÿ|Ŷ/' => 'Y',
    	'/ý|ÿ|ŷ/' => 'y',
    	'/Ŵ/' => 'W',
    	'/ŵ/' => 'w',
    	'/Ź|Ż|Ž/' => 'Z',
    	'/ź|ż|ž/' => 'z',
    	'/Æ|Ǽ/' => 'AE',
    	'/ß/'=> 'ss',
    	'/Ĳ/' => 'IJ',
    	'/ĳ/' => 'ij',
    	'/Œ/' => 'OE',
    	'/ƒ/' => 'f'
    );
    $data_out = preg_replace(array_keys($invalid_values), array_values($invalid_values), $data);
    return $data_out;
}

// CLEAN INPUTS AGAINST CROSS SITE SCRIPTING
function cleanXXS($input){
	$clean = str_replace(array("'",'"',"<",">","?","&amp;",'&lt;','&gt;',"&",";"), "", $input);
	return $clean;
}

function clean( $string ){
    return stripslashes( $string );
}

// Sanitize user data
function filter($data){
	global $db;
    if( !is_array($data) ){
        $data = strip_tags($data);
        if( get_magic_quotes_gpc() ){
            $data = stripslashes($data);
        }
    	$data = mysqli_real_escape_string($db, $data);
    }else{
        //Self call function to sanitize array data
        $data = array_map("filter", $data);
    }
	return $data;
}


