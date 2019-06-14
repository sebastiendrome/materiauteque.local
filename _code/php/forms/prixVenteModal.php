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
$item_data =  get_item_data($id, 'statut_id, prix');

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
	<a href="javascript:;" class="annuler closeBut">&times;</a>

<?php
// if article is already 'vendu', just show message
if($previous_statut_id == '4'){
	echo '<h2 class="warning">Cet article a déjà été vendu...</h2>';
}else{
	// if not, show form
?>

	<form name="prixDeVente" id="prixDeVente" action="/_code/php/admin/admin_ajax.php" method="post">
		<input type="hidden" name="id" value="<?php echo $id; ?>">
		<input type="hidden" name="previous_id" value="<?php echo $previous_statut_id; ?>">
		<input type="hidden" name="prix" value="<?php echo $prix; ?>">
		<h3>Prix de vente: <input type="text" style="width:60px; min-width:60px; text-align:right;" name="prix_vente" value="<?php echo str_replace('.' ,',' ,$prix); ?>" placeholder="0,00"> €</h3>
		<input type="checkbox" id="payement_cheque" name="payement_cheque" value="2" style="margin-left:0;"> <label for="payement_cheque">Payement par chèque</label> 
		<h3><button type="submit" name="prixVenteSubmit" id="prixVenteSubmit" style="width:100%; margin-left:0;">Enregistrer la vente</button></h3>
		<!--<a href="javascript:;" class="annuler button left hideModal">Annuler</a>-->

		<p>&nbsp;</p>
		<h3 style="text-align:center; margin:20px 0; clear:both;"> —— OU —— </h3>

	<span style="color:#383838; font-weight:bold; font-size:larger;">Vente partielle:</span> 
	<a href="/_code/php/forms/scinderArticle.php?article_id=<?php echo $id; ?>" class="button left">Scinder l'article en deux</a>

	<!--
	<div style="border-top:1px solid #ddd; margin:20px 0;"></div>
	<a href="javascript:;" class="button annuler left">Annuler</a>

	</div>
	-->
	</form>

</div>
<!-- update to vendu, add prix de vente END -->

	
<script type="text/javascript">
// when 'annuler' or the div.overlay are clicked, put the select input back to its previous state
$('body').on('click', 'a.annuler, div.overlay', function(e){
	e.preventDefault();
	$tr = $("table.data[data-id=<?php echo $table; ?>]").find("tr[data-id=<?php echo $id; ?>]");
	if( $tr ){
		var $select = $tr.find('select[name=statut_id]');
		if( $select ){
			var prev = $('form#prixDeVente input[name=previous_id]').val();
			//alert('current: '+$select.val()+', previous: '+prev);
			$select.val(prev);
		}
	}
	hideModal($(this));
});

</script>

<?php } ?>
