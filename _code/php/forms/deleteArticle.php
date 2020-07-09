<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}
if( !isset($title) ){
	$title = ' Supprimer un Article';
	require(ROOT.'_code/php/doctype.php');
	echo '<!-- admin css -->
	<link href="/_code/css/admincss.css?v='.$version.'" rel="stylesheet" type="text/css">'.PHP_EOL;
	echo '<!-- adminHeader start -->
	<div class="adminHeader">
	<h1><a href="/admin" class="admin">Admin <span class="home">&#8962;</span></a>'.$title.' </h1>';
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
	$message = '<a class="button articles edit" href="/admin/articles.php">Articles</a> <a class="button vente edit" href="/admin/ventes.php">Ventes</a><br>'.str_replace(array('0|', '1|', '2|'), array('<p class="error">', '<p class="success">', '<p class="note">'), $result).'</p>';
	echo $message;
}
?>

<?php
if($footer){
	echo '</div><!-- end admin container -->'.PHP_EOL;
	require(ROOT.'/_code/php/admin/admin_footer.php');
	echo '
	</body>
	</html>';
}
?>