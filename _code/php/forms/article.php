
<table>
        
        <!--
        <tr>
        <td>Vrac:<td><input type="radio" name="vrac" value="0"><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="vrac" value="1"><label for="1"> oui</label>
        
        <tr style="display:none;">
        <td>etiquette:<td><input type="text" name="etiquette" value="">
        -->

        <tr>
            <td colspan="2">Créé entre le: <input type="text" name="date[start]" id="startDate" value="" style="min-width:75px; width:100px;" placeholder="25-12-1970"> et le: <input type="text" name="date[end]" id="endDate" value="" style="min-width:75px; width:100px;" placeholder="<?php echo date('d-m-Y'); ?>">

        <tr>
        <td>Catégorie:<td><select name="categories_id">
            <option value="">Choisir...</option>
            <?php
            foreach($categories as $cat){
                echo '<option value="'.$cat['id'].'">'.$cat['id'].' = '.$cat['nom'].'</option>';
            }
            ?>
        </select>
        
        <tr>
        <td>Matière:<td><select name="matieres_id">
            <option value="">Choisir...</option>
            <?php
            foreach($matieres as $cat){
                echo '<option value="'.$cat['id'].'">'.$cat['id'].' = '.$cat['nom'].'</option>';
            }
            ?>
        </select>
        
        <tr>
        <td>Le Titre contient...<td><input type="text" name="titre" value="">

        <tr>
        <td>Le Descriptif contient...<td><textarea name="descriptif"></textarea>
        
        <tr>
        <td>Prix:<td><input type="number" name="prix" step="any" value="">
        
        <tr>
        <td>Poids (Kg):<td><input type="number" name="poids" step="any" value="">
        
        <tr>
        <td>Statut:<td><select name="statut_id">
        <option value="" selected>Choisir...</option>
		<?php
			$statut_array = get_table('statut'); // get contents of statut table ('id, nom)
			$options = '';

			foreach($statut_array as $st){ // loop through statut_array to output the options
				$options .= '<option value="'.$st['id'].'">'.$st['nom'].'</option>';
			}
			echo $options;

			?>
        </select>
        
        <tr>
        <td>Visible:<td><input type="radio" name="visible" value="0"><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="visible" value="1"><label for="1"> oui</label>
        </select>
        
        <tr>
        <td>Les Observations <br>contiennent...<td><textarea name="observations"></textarea>

    
    </table>