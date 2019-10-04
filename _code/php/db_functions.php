<?php
/******************************** COMMON FUNCTIONS DB (SQL) RELATED *********************************/

/** INDEX:
 * 1: ADMIN
	* 1. getters
	* 2. updaters
	* 3. inserters
	* 4. deleters
	* 5. finders
	* 6. other
* 2: PUBLIC
	* 1. show articles
 */




/**************************** 1.ADMIN *****************************/


/**** 1. GETTERS ****/

// GET TABLE DATA (generic)
function get_table($table, $where = '', $order = ''){
	global $db;
	$q = "SELECT * FROM $table";
	if( !empty($where) ){
		$q .= " WHERE ".$where;
	}
	if( !empty($order) ){
		$q .= " ORDER BY ".$order;
	}

	//echo '<pre>'.__FUNCTION__.PHP_EOL.$q.'</pre>';

	$query = mysqli_query($db, $q) or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);
	while($row = mysqli_fetch_assoc($query)){
		$data[] = $row;
	}
	if(!empty($data)){
		return($data);
	}else{
		return FALSE;
	}
}

/** Return multi-dimentional array from id_parent/Child hierarchy table
 * For 'categories' and 'matieres' tables
 *  */ 
function get_hierarchy_array($table, $visible_only = TRUE){
	global $db;
	$q = "SELECT * FROM $table";
	if($visible_only == TRUE){
		$q .= " WHERE visible = 1";
	}

	// debug
	//echo '<pre>'.__FUNCTION__.PHP_EOL.$q.'</pre>';

	$query = mysqli_query($db, $q) or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);
	while($row = mysqli_fetch_assoc($query)){
		if($row['id_parent'] == 0){
			$cats_array[$row['id']] = $row;
		}else{
			$cats_array[$row['id_parent']]['children'][] = $row;
		}
	}
	if(!empty($cats_array)){
		return($cats_array);
	}else{
		return FALSE;
	}
}

// get parent (main) items in hierarchy table (categories or matieres)
function get_parents($table, $visible_only = TRUE){
	global $db;
	$q = "SELECT * FROM $table WHERE id_parent = 0";
	if($visible_only == TRUE){
		$q .= " AND visible = 1";
	}
	// debug
	//echo '<pre>'.__FUNCTION__.PHP_EOL.$q.'</pre>';

	$query = mysqli_query($db, $q) or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);
	while($row = mysqli_fetch_assoc($query)){
		$parents_array[] = $row;
	}
	if(!empty($parents_array)){
		return($parents_array);
	}else{
		return FALSE;
	}
}

// get children (sub) items in hierarchy table (categories or matieres)
function get_children($table, $id_parent){
	global $db;
	$q = "SELECT * FROM $table WHERE id_parent = $id_parent";
	// debug
	//echo '<pre>'.__FUNCTION__.PHP_EOL.$q.'</pre>';

	$query = mysqli_query($db, $q) or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);
	while($row = mysqli_fetch_assoc($query)){
		$children_array[] = $row;
	}
	if(!empty($children_array)){
		return($children_array);
	}else{
		return FALSE;
	}
}

// get panier articles
function get_panier_articles($panier_id){
	global $db;
	$q = "SELECT id, titre, poids FROM articles WHERE panier_id = '$panier_id' ORDER BY date_vente DESC";
	//debug
	//echo '<pre>'.$q.'</pre>';
	$query = mysqli_query( $db, $q) or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
	while($row = mysqli_fetch_assoc($query)){
		$articles[] = $row;
	}
	if(!empty($articles)){
		return($articles);
	}else{
		return FALSE;
	}
}

// get all item data
function get_item_data($article_id, $fields = '*'){
	global $db;
	if( is_array($fields) ){
		if( !in_array('id', $fields) ){
			array_unshift($fields , 'id');
		}
		$fields_string = implode(", ", $fields);
		$fields = $fields_string;
	}
	$q = "SELECT $fields FROM articles WHERE id = '$article_id'";
	$query = mysqli_query( $db, $q) or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
	$item = mysqli_fetch_assoc( $query );
	return $item;
}

/** return array of articles data, 
 * optional filters: visible, categories_id. Sort as needed */
