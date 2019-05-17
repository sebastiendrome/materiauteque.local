<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

if( isset($_GET['article_id']) ){
    $article_id = urldecode($_GET['article_id']);
    $_SESSION['article_id'] = $article_id;
}elseif( isset($_SESSION['article_id']) ){
    $article_id = $_SESSION['article_id'];
}

if( !isset($title) ){
    $title = ' : Scinder un article en 2';
    require(ROOT.'_code/php/doctype.php');
    echo '<!-- admin css -->
    <link href="/_code/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">'.PHP_EOL;

	echo '<!-- adminHeader start -->
	<div class="adminHeader">
	<h1><a href="/admin/">Admin</a>'.$title.' </h1>'.PHP_EOL;
	echo '</div><!-- adminHeader end -->'.PHP_EOL;

    echo '<!-- start admin container -->
	<div id="adminContainer">'.PHP_EOL;
	
	echo '<div id="working">working...</div>
        <div id="done"></div>
        <div id="result"></div>';

    
    $footer = true;
}else{
    $footer = false;
}

// article ID:
if( !isset($article_id) || empty($article_id) ){
    unset($_SESSION['article_id']);
    //exit;
}else{
	$item_data = get_item($article_id); 
	$item_data_copy = $item_data;
    ?>

<?php
// make sure we get the needed data, if we don't have it already
if( !isset($categories) || empty($categories) ){
    $categories = get_table('categories');
}
if( !isset($dechette_categories) || empty($dechette_categories) ){
    $dechette_categories = get_table('dechette_categories');
}

// process form POST data (save original article, create new one)
if( isset($_POST['formSubmitted']) ){
    // unset all item data (we must use the $_POST vars instead), except for images
    foreach($item_data as $k => $v){
        if( $k !== 'images' ){
            unset($item_data[$k]);
        }
    }
    // new array of data from POST
    foreach($_POST as $k => $v){
        if($k !== 'formSubmitted' && $k !== 'editArticleSubmit' && $k !== 'types' && $k !== 'sizes'){
            $item_data[$k] = trim($v);
        }
    }
    $message = update_table('articles', $article_id, $item_data);
    //echo $message;

}elseif( isset($_GET['upload_result']) ){
    $message = urldecode($_GET['upload_result']);
}elseif( isset($_GET['message']) ){
    $message = urldecode($_GET['message']);
}
?>

<?php
// if standalone, show result message passed via query string
if( isset($message) ){
    echo $message;
}
?>

<!-- formsContainer start -->
<div id="formsContainer" style="display:inline-block;">


<form name="article_original" id="original" action="?article_id=<?php echo $article_id; ?>" method="post">
<h3>Partie 1 (Original)</h3>
	<table>

		<tr>
		<td><h3>Titre:</h3>
		<td><h3><input type="text" name="titre" value="<?php echo $item_data['titre']; ?>" required></h3>

		<tr>
        <td>Descriptif:<td><textarea name="descriptif"><?php echo $item_data['descriptif']; ?></textarea>
        
        <tr>
        <td>Vrac:<td><input type="radio" name="vrac" value="0"<?php if($item_data['vrac']==0){echo' checked';}?>><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="vrac" value="1"<?php if($item_data['vrac']==1){echo' checked';}?>><label for="1"> oui</label>
		
		<!--
        <tr>
        <td>etiquette:<td><input type="text" name="etiquette" value="">
        --> 
        <tr>
        <td>Catégorie:<td><select name="categories_id" required>
            <?php
            foreach($categories as $cat){
                $selected = '';
                if($item_data['categories_id'] == $cat['id']){
                    $selected = ' selected';
                }
                echo '<option value="'.$cat['id'].'"'.$selected.'>'.$cat['id'].' = '.$cat['nom'].'</option>';
            }
            ?>
        </select>
        
        <tr>
        <td>Déchet. Catégorie:<td><select name="dechette_categories_id" required>
            <?php
            foreach($dechette_categories as $cat){
                $selected = '';
                if($item_data['dechette_categories_id'] == $cat['id']){
                    $selected = ' selected';
                }
                echo '<option value="'.$cat['id'].'"'.$selected.'>'.$cat['id'].' = '.$cat['nom'].'</option>';
            }
            ?>
        </select>
        
        <tr>
        <td>Prix:<td><input type="number" min="0" name="prix" step="any" value="<?php echo $item_data['prix']; ?>">
        
        <tr>
        <td>Poids (Kg):<td><input type="number" min="0" name="poids" step="any" value="<?php echo $item_data['poids']; ?>" required>
        
        <tr>
        <td>Statut:<td>
		<select name="statut_id">
		<?php
		$statut_array = get_table('statut'); // get contents of statut table ('id, nom)
		$options = '';

		foreach($statut_array as $st){ // loop through statut_array to output the options
			if($st['id'] == $item_data['statut_id']){
				$selected = ' selected';
			}else{
				$selected = '';
			}
			$options .= '<option value="'.$st['id'].'"'.$selected.'>'.$st['nom'].'</option>';
		}
		echo $options;

		?>
        </select>

        <tr id="prixVente"<?php if($item_data['statut'] !== 'vendu'){echo ' style="display:none;"';} ?>>
        <td>Prix de vente:<td><input type="number" name="prix_vente" step="any" value="<?php echo $item_data['prix_vente']; ?>">
        
        <tr>
        <td>Visible:<td><input type="radio" id="visibleZero" name="visible" value="0"<?php if($item_data['visible'] == 0){echo ' checked';}?>><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="visibleOne" name="visible" value="1"<?php if($item_data['visible'] == 1){echo ' checked';}?>><label for="1"> oui</label>
        </select>
        
        <tr>
		<td>Observations:<td><textarea name="observations"><?php echo $item_data['observations']; ?></textarea>
    
	</table>

	<input type="hidden" name="id" value="<?php echo $article_id; ?>">
	</form>




<!-- COPY -->



<form name="article_copy" id="copy" action="?article_id=<?php echo $article_id; ?>" method="post">
	<h3>Partie 2 (Copie)</h3>
	<table>
	
	<tr>
	<td><h3>Titre:</h3>
	<td><h3><input type="text" name="titre" value="<?php echo $item_data_copy['titre']; ?>" required></h3>

	<tr>
	<td>Descriptif:<td><textarea name="descriptif"><?php echo $item_data_copy['descriptif']; ?></textarea>
	

	<tr>
	<td>Vrac:<td><input type="radio" name="vrac" value="0"<?php if($item_data_copy['vrac']==0){echo' checked';}?>><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="vrac" value="1"<?php if($item_data_copy['vrac']==1){echo' checked';}?>><label for="1"> oui</label>
	
	<!--
	<tr>
	<td>etiquette:<td><input type="text" name="etiquette" value="">
	--> 
	<tr>
	<td>Catégorie:<td><select name="categories_id" required>
		<?php
		foreach($categories as $cat){
			$selected = '';
			if($item_data_copy['categories_id'] == $cat['id']){
				$selected = ' selected';
			}
			echo '<option value="'.$cat['id'].'"'.$selected.'>'.$cat['id'].' = '.$cat['nom'].'</option>';
		}
		?>
	</select>
	
	<tr>
	<td>Déchet. Catégorie:<td><select name="dechette_categories_id" required>
		<?php
		foreach($dechette_categories as $cat){
			$selected = '';
			if($item_data_copy['dechette_categories_id'] == $cat['id']){
				$selected = ' selected';
			}
			echo '<option value="'.$cat['id'].'"'.$selected.'>'.$cat['id'].' = '.$cat['nom'].'</option>';
		}
		?>
	</select>
	
	<tr>
	<td>Prix:<td><input type="number" min="0" name="prix" step="any" value="<?php echo $item_data_copy['prix']; ?>">
	
	<tr>
	<td>Poids (Kg):<td><input type="number" min="0" name="poids"  step="any" value="<?php echo $item_data_copy['poids']; ?>" required>
	
	<tr>
	<td>Statut:<td>
	<select name="statut">
		<?php
		$statut_array = get_table('statut'); // get contents of statut table ('id, nom)
		$options = '';

		foreach($statut_array as $st){ // loop through statut_array to output the options
			if($st['id'] == $item_data['statut_id']){
				$selected = ' selected';
			}else{
				$selected = '';
			}
			$options .= '<option value="'.$st['id'].'"'.$selected.'>'.$st['nom'].'</option>';
		}
		echo $options;

		?>
	</select>

	<tr id="prixVente"<?php if($item_data_copy['statut'] !== 'vendu'){echo ' style="display:none;"';} ?>>
	<td>Prix de vente:<td><input type="number" name="prix_vente" step="any" value="<?php echo $item_data_copy['prix_vente']; ?>">
	
	<tr>
	<td>Visible:<td><input type="radio" id="visibleZero" name="visible" value="0"<?php if($item_data_copy['visible'] == 0){echo ' checked';}?>><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="visibleOne" name="visible" value="1"<?php if($item_data_copy['visible'] == 1){echo ' checked';}?>><label for="1"> oui</label>
	</select>
	
	<tr>
	<td>Observations:<td><textarea name="observations"><?php echo $item_data_copy['observations']; ?></textarea>

</table>

</form>






<div class="clearBoth"></div>

<div style="text-align:center;">
	<form name="dualForm" id="dualForm" action="" method="post">
	<input type="hidden" name="scinderFormSubmitted" id="scinderFormSubmitted" value="submitted">
	<a href="" class="button">Annuler</a>
	<button type="submit" name="editArticleSubmit" id="editArticleSubmit">Enregistrer les modifications</button>
	</form>
</div>

</div><!-- end formsContainer -->

<?php //require(ROOT.'/_code/php/forms/newArticleImages.php'); ?>



<?php
if($footer){
    echo '</div><!-- end admin container -->'.PHP_EOL;
	require(ROOT.'/_code/php/admin/admin_footer.php');
}
?>
<script type="text/javascript">
$('form#original, form#copy').on("submit", function(e){
	e.preventDefault();
});
$("form#dualForm").on("submit", function(e){
	e.preventDefault();
	
	var original = $('form#original').serializeArray();
	var copy = $('form#copy').serializeArray();
	//console.log( original );
	//console.log('--------------------------------------------------------------');
	//console.log(copy);
	$.ajax({
		url: '/_code/php/admin/admin_ajax.php',
		type: "POST",
		data: {original, copy},
		// on success show message
		success : function(msg) {
			$('#formsContainer').html(msg);
			return true;
		}
	});
});
</script>
<?php } ?>

</body>
</html>