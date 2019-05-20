
<?php 
if(!isset($item_data)){
	$item_data = array();
}
if(!isset($context)){
	//echo '<pre>Erreur! Pas de contexte...</pre>';
	$context = '';
}
$required = array();
if($context !== 'search'){
	$required = array('titre', 'descriptif', 'categories_id', 'matieres_id', 'poids');
}
?>


<table>

	<tr>
        <td><h3>Titre:</h3><td><h3><input type="text" name="titre" value="<?= $item_data['titre'] ?? '' ?>"<?php echo in_array('titre', $required) ? " required" : ""; ?>></h3>

        <tr>
        <td>Descriptif:<td><textarea name="descriptif"<?php echo in_array('descriptif', $required) ? " required" : ""; ?>><?= $item_data['descriptif'] ?? '' ?></textarea>
        

        <tr>
        <td>Vrac:<td><input type="radio" name="vrac" value="0"<?php if((!isset($item_data['vrac']) && $context !== 'search') || (isset($item_data['vrac']) && $item_data['vrac'] == 0)){echo ' checked';}?>><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="vrac" value="1"<?php if(isset($item_data['vrac']) && $item_data['vrac'] == 1){echo ' checked';}?>><label for="1"> oui</label>
		
		<!--
        <tr>
        <td>etiquette:<td><input type="text" name="etiquette" value="<?= $item_data['etiquette'] ?? '' ?>">
		-->

		<?php 
		if($context == 'search'){
		?>
		<tr>
			<td colspan="2">Créé entre le: <input type="text" name="date[start]" id="startDate" value="<?php if(isset($key_val_pairs['date']['start'])){echo $key_val_pairs['date']['start'];} ?>" style="min-width:75px; width:100px;" placeholder="25-12-1970"> et le: <input type="text" name="date[end]" id="endDate" value="<?php if(isset($key_val_pairs['date']['end'])){echo $key_val_pairs['date']['end'];} ?>" style="min-width:75px; width:100px;" placeholder="<?php echo date('d-m-Y'); ?>"></td>
		<?php 
		}
		?>

		<tr>
		<td>Catégorie:<td><select name="categories_id"<?php echo in_array('categories_id', $required) ? " required" : ""; ?>>
			<?php
			$options = '';
			if( !isset($item_data['categories_id']) ){
				if($context == 'search'){
					$options .= '<option value="">Toutes catégories</option>';
				}else{
					$options .= '<option value="">Choisir...</option>';
				}
			}
            foreach($categories as $cat){
				$selected = '';
				if( isset($item_data['categories_id']) ){
					if($item_data['categories_id'] == $cat['id']){
						$selected = ' selected';
					}
				}
				$options .= '<option value="'.$cat['id'].'"'.$selected.'>'.$cat['nom'].'</option>';
			}
			echo $options;
			?>
        </select>
        
        <tr>
        <td>Matière:<td><select name="matieres_id"<?php echo in_array('matieres_id', $required) ? " required" : ""; ?>>
			<?php
			$options = '';
			if( !isset($item_data['matieres_id']) ){
				if($context == 'search'){
					$options .= '<option value="">Toutes matières</option>';
				}else{
					$options .= '<option value="">Choisir...</option>';
				}
			}
            foreach($matieres as $cat){
				$selected = '';
				if( isset($item_data['matieres_id']) ){
					if($item_data['matieres_id'] == $cat['id']){
						$selected = ' selected';
					}
				}
                $options .= '<option value="'.$cat['id'].'"'.$selected.'>'.$cat['nom'].'</option>';
			}
			echo $options;
            ?>
        </select>
        
        <tr>
        <td>Prix:<td><input type="number" min="0" name="prix" step="any" value="<?= $item_data['prix'] ?? '' ?>">
        
        <tr>
        <td>Poids (Kg):<td><input type="number" min="0" name="poids"  step="any" value="<?= $item_data['poids'] ?? '' ?>"<?php echo in_array('poids', $required) ? " required" : ""; ?>>
        
        <tr>
        <td>Statut:<td>
		<select name="statut_id">

		<?php
		$statut_array = get_table('statut'); // get contents of statut table ('id, nom)
		$options = '';
		if($context == 'search'){
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

		?>
        </select>

        <tr id="prixVente"<?php if(!isset($item_data['statut_id']) || (isset($item_data['statut_id']) && $item_data['statut_id'] !== 6)){echo ' style="display:none;"';} ?>>
        <td>Prix de vente:<td><input type="number" name="prix_vente" step="any" value="<?= $item_data['prix_vente'] ?? '' ?>">
        
        <tr>
        <td>Visible:<td><input type="radio" id="visibleZero" name="visible" value="0"<?php if(isset($item_data['visible']) && $item_data['visible'] == 0){echo ' checked';}?>><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="visibleOne" name="visible" value="1"<?php if((!isset($item_data['visible']) && $context !== 'search') || (isset($item_data['visible']) && $item_data['visible'] == 1)){echo ' checked';}?>><label for="1"> oui</label>
        </select>
        
        <tr>
        <td>Observations:<td><textarea name="observations"><?= $item_data['observations'] ?? '' ?></textarea>
    
    </table>