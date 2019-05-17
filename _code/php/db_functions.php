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

	$query = mysqli_query( $db, $q) or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
	while($row = mysqli_fetch_assoc($query)){
		$data[] = $row;
	}
	if(!empty($data)){
		return($data);
	}else{
		return FALSE;
	}
}

// GET (visible) CATEGORIES
function get_categories($visible = ''){
	global $db;
	if( $visible !== '' ){
		$filter = " WHERE visible = ".$visible;
	}else{
        $filter = '';
    }
	$query = mysqli_query( $db, "SELECT * FROM categories".$filter) or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
	while($row = mysqli_fetch_assoc($query)){
		$categories[] = $row;
	}
	if(!empty($categories)){
		return($categories);
	}else{
		return FALSE;
	}
}

// get the categories_id from an article id
function get_item_category($article_id){
	global $db;
    $query = mysqli_query( $db, "SELECT categorie_id FROM articles WHERE id = '$article_id'") or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
	$categorie_id = mysqli_fetch_row($query);
	return $categorie_id[0];
}

// get article field
function get_item_field($article_id, $field){
	global $db;
    $query = mysqli_query( $db, "SELECT $field FROM articles WHERE id = '$article_id'") or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
	$row = mysqli_fetch_row($query);
	return $row[0];
}

// get all item data
function get_item($article_id){
	global $db;
    $query = mysqli_query( $db, "SELECT * FROM articles WHERE id = '$article_id'") or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
	$item = mysqli_fetch_assoc( $query );
	// image(s)?
	//$item['images'] = get_article_images($article_id);
	return $item;
}

/* NOT USED 
// get statut name from id
function statut_name($statut_id){
	global $db;
	$query = mysqli_query( $db, "SELECT 'nom' FROM 'statut' WHERE id = '$statut_id'") or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
	$statut_name = mysqli_fetch_row( $query );
	return $statut_name[0];
}
*/

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
		$filter[] = " statut_id != 6";
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
	$q = "SELECT nom FROM $table WHERE id = '$id'";
	//echo $q.'<br>';
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

// get article images
function get_article_images($article_id = '', $size = '_M', $path = 'uploads'){
	$images_array = array();
	if( preg_match('/\/(_L|_M|_XL|_S)\//', $path) ){
		$size = '';
	}
	$img_dir = $path.'/'.$article_id.'/'.$size.'/';
	$scan_dir = preg_replace('/\/+/', '/', ROOT.$img_dir);
	$img_dir = preg_replace('/\/+/', '/', $img_dir); // make sure there are no duplicate slashes
	$files = scandir($scan_dir);
	foreach($files as $f){
		if(substr($f, 0, 1) !== '.'){
			$images_array[] = $img_dir.$f;
		}
	}
	return $images_array;
}









/**** 2. UPDATERS ****/

/*
function update_item($article_id, $update){
	global $db;
	$q = "UPDATE articles SET ";
	$q .= update_sql($update);
	$q .= " WHERE id = $article_id";
	
	//echo '<pre>'.__FUNCTION__.PHP_EOL.$q.'</pre>';
	
	if( $query = mysqli_query($db, $q) ){
		return '<p class="success">Article #'.$article_id.' modifié.</p>';
	}else{
		log_db_errors( mysqli_error($db), 'Query: '.$q.' Function: '.__FUNCTION__ );
	}
}
*/

// UPDATE TABLE DATA (generic)
function update_table($table, $article_id, $update){
	global $db;
	$q = "UPDATE $table SET ";
	$q .= update_sql($update);
	$q .= " WHERE id = $article_id";
	
	//echo '<pre>'.__FUNCTION__.PHP_EOL.$q.'</pre>';
	
	if( $query = mysqli_query($db, $q) ){
		$result = '<p class="success">Modifications enregistrées.</p>';
	}else{
		$result = '<p class="error">Erreur: '.mysqli_error($db).'</p>';
		log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__ );
	}
	return $result;
}







/**** 3. INSERTERS ****/

/*
function insert_article($item_data){
	global $db;

	$array_keys = array();
	foreach($item_data as $k => $v){
		if( !in_array(strtolower($k), $array_keys) ){
			$array_keys[] = strtolower($k);
			if($v !== ''){
				$array_values[] = "'".filter($v)."'";
			}else{
				$array_values[] = 'NULL';
			}
		}
		
		$string_keys = implode(',', $array_keys);
		$string_values = implode(',', $array_values);
	}
		
	//echo $string_keys.'<br>'.$string_values.'<br>'; exit;
	
	if(mysqli_query($db, "INSERT INTO articles (".$string_keys.") VALUES (".$string_values.")")){
		echo '<p class="success">L\'article #'.mysqli_insert_id($db).' a été ajouté.</p>';
		return true;
	}else{
		echo '<p class="error">'.mysqli_error($db).'</p>';
		return false;
	}
}
*/

