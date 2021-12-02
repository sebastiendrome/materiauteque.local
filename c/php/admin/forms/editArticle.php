<?php
if(!defined("ROOT")){
	$code = basename( dirname(__FILE__, 4) );
	require preg_replace('/\/'.$code.'\/.*$/', '/'.$code.'/php/first_include.php', __FILE__);
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
	$item_data = get_article_data($article_id); 
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
		if($k !== 'editArticleSubmitted' && $k !== 'editArticleSubmit'){
			$v = str_replace('"', '&quot;', $v);
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
	require(ROOT.'c/php/doctype.php');
	echo '<!-- admin css -->
	<link href="'.REL.'c/css/admincss.css?v='.$version.'" rel="stylesheet" type="text/css">'.PHP_EOL;

	echo '<div id="working"><div class="note">working...</div></div>';
	echo '<div id="done">'.$message.'</div>';

	echo '<!-- adminHeader start -->
	<div class="adminHeader">
	<h1 style="margin-right:0;"><a href="'.REL.'c/admin/" class="admin">Admin <span class="home">&#8962;</span></a></h1> <a href="'.REL.'c/admin/articles.php" class="button edit articles artSH" style="margin-right:20px;">Articles</a> <h2>'.$title.' </h2>'.PHP_EOL;
	echo ' <a href="javascript:;" class="button vente showModal venSH" rel="prixVenteModal?article_id='.$article_id.'">€ Vendre cet article</a> ';
	echo ' <a href="'.REL.'c/php/admin/forms/scinderArticle.php?article_id='.$article_id.'" class="button scinder" rel="scinderArticle.php?article_id='.$article_id.'">Scinder l\'article en 2</a> ';
	//scinderArticle.php
	echo ' <a href="javascript:;" class="showModal button remove" rel="deleteArticleModal?article_id='.$article_id.'">Supprimer cet article</a>';
	echo '</div><!-- adminHeader end -->'.PHP_EOL;

	include(ROOT.'c/php/admin/forms/paniersModal.php');

	echo '<!-- start admin container -->
	<div id="adminContainer">'.PHP_EOL;

	$footer = true;
}else{
	echo $message;
	$footer = false;
}

?>

<div id="formsContainer">

<form name="newArticle" id="newArticle" action="?article_id=<?php echo $article_id; ?>" method="post" style="display:inline-block; float:left; margin-right:10px;">


	<?php
	require(ROOT.'c/php/admin/forms/edit_article_table.php');
	?>

	<input type="hidden" name="editArticleSubmitted" id="editArticleSubmitted" value="editArticleSubmitted">
	<button type="submit" name="editArticleSubmit" id="editArticleSubmit" class="right" >Modifier</button>

</form>


<?php require(ROOT.'/c/php/admin/forms/newArticleImages.php'); ?>

</div>

<?php
if($footer){
	echo '</div><!-- end admin container -->'.PHP_EOL;
	require(ROOT.'/c/php/admin/admin_footer.php');
	echo $message_script;
	echo '</body></html>';
}else{
	echo $message_script;
}
?>

<?php } ?>

