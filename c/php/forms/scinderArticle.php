<?php
if( !defined("ROOT") ){
	if(!defined("ROOT")){
	$code = basename( dirname(__FILE__, 3) );
	require preg_replace('/\/'.$code.'\/.*$/', '/'.$code.'/php/first_include.php', __FILE__);
}
	require(ROOT.'c/php/admin/not_logged_in.php');
	require(ROOT.'c/php/admin/admin_functions.php');
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
	$part_1 = 'Partie <u>restante</u>';
	$part_2 = 'Partie <u>à vendre</u>';
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
		if($k !== 'formSubmitted' && $k !== 'editArticleSubmit'){
			$v = str_replace('"', '&quot;', $v);
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
	require(ROOT.'c/php/doctype.php');
	echo '<!-- admin css -->
	<link href="'.REL.'c/css/admincss.css?v='.$version.'" rel="stylesheet" type="text/css">'.PHP_EOL;

	echo '<div id="working"><div class="note">working...</div></div>';
	echo '<div id="done">'.$message.'</div>';

	echo '<!-- adminHeader start -->
	<div class="adminHeader">
	<h1 style="margin-right:0;"><a href="'.REL.'c/admin/" class="admin">Admin <span class="home">&#8962;</span></a></h1> <a href="'.REL.'c/admin/articles.php" class="button edit articles artSH" style="margin-right:20px;">Articles</a> <h2>'.$title.' </h2>'.PHP_EOL;
	echo '</div><!-- adminHeader end -->'.PHP_EOL;

	include(ROOT.'c/php/forms/paniersModal.php');

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
	$item_data = get_article_data($article_id); 
	$item_data_copy = $item_data;
	?>

<!-- formsContainer start -->
<div id="formsContainer" style="display:inline-block;">


<form name="article_original" id="original" action="?article_id=<?php echo $article_id; ?>" method="post">
<h3><?php echo $part_1; ?></h3>
	
	<?php
	$article_form_context = $context_1;
	require(ROOT.'c/php/forms/edit_article_table.php');
	?>

	<input type="hidden" name="id" value="<?php echo $article_id; ?>">
	</form>




<!-- COPY -->



<form name="article_copy" id="copy" action="?article_id=<?php echo $article_id; ?>" method="post"<?php echo $vente_style; ?>>
	<h3><?php echo $part_2; ?></h3>

	<?php
	$item_data = $item_data_copy;
	$article_form_context = $context_2;
	require(ROOT.'c/php/forms/edit_article_table.php');
	?>

	<!-- the following div is used to load vente-paniers.php in the context of scinderArticle.php, when the scission of an article to be sold has been succesfully made. vente-paniers.php allows us to finalize the sale, by choosing between 'Vendre directement' & 'Ajouter au panier'
	-->
	<div id="vpLoader" style="background-color:#f5f5f5;"></div>

</form>






<div class="clearBoth"></div>

<div style="text-align:center;">
	<form name="dualForm" id="dualForm" action="" method="post" style="display:block;">
	<input type="hidden" name="scinderFormSubmitted" id="scinderFormSubmitted" value="submitted">
	<!--<a href="" class="button">Annuler</a>-->
	<button type="submit" name="editArticleSubmit" id="editArticleSubmit" style="margin-left:0; width: calc( 100% - 22px );">Enregistrer les modifications<?php if( isset($_GET['vendre']) ){echo ' + vendre la partie de droite';} ?></button>
	</form>
</div>

</div><!-- end formsContainer -->



<?php
if($footer){
	echo '</div><!-- end admin container -->'.PHP_EOL;
	require(ROOT.'/c/php/admin/admin_footer.php');
	echo $message_script;
}
?>
<script type="text/javascript">
$('form#original, form#copy').on("submit", function(e){
	e.preventDefault();
});

$("form#dualForm").on("submit", function(e){
	e.preventDefault();

	var error = false;
	// make sure all required fields have a value (original article)
	$required_1 = $('form#original').find('input, select, textarea').filter('[required]');
	$required_1.each(function( index ) {
		if( !$(this).val().length ){
			error = true;
			$(this).focus();
			return false; // break the loop
		}
	});

	if(error){
		return false; // stop the execution of the function
	}

	// make sure all required fields have a value (article to copy)
	$required_2 = $('form#copy').find('input, select, textarea').filter('[required]');
	$required_2.each(function( index ) {
		if( !$(this).val().length ){
			error = true;
			$(this).focus();
			return false; // break the loop
		}
	});
	
	if(error){
		return false; // stop the execution of the function
	}
	
	var original = $('form#original').serializeArray();
	var copy = $('form#copy').serializeArray();
	
	// debug
	/*
	console.log( original );
	console.log('--------------------------------------------------------------');
	console.log(copy);
	*/
	$.ajax({
		url: rel+'c/php/admin/admin_ajax.php',
		type: "POST",
		data: {original, copy},
		
		// on success show message 
		// db_function.php scinde_article() returns 2 results separated with <br>, so split them into 2 to format each one...
		success : function(msg) {
			var error = false;
			var msg_1, msg_2;
			var ms_array = msg.split("<br>");
			var err_1 = ms_array[0].substr(0, 2);
			if(err_1 == '0|'){
				error = true;
				msg_1 = '<p class="error">'+ms_array[0].substr(2)+'</p>';
			}else if(err_1 == '1|'){
				msg_1 = '<p class="success">'+ms_array[0].substr(2)+'</p>';
				// match new article ID from result message
				var newId = ms_array[0].match(/\d*$/);
				//alert(newId);
			}else if(err_1 == '2|'){
				msg_1 = '<p class="note">'+ms_array[0].substr(2)+'</p>';
			}
			var err_2 = ms_array[1].substr(0, 2);
			if(err_2 == '0|'){
				error = true;
				msg_2 = '<p class="error">'+ms_array[1].substr(2)+'</p>';
			}else if(err_2 == '1|'){
				msg_2 = '<p class="success">'+ms_array[1].substr(2)+'</p>';
			}else if(err_2 == '2|'){
				msg_2 = '<p class="note">'+ms_array[1].substr(2)+'</p>';
			}

			/* we need to know if
			we're just duplicating an article
			or 
			duplicating an article FOR SELLING IT
			If we're duplicating FOR SELLING, we need to know 
				- the ID of the article to sale
				- display vente-paniers.php
			*/
			<?php
			if( isset($_GET['vendre']) ){ // we're selling, show vente-paniers.php, perpend new article creation message ('Nouvel article crée, ID:XXXX')
			?>
				if(!error){
					// now we need to display a forms that will allow us to sell the new article
					$('form#original').remove();
					$('form#dualForm').remove();
					$('form#copy h3').after(msg_1);
					$('form#copy').append('<input type="hidden" name="old_poids" value="0">');
					// update article id in form!
					$('form#copy input[name="id"]').val(newId);
					$('form#copy table.editArticle').hide();
					$('form#copy div#vpLoader').load(rel+'c/php/forms/vente-paniers.php', function(){
						$(this).css('padding', '10px');
					});
				}else{
					$('#formsContainer').html(msg_1+msg_2);
				}
			<?php
			}else{ // we're not
			?>
			$('#formsContainer').html(msg_1+msg_2);
			<?php
			}
			?>
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