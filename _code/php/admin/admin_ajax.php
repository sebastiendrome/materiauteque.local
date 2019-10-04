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
	$id = urldecode($_GET['id']);
	$col = urldecode($_GET['col']);
	$value = urldecode($_GET['value']);
	$update = array();
	// if many cols - vals are passed (separated with $separator), make array
	$separator = 'qQq';
	if( strstr($col, $separator) && strstr($value, $separator) ){
		$col_array = explode($separator, $col);
		$val_array = explode($separator, $value);
		$count_col = count($col_array);
		$count_val = count($val_array);
		if($count_col === $count_val){
			for($i=0; $i<$count_col; $i++){
				// format numbers 2,4 => 2.3
				if( is_numeric($val_array[$i]) ){
					$val_array[$i] = str_replace(',','.',$val_array[$i]);
				}
				$update[$col_array[$i]] = $val_array[$i];
			}
		}else{
			$result = '0|$_GET[updateTable] error: columns and values don\'t match!';
		}
	}else{
		// format numbers 2,4 => 2.3
		if( is_numeric($value) ){
			$value = str_replace(',','.',$value);
		}
		$update[$col] = $value;
	}
	
	$result = update_table($table, $id, $update);
}


// get children (for categories_id and matiere_id hierarchical SQL tables)
if( isset($_GET['get_children']) ){
	$result = '<option value="">Choisir...</option>';
	$table = urldecode($_GET['table']);
	$id_parent = urldecode($_GET['id_parent']);
	$children_array = get_children($table, $id_parent);
	foreach($children_array as $ca){
		$result .= '<option value="'.$ca['id'].'">'.$ca['nom'].'</option>';
	}
}

// save 2 articles from 1 (original and copy are js arrays)
// uses: scinde_article()
if( isset($_POST['original']) && isset($_POST['copy']) ){
	$result = scinde_article($_POST['original'], $_POST['copy']);
}

// create vrac vente
// uses: duplicate_vrac_article()
if( isset($_GET['vrac_vente']) ){
	$original_id = $_GET['original_id'];
	$old_poids = $_GET['old_poids'];
	$old_prix = $_GET['old_prix'];
	$result = duplicate_vrac_article($original_id, $old_poids, $old_prix);
}

// create panier
// uses: insert_new()
if( isset($_GET['create_panier']) ){
	$data['nom'] = $_GET['nom'];
	$data['paiement_id'] = $_GET['paiement_id'];
	$data['total'] = $_GET['prix'];
	$data['date_vente'] = time();
	$data['statut'] = $_GET['statut'];
	$data['poids'] = $_GET['poids'];

	$new_id = insert_new('paniers', $data);
	if( !$new_id ){
		$result = '0|erreur create_panier: '.$new_id;
	}else{
		$result = $new_id;
	}
}

// create article
// uses: insert_new()
if( isset($_GET['create_article']) ){
	// build $data array from key=val pairs, remove unwanted keys

	// debug
	//print_r($_GET);
	
	foreach($_GET as $key => $val){
		if($key !== 'create_article' && $key !== 'paiement_id' && $key !== 'panierNom'){
			if( is_numeric($val) ){
				$val = str_replace(',', '.', $val);
			}
			$data[$key] = $val;
		}
	}
	$new_id = insert_new('articles', $data);
	if( !$new_id ){
		$result = '0|erreur create_article new ID '.$new_id;
	}else{
		$result = $new_id;
	}
}

// update paniers modal
if( isset($_GET['updatePaniersModal']) ){
	$paniers = get_table('paniers', 'statut=0', 'date DESC');
	$result = display_paniers($paniers);
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
