<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

// set $article_form_context for edit_article_table.php vars
$context_1 = $context_2 = 'scinder'; // = $article_form_context for each form
$pour_la_vente = $vente_style = '';
$part_1 = 'Partie 1 (Original)';
$part_2 = 'Partie 2 (Copie)';
if( isset($_GET['vendre']) ){
	$context_2 = 'vente'; // $article_form_context for 2nd form
	$pour_la_vente = ' pour la vente';
	$vente_style = ' style="background-color:rgb(242,202,58);"';
	$part_1 = 'Partie <u>Restante</u>';
	$part_2 = 'Partie <u>Vendue</u>';
}

if( isset($_GET['article_id']) ){
	$article_id = urldecode($_GET['article_id']);
	$_SESSION['article_id'] = $article_id;
}elseif( isset($_SESSION['article_id']) ){
	$article_id = $_SESSION['article_id'];
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

// result message passed via query string
if( isset($message) && !empty($message) ){
	$message = str_replace(array('0|', '1|', '2|'), array('<p class="error">', '<p class="success">', '<p class="note">'), $message).'</p>';
	$message_script = '<script type="text/javascript">showDone();</script>';
}else{
	$message = $message_script = '';
}

if( !isset($title) ){
	$title = ' Scinder un article en 2'.$pour_la_vente;
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

// article ID:
if( !isset($article_id) || empty($article_id) ){
	unset($_SESSION['article_id']);
	//exit;
}else{
	$item_data = get_item_data($article_id); 
	$item_data_copy = $item_data;
	?>

<!-- formsContainer start -->
<div id="formsContainer" style="display:inline-block;">


<form name="article_original" id="original" action="?article_id=<?php echo $article_id; ?>" method="post">
<h3><?php echo $part_1; ?></h3>
	
	<?php
	$article_form_context = $context_1;
	require(ROOT.'_code/php/forms/edit_article_table.php');
	?>

	<input type="hidden" name="id" value="<?php echo $article_id; ?>">
	</form>




<!-- COPY -->



<form name="article_copy" id="copy" action="?article_id=<?php echo $article_id; ?>" method="post"<?php echo $vente_style; ?>>
	<h3><?php echo $part_2; ?></h3>

	<?php
	$item_data = $item_data_copy;
	$article_form_context = $context_2;
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
	echo $message_script;
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
		// db_function.php scinde_article() returns 2 results separated with <br>, so split them into 2 to format each one...
		success : function(msg) {
			var ms = msg.split("<br>");
			var msg_1; 
			var msg_2;
			var m_1 = ms[0].substr(0, 2);
			if(m_1 == '0|'){
				msg_1 = '<p class="error">'+ms[0].substr(2)+'</p>';
			}else if(m_1 == '1|'){
				msg_1 = '<p class="success">'+ms[0].substr(2)+'</p>';
			}else if(m_1 == '2|'){
				msg_1 = '<p class="note">'+ms[0].substr(2)+'</p>';
			}
			var m_2 = ms[1].substr(0, 2);
			if(m_2 == '0|'){
				msg_2 = '<p class="error">'+ms[1].substr(2)+'</p>';
			}else if(m_2 == '1|'){
				msg_2 = '<p class="success">'+ms[1].substr(2)+'</p>';
			}else if(m_2 == '2|'){
				msg_2 = '<p class="note">'+ms[1].substr(2)+'</p>';
			}
			$('#formsContainer').html(msg_2+msg_1);
			return true;
		}
	});
});
</script>

<?php
if($footer){
	echo '</body></html>';
}else{
	echo $message_script;
}
?>
<?php } 
?>