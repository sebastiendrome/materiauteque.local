<?php 
if( !isset($item_data) ){
	$item_data = array();
}
// $article_form_context will differentiqte between search, edit or create article
// edit, search, new, vente, scinder
if( !isset($article_form_context) ){
	//echo '<pre>Erreur! Pas de contexte...</pre>';
	$article_form_context = '';
}

$statut_array = get_table('statut'); // get contents of statut table ('id, nom)
// remove statut 'vendu' from array, only if not in search mode, and if article statut is not vendu
if($article_form_context !== 'search' && ( isset($item_data['statut_id']) && id_to_name($item_data['statut_id'], 'statut') !== 'vendu' ) ){
	foreach($statut_array as $k => $v){
		if( $v['nom'] == 'vendu' ){
			unset($statut_array[$k]);
			break;
		}
	}
}

// set required, visible and editable fields, depending on article_form_context
if($article_form_context == 'search'){
	$required = array(); // nothing is required

}elseif($article_form_context == 'vente'){
	$required = array(/*'titre', */'categories_id', 'matieres_id', 'poids');

}else{
	$required = array(/*'titre', */'categories_id', 'matieres_id', 'poids'); // default
}

// set autofocus on first field (title)
if( $article_form_context == 'new' && !isset($_GET['upload_result']) ){
	$autofocus = ' autofocus';
}else{
	$autofocus = '';
}

// make sure we have the categories and matieres for displaying the select options
if(!isset($categories)){
	//$categories = get_hierarchy_array('categories');
	$categories = get_parents('categories');
	/*echo '<pre>';
	print_r($categories);
	echo '</pre>';*/
}
if(!isset($matieres)){
	$matieres = get_parents('matieres');
}

/** !!!!!!!! this gets 'Participations id for showing/hiding Sous Catégories select menu */
$participations_id = name_to_id('Participations', 'categories');
/** !!!!!!!! this gets matieres id for "Autre", for selecting "Autre" if "Participations" is selected as Categories */
$matiere_autre_id = name_to_id('Autre', 'matieres');
//$matiere_autre_id = name_to_id('(hors matériaux)', 'matieres'); // matériauthèque

if( isset($item_data['id']) && !empty($item_data['id']) ){
	echo '<input type="hidden" name="id" value="'.$item_data['id'].'">';
}
?>

<table class="editArticle" data-id="articles" style="min-width:450px;">



	<tr>
		<td style="min-width:120px;"><!--<h3>-->Titre:<!--</h3>--><td><!--<h3>--><input type="text" name="titre" value="<?= $item_data['titre'] ?? '' ?>"<?php echo in_array('titre', $required) ? " required" : ""; ?><?php echo $autofocus; ?> placeholder="facultatif"><!--</h3>-->

		<?php
		if($article_form_context !== 'vente'){
		?>
		<tr>
		<td>Descriptif:<td><textarea name="descriptif"<?php echo in_array('descriptif', $required) ? " required" : ""; ?>><?= $item_data['descriptif'] ?? '' ?></textarea>
		<?php } ?>

		<!--
		<tr>
		<td>Vrac:<td><input type="radio" name="vrac" value="0"<?php if((!isset($item_data['vrac']) && $article_form_context !== 'search') || (isset($item_data['vrac']) && $item_data['vrac'] == 0)){echo ' checked';}?>><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="vrac" value="1"<?php if(isset($item_data['vrac']) && $item_data['vrac'] == 1){echo ' checked';}?>><label for="1"> oui</label>
		-->
		
		<!--
		<tr>
		<td>etiquette:<td><input type="text" name="etiquette" value="<?= $item_data['etiquette'] ?? '' ?>">
		-->

		<?php 
		if($article_form_context == 'search'){
		?>
		<!--
			<tr>
			<td colspan="2">Créé entre le: <input type="text" name="date[start]" id="startDate" value="<?php if(isset($key_val_pairs['date']['start'])){echo $key_val_pairs['date']['start'];} ?>" style="min-width:75px; width:100px;" placeholder="25-12-1970"> et le: <input type="text" name="date[end]" id="endDate" value="<?php if(isset($key_val_pairs['date']['end'])){echo $key_val_pairs['date']['end'];} ?>" style="min-width:75px; width:100px;" placeholder="<?php echo date('d-m-Y'); ?>"></td> 
		-->
		<?php 
		}
		?>

