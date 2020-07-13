<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

if( isset($_GET['table']) ){
	$table = urldecode($_GET['table']);
}
if( !isset($table) || ($table !== 'categories' && $table !== 'matieres') ){
	exit();
}else{
	if($table == 'categories'){
		$table_fr = 'catégories';
		$singulier = 'catégorie';
	}elseif($table == 'matieres'){
		$table_fr = 'matières';
		$singulier = 'matière';
	}
}

// UPDATE CATEGORIES
if(isset($_POST['update'])){
	
	// debug
	//print_r($_POST); //exit;
	
	foreach($_POST as $k => $v){

		if( $k !== 'update'){ // ignore unwanted POSTs
			foreach($v as $id => $values){
				$update['nom'] = $values['nom'];
				$update['visible'] = $values['visible'];
				$update['id_parent'] = $values['id_parent'];
				$db_message = update_table($table, $id, $update);
			}
		}
	}
}

// get parent (main) categories ou matieres
if( !isset($categories) || empty($categories) ){
	$categories = get_table($table, 'id_parent=0');
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
		$message = '0|Le nom de la '.$singulier.' contient des signes interdits: '.$m;
	}
	foreach($categories as $cat){ // avoid overwritting existing section
		if($newCategory == $cat['nom']){
			$error = true;
			$message = '0|Une '.$singulier.' nommée <b>'.$newCategory.'</b> existe déjà!';
			break;
		}
	}
	
	if($error == false){
		$item_data['nom'] = $newCategory;
		$item_data['visible'] = $_POST['visible'];
		$item_data['id_parent'] = $_POST['id_parent'];
		if( insert_new($table, $item_data) ){
			$message = '1|La nouvelle '.$singulier.' <b>'.$newCategory.'</b> a été créée.';
			//unset($categories);
			$categories = get_table($table, 'id_parent=0');
			$c_count = count($categories);
		}else{
			$message = '0|ERREUR - La '.$singulier.' <b>'.$newCategory.'</b> n\'a pas pu être créée.';
		}
	}
}

// result message passed via query string
if( !empty($message) || !empty($db_message) ){
	$message_script = '<script type="text/javascript">showDone();</script>';
}else{
	$message_script = '';
}
if( !empty($message) ){
	$message = str_replace(array('0|', '1|', '2|'), array('<p class="error">', '<p class="success">', '<p class="note">'), $message).'</p>';
}else{
	$message = '';
}
if( !empty($db_message) ){
	$db_message = str_replace(array('0|', '1|', '2|'), array('<p class="error">', '<p class="success">', '<p class="note">'), $db_message).'</p>';
}else{
	$db_message = '';
}
$message = $message.$db_message;


require(ROOT.'_code/php/doctype.php');
if( isset($_SESSION['article_id']) ){
	unset($_SESSION['article_id']);
}
?>

<!-- admin css -->
<link href="/_code/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">

<?php
echo '<div id="working"><div class="note">working...</div></div>';
echo '<div id="done">'.$message.'</div>';
?>

<div class="adminHeader">

<h1><a href="/admin" class="admin">Admin <span class="home">&#8962;</span></a> <?php echo ucwords($table_fr); ?></h1>

</div>



<!-- start admin container -->
<div id="adminContainer">

<form action="" name="updateNamesForm" method="post" style="display:inline-block; float:left; margin-right:10px;">

<table class="data">
<thead>
	<tr class="topRow">
<th class="header"><strong>Nom</strong></th>
<th class="header"><strong>Parent</strong></th>
<th class="header"><strong>Visible</strong></th>
<!-- <th class="header"><strong>Supprimer</strong></th> -->
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
		$style = ' style="background-color:#ddd;"';
	}

	$sub_cats = get_children($table, $cat['id']);
	
	echo '<tr>
	<td'.$style.'>
	<input name="cat['.$cat['id'].'][nom]" type="text" value="'.$cat['nom'].'">
	<input name="cat['.$cat['id'].'][old_name]" type="hidden" value="'.$cat['nom'].'">
	</td>
	<td'.$style.'><input name="cat['.$cat['id'].'][id_parent]" type="hidden" value="'.$cat['id_parent'].'"></td>
	<td'.$style.'><select name="cat['.$cat['id'].'][visible]" style="width:auto; min-width:auto;">
	<option value="0"'.$non.'>non</option>
	<option value="1"'.$oui.'>oui</option>
	</select>
	</td> 
	</tr>';

	if( !empty($sub_cats) ){
		foreach($sub_cats as $sb){
			if($sb['visible'] == 0){
				$oui = '';
				$non = ' selected';
				$style = ' style="text-align:right; opacity:.5; background-color:red;"';
			}else{
				$oui = ' selected';
				$non = '';
				$style = ' style="text-align:right;"';
			}
	
			echo '<tr>
			<td'.$style.'>
			<input name="cat['.$sb['id'].'][nom]" type="text" value="'.$sb['nom'].'" style="width:170px; min-width:170px;">
			<input name="cat['.$sb['id'].'][old_name]" type="hidden" value="'.$sb['nom'].'">
			</td>
			<td'.$style.'><select name="cat['.$sb['id'].'][id_parent]" style="min-width:100px;">';
			foreach($categories as $c){
				if($c == $cat){
					$selected = ' selected';
				}else{
					$selected = '';
				}
				echo '<option value="'.$c['id'].'"'.$selected.'>'.$c['nom'].'</option>';
			}
			echo '</select>
			</td>
			<td'.$style.'>
			<select name="cat['.$sb['id'].'][visible]" style="width:auto; min-width:auto;">
			<option value="0"'.$non.'>non</option>
			<option value="1"'.$oui.'>oui</option>
			</select> 
			</tr>';
		}
	}
}
?>
</table>

<button name="update" type="submit" class="right">SAUVEGARDER</button>

</form>



<form action="" name="createForm" method="post">
<h3>Créer une nouvelle <?php echo $singulier; ?>:</h3>
<table>
<tr>
<th>Nom:</th>
<th>Parent:</th>
<th>Visible:</th>
</tr>
<tr>
<td><input name="newCategory" type="text" value=""></td>
<td>
<select name="id_parent" style="width:auto; min-width:auto;">
	<option value="0">aucun</option>
	<?php
	foreach($categories as $cat){
		echo '<option value="'.$cat['id'].'">'.$cat['nom'].'</option>';
	}
	?>
</select>
</td>
<td>
<select name="visible" style="width:auto; min-width:auto;">
	<option value="0">non</option>
	<option value="1">oui</option>
</select>
</td>
</tr>
</table>
<button name="create" type="submit" class="right">CRÉER</button>
</form>



</div>
<!-- end admin container -->

<?php
require(ROOT.'/_code/php/admin/admin_footer.php');
echo $message_script;
?>

</body>
</html>