function get_items_data($fields = '*', $visible = 'all', $vendu = FALSE, $categories_id = 'all', $sort = 'date DESC', $limit = NULL, $offset = NULL){
	global $db;
	if( is_array($fields) ){
		if( !in_array('id', $fields) ){
			array_unshift($fields , 'id');
		}
		$fields_string = implode(", ", $fields);
		$fields = $fields_string;
	}
	
	$q = "SELECT ".$fields." FROM articles";

	// VISIBLE, STATUT(vendu) and CATEGORY filters
	$filter = array();
	if($visible !== 'all'){
		$filter[] = " visible = ".$visible;
	}
	if(!$vendu){
		$filter[] = " statut_id < 4";
	}
	if($categories_id !== 'all'){
		$filter[] = " categories_id = ".$categories_id;
	}
	if( !empty($filter) ){
		$q .= " WHERE";
		$q .= implode(" AND ", $filter);
	}

	// SORT (ORDER)
	$q .= " ORDER BY ".$sort;

	// LIMIT and OFFSET
	if( $limit !== NULL ){
		$q .= " LIMIT ".$limit;
	}
	if( $offset !== NULL ){
		$q .= " OFFSET ".$offset;
	}

	//echo '<pre>'.$q.'</pre><br>';
	$query = mysqli_query($db, $q) or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
	while( $row = mysqli_fetch_assoc($query) ){
		$items[] = $row;
	}
	if(isset($items) && !empty($items)){
		return $items;
	}else{
		return false;
	}
}

// get name from id
function id_to_name($id, $table){
	global $db;
	// exception for sous_categories and sous_matieres (don't exist as real SQL tables)
	if($table == 'sous_categories' || $table == 'sous_matieres'){
		$table = substr($table, 5); // remove 'sous_' prefix
	}
	$q = "SELECT nom FROM $table WHERE id = '$id'";
	// debug
	//echo '<pre>'.$q.'</pre>';
	$query = mysqli_query($db, $q) or log_db_errors( mysqli_error($db), 'Query: '.$q.' Function: '.__FUNCTION__ );
	$name = mysqli_fetch_row($query);
	return $name[0];
}

// get id from name
function name_to_id($name, $table){
	global $db;
	$q = "SELECT id FROM $table WHERE nom = '$name'";
	//echo $q.'<br>';
	$query = mysqli_query($db, $q) or log_db_errors( mysqli_error($db), 'Query: '.$q.' Function: '.__FUNCTION__ );
	$id = mysqli_fetch_row($query);
	return $id[0];
}









/**** 2. UPDATERS ****/

// UPDATE TABLE DATA (generic)
function update_table($table, $id, $update){
	global $db;
	$q = "UPDATE $table SET ";
	$q .= update_sql($update);
	$q .= " WHERE id = $id";
	
	// debug
	//echo '<pre>'.__FUNCTION__.PHP_EOL.$q.'</pre>';
	
	if( $query = mysqli_query($db, $q) ){
		$result = '1|Modifications enregistrées.';
	}else{
		$result = '0|Erreur: '.mysqli_error($db);
		log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
	}
	return $result;
}







/**** 3. INSERTERS ****/


function insert_new($table, $item_data){
	global $db;

	$array_keys = $array_values = array();

	// add date (php timestamp) for certain tables
	if($table == 'adhesions' || $table == 'articles' || $table == 'passages' || $table == 'ventes' || $table == 'paniers'){
		$array_keys[] = 'date';
		$array_values[] = time();
	}

	foreach($item_data as $k => $v){
		if( ( !empty($v) && !is_numeric($v) ) || $v === '0' ){ // string values that are not empty or '0'
			$array_keys[] = strtolower($k);
			$array_values[] = "'".filter($v)."'";
		}elseif( !empty($v) || $v === 0 ){		// numeric values that are not empty or = 0
			$array_keys[] = strtolower($k);
			$array_values[] = $v;
		}										// other fields will be skiped (and assumed to be 'allowed NULL' in db table)
	}
	
	$string_keys = implode(',', $array_keys);
	$string_values = implode(',', $array_values);

	$q = "INSERT INTO $table (".$string_keys.") VALUES (".$string_values.")";
	
	// debug
	//echo '<pre>'.__FUNCTION__.PHP_EOL.$q.'</pre>';

	if( mysqli_query($db, $q) ){
		// create images directory if article
		$new_id = mysqli_insert_id($db);
		if($table == 'articles'){
			copyr(ROOT.'templates/img_dir', ROOT.'uploads/'.$new_id);
		}
		return $new_id;
	}else{
		log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
		return false;
	}
}