function insert_new($table, $item_data){
	global $db;

	$array_keys = $array_values = array();

	// add date (php timestamp) for certain tables
	if($table == 'adhesions' || $table == 'articles' || $table == 'passages' || $table == 'ventes'){
		$array_keys[] = 'date';
		$array_values[] = time();
	}

	foreach($item_data as $k => $v){
		//if( !in_array(strtolower($k), $array_keys) ){
			$array_keys[] = strtolower($k);
			if($v !== ''){
				if( is_numeric($v) ){
					$array_values[] = $v;
				}else{
					$array_values[] = "'".filter($v)."'";
				}
				
			}else{
				$array_values[] = 'NULL';
			}
		//}
		
		$string_keys = implode(',', $array_keys);
		$string_values = implode(',', $array_values);
	}
		
	//echo '<pre>'.__FUNCTION__.PHP_EOL.$string_keys.PHP_EOL.$string_values.'</pre>';
	
	if(mysqli_query($db, "INSERT INTO $table (".$string_keys.") VALUES (".$string_values.")")){
		// create images directory if article
		$new_id = mysqli_insert_id($db);
		if($table == 'articles'){
			copyr(ROOT.'templates/img_dir', ROOT.'uploads/'.$new_id);
		}
		return $new_id;
	}else{
		return false;
	}
}








/**** 4. DELETERS ****/

