<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

if( isset($_GET['id']) && !empty($_GET['id']) ){
	$panier_id = urldecode($_GET['id']);
	$_SESSION['panier_id'] = $panier_id;
}elseif( isset($_SESSION['panier_id']) ){
	$panier_id = $_SESSION['panier_id'];
}

/*
echo '<pre>'.__FILE__.PHP_EOL;
echo '$panier_id = '.$panier_id;
echo '</pre>';
*/

// article ID:
if( !isset($panier_id) || empty($panier_id) ){
	unset($_SESSION['panier_id']);
	//exit;
}else{
	$item_data = get_table('paniers', $where = 'id = '.$panier_id); 
	
/*
echo '<pre>'.__FILE__.PHP_EOL;
print_r($item_data);
echo '</pre>';
//exit;
*/

?>

<div class="modal">
	<a href="javascript:;" class="closeBut hideModal">&times</a>

<form name="newArticle" id="newArticle" action="?article_id=<?php echo $article_id; ?>" method="post" style="display:inline-block; margin-right:10px;">


	<?php
	//require(ROOT.'_code/php/forms/edit_panier_table.php');
	?>

	<input type="hidden" name="editPanierSubmitted" id="editPanierSubmitted" value="editPanierSubmitted">
	<button type="submit" name="editPanierSubmit" id="editPanierSubmit" class="right" >Modifier</button>

</form>

</div>

<?php } ?>

