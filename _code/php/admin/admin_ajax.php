<?php
require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
require(ROOT.'_code/php/admin/not_logged_in.php');
require(ROOT.'_code/php/admin/admin_functions.php');



// file upload
if( isset($_POST['contextNewFile']) ){
	$path = urldecode($_POST['path']);
	$replace = urldecode($_POST['replace']);
	$result = upload_file($path, $replace);
}

// save sql
if( isset($_GET['updateTable']) ){
	$table = urldecode($_GET['table']);
	$article_id = urldecode($_GET['id']);
	$col = urldecode($_GET['col']);
	$value = urldecode($_GET['value']);
	$update = array();
	if(is_numeric($value)){
		$value = str_replace(',','.',$value);
	}
	if($col == 'prix_vente'){
		$update['statut'] = 'vendu';
		$update['visible'] = '0';
		$update['date_vente'] = time();
	}
	$update[$col] = $value;
	$result = update_table($table, $article_id, $update);
}

// save 2 articles from 1 (original and copy are js arrays)
if( isset($_POST['original']) ){
	$original = $_POST['original'];
	$copy = $_POST['copy'];
	$result = scinde_article($original, $copy);
}

// show popup to edit table content
if( isset($_GET['editPopup']) ){
	$table = urldecode($_GET['table']);
	$article_id = urldecode($_GET['id']);
	$col = urldecode($_GET['col']);
	$result = popup_edit($table, $article_id, $col);
}


if( isset($result) ){
	echo $result;
}
