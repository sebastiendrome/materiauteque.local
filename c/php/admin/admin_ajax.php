<?php
/* RECEIVES AJAX CALLS FROM admin_j.js */
if(!defined("ROOT")){
	$code = basename( dirname(__FILE__, 3) );
	require preg_replace('/\/'.$code.'\/.*$/', '/'.$code.'/php/first_include.php', __FILE__);
}

// refresh ventes in caisse.php on window focus (called from caisse.php)
if( isset($_GET['refreshVentes']) ){
	$date = urldecode($_GET['date']);
	$result = refresh_ventes($date);
}
// refresh ventes CB in caisse.php on window focus (called from caisse.php)
if( isset($_GET['refreshVentesCb']) ){
	$date = urldecode($_GET['date']);
	$result = refresh_ventes_cb($date);
}

// file upload
if( isset($_POST['contextNewFile']) ){
	$path = urldecode($_POST['path']);
	$replace = urldecode($_POST['replace']);
	$result = upload_file($path, $replace);
}

// save panier changes
if( isset($_POST['savePanierSubmitted']) ){
	$post = $_POST;
	$result = save_panier_changes($post);
}

// display one panier
if( isset($_GET['display_panier']) ){
	$result = display_panier($_GET['id'], $_GET['context']);
}

// display one article
if( isset($_GET['display_article_panier']) ){
	$result = display_article_panier($_GET['articleId'], $_GET['panierId'], $_GET['context']);
}


// save sql table
if( isset($_GET['updateTable']) ){
	$table = urldecode($_GET['table']);
	$id = urldecode($_GET['id']);
	$col = urldecode($_GET['col']);
	//$value = urldecode($_GET['value']);
	$value = $_GET['value'];
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
			return false;
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

// delete img directories for articles within sold paniers
if( isset($_GET['removeDirs']) && !empty($_GET['removeDirs']) ){
	$dir_string = $_GET['removeDirs'];
	$dir_array = explode('qQq', $dir_string);
	foreach($dir_array as $dir){
		if( !empty($dir) && file_exists(ROOT.'_ressource_custom/uploads/'.$dir) ){
			rmdirr(ROOT.'_ressource_custom/uploads/'.$dir);
		}
	}
	$result = '1|image directories deleted: '.str_replace('qQq', ' ', $dir_string);
}


// get children (for categories_id and matiere_id hierarchical SQL tables)
if( isset($_GET['get_children']) ){
	$result = '<option value="">Choisir...</option>';
	$table = urldecode($_GET['table']);
	$id_parent = urldecode($_GET['id_parent']);
	$children_array = get_children($table, $id_parent);
	if( !empty($children_array) ){
		foreach($children_array as $ca){
			$result .= '<option value="'.$ca['id'].'">'.$ca['nom'].'</option>';
		}
	}
}

// save 2 articles from 1 (original and copy are js arrays)
// uses: scinde_article()
if( isset($_POST['original']) && isset($_POST['copy']) ){
	$result = scinde_article($_POST['original'], $_POST['copy']);
}

// create vrac vente
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
	$data['statut_id'] = $_GET['statut_id'];
	$data['poids'] = $_GET['poids'];

	$new_id = insert_new('paniers', $data);
	if( !$new_id ){
		$result = '0|erreur create_panier: '.$new_id;
	}else{
		$result = '1|'.$new_id;
	}
}

// delete panier
if( isset($_GET['deleteItem']) ){
	$id = urldecode($_GET['id']);
	$table = urldecode($_GET['table']);
	if( !empty($id) && is_numeric($id) && !empty($table) ){
		$result = delete_item($table, $id);
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
		$result = '1|'.$new_id;
	}
}

// update paniers modal
if( isset($_GET['updatePaniersModal']) ){
	$open_statut = name_to_id('disponible', 'statut');
	if( $paniers = get_table('paniers', 'statut_id='.$open_statut, 'date DESC') ){
		$paniers_count = count($paniers);
	}else{
		$paniers_count = '0';
	}
	$result = $paniers_count.'|'.display_paniers_en_cours($paniers);
}


if( isset($result) ){
	echo $result;
}
