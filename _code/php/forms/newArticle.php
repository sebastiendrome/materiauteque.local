<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

if( !isset($title) ){
    $title = ' : Nouvel Article';
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

// make sure we get the needed data, if we don't have it already
if( !isset($categories) || empty($categories) ){
    $categories = get_table('categories');
}
if( !isset($dechette_categories) || empty($dechette_categories) ){
    $dechette_categories = get_table('dechette_categories');
}

$titre_autofocus = true;


// process form POST data
if( isset($_POST['newArticleSubmitted']) ){
	$titre_autofocus = false;
    foreach($_POST as $k => $v){
        if($k !== 'newArticleSubmitted' && $k !== 'newArticleSubmit' && $k !== 'types' && $k !== 'sizes'){
            $item_data[$k] = trim($v);
        }
    }
    if($article_id = insert_new('articles', $item_data)){
		$_SESSION['article_id'] = $article_id;
		$new_item[0] = get_item($article_id);
		$items_table = items_table_output($new_item);
        $message = '<p class="success">Nouvel Article créé. ID: '.$article_id.'</p>';
        $path = 'uploads/'.$article_id;
        
    }else{
        $message = '<p class="error">'.mysqli_error($db).'</p>';
    }
}elseif( isset($_GET['upload_result'])){
	$titre_autofocus = false;
	$new_item[0] = get_item($_SESSION['article_id']);
	$items_table = items_table_output($new_item);
    $message = urldecode($_GET['upload_result']);
    $path = 'uploads/'.$_SESSION['article_id'];
}
?>

<?php
// if standalone, show result message passed via query string
if( isset($message) ){
	echo $message;
	//$titre_autofocus = false;
}
if(isset($items_table)){
	echo $items_table;
}
?>

<form name="newArticle" id="newArticle" action="" method="post" style="display:inline-block; float:left; margin-right:10px;">

<!--<h3>Ajouter un article:</h3>-->

    <table>

	<tr>
        <td><h3>Titre:</h3><td><h3><input type="text" name="titre" value=""<?php if($titre_autofocus){echo ' autofocus';}?> required></h3>

        <tr>
        <td>Descriptif:<td><textarea name="descriptif"></textarea>
        
        <tr>
        <td>Vrac:<td><input type="radio" name="vrac" value="0" checked><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="vrac" value="1"><label for="1"> oui</label>
        <!--
        <tr>
        <td>etiquette:<td><input type="text" name="etiquette" value="">
        --> 
        <tr>
        <td>Catégorie:<td><select name="categories_id" required>
            <option value="">Choisir...</option>
            <?php
            foreach($categories as $cat){
                echo '<option value="'.$cat['id'].'">'.$cat['id'].' = '.$cat['nom'].'</option>';
            }
            ?>
        </select>
        
        <tr>
        <td>Déchet. Catégorie:<td><select name="dechette_categories_id" required>
            <option value="">Choisir...</option>
            <?php
            foreach($dechette_categories as $cat){
                echo '<option value="'.$cat['id'].'">'.$cat['id'].' = '.$cat['nom'].'</option>';
            }
            ?>
        </select>
        
        <tr>
        <td>Prix:<td><input type="number" min="0" name="prix" step="any" value="">
        
        <tr>
        <td>Poids (Kg):<td><input type="number" min="0" name="poids" step="any" value="" required>
        
        <tr>
        <td>Statut:<td><select name="statut">
            <option value="disponible" selected>disponible</option>
            <option value="à réparer">à réparer</option>
            <option value="réservé">réservé</option>
            <option value="vendu">vendu</option>
        </select>
        
        <tr>
        <td>Visible:<td><input type="radio" name="visible" value="0"><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="visible" value="1" checked><label for="1"> oui</label>
        </select>
        
        <tr>
        <td>Observations:<td><textarea name="observations"></textarea>



    </table>

    <input type="hidden" name="newArticleSubmitted" id="newArticleSubmitted" value="newArticleSubmitted">
    <button type="submit" name="newArticleSubmit" id="newArticleSubmit" class="right" >Créer l'article</button>

</form>


<?php
if($footer){
    echo '</div><!-- end admin container -->'.PHP_EOL;
	require(ROOT.'/_code/php/admin/admin_footer.php');
	echo '
	</body>
	</html>';
}
?>