/* scinde un article en deux (save first one, create second one)
$original and $copy are arrays containing each article data */
function scinde_article($original, $copy){

	// original reste, copy est vendu
	// reformat both arrays
	foreach($original as $o){
		$o_array[$o['name']] = $o['value'];
	}
	foreach($copy as $c){
		$c_array[$c['name']] = $c['value'];
	}
	// make sure we don't pass the article id to insert_new function
	if( isset($c_array['id']) ){
		unset($c_array['id']);
	}
	// attempt to create new article
	if( $new_id = insert_new('articles', $c_array) ){
		$result = '1|Nouvel article crée, ID:'.$new_id;
	}else{
		$result = '0|Erreur: Le nouvel article n\'a pas pu être créé!';
	}
	// save original article
	$result .= '<br>'.update_table('articles', $o_array['id'], $o_array);
	return $result;
}

/* scinde un article vrac en deux pour la vente (from prixVenteModal, #directeVenteSubmit on click) */
function duplicate_vrac_article($original_id, $old_poids, $old_prix){
	$item_data = get_item_data($original_id);
	unset($item_data['id']);
	unset($item_data['date']);
	$item_data['prix'] = $old_prix;
	$item_data['poids'] = $old_poids;
	if( $new_id = insert_new('articles', $item_data) ){
		$result = '1|Vrac article copié, ID:'.$new_id;
	}else{
		$result = '0|Erreur: L\'article vrac n\'a pas pu être dupliqué!';
	}
	return $result;
}




/**** 4. DELETERS ****/

function delete_item($table, $article_id){
	global $db;
	
	// delete database col
	if( $delete = mysqli_query($db, "DELETE FROM $table WHERE id = '$article_id'") ){
		$result = '1|Élément #'.$article_id.' éffacé du tableau <b>'.$table.'</b>';
	}else{
		$result = '0|L\'élément #'.$article_id.' n\'a pas pu être éffacé du tableau <b>'.$table.'</b>';
	}
	// delete image directory
	$dir = ROOT.'uploads/'.$article_id;
	if( is_dir($dir) ){
		rmdirr($dir);
	}
	return $result;
}








/**** 5. FINDERS ****/

/** 
 * find article(s) from array of key=val pairs such as 
 * $key_val_pairs = array('categories_id' => 'bois', 'matieres_id' => 1, 'descriptif' => 'hello');
 * all articles matching one or more pairs are returned, sorted from best to worst match 
 * returns: array[article id][sort value]
 * */
