<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

$new_categorie_message = $update_message = '';

$table = 'categories';

// UPDATE CATEGORIES
if(isset($_POST['update'])){
	unset($_POST['update'], $_POST['sizes'], $_POST['types']); // unset unwanted POSTs
	
	// debug
	//print_r($_POST); exit;
	
	foreach($_POST as $k => $v){

		foreach($v as $id => $values){
			$update['nom'] = $values['nom'];
			$update['visible'] = $values['visible'];
			$update_message = update_table($table, $id, $update);
		}
		
		if( !isset($update_message) ){
			$update_message = '<p class="error">The database has not been updated.</p>';
		}
	}
}

// make sure we get the needed data, if we don't have it already
if( !isset($categories) || empty($categories) ){
	$categories = get_table($table);
}


//print_r($categories);//exit;
$c_count = count($categories);
//echo $c_count;


// CREATE CATEGORY
if(isset($_POST['create']) && !empty($_POST['newCategory'])){
	$error = false;
	
	$newCategory = trim($_POST['newCategory']);
	if(preg_match('/(:|,|;|\/|\||\\|&|#|\+|®|™)/',$newCategory, $matches)){ // check section format
		$error = true;
		$m = implode(',',$matches);
		$message .= '<p class="error">Le nom de la catégorie contient des signes interdits: '.$m.'</p>';
	}
	foreach($categories as $cat){ // avoid overwritting existing section
		if($newCategory == $cat['nom']){
			$error = true;
			$message .= '<p class="error">Une catégorie nommée <strong>'.$newCategory.'</strong> existe déjà!</p>';
		}
	}
	
	if($error == false){
		$item_data['nom'] = $newCategory;
		$item_data['visible'] = 0;
		if( insert_new($table, $item_data) ){
			$message = '<p class="success">La nouvelle '.str_replace(array('_','s'),' ',$table).' <strong>'.$newCategory.'</strong> has been created.</p>';
			unset($categories);
			$categories = get_table($table);
			$c_count = count($categories);
		}else{
			$message = '<p class="error">ERROR - The new category <strong>'.$newCategory.'</strong> could not be created.</p>';
		}
	}
}




require(ROOT.'_code/php/doctype.php');
if( isset($_SESSION['article_id']) ){
	unset($_SESSION['article_id']);
}
?>

<!-- admin css -->
<link href="/_code/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">

<div class="adminHeader">

<h1><a href="/admin/">Admin</a> : Catégories</h1>

</div>

<?php echo $message; if(isset($database_message)){echo $database_message;}; ?>

<!-- start admin container -->
<div id="adminContainer">

<form action="" name="updateNamesForm" method="post" style="display:inline-block; float:left; margin-right:10px;">
<table class="data">
<thead>
	<tr class="topRow">
<th class="header"><strong>Nom</strong></th>
<th class="header"><strong>visible</strong></th>
	</tr>
</thead>

<?php
foreach($categories as $cat){

	if($cat['visible'] == 0){
		$oui = '';
		$non = ' selected';
		$style = ' style="opacity:.5; background-color:red;"';
	}else{
		$oui = ' selected';
		$non = '';
		$style = '';
	}
	
	echo '<tr>
	<td'.$style.'>
	<input name="cat['.$cat['id'].'][nom]" type="text" value="'.$cat['nom'].'">
	<input name="cat['.$cat['id'].'][old_name]" type="hidden" value="'.$cat['nom'].'">
	</td> 
	<td'.$style.'><select name="cat['.$cat['id'].'][visible]" style="width:100px; min-width:100px;">
	<option value="0"'.$non.'>non</option>
	<option value="1"'.$oui.'>oui</option>
	</select>
	</td> 
	</tr>';
}
?>
</table>

<div class="clearBoth" style="padding:10px;"></div>

<button name="update" type="submit" style="float:right;">SAUVEGARDER</button>

</form>



<form action="" name="createForm" method="post">
<h3>Créer une nouvelle catégorie:</h3>
<strong>Nom:</strong> <input name="newCategory" type="text" value=""> 
<button name="create" type="submit">CRÉER</button>
</form>



</div>
<!-- end admin container -->


</body>
</html>
