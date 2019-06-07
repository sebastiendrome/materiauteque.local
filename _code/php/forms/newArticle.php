<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

if( !isset($title) ){
	$title = ' Nouvel Article';
	require(ROOT.'_code/php/doctype.php');
	echo '<!-- admin css -->
	<link href="/_code/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">'.PHP_EOL;

	echo '<!-- adminHeader start -->
	<div class="adminHeader">
	<h1><a href="/admin" class="admin">Admin <span class="home">&#8962;</span></a>'.$title.' </h1>'.PHP_EOL;
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
		$new_item[0] = get_item_data($article_id);
		$items_table = items_table_output($new_item);
		$message = '<p class="success">Nouvel Article créé. ID: '.$article_id.'</p>';
		$path = 'uploads/'.$article_id;
		
	}else{
		$message = '<p class="error">'.mysqli_error($db).'</p>';
	}
}elseif( isset($_GET['upload_result'])){
	$titre_autofocus = false;
	$new_item[0] = get_item_data($_SESSION['article_id']);
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
	
	<?php
	require(ROOT.'_code/php/forms/edit_article_table.php');
	?>

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