function find_articles($key_val_pairs, $include_vendus = FALSE){
	global $db;
	$id_matches = array();
	foreach($key_val_pairs as $key => $value){
		$value = filter($value);
		// for 'descriptif' and 'observations' evaluate match with LIKE
		if($key == 'descriptif' || $key == 'observations' || $key == 'titre'){
			$q = "SELECT id FROM articles WHERE $key LIKE '%$value%'";
		// for categories name (nom) convert to id
		/*
		}elseif( $key == 'categories_id' || $key == 'matieres_id' && !is_numeric($value)  ){
			$value = name_to_id($value, substr($key, 0, -3) );
			$q = "SELECT id FROM articles WHERE $key = '$value'";*/
		// start/end date: gather both start and end, if set, then compare dates at end of foreach loop
		}elseif( $key == 'date'  ){
			// if both start and end are empty, skip this iteration of the foreach loop (continue)
			if( empty($value['start']) && empty($value['end']) ){
				continue;
			}elseif( empty($value['start']) ){
				$value['start'] = '01-01-1970';
			}elseif( empty($value['end']) ){
				$value['end'] = date('d-m-Y');
			}
			$start_date = $value['start'];
			$end_date = $value['end'];
			/*
			echo '<pre>'.__FUNCTION__.'
			$start_date:	'.$start_date.'
			$end_date:	'.$end_date.'</pre>';
			*/
			if( $start_date = valid_date($start_date) ){
				$year_start = substr($start_date, -4);
				$month_start = substr($start_date, 3, 2);
				$day_start = substr($start_date, 0, 2);
				$time_start = mktime(0, 0, 0, $month_start, $day_start, $year_start);
			}
			if( $end_date = valid_date($end_date) ){
				$year_end = substr($end_date, -4);
				$month_end = substr($end_date, 3, 2);
				$day_end = substr($end_date, 0, 2);
				$time_end = mktime(0, 0, 86399, $month_end, $day_end, $year_end); // add almost 24 hours (86400)
			}
			if( isset($time_start) && isset($time_end) ){
				// get DB orders within date range
				$q = "SELECT id FROM articles WHERE date >= $time_start AND date <= $time_end";
			}else{
				echo '0|Date mal formée! Le correct format est:  31-05-2018';
			}
			
		// default
		}else{
			$q = "SELECT id FROM articles WHERE $key = '$value'";
		}

		// include statut=4 (=vendu) or not
		if(!$include_vendus){
			$q .= " AND  statut_id < 4";
		}

		// assign values to search criterias
		if($key == 'titre' || $key == 'descriptif' || $key == 'observations'){
			$value = 4;
		}elseif($key == 'visible'){
			$value = 1;
		}else{
			$value = 2;
		}
		
		if( isset($q) ){

			// debug
			//echo '<pre>'.__FILE__.', Fonction '.__FUNCTION__.'(), line '.__LINE__.PHP_EOL.$q.'</pre>';

			$query = mysqli_query($db, $q) or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
			while( $row = mysqli_fetch_row($query) ){
				$id = $row[0];
				//echo 'ID: '.$id.'<BR>';
				if( !array_key_exists($id, $id_matches) ){
					$id_matches[$id] = $value;
				}else{
					$id_matches[$id] = $id_matches[$id] + $value; // increament value for each new match, then arsort() will push best match in first position
				}
			}
		}
	}
	if( !empty($id_matches) ){
		arsort($id_matches);
		return $id_matches;
	}else{
		return false;
	}
}

/**
 * search articles. Search by keywords, category, and with/out invisible and sold items.
 * returns: array[][id]
 */
function search($keywords = '', $category = '', /*$sous_category = '', */$visible = TRUE, $vendus = FALSE){
	global $db;
	
	$items = array();
	$filters_array = array();
	$filters_string = '';

	// if some keywords end with 's' (plural), add their singular version to the keywords, and if not add plural version
	$keywords_array = explode(' ', $keywords);
	foreach($keywords_array as $word){
		$word = trim($word);
		if(strlen($word)>3 && substr(strtolower($word), -1) == 's'){
			$keywords .= ' '.substr($word, 0, -1);
		}elseif(strlen($word)>2 && substr(strtolower($word), -1) !== 's'){
			$keywords .= ' '.$word.'s';
		}
	}
	
	// START SQL QUERY
	$q = "SELECT id, date FROM `articles` WHERE ";

	// visible = TRUE > show only items where visible=1
	if( $visible ){
		$filters_array['visible'] = "`visible` = 1";
	}
	// vendus = FALSE > show only items where statut_id is NOT 4 (=vendu)
	if( !$vendus ){
		$filters_array['vendus'] = "`statut_id` < 4";
	}
	// if a category is specified show only this category
	if($category !== ''){
		$filters_array['category'] = "`categories_id` = '".$category."'";
	}
	/*
	// if a sous_category is specified show only this category
	if($sous_category !== ''){
		$filters_array['category'] = "(".$filters_array['category']." OR `categories_id` = '".$sous_category."')";
	}
	*/
	// implode filters array into string separated with " AND " 
	if( !empty($filters_array) ){
		$filters_string = implode(" AND ", $filters_array);
	}
	
	// append filters string to SQL QUERY
	$q .= $filters_string;

	// add trailing AND between filters and keywords, if both are used
	if( !empty($filters_string) && !empty($keywords) ){
		$q .= " AND ";
	}

	// complete SQL QUERY with keywords search if keywords are used
	if( !empty($keywords) ){
		$q .= "MATCH(`titre`, `descriptif`) AGAINST ('".$keywords."' IN NATURAL LANGUAGE MODE)";
	}else{
	// if not, sort results by date (from most recent)
		$q .= " ORDER BY date DESC";
	}

	// debug
	//echo '<pre>'.__FILE__.', Fonction '.__FUNCTION__.'(), line '.__LINE__.PHP_EOL.$q.'</pre>';
	
	$query = mysqli_query($db, $q) or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
	if($query !== FALSE){
		while( $row = mysqli_fetch_array($query) ){
			$id = $row[0];
			$items[] = $id;
		}
	}

	// debug
	//echo '<pre>'.__FILE__.', Fonction '.__FUNCTION__.'(), line '.__LINE__.PHP_EOL; print_r($items); echo '</pre>';

	if( !empty($items) ){
		return $items;
	}else{
		return false;
	}
}




