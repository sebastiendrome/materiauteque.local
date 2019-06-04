<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}
if( !isset($title) ){
	$title = ' : Supprimer un Article';
	require(ROOT.'_code/php/doctype.php');
	echo '<!-- admin css -->
	<link href="/_code/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">'.PHP_EOL;
	echo '<!-- adminHeader start -->
	<div class="adminHeader">
	<h1><a href="/admin/">Admin</a>'.$title.' </h1>';
	echo '</div><!-- adminHeader end -->'.PHP_EOL;

	echo '<!-- start admin container -->
	<div id="adminContainer">'.PHP_EOL;

	$footer = true;
}else{
	$footer = false;
}

// process form POST data
if( isset($_POST['deleteArticleSubmitted']) ){
	$article_id = $_POST['delete_id'];
}elseif( isset($_GET['article_id']) ){
	$article_id = urldecode($_GET['article_id']);
}

if( isset($article_id) && !empty($article_id) ){
	$table = 'articles';
	$result = delete_item($table, $article_id);
	echo $result;
}
?>

<!--
<form name="deleteArticle" id="deleteArticle" action="" method="post" style="display:inline-block;">

<h3>Supprimer un article:</h3>

	Article ID: <input type="number" name="delete_id" id="delete_id" step="any" value="">

	<input type="hidden" name="deleteArticleSubmitted" id="deleteArticleSubmitted" value="deleteArticleSubmitted">
	<button type="submit" name="deleteArticleSubmit" id="deleteArticleSubmit" class="right">Supprimer</button>
</form>
-->

<?php
if($footer){
	echo '</div><!-- end admin container -->'.PHP_EOL;
	require(ROOT.'/_code/php/admin/admin_footer.php');
	echo '
	</body>
	</html>';
}
?>