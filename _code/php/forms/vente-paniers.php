<?php
// 
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}
// are we in index.php or ventes.php? js code for identifying the clicked buttons is different
if( strstr($_SERVER['REQUEST_URI'], 'forms/ventes.php') ){
	$direct_submit = 'newArticleDirectVenteSubmit';
	$ajout_submit = 'newArticleAjoutPanierSubmit';
}else{
	$direct_submit = 'directeVenteSubmit';
	$ajout_submit = 'ajoutPanierSubmit';
}

?>

<div style="text-align:center;">
	<a href="javascript:;" id="directVente" class="button">Vendre directement</a> ou 
	<a href="javascript:;" id="ajoutPanier" class="button">Ajouter au panier</a>
</div>

<div class="clearBoth">
	
	<div id="paniers" style="display:none; padding-top:10px;">
		<p>
		<?php
		// avoid error 'Undefined variable: paniers' when loading via javascript into scinderArticle.php, after new article duplicate was created to be sold
		if( !isset($paniers) ){
			$paniers = get_table('paniers', 'statut_id=1', 'date DESC');
		}
		if( $paniers ){
			echo '<div style="padding:0 5px;">
			<span class="below">';
			$cp = count($paniers);
			if($cp > 1){
				echo 'paniers en cours';
			}else{
				echo 'panier en cours';
			}
			echo '</span>
			<!--<span style="float:right;"><a href="javascript:;" class="right" id="aNP">+ nouveau panier</a></span>-->';
			echo '</div>';
			echo '
			<select name="panier_id" id="paniers" style="background-image:none; padding-right:0 !important; line-height:25px;" size="'.(count($paniers)+1).'">';
			$i=0;
			foreach($paniers as $panier){
				if($i==0){$selected = ' selected';}else{$selected='';}
				echo '<option value="'.$panier['id'].'"'.$selected.'>'.$panier['nom'].'</option>';
				$i=1;
			}
			echo '<option value="">+ nouveau panier...</option>';
			echo '</select>';
			$hide_panierNom = ' display:none;';
			$disable_submit = '';
		}else{
			echo '<select name="panier_id" id="paniers" style="display:none;">
			<option value="">+ nouveau panier...</option>
			</select>';
			$hide_panierNom = '';
			$disable_submit = ' disabled';
		}
		?>
		</p>
		<p id="pPN" style="padding:0 5px;<?php echo $hide_panierNom; ?>">Nom du panier:<input type="text" name="panierNom" id="panierNom" placeholder="ex: éric lavabo" style="max-width:auto; min-width:250px; width:auto;"></p>
		<button type="submit" name="<?php echo $ajout_submit; ?>" id="<?php echo $ajout_submit; ?>" style="width:100%; margin-left:0;"<?php echo $disable_submit; ?>>Ajouter</button>

	</div>

	<div id="direct" style="display:none; padding-top:10px;">
		<p>Prix: <input type="number" min="0" step="any" style="width:60px; min-width:60px; text-align:right;" name="prix" id="prixVente" value="<?php if( isset($item_data['prix']) ){echo $item_data['prix']; } ?>" placeholder="0,00" required> €
		<input type="checkbox" id="paiement_id" name="paiement_id" value="2" style="margin-left:20px;"> <label for="paiement_id">Paiement par chèque</label></p>

		<button type="submit" name="<?php echo $direct_submit; ?>" id="<?php echo $direct_submit; ?>" class="vente" style="width:100%; margin-left:0;"<?php if( !isset($item_data['prix']) || $item_data['prix']<= 0){echo ' disabled';} ?>>Enregistrer la vente</button>
	</div>
	
</div>