/**** 6. OTHER (ADMIN)****/


/* generate SQL UPDATE query string, from $update (string or array) */
function update_sql($update){

	$q = '';
	if( is_array($update) ){
		$count = count($update);
		$i = 1;

		foreach($update as $k => $v){
			// skip images
			if($k == 'images'){
				$i++;
				continue;
			}
			// change EU number format to US (SQL) format (replace commas with dots, remove dots)
			if( preg_match('/^\d*\,?\d+$/', $v) ){
				$v = str_replace( array('.',','), array('','.'), $v );
				$v = (float)$v;
			}
			/*
			// filter strings, not numbers
			if( is_string($v) ){
				$q .= "$k = '".filter($v)."'";
			}else{
				$q .= "$k = ".$v;
			}
			*/
			// filter strings, not number - and set empty values to NULL
			if($v !== ''){
				if( is_numeric($v) ){
					$q .= "$k = ".$v;
				}else{
					$q .= "$k = '".filter($v)."'";
				}
				
			}else{
				$q .= "$k = NULL";
			}


			// add coma separator between each key=value, but not for the last one
			if($i < $count){
				$q .= ", ";
			}
			$i++;
		}
	}else{
		$q .= $update;
	}
	
	//echo '<pre>'.__FUNCTION__.PHP_EOL.$q.'</pre>';
	
	return $q;
}

/* format SQL values for presentation, in items tables for admin (function below) */
function present($k, $v){
	
	// show select input for statut_id
	if($k == 'statut_id'){

		$statut_array = get_table('statut'); // get contents of statut table ('id, nom)
		$options = '';

		foreach($statut_array as $st){ // loop through statut_array to output the options
			// exclude 'vendu' statut
			if($st['nom'] !== 'vendu'){
				if($st['id'] == $v){
					$selected = ' selected';
				}else{
					$selected = '';
				}
				$options .= '<option value="'.$st['id'].'"'.$selected.'>'.$st['nom'].'</option>';
			}
		}
		$v = '<select name="statut_id" style="min-width:50px;" class="ajax" title="Modifier le statut">'.$options.'</select>';

	// show name of keys_id
	}elseif( substr($k, -3) == '_id' && $v !== NULL){
		// exception for sous_categories and sous_matieres (don't exist as real SQL tables)
		if($k == 'sous_categories_id' || $k == 'sous_matieres_id'){
			$k = substr($k, 5); // remove 'sous_' prefix
		}
		$name = id_to_name( $v, substr($k, 0, -3) );
		$v = $name;

	// show select input for visible
	}elseif($k == 'visible'){
		$selected_1 = $selected_2 = '';
		if($v == '1'){
			$selected_1 = ' selected';
		}else{
			$selected_2 = ' selected';
		}
		$v = '<select name="visible" style="min-width:50px;" class="ajax" title="Modifier la visibilité">
		<option value="1"'.$selected_1.'>oui</option>
		<option value="0"'.$selected_2.'>non</option>
		</select>';

	// show select input for vrac
	}elseif($k == 'vrac'){
		$selected_1 = $selected_2 = '';
		if($v == '1'){
			$selected_1 = ' selected';
		}else{
			$selected_2 = ' selected';
		}
		$v = '<select name="vrac" style="min-width:50px;" class="ajax" title="Choisir...">
		<option value="1"'.$selected_1.'>oui</option>
		<option value="0"'.$selected_2.'>non</option>
		</select>';

	// format numbers to EU format
	}elseif($k == 'prix' || $k == 'poids' || $k == 'prix_vente' ){
		$v = str_replace(array(',','.'), array('.',','), $v);

	// format php timestamp to readbale date in EU format
	}elseif($k == 'date'){
		$v = date('d-m-Y', $v);

	// show short descriptif, long on mouse enter
	}elseif( ($k == 'descriptif' || $k == 'observations') && !empty($v) ){
		$less = mb_substr($v, 0, 15);
		if($less !== $v){
			$v = '<div class="short">'.$less.'…<div class="long">'.$v.'</div></div>';
		}else{
			$v = $less;
		}
	}

	// deal with arrays (notably for images array in articles data)
	if( is_array($v) ){
		$v = count($v);
	}
	return $v;
}


