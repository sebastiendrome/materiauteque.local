<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

// set $article_form_context for edit_article_table.php vars
$article_form_context = 'edit';

if( isset($_GET['article_id']) && $_GET['article_id'] !== 'undefined' && !empty($_GET['article_id']) ){
	$article_id = urldecode($_GET['article_id']);
	$_SESSION['article_id'] = $article_id;
}elseif( isset($_SESSION['article_id']) ){
	$article_id = $_SESSION['article_id'];
}

/*
echo '<pre>'.__FILE__.PHP_EOL;
echo '$article_id = '.$article_id;
echo '</pre>';
*/

// article ID:
if( !isset($article_id) || empty($article_id) ){
	unset($_SESSION['article_id']);
	//exit;
}else{
	$item_data = get_item_data($article_id); 
	?>

<?php
/*
echo '<pre>'.__FILE__.PHP_EOL;
print_r($item_data);
echo '</pre>';
//exit;
*/


// process form POST data
if( isset($_POST['editArticleSubmitted']) ){
	// unset all item data (we must use the $_POST vars instead), except for images
	foreach($item_data as $k => $v){
		if( $k !== 'images' ){
			unset($item_data[$k]);
		}
	}
	// new array of data from POST
	foreach($_POST as $k => $v){
		if($k !== 'editArticleSubmitted' && $k !== 'editArticleSubmit' && $k !== 'types' && $k !== 'sizes'){
			$item_data[$k] = trim($v);
		}
	}
	$message = update_table('articles', $article_id, $item_data);

}elseif( isset($_GET['upload_result']) ){
	$message = urldecode($_GET['upload_result']);
}elseif( isset($_GET['message']) ){
	$message = urldecode($_GET['message']);
}

// result message passed via query string
if( isset($message) && !empty($message) ){
	$message = str_replace(array('0|', '1|', '2|'), array('<p class="error">', '<p class="success">', '<p class="note">'), $message).'</p>';
	$message_script = '<script type="text/javascript">showDone();</script>';
}else{
	$message = $message_script = '';
}

if( !isset($title) ){
	$title = ' Modifier un Article';
	require(ROOT.'_code/php/doctype.php');
	echo '<!-- admin css -->
	<link href="/_code/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">'.PHP_EOL;

	echo '<div id="working"><div class="note">working...</div></div>';
	echo '<div id="done">'.$message.'</div>';

	echo '<!-- adminHeader start -->
	<div class="adminHeader">
	<h1><a href="/admin" class="admin">Admin <span class="home">&#8962;</span></a>'.$title.' </h1>'.PHP_EOL;
	echo ' <a href="javascript:;" class="button vente showModal" rel="prixVenteModal?article_id='.$article_id.'">€ Vendre cet article</a> ';
	echo ' <a href="/_code/php/forms/scinderArticle.php?article_id='.$article_id.'" class="button" rel="scinderArticle.php?article_id='.$article_id.'">Scinder l\'article en 2</a> ';
	//scinderArticle.php
	echo ' <a href="javascript:;" class="showModal button remove" rel="deleteArticleModal?article_id='.$article_id.'">Supprimer cet article</a>';
	echo '</div><!-- adminHeader end -->'.PHP_EOL;

	echo '<!-- start admin container -->
	<div id="adminContainer">'.PHP_EOL;
	
	$footer = true;
}else{
	echo $message;
	$footer = false;
}
?>

<form name="newArticle" id="newArticle" action="?article_id=<?php echo $article_id; ?>" method="post" style="display:inline-block; float:left; margin-right:10px;">


	<?php
	require(ROOT.'_code/php/forms/edit_article_table.php');
	?>

	<input type="hidden" name="editArticleSubmitted" id="editArticleSubmitted" value="editArticleSubmitted">
	<button type="submit" name="editArticleSubmit" id="editArticleSubmit" class="right" >Modifier</button>

</form>

<?php require(ROOT.'/_code/php/forms/newArticleImages.php'); ?>



<?php
if($footer){
	echo '</div><!-- end admin container -->'.PHP_EOL;
	require(ROOT.'/_code/php/admin/admin_footer.php');
	echo $message_script;
	echo '</body></html>';
}else{
	echo $message_script;
}
?>

<?php } ?>

