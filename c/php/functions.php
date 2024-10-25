<?php

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
		if (substr($entry, 0, 1) == '.') {// Skip pointers
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

/***** STRING and file name MANIPULATIONS *******/

// CLEAN INPUTS AGAINST CROSS SITE SCRIPTING
function cleanXXS($input){
	$clean = str_replace(array("'",'"',"<",">","?","&amp;",'&lt;','&gt;',"&",";"), "", $input);
	return $clean;
}

function clean( $string ){
	return stripslashes( $string );
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

// Sanitize user data
function filter($data){
	global $db;
	if( !is_array($data) ){
		$data = strip_tags($data);
		/*if( get_magic_quotes_gpc() ){
			$data = stripslashes($data);
		}*/
		$data = mysqli_real_escape_string($db, $data);
	}else{
		//Self call function to sanitize array data
		$data = array_map("filter", $data);
	}
	return $data;
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