// get article images
function get_article_images($article_id = '', $size = '_M', $path = 'uploads'){
	$images_array = array();
	if( preg_match('/\/(_L|_M|_XL|_S)\//', $path) ){
		$size = '';
	}
	$img_dir = $path.'/'.$article_id.'/'.$size.'/';
	$scan_dir = preg_replace('/\/+/', '/', ROOT.$img_dir);
	// make sure the directory exists
	if( !is_dir($scan_dir) ){
		copyr(ROOT.'templates/img_dir', ROOT.'uploads/'.$article_id);
	}
	$img_dir = preg_replace('/\/+/', '/', $img_dir); // make sure there are no duplicate slashes
	$files = scandir($scan_dir);
	foreach($files as $f){
		if(substr($f, 0, 1) !== '.'){
			$images_array[] = $img_dir.$f;
		}
	}
	return $images_array;
}


/* echo interactive items table (uses present() function above) for admin */
function items_table_output($result_array, $limit = NULL, $offset = 0){

	if( empty($result_array) ){
		return false;
	}
	
	if($limit === NULL){
		$limit = count($result_array);
	}
	$start = $limit*$offset;
	$end = $start+$limit;
	
	// debug
	//echo '<pre>'.__FUNCTION__.PHP_EOL;print_r($result_array);echo '</pre>';
	
	$editable = array('categories_id', 'matieres_id', 'titre', 'descriptif', 'observations', 'prix', 'poids', 'statut_id', 'visible');
	$exclude = array('id', 'date', 'date_vente', 'vrac', 'etiquette', 'panier_id', 'visible', 'paiement_id');

	$output = '';
	$i = $n = 0;
	$output .= '<table class="data" data-id="articles">'.PHP_EOL;
	
	foreach($result_array as $key => $value){

		if($n >= $start && $n < $end){
			$article_id = $value['id'];

			// get images
			$images_array = get_article_images($article_id, '_S');
			//$img_count = count($images_array);
			
			// first iteration, show top row = key name
			if($i == 0){
				$output .= '<thead>';
				$output .= '<tr class="topRow">';
				// th for images
				$output .= '<th>Image</th>';
				foreach($value as $k => $v){
					if( $k == 'date'){
						$class = ' class="headerSortUp"';
					}else{
						$class = '';
					}
					if( !in_array($k, $exclude) ){
						$output .= '<th'.$class.'>'.str_replace('_id', '', $k).'</th>';
					}
				}
				// th for 'modifier' button
				//$output .= '<th style="background-image:none; padding-left:5px;">Modifier</th>';
				// th for 'vendre' button
				$output .= '<th style="background-image:none; padding-left:5px;">Vendre</th>';

				$output .= '</tr>';
				$output .= '</thead><tbody>'; 
			}

			if($i % 2 == 0){
				$tr_class = 'pair';
			}else{
				$tr_class = 'impair';
			}
			// show results
			$output .= '<tr data-id="'.$article_id.'" class="'.$tr_class.'" title="Modifier cet article">';


			// images
			$output .= '<td>';
			$output .= '<a href="javascript:;" title="Modifier ou ajouter une image" class="showModal" rel="newArticleImages?article_id='.$article_id.'">';
			if(!empty($images_array)){
				$output .= '<img src="/'.$images_array[0].'" style="display:block; width:70px; margin:-3px;">';
			}else{
				$output .= '<span class="warning">ajouter</span>';
			}
			$output .= '</a>';
			$output .= '</td>';

			foreach($value as $k => $v){

				if( !in_array($k, $exclude) ){
					$v_present = present($k, $v);
					if( in_array($k, $editable) ){
						$data = ' class="'.$k.'" data-col="'.$k.'"';
					}else{
						$data = '';
					}
					$output .= '<td'.$data.'>'.$v_present.'</td>';
				}
			}
			
			// edit button
			/*$output .= '<td>
			<!--<div data-id="'.$article_id.'">
			<select name="actions" style="min-width:50px;">
			<option name="" value="">Choisir...</option>
			<option name="vendu" value="vendu">vendu</option>
			<option name="images" value="images">[↑]images</option>
			<option name="modifier" value="modifier">modifier...</option>
			</select>
			</div>-->
			<a href="/_code/php/forms/editArticle.php?article_id='.$article_id.'" class="button edit">modifier</a> 
			</td>';*/

			$output .= '<td>';
			if($value['statut_id'] < 4){
				$output .= '<a href="/_code/php/forms/editArticle.php?article_id='.$article_id.'" class="button vente vendre" style="margin:0 !important;" title="vendre cet article">&rarr;&nbsp;€</a>';
			}else{
				$output .= '';
			}
			$output .= '</td>';
			
			$output .= '</tr>';
			$i++;
			$n++;
		}
	}
	$output .= '</tbody></table>'.PHP_EOL;
	return $output;
}