<!-- !!!!!!!!
 ci-dessous: quand $(this).val()==$participations_id, matières select menu selectionne l'option "Autre", puis poids = '0'; sinon, matières select menu selectionne "Choisir...", et poids=''. 
-->
		<tr>
		<td>Catégorie:<td>
		<select name="categories_id"<?php echo in_array('categories_id', $required) ? " required" : ""; ?> onchange="if($(this).val()=='<?php echo $participations_id; ?>'){$('input[name=poids]').val('0');$('#matieres_id option[value=<?php echo $matiere_autre_id; ?>]').prop('selected', true);}else{$('input[name=poids]').val('');$('#matieres_id option:eq(0)').prop('selected', true);}">
			<?php
			$options = '';
			/*if( !isset($item_data['categories_id']) ){
				if($article_form_context == 'search'){
					$options .= '<option value="">Toutes catégories</option>';
				}else{
					$options .= '<option value="">Choisir...</option>';
				}
			}else{
				$sous_cats = get_children('categories', $item_data['categories_id']);
			}*/
			foreach($categories as $cat){
				$selected = '';
				if( isset($item_data['categories_id']) ){
					if($item_data['categories_id'] == $cat['id']){
						$selected = ' selected';
					}
				}elseif($cat['nom'] == 'Vente'){ // 'ventes matériauthèques' pour la matériauthèque
					$selected = ' selected';
				}
				$options .= '<option value="'.$cat['id'].'"'.$selected.'>'.$cat['nom'].'</option>';
			}
			echo $options;
			?>
		</select>

		<?php 
		if(isset($sous_cats)){ 
			$sous_cats_enabled = '';
		}else{
			$sous_cats_enabled = ' disabled';
			$sous_cats = array();
		}	
		?>

		
		<tr id="sousCatTR">
		<td>Sous-catégorie:<td>
		<select name="sous_categories_id"<?php echo in_array('sous_categories_id', $required) ? " required" : ""; ?><?php echo $sous_cats_enabled; ?>>
			<?php
			$options = '';
			if( !isset($item_data['sous_categories_id']) ){
				if($article_form_context == 'search'){
					$options .= '<option value="">Toutes catégories</option>';
				}else{
					$options .= '<option value="">Choisir...</option>';
				}
			}
			if( !empty($sous_cats) ){
				foreach($sous_cats as $s_cat){
					$selected = '';
					if( isset($item_data['categories_id']) ){
						if($item_data['categories_id'] == $s_cat['id']){
							$selected = ' selected';
						}
					}
					$options .= '<option value="'.$s_cat['id'].'"'.$selected.'>'.$s_cat['nom'].'</option>';
				}
			}
			echo $options;
			?>
		</select>

		<?php
		if($article_form_context !== 'search'){ 
		?>
		<tr id="matiereTR">
		<td>Matière:<td>
		<select id="matieres_id" name="matieres_id"<?php echo in_array('matieres_id', $required) ? " required" : ""; ?>>
			<?php
			$options = '';
			if( !isset($item_data['matieres_id']) ){
				if($article_form_context == 'search'){
					$options .= '<option value="">Toutes matières</option>';
				}else{
					$options .= '<option value="">Choisir...</option>';
				}
			}else{
				$sous_mats = get_children('matieres', $item_data['matieres_id']);
			}
			foreach($matieres as $mat){
				$selected = '';
				if( isset($item_data['matieres_id']) ){
					if($item_data['matieres_id'] == $mat['id']){
						$selected = ' selected';
					}
				}
				$options .= '<option value="'.$mat['id'].'"'.$selected.'>'.$mat['nom'].'</option>';
			}
			echo $options;
			?>
		</select>
		


		<?php
		if(isset($sous_mats)){ 
			$sous_mats_enabled = '';
		}else{
			$sous_mats_enabled = ' disabled';
			$sous_mats = array();
		}
		?>
		<!--
		<tr>
		<td>Sous-matière:<td>
		<select name="sous_matieres_id"<?php echo in_array('sous_matiere_id', $required) ? " required" : ""; ?><?php echo $sous_mats_enabled; ?>>
			<?php
			$options = '';
			if( !isset($item_data['sous_matieres_id']) ){
				if($article_form_context == 'search'){
					$options .= '<option value="">Toutes matières</option>';
				}else{
					$options .= '<option value="">Choisir...</option>';
				}
			}
			if( !empty($sous_mats) ){
				foreach($sous_mats as $s_mat){
					$selected = '';
					if( isset($item_data['sous_matieres_id']) ){
						if($item_data['sous_matieres_id'] == $s_mat['id']){
							$selected = ' selected';
						}
					}
					$options .= '<option value="'.$s_mat['id'].'"'.$selected.'>'.$s_mat['nom'].'</option>';
				}
			}
			echo $options;
			?>
		</select>
		-->

		<tr id="poidsTR">
		<td>Poids (Kg):<td><input type="number" step="any" min="0" class="weight" name="poids" value="<?= $item_data['poids'] ?? '' ?>"<?php echo in_array('poids', $required) ? " required" : ""; ?>>
		
		<?php
		if($article_form_context !== 'vente'){
		?>
		<tr>
		<td>Prix :<td><input type="number" step="any" min="0" class="currency" name="prix" value="<?= $item_data['prix'] ?? '' ?>"<?php echo in_array('prix', $required) ? " required" : ""; ?>>
		<?php 
		} 
		?>

		<?php 
		} // if($article_form_context !== 'search')
		?>

		<?php
		// if context = 'vente', let's not show the statut_id to the user, and set it to vendu (or réservé if in of scinderArticle.php)
		if($article_form_context == 'vente'){
			if( isset($_GET['vendre']) ){ // we're in scinderArticle.php and the statut_id should be 'réservé': it is about to be sold, either directly or added to an existing panier
				$st_id = name_to_id('réservé', 'statut');
			}else{
				$st_id = name_to_id('vendu', 'statut');
			}
			echo '<input type="hidden" name="statut_id" value="'.$st_id.'">';
			if( isset($item_data['prix']) ){
				$item_prix = $item_data['prix'];
			}else{
				$item_prix = '';
			}
			echo '<input type="hidden" name="prix" value="'.$item_prix.'">';
		}else{

			echo '<tr>
			<td>Statut:<td>
			<select name="statut_id">';

			$options = '';
			if($article_form_context == 'search'){
				$options .= '<option value="">Tous statuts</option>';
			}
			foreach($statut_array as $st){ // loop through statut_array to output the options
				$selected = '';
				if( isset($item_data['statut_id']) ){
					if($st['id'] == $item_data['statut_id']){
						$selected = ' selected';
					}
				}
				$options .= '<option value="'.$st['id'].'"'.$selected.'>'.$st['nom'].'</option>';
			}
			echo $options;
			echo '</select>';
		}
		?>
		
		
		<?php
		if($article_form_context !== 'search'){
			if($article_form_context == 'vente'){
				$display_none = ' style="display:none;"';
				
				$visibleZero_checked = ' checked';
				$visibleOne_checked = '';
			}else{
				$display_none = '';
				
				if($article_form_context == 'new'){
					$visibleZero_checked = '';
					$visibleOne_checked = ' checked';
				}else{
					if($item_data['visible'] == '0'){
						$visibleZero_checked = ' checked';
						$visibleOne_checked = '';
					}else{
						$visibleZero_checked = '';
						$visibleOne_checked = ' checked';
					}
				}
			}

			?>
			<tr<?php echo $display_none; ?>>
			<td>Visible:
				<td><input type="radio" id="visibleZero" name="visible" value="0"<?php echo $visibleZero_checked; ?>><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="visibleOne" name="visible" value="1"<?php echo $visibleOne_checked; ?>><label for="1"> oui</label>
			</select>
		<?php } ?>
		
		<!--
		<tr>
		<td>Observations:<td><textarea name="observations"><?= $item_data['observations'] ?? '' ?></textarea>
		-->
	
	</table>