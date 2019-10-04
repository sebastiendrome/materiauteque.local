<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

// set $article_form_context for edit_article_table.php vars
$article_form_context = 'new';

// process form POST data
if( isset($_POST['newArticleSubmitted']) ){
	foreach($_POST as $k => $v){
		if($k !== 'newArticleSubmitted' && $k !== 'newArticleSubmit' && $k !== 'types' && $k !== 'sizes'){
			$new_item_data[$k] = trim($v);
		}
	}
	if($article_id = insert_new('articles', $new_item_data)){
		$_SESSION['article_id'] = $article_id;
		$new_item[0] = get_item_data($article_id);
		$items_table = items_table_output($new_item);
		$message = '1|Nouvel Article créé. ID: '.$article_id;
		$path = 'uploads/'.$article_id;
		
	}else{
		$message = '0|'.mysqli_error($db);
	}
}elseif( isset($_GET['upload_result'])){
	$new_item[0] = get_item_data($_SESSION['article_id']);
	$items_table = items_table_output($new_item);
	$message = urldecode($_GET['upload_result']);
	$path = 'uploads/'.$_SESSION['article_id'];
}
?>

<?php
// result message passed via query string
if( isset($message) && !empty($message) ){
	$message = str_replace(array('0|', '1|', '2|'), array('<p class="error">', '<p class="success">', '<p class="note">'), $message).'</p>';
	$message_script = '<script type="text/javascript">showDone();</script>';
}else{
	$message = $message_script = '';
}

if( !isset($title) ){
	$title = ' Nouvel Article';
	require(ROOT.'_code/php/doctype.php');
	echo '<!-- admin css -->
	<link href="/_code/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">'.PHP_EOL;

	echo '<div id="working"><div class="note">working...</div></div>';
	echo '<div id="done">'.$message.'</div>';

	echo '<!-- adminHeader start -->
	<div class="adminHeader">
	<h1><a href="/admin" class="admin">Admin <span class="home">&#8962;</span></a>'.$title.' </h1>'.PHP_EOL;
	echo '</div><!-- adminHeader end -->'.PHP_EOL;

	echo '<!-- start admin container -->
	<div id="adminContainer">'.PHP_EOL;
		
	$footer = true;
}else{
	echo $message;
	$footer = false;
}

$paniers = get_table('paniers', 'statut=0', 'date DESC');
include(ROOT.'_code/php/forms/paniersModal.php');

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
	echo $message_script;
	echo '</body></html>';
}else{
	echo $message_script;
}
?>
