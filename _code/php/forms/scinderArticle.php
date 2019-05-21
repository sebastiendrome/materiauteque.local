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
	$item_data = get_item_data($article_id); 
	$item_data_copy = $item_data;
	?>

<?php

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
	
	<?php
	require(ROOT.'_code/php/forms/edit_article_table.php');
	?>

	<input type="hidden" name="id" value="<?php echo $article_id; ?>">
	</form>




<!-- COPY -->



<form name="article_copy" id="copy" action="?article_id=<?php echo $article_id; ?>" method="post">
	<h3>Partie 2 (Copie)</h3>

	<?php
	$item_data = $item_data_copy;
	require(ROOT.'_code/php/forms/edit_article_table.php');
	?>

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