/* validate date and reformat to EU date: date(d-m-Y) = "dd-mm-YYYY" */
function valid_date($date){
	$error = false;
	// trim date
	$valid_date = trim($date);
	// perfect format, return valid_date
	if( preg_match('/^\d\d-\d\d-\d\d\d\d$/', $valid_date) ){ // valid
		return $valid_date;
	
	// else, validate...
	// split numbers by non-number-chars, and pad each to desired length
	}elseif( preg_match('/^\d\d?[^a-zA-Z0-9]\d\d?[^a-zA-Z0-9]\d\d\d?\d?$/', $valid_date) ){
		$date_pieces = preg_split('/[^a-zA-Z0-9]+/', $valid_date);
		if( count($date_pieces) !== 3 ){
			$error = true;
		}else{
			$day = str_pad($date_pieces[0], 2, "0", STR_PAD_LEFT);
			$month = str_pad($date_pieces[1], 2, "0", STR_PAD_LEFT);
			$year = str_pad($date_pieces[2], 4, "20", STR_PAD_LEFT);
			if($day < 32 && $day > 0 && $month < 13 && $month > 0 && $year > 1){
				$valid_date = $day.'-'.$month.'-'.$year;
			}else{
				$error = true;
			}
		}
		
	}else{
		$error = true;
	}
	if( $error ){
		return false;
	}else{
		//echo '<pre>'.__FUNCTION__.' => '.$valid_date.'</pre>';
		return $valid_date;
	}
}


/* echo item (article) data in a table */
/*
function echo_item_table($item){
	$output = '';
	if( !empty($item['images']) ){
		$other_imgs = count($item['images']);
		$item['images'] = '<img src="/'.$item['images'][0].'">';
		if($other_imgs > 1){
			if($other_imgs > 2){$s='s';}else{$s='';} // plural or singular
			$item['images'] .= '<p>+ '.$other_imgs.' autre'.$s.'...</p>';
		}
	}else{
		$item['images'] = '<span class="warning">Pas d\'image...</span>';
	}
	
	$output .= '<table class="data">'.PHP_EOL;
	//$output .= '<tr><td>Image</td><td>'.$img_output.'</td></tr>';
	foreach($item as $k => $v){
		$output .= '<tr>
		<td>'.ucwords($k).'</td>';
		$v = present($k, $v);
		$output .= '<td>'.$v.'</td>'.PHP_EOL;
	}
	$output .= '</tr>'.PHP_EOL;
	$output .= '</table>'.PHP_EOL;
	echo $output;
}
*/