function delete_item($table, $article_id){
	global $db;
	
	// delete database col
	if( $delete = mysqli_query($db, "DELETE FROM $table WHERE id = '$article_id'") ){
		$result = '<p class="success">Élément #'.$article_id.' éffacé du tableau <b>'.$table.'</b></p>';
	}else{
		$result = '<p class="error">L\'élément #'.$article_id.' n\'a pas pu être éffacé du tableau <b>'.$table.'</b></p>';
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
 * $key_val_pairs = array('categories_id' => 'bois', 'dechette_categories_id' => 1, 'descriptif' => 'hello');
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
		}elseif( $key == 'categories_id' || $key == 'dechette_categories_id' && !is_numeric($value)  ){
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
				echo '<p class="error">Date mal formée! Le correct format est:  31-05-2018</p>';
			}
			
		// default
		}else{
			$q = "SELECT id FROM articles WHERE $key = '$value'";
		}

		// include statut='vendu' or not
		if(!$include_vendus){
			$q .= " AND statut_id != 6";
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
function search($keywords = '', $category = '', $visible = TRUE, $vendus = FALSE){
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
	// vendus = FALSE > show only items where statut is NOT 'vendu'
	if( !$vendus ){
		$filters_array['vendus'] = "`statut_id` != 6";
	}
	// if a category is specified show only this category
	if($category !== ''){
		$filters_array['category'] = "`categories_id` = '".$category."'";
	}

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
			if($st['id'] == $v){
				$selected = ' selected';
			}else{
				$selected = '';
			}
			$options .= '<option value="'.$st['id'].'"'.$selected.'>'.$st['nom'].'</option>';
		}
		$v = '<select name="statut_id" style="min-width:50px;" class="ajax">'.$options.'</select>';

	// show name of keys_id
	}elseif( substr($k, -3) == '_id'){
		$name = id_to_name( $v, substr($k, 0, -3) );
		$v = $name;
		//$v .= '-'.$name;

	// show select input for visible
	}elseif($k == 'visible'){
		$selected_1 = $selected_2 = '';
		if($v == '1'){
			$selected_1 = ' selected';
		}else{
			$selected_2 = ' selected';
		}
		$v = '<select name="visible" style="min-width:50px;" class="ajax">
		<option value="1"'.$selected_1.'>oui</option>
		<option value="0"'.$selected_2.'>non</option>
		</select>';

	// show delect input for vrac
	}elseif($k == 'vrac'){
		$selected_1 = $selected_2 = '';
		if($v == '1'){
			$selected_1 = ' selected';
		}else{
			$selected_2 = ' selected';
		}
		$v = '<select name="vrac" style="min-width:50px;" class="ajax">
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
		$less = substr($v, 0, 15);
		if($less !== $v){
			$v = '<div class="short">'.$less.'...<div class="long">'.$v.'</div></div>';
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

/* echo interactive items table (uses present() function above) for admin */
function items_table_output($result_array, $limit = NULL, $offset = 0){

	if($limit === NULL){
		$limit = count($result_array);
	}
	$start = $limit*$offset;
	$end = $start+$limit;
	
	// debug
	//echo '<pre>'.__FUNCTION__.PHP_EOL;print_r($result_array);echo '</pre>';
	
	$editable = array('categories_id', 'dechette_categories_id', 'titre', 'descriptif', 'observations', 'prix', 'poids', 'statut_id', 'visible');
	$exclude = array('id', 'date', 'date_vente', 'vrac', 'etiquette', 'prix_vente', 'payement_id');

	$output = '';
	$i = $n = 0;
	$output .= '<table class="data" data-id="articles">'.PHP_EOL;
	
    foreach($result_array as $key => $value){

		if($n >= $start && $n < $end){
			$article_id = $value['id'];

			// get images
			$images_array = get_article_images($article_id, '_S');
			$img_count = count($images_array);
			
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
				// th for edit button
				$output .= '<th style="background-image:none; padding-left:5px;"><!--Actions--></th>';

				$output .= '</tr>';
				$output .= '</thead><tbody>'; 
			}

			// show results
			$output .= '<tr data-id="'.$article_id.'">';

			if($i % 2 == 0){
				$style = ' style="background-color:#f5f5f5;"';
			}else{
				$style = '';
			}

			// images
			$output .= '<td'.$style.'>';
			$output .= '<a href="javascript:;" title="ajouter" class="showModal" rel="newArticleImages?article_id='.$article_id.'">';
			if(!empty($images_array)){
				$output .= '<img src="/'.$images_array[0].'" style="display:block; width:70px; margin:-3px;">';
			}else{
				$output .= '<span class="warning">ajouter</span>';
			}
			$output .= '</a>';
			$output .= '</td>';

			foreach($value as $k => $v){

				if( !in_array($k, $exclude) ){
					$v = present($k, $v);
					if( in_array($k, $editable) ){
						$data = ' class="'.$k.'" data-col="'.$k.'"';
					}else{
						$data = '';
					}
					$output .= '<td'.$style.$data.'>'.$v.'</td>';
				}
			}
			
			// edit button
			$output .= '<td'.$style.'>
			<!--<div data-id="'.$article_id.'">
			<select name="actions" style="min-width:50px;">
			<option name="" value="">Choisir...</option>
			<option name="vendu" value="vendu">vendu</option>
			<option name="images" value="images">[↑]images</option>
			<option name="modifier" value="modifier">modifier...</option>
			</select>
			</div>-->
			<a href="/_code/php/forms/editArticle.php?article_id='.$article_id.'" class="button edit" style="margin:0;">modifier</a>
			</td>';
			
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

/* scinde un article en deux (save first one, create second one)
$original and $copy are an array containing article data */
function scinde_article($original, $copy){
	// reformat both arrays
	foreach($original as $o){
		$o_array[$o['name']] = $o['value'];
	}
	foreach($copy as $c){
		$c_array[$c['name']] = $c['value'];
	}
	// attempt to create new article
	if( $new_id = insert_new('articles', $c_array) ){
		$result = '<p class="success">Nouvel article crée, ID:'.$new_id.'</p>';
	}else{
		$result = '<p class="error">Erreur: Le nouvel article n\'a pas pu être créé!</p>';
	}
	// save original article
	$result .= update_table('articles', $o_array['id'], $o_array);
	return $result;
}




/* echo table to represent SQL results (uses function present() above) */
/*
function echo_table($result_array){
	$output = '';
	$i = 0;
    $output .= '<table class="data">'.PHP_EOL;
    foreach($result_array as $key => $value){
		
		// first iteration, show top row = key name
        if($i == 0){
			$output .= '<thead>';
			$output .= '<tr class="topRow">';
            foreach($value as $k => $v){
                $output .= '<th>'.$k.'</th>';
			}
			if( isset($value['statut']) ){
				$output .= '<th>&nbsp;</th>';
			}
			$output .= '</tr>';
			$output .= '</thead><tbody>'; 
		}

		// show results
		$output .= '<tr>';
        foreach($value as $k => $v){
			$v = present($k, $v);
			if($i % 2 == 0){
				$style = ' style="background-color:#f5f5f5;"';
			}else{
				$style = '';
			}
            $output .= '<td'.$style.'>'.$v.'</td>';
		}
		if( isset($value['statut']) ){
			$output .= '<td'.$style.'><a href="/_code/php/forms/editArticle.php?article_id='.$value['id'].'" class="button edit" style="margin:0;">modifier</a></td>';
        $output .= '</tr>';
		}
        $i++;
    }
	$output .= '</tbody></table>'.PHP_EOL;
	echo $output;
}
*/


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
	$kg = 'kg';
	$poids = preg_replace('/\.0+$/', '', $item_array['poids']);
	if(preg_match('/^0*\./', $poids, $matches)){
		$kg = 'g';
		$poids = str_replace($matches[0], '', $poids);
	}
	if($item_array['statut_id'] == 1){
		$statut = 'success';
	}elseif($item_array['statut_id'] == 2){
		$statut = 'note';
	}else{
		$statut = 'error';
	}
	
	
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
	$output .= 'Catégorie: '.ucwords(id_to_name($item_array['categories_id'], 'categories')).'<br>'.PHP_EOL;
	$output .= 'Article entré le '.date('d-m-Y', $item_array['date']).PHP_EOL;
	$output .= '</p>';
	$output .= '</div><!-- end detail -->'.PHP_EOL;
	$output .= '<br class="clearBoth"></div><!-- end article -->'.PHP_EOL;
	return $output;
}


/* 
// Somme des ventes et du poids, entre 2 dates, classés par déchette-catégorie:

SELECT sum(prix_vente) AS vente_total, sum(poids) AS poids_total, dechette_categories_id
FROM articles
WHERE date_vente BETWEEN 1532988000 AND 1548940324
GROUP BY dechette_categories_id 
ORDER BY vente_total DESC
*/