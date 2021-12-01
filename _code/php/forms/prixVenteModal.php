<?php
// upload file form
// used inline or loaded via ajax, so check for necessary vars and require files accordingly
if( !defined("ROOT") ){
	require('../../../_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}
if( isset($_GET['article_id']) ){
	$id = urldecode($_GET['article_id']);
}
if( !isset($id) || empty($id) ){
	exit;
}

// we'll need to know these fields for item
$item_data =  get_article_data($id, 'titre, statut_id, prix, poids, vrac');


// get the suggested 'prix' of the article, we'll pre-fill the 'prix_vente' input with it
if( isset($_GET['prix']) && $_GET['prix']!=='undefined'){
	$prix = urldecode($_GET['prix']);
}else{
	$prix = $item_data['prix'];
}

// used for javascript at end of file
$table = 'articles';

// make sure we know the select input value previous to the change, so we can change it back if action is aborted
if( isset($_GET['previous_id']) && !empty($_GET['previous_id']) && $_GET['previous_id']!=='undefined' && $_GET['previous_id']!=='null'){
	$previous_statut_id = $_GET['previous_id'];
}else{
	$previous_statut_id = $item_data['statut_id'];
}
?>


<!-- update to vendu, add prix de vente START -->
<div class="modal" id="prixVenteModal">
	<a href="javascript:;" class="annuler closeBut hideModal">&times;</a>

<?php
// if article is already 'vendu', just show message
if($previous_statut_id == name_to_id('vendu', 'statut') ){
	echo '<form name="prixDeVente" id="prixDeVente" action="'.REL.'_code/php/admin/admin_ajax.php" method="post" style="margin:0 !important;">
	<h3 class="warning">Cet article a déjà été vendu.</h3>
	<input type="hidden" name="previous_id" value="'.$previous_statut_id.'">
	</form>
	</div>';
}else{
	// if not, show form
	$images_array = get_article_images($id, '_M');
	if(!empty($images_array)){
		$img = $images_array[0];
		$class = "articleVente";
	}else{
		$class = 'articleVente noImg';
		$img = '';
	}
	?>
	<form name="prixDeVente" id="prixDeVente" action="<?php echo REL; ?>_code/php/admin/admin_ajax.php" method="post">
	<?php 
	echo '<div class="'.$class.'" style="background-image:url('.REL.$img.');">
	<h3>'.$item_data['titre'].'</h3>
	</div>'; ?>
		<input type="hidden" name="id" value="<?php echo $id; ?>">
		<input type="hidden" name="vrac" value="<?php echo $item_data['vrac']; ?>">
		<input type="hidden" name="old_poids" value="<?php echo $item_data['poids']; ?>">
		<input type="hidden" name="old_prix" value="<?php echo $item_data['prix']; ?>">
		<input type="hidden" name="titre" value="<?php echo $item_data['titre']; ?>">
		
		<p>
		Poids:
		<input type="number" step="any" min="0" class="weight" style="width:60px; min-width:60px; text-align:right;" name="poids" value="<?php echo $item_data['poids']; ?>" placeholder="0,000" required> Kg</p>

		<div id="vpLoader">
		<?php require(ROOT.'_code/php/forms/vente-paniers.php'); ?>
		</div>

		
		<?php if($item_data['vrac'] == 0){ ?>
			<p>&nbsp;</p>
			<h3 style="text-align:center; margin:20px 0; clear:both;"> —— OU —— </h3>

			<span style="color:#383838; font-weight:bold; font-size:larger;">Vente partielle:</span> 
			<a href="<?php echo REL; ?>_code/php/forms/scinderArticle.php?article_id=<?php echo $id; ?>&vendre" class="button left scinder">Scinder l'article en 2</a>
		<?php } ?>
		

	</form>

</div>
<!-- update to vendu, add prix de vente END -->


<?php } ?>
