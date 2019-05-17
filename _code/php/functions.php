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

/***** STRING and file name MANIPULATIONS *******/

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


