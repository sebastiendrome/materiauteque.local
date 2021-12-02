<?php
if(!defined("ROOT")){
	$code = basename( dirname(__FILE__, 4) );
	require preg_replace('/\/'.$code.'\/.*$/', '/'.$code.'/php/first_include.php', __FILE__);
}

if( !isset($title) ){
	$title = ' Supprimer un Article';
	require(ROOT.'c/php/doctype.php');
	echo '<!-- admin css -->
	<link href="'.REL.'c/css/admincss.css?v='.$version.'" rel="stylesheet" type="text/css">'.PHP_EOL;
	echo '<!-- adminHeader start -->
	<div class="adminHeader">
	<h1><a href="'.REL.'admin" class="admin">Admin <span class="home">&#8962;</span></a>'.$title.' </h1>';
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
	$message = '<a class="button articles edit artSH" href="'.REL.'c/admin/articles.php">Articles</a> <a class="button vente edit venSH" href="'.REL.'c/admin/ventes.php">Ventes</a><br>'.str_replace(array('0|', '1|', '2|'), array('<p class="error">', '<p class="success">', '<p class="note">'), $result).'</p>';
	echo $message;
}
?>

<?php
if($footer){
	echo '</div><!-- end admin container -->'.PHP_EOL;
	require(ROOT.'/c/php/admin/admin_footer.php');
	echo '
	</body>
	</html>';
}
?>