/****************************** PUBLIC ***************************** */

// display article data array on public pages (array provided by get_items_data())
function show_article($item_array){
	// format item poids
	$kg = 'kg';
	$poids = preg_replace('/\.0+$/', '', $item_array['poids']);
	if(preg_match('/^0*\./', $poids, $matches)){
		$kg = 'g';
		$poids = str_replace($matches[0], '', $poids);
	}
	// item statut
	if( $item_array['statut_id'] == name_to_id('disponible', 'statut') ){ 	// disponible
		$statut = 'success';
	}elseif($item_array['statut_id'] == name_to_id('réservé', 'statut') ){ 	// réservé
		$statut = 'note';
	}else{ 																	// vendu, transféré, rejeté
		$statut = 'error';
	}
	
	// images
	if( !isset($item_array['images']) ){
		$item_array['images'] = get_article_images($item_array['id']);
	}
	$inner_img_output = $img_nav = '';
	$n = count($item_array['images']);
	if($n > 0){
		$img = $item_array['images'][0];
		if( $n > 1){
			$img_nav .= '<span class="imgNav">';
			for($i=0; $i<$n; $i++){
				if($i == 0){
					$extra = ' selected';
				}else{
					$extra = '';
				}
				$img_nav .= '<a href="/'.$item_array['images'][$i].'" class="showModal'.$extra.'" rel="imageGallery?path='.urlencode('/uploads/'.$item_array['id'].'/_L/').'&img='.$i.'">•</a>';
			}
			$img_nav .= '</span>';
		}
		$inner_img_output = '<a href="/'.$img.'" class="clicker showModal" rel="imageGallery?path='.urlencode('/uploads/'.$item_array['id']).'">&nbsp;</a>'.$img_nav;
	}else{
		$img = '_code/images/404.gif';
	}

	$output = '';
	$output .= '<!-- start article -->'.PHP_EOL.'<div class="article" id="'.$item_array['id'].'">'.PHP_EOL;
	$output .= '<div class="imgContainer" style="background-image:url(/'.$img.');">'.$inner_img_output.'</div>'.PHP_EOL;

	$output .= '<!-- start detail -->'.PHP_EOL.'<div class="detail">'.PHP_EOL;
	$output .= '<p class="title">'.$item_array['titre'].'</p>'.PHP_EOL;
	$output .= '<p>'.$item_array['descriptif'].'</p>'.PHP_EOL;
	$output .= '<p><span class="'.$statut.'">'.id_to_name($item_array['statut_id'], 'statut').'</span></p>'.PHP_EOL;
	$output .= '<p>';
	if( !empty($item_array['prix']) && $item_array['prix'] > 0){
		$output .= 'Prix conséillé: € '.str_replace('.', ',', $item_array['prix']).'<br>'.PHP_EOL;
	}
	$output .= 'Poids: '.$poids.' '.$kg.'<br>'.PHP_EOL;
	$output .= 'Catégorie: '.ucwords(id_to_name($item_array['categories_id'], 'categories'));
	if(!empty($item_array['sous_categories_id'])){
		$output .= ', '.ucwords(id_to_name($item_array['sous_categories_id'], 'categories'));
	}
	$output .= '<br>'.PHP_EOL;
	$output .= 'Article entré le '.date('d-m-Y', $item_array['date']).PHP_EOL;
	$output .= '</p>';
	$output .= '</div><!-- end detail -->'.PHP_EOL;
	$output .= '<br class="clearBoth"></div><!-- end article -->'.PHP_EOL;
	return $output;
}


/* 
// Somme des ventes et du poids, entre 2 dates, classés par matière:

SELECT sum(prix_vente) AS vente_total, sum(poids) AS poids_total, matieres_id
FROM articles
WHERE date_vente BETWEEN 1532988000 AND 1548940324
GROUP BY matieres_id 
ORDER BY vente_total DESC
*/