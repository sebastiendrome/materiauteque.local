<?php
/* Fond de caisse: inclus chèques, ou seulement espèces?...
 * Recettes = fermeture - ouverture
 * Delta = Recettes - Ventes
 * Fond de caisse = total_fermeture - total_depot_banque
 * Possibilité de rouvrir la caisse just apres fermeture.. (message)
 * Message si Fond de caisse precedente != ouverture
 * Page Voir Caisses du Mois
 * */
require('../php/first_include.php');
require(ROOT.'c/php/admin/not_logged_in.php');
require(ROOT.'c/php/admin/admin_functions.php');
$title = 'CAISSE';
require(ROOT.'c/php/doctype.php');
if( isset($_SESSION['article_id']) ){
	unset($_SESSION['article_id']);
}

/* rouvrir la caisse... */
if( isset($_GET['rouvrir']) ){
	$caisse_date = $_GET['rouvrir'];
	// get table id from date 
	if( $caisse = get_table('caisse', 'date="'.$caisse_date.'"') ){
		// set statut id to 1 = 'ouverte'
		$item_data['statut_id'] = '1';
		$result = update_table('caisse', $caisse[0]['id'], $item_data);
		if( substr($result, 0, 2) == '1|' ){
			$result = '<p class="success" style="text-align:center;">La Caisse est de nouveau ouverte...</p>
			<p class="warning" style="border-radius:3px; border:1px solid rgb(252, 123, 2); padding:5px; background-color:#fffdee;"><b>Attention:</b><br>• Si vous voulez modifier des montants, <b><u>l\'intégralité des montants de la caisse et du dépôt banque doivent être à nouveau renseignés</u></b>.<br>• Si par contre vous ne souhaitez <i>que</i> modifier les champs "Référents", "Horaires", "Passages" ou "Remarques", <b>aucun des montants ne doivent être à nouveau renseignés</b>.</p>';
			$caisse_statut = 'Ouverte';
		}else{
			$result = '<p class="error">Une erreur est survenue:<br>'.$result.'</p>';
		}
	}else{
		$result = '<p class="error">La caisse du '.$caisse_date.' n\'existe pas!...</p>';
	}


/* FORM SUBMIT validation and process starts */
}elseif( isset($_POST['caisseSubmitted']) ){
	$caisse_date = $_POST['date'];

	foreach($_POST as $key => $val){
		// skip unnecessary fields
		if($key !== 'caisseSubmitted' && $key !== 'fond' && $key !== 'caisseSubmit' && !preg_match('/^(\d|total\d|cheque\d)/', $key) ){
			$val = trim($val);
			// format horaires
			if(substr($key, 0, 8) == 'horaire_'){
				// check if there are non numeric characters
				if( !preg_match('/^\d+$/', $val) ){
					$split = preg_split('/[^\d]+/', $val);
					$i = 0;
					foreach($split as $k => $v){
						// for pm hr, change 6 to 18
						if( strstr($key, '_pm_') && $i == 0 && intval($split[0]) < 12 ){
							$val = intval($val)+12;
						}else{
							$split[$k] = sprintf('%02d', $v);
						}
						$i++;
					}
					// debug
					//print_r($split);

					$val = implode(':', $split);
					if(count($split) == 2){
						$val .= ':00';
					}
				}else{
					if( strstr($key, '_pm_') && intval($val) < 12 ){
						$val = intval($val)+12;
					}
					$val = sprintf('%02d', $val).':00:00';
				}
				// debug
				//echo $val.'<br>';
			// replace coma with dot for number values (especes|cheques)
			}elseif(preg_match('/(especes|cheques)/', $key)){
				$val = str_replace(',', '.', $val);
			}
			$item_data[$key] = $val;
		}
	}
	// debug
	/*
	echo 'POST:<br><pre>';
	print_r($_POST);
	echo '</pre>';
	echo 'Item Data:<br><pre>';
	print_r($item_data);
	echo '</pre>';
	//exit();
	*/

	// $_POST['statut_id'] will be next statut id if form processed successfully
	if($_POST['statut_id'] == 0){
		$caisse_statut = 'Ouverture';
		$caisse_next_statut = 'Ouverte';
	}elseif($_POST['statut_id'] == 1){
		$caisse_statut = 'Ouverte';
		$caisse_next_statut = 'Fermée';
	}else{
		$caisse_statut = $caisse_next_statut = 'Fermée';
	}

	// double-check if caisse already exists, regardless of caisse_statut (we don't want to create caisse duplicates when page is refreshed after submiting the form)
	if( $caisse = get_table('caisse', 'date="'.$caisse_date.'"') ){ // caisse already exists
		$create_caisse = false;
		// these data fields should be concatenated to previous data if $caisse_statut = 'Ouverte'
		if($caisse_statut == 'Ouverte'){
			/* !!!!!!!! problems */
			$prev_refs = $caisse[0]['referents'];
			$prev_rem = $caisse[0]['remarques'];
			if($item_data['referents'] !== $prev_refs){
				$item_data['referents'] = str_replace($item_data['referents'], '', $prev_refs).', '.str_replace($prev_refs, '', $item_data['referents']);
			}
			if($prev_rem !== $item_data['remarques']){
				$item_data['remarques'] = str_replace($item_data['remarques'], '', $prev_rem).' -- '.str_replace($prev_rem, '', $item_data['remarques']);
			}
		}
	}else{
		$create_caisse = true;
	}
	
	// ouverture de la caisse: create new caisse
	if($caisse_statut == 'Ouverture'){
		$item_data['statut_id'] = '1'; // caisse statut will be updated to 1 = 'Ouverte'
		$success_message = 'La Caisse est ouverte.';
	
	// fermeture de la caisse: save form inputs into existing caisse id, change statut_id to 2
	}elseif($caisse_statut == 'Ouverte'){
		$item_data['statut_id'] = '2'; // caisse statut will be updated to 2 = 'Fermée'
		$success_message = 'La Caisse est fermée.<br><br><a href="?rouvrir='.$caisse_date.'" class="button undo"><i>Caisse fermée trop tôt?...</i> Rouvrir la caisse </a>';
	
	// caisse fermée: save modifications
	}elseif($caisse_statut == 'Fermée'){
		$success_message = 'Modifications Sauvegardées.';
	}

	// caisse does not exist yet, it will be inserted in table
	if($create_caisse){
		if( insert_new('caisse', $item_data) ){
			$result = '<p class="success" style="text-align:center;">'.$success_message.'</p>';
			$caisse_statut = 'Ouverte'; // update caisse statut
		}else{
			$result = '<p class="error">Une erreur est survenue. La Caisse n\'a pas été ouverte...</p>';
		}
	// caisse already exists, it will be updated
	}else{
		$result = update_table('caisse', $caisse[0]['id'], $item_data);
		if( substr($result, 0, 2) == '1|' ){
			$result = '<p class="success" style="text-align:center;">'.$success_message.'</p>';
			$caisse_statut = 'Fermée'; // update caisse statut
		}else{
			$result = '<p class="error">Une erreur est survenue:<br>'.$result.'</p>';
		}
	}
/* FORM SUBMIT validation and process ends */

// date form submit (form.dateForm)
}elseif(isset($_POST['day']) && isset($_POST['month']) && isset($_POST['year']) ){
	if( is_numeric($_POST['day']) && is_numeric($_POST['month']) && is_numeric($_POST['year']) ){
		$day = sprintf('%02d', $_POST['day']);
		$month = sprintf('%02d', $_POST['month']);
		$year = $_POST['year'];
		if( empty($year) ){
			$year = '2020';
		}elseif(strlen($year) == 2){
			$year = '20'.$year;
		}
		$caisse_date = $year.'-'.$month.'-'.$day;
	}else{
		$caisse_date = date('Y-m-d'); // = today
		$error = '<p class="error">La date est mal formée (la forme doit être: 01 01 2020)</p>';
	}
}else{
	$caisse_date = date('Y-m-d'); // = today
}


list($year, $month, $day) = explode('-', $caisse_date);
$today = date('Y-m-d');


// get the date's caisse if it already exists, set required values
if($caisse_table = get_table('caisse', 'date="'.$caisse_date.'"')){
	$caisse = $caisse_table[0];
	$statut_id = $caisse['statut_id'];
	if($statut_id == 1){ // 1 = caisse ouverte, 2 = caisse fermée
		$caisse_statut = 'Ouverte';
		$caisse_action = 'FERMER LA CAISSE';
		$total_especes = 'especes_fermeture';
		$total_cheques = 'cheques_fermeture';
		$total_element = 'total_fermeture';
	
	}else if($statut_id == 2){ /** !!!!! what to show in the form then? */
		$caisse_statut = 'Fermée';
		$caisse_action = 'SAUVEGARDER';
	}


// caisse does not exist yet, a new caisse will be created by form submition. Initialize required values
}else{
	$caisse_statut = 'Ouverture';
	$statut_id = 0;
	$caisse_action = 'OUVRIR LA CAISSE';
	$total_especes = 'especes_ouverture';
	$total_cheques = 'cheques_ouverture';
	$total_element = 'total_ouverture';
}

// get total ventes of this date's caisse, if caisse has already been opened
if($statut_id > 0){ // caisse déjà créée
	$ventes = get_ventes_total($caisse_date);
	$previous_fond_de_caisse = false;
}else{ // nouvelle caisse, 'Ouverture'. Get previous caisse 'fond de caisse' to compare with total_ouverture
	$ventes = 0;
	// get most recent previous caisse
	if($previous = get_table('caisse', 'date < "'.$caisse_date.'"', 'date DESC')){
		if( !empty($previous[0]) ){
			$previous_caisse = $previous[0];
			// calculate fond de caisse
			$previous_fond_de_caisse = ($previous_caisse['especes_fermeture']+$previous_caisse['cheques_fermeture'])-($previous_caisse['depot_especes']+$previous_caisse['depot_cheques']);
		}else{
			$previous_fond_de_caisse = false;
		}
	}else{
		$previous_fond_de_caisse = false;
	}
	// debug
	//echo 'Dernier fond de caisse: '.$previous_fond_de_caisse;
}

/* FORM VARIABLES */
// horaires validation regex (must be trimed first)
/* must validate: 09:30:00, 9, 9h, 9h30, 9h 30, 9:30, 9.30, 9,30, 9-30 */
$horaire_valid = '/^\d{1,2}(\.|:|,|-|h)? ?\d{0,2}(:00)?$/';
// tab index
$tab_index = 1;



// debug
//echo '<h1>ventes: '.$ventes.'</h1>';


// calculs
$total_ouverture = $total_fermeture = $total_depot_banque = 0;
// total ouverture
if( isset($caisse['especes_ouverture']) && isset($caisse['cheques_ouverture']) ){
	$total_ouverture = $caisse['especes_ouverture']+$caisse['cheques_ouverture'];
}
// total fermeture
if( isset($caisse['especes_fermeture']) && isset($caisse['cheques_fermeture']) ){
	$total_fermeture = $caisse['especes_fermeture']+$caisse['cheques_fermeture'];
}
// total depot banque
if( isset($caisse['depot_especes']) && isset($caisse['depot_cheques']) ){
	$total_depot_banque = $caisse['depot_especes']+$caisse['depot_cheques'];
}
// recettes (fermeture - ouverture)
$recettes = $total_fermeture-$total_ouverture;
// Delta = Recettes - Ventes
$delta = $recettes-$ventes;
// Fond de caisse = Fermeture - Depot banque
$fond_de_caisse = $total_fermeture-$total_depot_banque;

?>

<!-- admin css -->
<link href="<?php echo REL; ?>c/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">

<style>
table.amountsDetail{border:5px solid #f5f5f5; float:left; margin-right:20px;}
table.amountsDetail table{border:none;}
table.amountsDetail td{vertical-align:middle; text-align:right; padding:0; border-bottom:0;}
table.amountsDetail input{width:60px; min-width:60px; text-align:right; margin:0; margin-left:4px; padding:2px;}
table.amountsDetail input.disabled{border:1px solid transparent; background-color:transparent; cursor:default;}
table.amountsDetail thead td{text-align: left; font-weight: bold; padding:3px;}
.tTitle div{border-bottom:1px solid #666; margin:-5px 0 5px 0; padding:4px; text-align:center;}
div#verification{display:inline-block;/* float:left;*/}
/*div#verification div{color:red;}*/
div#extra input.horaires, div#extra input#passages{width:60px; min-width:60px;}
div#extra textarea{margin:0 10px 10px 0; width:70%; min-width:350px;}
div#horVal{
	display:none;
	position:absolute; 
	box-shadow:2px 2px 5px rgba(0,0,0,.3); 
	font-size:smaller;
	width:300px;
	bottom:100%;
}
</style>

<?php
if( isset($_GET['message']) ){
	$message = urldecode($_GET['message']);
}
if( isset($message) && !empty($message) ){
	$message = str_replace(array('0|', '1|', '2|'), array('<p class="error">', '<p class="success">', '<p class="note">'), $message).'</p>';
	$message_script = '<script type="text/javascript">showDone();</script>';
}else{
	$message = $message_script = '';
}

echo '<div id="working"><div class="note">working...</div></div>';
echo '<div id="done">'.$message.'</div>';
?>

<!-- adminHeader start -->
<div class="adminHeader">
<h1 style="color:rgb(177, 0, 0);">CAISSE</h1>
<h2><?php echo ' <u>'.$caisse_statut.'</u>'; ?> <form name="dateVentes" class="dateForm" action="<?php echo REL; ?>c/admin/caisse.php" method="post"><input type="text" name="day" value="<?php echo $day; ?>" size="2" maxlength="2"><input type="text" name="month" value="<?php echo $month; ?>" size="2" maxlength="2"><input type="text" name="year" value="<?php echo $year; ?>" size="4" maxlength="4"><input type="submit" name="submitDateVentes" value="&gt;" style="position:absolute; top:-100px;"></form></h2>
<?php if($caisse_date !== $today){echo '<a class="button" href="<?php echo REL; ?>c/admin/caisse.php">Voir la Caisse d\'aujourd\'hui</a>';} ?>
<!--
<a href="javascript:;" class="button paniersBut right showPaniers venSH"><img src="<?php echo REL; ?>c/images/panier.svg" style="width:15px;height:15px; margin-bottom:-2px; margin-right:10px;">Paniers en cours (<span id="paniersCount"><?php echo $paniers_count; ?></span>)</a> -->

<div class="clearBoth"></div>
</div>
<!-- adminHeader end -->

<?php 
//include(ROOT.'c/php/forms/paniersModal.php');
?>


<!-- start admin container -->
<div id="adminContainer">

<?php
// show caisse result after ouverture or fermeture - and exit (do not display the form)
if( isset($result) ){
	echo $result;
}

// caisse fermée: end markup, include footer and EXIT
if($caisse_statut == 'Fermée'){ 
	echo '<p class="note">La caisse du '.$day.'-'.$month.'-'.$year.' est fermée.</p>';
	echo '</div><!-- end admin container -->';
	require(ROOT.'/c/php/admin/admin_footer.php');
	echo '</body></html>';
	exit();
}
?>

<form name="caisse" action="<?php echo REL; ?>c/admin/caisse.php" method="post">
<input type="hidden" name="caisseSubmitted" value="caisseSubmitted">
<input type="hidden" name="date" value="<?php echo $caisse_date; ?>">
<input type="hidden" name="statut_id" value="<?php echo $statut_id; ?>">


<div id="extra">
	<div style="display:inline-block;">
		<span style="white-space:nowrap;">Référents:<input type="text" name="referents" id="referents" value="<?php if($caisse_statut !== 'Ouverte' && isset($caisse['referents'])){echo $caisse['referents'];}?>" tabindex="<?php echo $tab_index++; ?>" maxlength="255"></span>
		<?php
		if($caisse_statut == 'Ouverte'){ 
		?> 
		<br><span style="white-space:nowrap;">Passages:<input type="number" name="passages" id="passages" value="<?php if(isset($caisse['passages'])){echo $caisse['passages'];}?>" tabindex="<?php echo $tab_index++; ?>"></span> 
		<?php
		// end if $caisse_statut == 'Ouverte'
		}
		?>
	</div>
	<div style="display:inline-block; position:relative;">
		<span style="white-space:nowrap;">Horaires: de<input type="text" maxlength="8" class="horaires" name="horaire_am_start" id="horaire_am_start" value="<?php if(isset($caisse['horaire_am_start'])){echo $caisse['horaire_am_start'];}?>" tabindex="<?php echo $tab_index++; ?>"> à<input type="text" maxlength="8" class="horaires" name="horaire_am_end" id="horaire_am_end" value="<?php if(isset($caisse['horaire_am_end'])){echo $caisse['horaire_am_end'];}?>" tabindex="<?php echo $tab_index++; ?>"></span><br>
		<span style="white-space:nowrap;"><span style="color:transparent;">Horaires: </span>de<input type="text" maxlength="8" class="horaires" name="horaire_pm_start" id="horaire_pm_start" value="<?php if(isset($caisse['horaire_pm_start'])){echo $caisse['horaire_pm_start'];}?>" tabindex="<?php echo $tab_index++; ?>"> à<input type="text" maxlength="8" class="horaires" name="horaire_pm_end" id="horaire_pm_end" value="<?php if(isset($caisse['horaire_pm_end'])){echo $caisse['horaire_pm_end'];}?>" tabindex="<?php echo $tab_index++; ?>"></span>
		<div class="error" id="horVal"><a href="javascript:;" class="closeMessage">&times;</a>Veuillez utiliser l'un des formats suivants:<br>
		9:35 ou 9h35 ou 9 (pour l'heure pile)</div>
	</div>
	
	<div style="margin-bottom:10px;"><a href="javascript:;" class="note addNote left" style="display: inline-block;">Remarques...</a>
	<div class="tAreaResizer" style="display:<?php if(isset($caisse['remarques']) && !empty($caisse['remarques'])){echo 'block'; $node = $caisse['remarques'];}else{echo 'none'; $node = '&nbsp;';} ?>;"><?php echo $node; ?><textarea class="notes" name="remarques" maxlength="255"><?php if(isset($caisse['remarques'])){echo $caisse['remarques'];}?></textarea></div>
	</div>
</div>



<table class="amountsDetail" id="of">

	<thead>
	<tr>
		<td colspan="2" class="tTitle"><div><?php if($caisse_statut == 'Ouverture'){echo 'OUVERTURE';}elseif($caisse_statut == 'Ouverte'){echo 'FERMETURE';} ?></div></td>
	</tr>
	</thead>
	
	<tr>
		<td style="vertical-align:top;">
			<table>
				
				<thead>
				<tr>
					<td style="text-align:right;">BILLETS</td>
					<td class="below" style="text-align:right;">quant.</td>
					<td style="text-align:right;">sous-tot.</td>
				</tr>
				</thead>

				<tr>
				<td>100,00 €</td>
				<td><input name="100" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total100" value="0,00"> €</td>
				</tr>
				<tr>
				<td>50,00 €</td>
				<td><input name="50" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total50" value="0,00"> €</td>
				</tr>
				<tr>
				<td>20,00 €</td>
				<td><input name="20" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total20" value="0,00"> €</td>
				</tr>
				<tr>
				<td>10,00 €</td>
				<td><input name="10" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total10" value="0,00"> €</td>
				</tr>
				<tr>
				<td>5,00 €</td>
				<td><input name="5" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total5" value="0,00"> €</td>
				</tr>
				<tr>
					<td>&nbsp;</td><td></td><td></td>
				</tr>
				<tr style="font-weight:bold;">
				<td>PIÈCES</td>
				<td></td>
				<td></td>
				</tr>
				<tr>
				<td>2,00 €</td>
				<td><input name="2" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total2" value="0,00"> €</td>
				</tr>
				<tr>
				<td>1,00 €</td>
				<td><input name="1" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total1" value="0,00"> €</td>
				</tr>
				<tr>
				<td>0,50 €</td>
				<td><input name="0.5" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total0.5" value="0,00"> €</td>
				</tr>
				<tr>
				<td>0,20 €</td>
				<td><input name="0.2" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total0.2" value="0,00"> €</td>
				</tr>
				<tr>
				<td>0,10 €</td>
				<td><input name="0.1" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total0.1" value="0,00"> €</td>
				</tr>
				<tr>
				<td>0,05 €</td>
				<td><input name="0.05" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total0.05" value="0,00"> €</td>
				</tr>
				<tr>
				<td>0,02 €</td>
				<td><input name="0.02" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total0.02" value="0,00"> €</td>
				</tr>
				<tr>
				<td>0,01 €</td>
				<td><input name="0.01" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total0.01" value="0,00"> €</td>
				</tr>
			</table>
		</td>

		<td style="vertical-align:top;">
			<table style="float:right;" class="tCheques">
				<tr style="font-weight:bold;">
				<td style="text-align:right;">CHÈQUES</td>
				<td class="below" style="text-align:right;">montant</td>
				</tr>
				<tr>
				<td>N°1</td>
				<td><input type="number" step="any" min="0" class="currency cheque" value="" tabindex="<?php echo $tab_index++; ?>"> €</td>
				</tr>
				<tr>
				<td>N°2</td>
				<td><input type="number" step="any" min="0" class="currency cheque" value="" tabindex="<?php echo $tab_index++; ?>"> €</td>
				</tr>
				<tr>
				<td>N°3</td>
				<td><input type="number" step="any" min="0" class="currency cheque" value="" tabindex="<?php echo $tab_index++; ?>"> €</td>
				</tr>
			</table>
		</td>
	</tr>
	

	<thead>
	<tr>
	<td style="text-align:right;">Total Espèces:<input type="text" name="<?php echo $total_especes; ?>" id="<?php echo $total_especes; ?>" class="totalEspeces disabled" value="<?php if(isset($caisse[$total_especes])){echo number_format($caisse[$total_especes], 2, ',', '');}else{echo '0,00';} ?>"> €</td>
	<td style="text-align:right;">Total Chèques:<input type="text" name="<?php echo $total_cheques; ?>" id="<?php echo $total_cheques; ?>" class="totalCheques disabled" value="<?php if(isset($caisse[$total_cheques])){echo number_format($caisse[$total_cheques], 2, ',', '');}else{echo '0,00';} ?>"> €</td>
	</tr>
	</thead>

	<thead>
	<tr>
	<td style="text-align:center;" colspan="2"><h3>TOTAL CAISSE <?php if($caisse_statut == 'Ouverture'){echo 'OUVERTURE'; $total_val = $total_ouverture;}elseif($caisse_statut == 'Ouverte'){echo 'FERMETURE'; $total_val = $total_fermeture;} ?>: <span class="<?php echo $total_element; ?>"><?php echo $total_val; ?></span> €</h3></td>
	</tr>
	</thead>

</table>



<?php 
if($caisse_statut == 'Ouverte'){ 
?>

<table class="amountsDetail" id="banque">

	<thead>
	<tr>
		<td colspan="2" class="tTitle"><div>Dépôt Banque<span style="font-weight:normal; text-decoration:none;"> fait par:<input type="text" name="porteur_depot_banque" id="porteur_depot_banque" value="<?php if(isset($caisse['porteur_depot_banque'])){echo $caisse['porteur_depot_banque'];} ?>" style="width:200px; text-align:left; margin:0;padding:0 4px;"></span></div></td>
	</tr>
	</thead>

	<tr>
		<td style="vertical-align:top;">
			<table>
				
				<thead>
				<tr>
					<td style="text-align:right;">BILLETS</td>
					<td class="below" style="text-align:right;">quant.</td>
					<td style="text-align:right;">sous-tot.</td>
				</tr>
				</thead>
	
				<tr>
				<td>100,00 €</td>
				<td><input name="100" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total100" value="0,00"> €</td>
				</tr>
				<tr>
				<td>50,00 €</td>
				<td><input name="50" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total50" value="0,00"> €</td>
				</tr>
				<tr>
				<td>20,00 €</td>
				<td><input name="20" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total20" value="0,00"> €</td>
				</tr>
				<tr>
				<td>10,00 €</td>
				<td><input name="10" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total10" value="0,00"> €</td>
				</tr>
				<tr>
				<td>5,00 €</td>
				<td><input name="5" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total5" value="0,00"> €</td>
				</tr>
				<tr>
					<td>&nbsp;</td><td></td><td></td>
				</tr>
				<tr style="font-weight:bold;">
				<td>PIÈCES</td>
				<td></td>
				<td></td>
				</tr>
				<tr>
				<td>2,00 €</td>
				<td><input name="2" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total2" value="0,00"> €</td>
				</tr>
				<tr>
				<td>1,00 €</td>
				<td><input name="1" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total1" value="0,00"> €</td>
				</tr>
				<tr>
				<td>0,50 €</td>
				<td><input name="0.5" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total0.5" value="0,00"> €</td>
				</tr>
				<tr>
				<td>0,20 €</td>
				<td><input name="0.2" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total0.2" value="0,00"> €</td>
				</tr>
				<tr>
				<td>0,10 €</td>
				<td><input name="0.1" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total0.1" value="0,00"> €</td>
				</tr>
				<tr>
				<td>0,05 €</td>
				<td><input name="0.05" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total0.05" value="0,00"> €</td>
				</tr>
				<tr>
				<td>0,02 €</td>
				<td><input name="0.02" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total0.02" value="0,00"> €</td>
				</tr>
				<tr>
				<td>0,01 €</td>
				<td><input name="0.01" type="number" step="1" min="0" class="qty" value="" tabindex="<?php echo $tab_index++; ?>"></td>
				<td><input type="text" class="sousTotal disabled" name="total0.01" value="0,00"> €</td>
				</tr>
			</table>
		</td>

		<td style="vertical-align:top;">
			<table style="float:right;" class="tCheques">
				<tr style="font-weight:bold;">
				<td style="text-align:right;">CHÈQUES</td>
				<td class="below" style="text-align:right;">montant</td>
				</tr>
				<tr>
				<td>N°1</td>
				<td><input type="number" step="any" min="0" class="currency cheque" value="" tabindex="<?php echo $tab_index++; ?>"> €</td>
				</tr>
				<tr>
				<td>N°2</td>
				<td><input type="number" step="any" min="0" class="currency cheque" value="" tabindex="<?php echo $tab_index++; ?>"> €</td>
				</tr>
				<tr>
				<td>N°3</td>
				<td><input type="number" step="any" min="0" class="currency cheque" value="" tabindex="<?php echo $tab_index++; ?>"> €</td>
				</tr>
			</table>
		</td>
	</tr>
	

	<thead>
	<tr>
	<td style="text-align:right;">Total Espèces:<input type="text" name="depot_especes" id="depot_especes" class="totalEspeces disabled" value="<?php if(isset($caisse['depot_especes'])){echo number_format($caisse['depot_especes'], 2, ',', '');}else{echo '0,00';} ?>"> €</td>
	<td style="text-align:right;">Total Chèques:<input type="text" name="depot_cheques" id="depot_cheques" class="totalCheques disabled" value="<?php if(isset($caisse['depot_cheques'])){echo number_format($caisse['depot_cheques'], 2, ',', '');}else{echo '0,00';} ?>"> €</td>
	</tr>
	</thead>

	<thead>
	<tr>
	<td style="text-align:center;" colspan="2"><h3>TOTAL DÉPÔT BANQUE: <span class="total total_depot_banque"><?php echo $total_depot_banque; ?></span> €</h3></td>
	</tr>
	</thead>


</table>



<table class="amountsDetail" id="calculs">
	<thead>
	<tr>
		<td colspan="2" class="tTitle"><div>Calculs</div></td>
	</tr>
		<tr>
			<td>Ouverture</td>
			<td style="text-align:right;"><span class="total_ouverture"><?php echo number_format($total_ouverture, 2, ',', ''); ?></span> €</td>
		</tr>
		<tr>
			<td>Fermeture</td>
			<td style="text-align:right;"><span class="total_fermeture"><?php echo number_format($total_fermeture, 2, ',', ''); ?></span> €</td>
		</tr>
		<tr>
			<td>Recettes</td>
			<td style="text-align:right;"><span id="recettes"><?php echo number_format($recettes, 2, ',', ''); ?></span> €</td>
		</tr>
		<tr>
			<td>Ventes</td>
			<td style="text-align:right;"><span id="ventes"><?php echo number_format($ventes, 2, ',', ''); ?></span> €</td>
		</tr>
		<tr>
			<td>Delta</td>
			<td style="text-align:right;"><span id="delta"><?php echo number_format($delta, 2, ',', ''); ?></span> €</td>
		</tr>
		<tr>
			<td>Fond de caisse</td>
			<td style="text-align:right;"><span id="fond_de_caisse"><?php echo number_format($fond_de_caisse, 2, ',', ''); ?></span> €</td>
		</tr>
	</thead>
</table><br>

<?php
// end if $caisse_statut == 'Ouverte'
}
?>

<div id="verification">
	<!--<span><u><b>Vérification:</b></u></span><br>-->
	<div id="VerifReferents" class="warning">Référents Manquants</div>
	<div id="VerifHoraires" class="warning">Horaires Manquants</div>
	<?php 
	if($caisse_statut == 'Ouverte'){ 
	?>
	<div id="VerifPassages" class="warning">Passages Manquants</div>
	<div id="VerifDepot" class="warning" style="display:none;">Porteur du dépôt Manquant</div>
	<?php
	// end if $caisse_statut == 'Ouverte'
	}
	?>
	<div id="VerifCaisse" class="warning">Caisse Manquante</div>

</div>

<?php 
// previous fond de caisse (if applicable)
if($previous_fond_de_caisse){ 
	echo '<div id="VerifFond" style="margin:10px 0;">Dernier fond de caisse: <span id="previous_fond_de_caisse">'.number_format($previous_fond_de_caisse, 2, ',', '').'</span> €</div>';
}
?>

<div class="clearBoth">
	<!--<button type="reset" name="resetForm" class="reset"> Annuler </button>-->
	<button type="submit" name="caisseSubmit" class="right" tabindex="<?php echo $tab_index++; ?>"> <?php echo $caisse_action; ?> </button>
</div>


</form>


</div><!-- end admin container -->

<?php
require(ROOT.'/c/php/admin/admin_footer.php');
?>

<script type="text/javascript">
// auto adjust the height of textarea via tAreaContainer hidden div
$('div#extra').on('keyup', 'textarea.notes', function(e){
	var cont = this.parentElement;
	var text_to_change = cont.childNodes[0];
	if(e.which == 13){
		text_to_change.nodeValue += '\n&nbsp;';
		return false;
	}
	if(this.value.length){
		text_to_change.nodeValue = this.value;
	}else{
		text_to_change.nodeValue = '&nbsp;';
	}
});
// save panier notes when loosing focus on textarea.notes
// oldVal is set as global var at top of page, and updated on focus (above)
$('div#extra').on('blur', 'textarea.notes', function(){
	var $this = $(this);
	var newVal = $this.val();
	// hide textarea if value is empty - show 'add note' button
	if(newVal == ''){
		setTimeout( function(){
			$('div.tAreaResizer').css('display','none');
		}, 150);
	}
});
$('div#extra').on('click', 'a.addNote', function(e){
	e.preventDefault();
	$('div.tAreaResizer').css('display','block');
	$('textarea.notes').focus();
});


// caisse statut
var caisse_statut = '<?php echo $caisse_statut; ?>';
if(caisse_statut == 'Ouverte'){
	var total_element = 'total_fermeture';
}else if(caisse_statut == 'Ouverture'){
	var total_element = 'total_ouverture';
}
// calculs
var total_ouverture = <?php echo $total_ouverture; ?>;
var total_fermeture = <?php echo $total_fermeture; ?>;
var total_depot_banque = <?php echo $total_depot_banque; ?>;
var recettes = <?php echo $recettes; ?>;
var ventes = <?php echo $ventes; ?>;
var delta = <?php echo $delta; ?>;
var fond_de_caisse = <?php echo $fond_de_caisse; ?>;

<?php 
// previous fond de caisse (if applicable)
if($previous_fond_de_caisse){ 
	echo 'var previous_fond_de_caisse = '.$previous_fond_de_caisse.';
';
}else{
	echo 'var previous_fond_de_caisse = false;
';
}
?>

// duplicate last cheque table row for more 
$('table.amountsDetail table').on('focus', 'tr:last input.cheque', function(){
	var $table = $(this).closest('table');
	var i = 1;
	var dupe = true; // will append duplicate
	var $rows = $table.find('input.cheque');
	var l = $rows.length;
	// if some cheque inputs are empty (except last one), do not append duplicate
	$rows.each(function(index){
		if($(this).val() == '' && (index+1)<l){
			dupe = false; // will not append duplicate
		}
	});
	// append duplicate
	if(dupe){
		$table.append('<tr><td>N°'+(l+1)+'</td><td><input type="number" step="any" min="0" class="currency cheque" value=""> €</td></tr>');
	}
});

// disable focus/change on auto-calculated inputs
$('table.amountsDetail input.disabled').on('focus', function(){
	$(this).blur();
});

// calculate especes total & update ouverture/fermeture/depot banque total
$('table.amountsDetail tr input.qty').on('blur', function(){
	var $thisTable = $(this).closest('table.amountsDetail');
	if($thisTable.attr('id') == 'of'){ // ouverture | fermeture
		var verif = 'VerifCaisse';
		if(caisse_statut == 'Ouverte'){
			total_element = 'total_fermeture';
		}else if(caisse_statut == 'Ouverture'){
			total_element = 'total_ouverture';
		}
	}else if($thisTable.attr('id') == 'banque'){ // depot banque
		var verif = 'VerifDepot';
		total_element = 'total_depot_banque';
	}
	var $totalEspeces = $thisTable.find('input.totalEspeces');
	var $totalCheques = $thisTable.find('input.totalCheques');
	var $totalSpans = $thisTable.closest('form').find('span.'+total_element);
	var especeTotal = 0;
	var $row = $(this).closest('tr');
	var billet = $(this).attr('name');
	var $sousTotal = $row.find('input.sousTotal');
	var val = $(this).val();
	if(val === ''){
		val = 0;
		$(this).attr('value', 0);
	}
	var tot = parseFloat(val)*parseFloat(billet);
	//alert(tot);
	var v = tot.toFixed(2);
	$sousTotal.val( v.replace(".",",") );
	$sousTotal.attr('value', v);
	var $sousTots = $thisTable.find('input.sousTotal');
	$sousTots.each(function(){
		//alert($(this).val());
		especeTotal += parseFloat($(this).val().replace(",","."));
		var v = especeTotal.toFixed(2);
		//alert(especeTotal);
		$totalEspeces.val( v.replace(".",",") );
		$totalEspeces.attr('value', v);
	});
	totalVal = especeTotal+parseFloat( $totalCheques.val().replace(",",".") );
	//alert(totalVal);
	if(verif == 'VerifCaisse'){
		if(totalVal>0){
			$('div#'+verif).css('display','none');
		}else{
			$('div#'+verif).css('display','block');
		}
		total_fermeture = totalVal;
		fond_de_caisse = total_fermeture-total_depot_banque;
	}else if(verif == 'VerifDepot'){
		if(totalVal>0 && $('input#porteur_depot_banque').val() == ''){
			$('div#VerifDepot').css('display','block');
		}else if(totalVal == 0){
			$('div#VerifDepot').css('display','none');
		}
		total_depot_banque = totalVal;
		fond_de_caisse = total_fermeture-total_depot_banque;
	}
	var v = totalVal.toFixed(2);
	$totalSpans.text( v.replace(".",",") );
	// calculs
	if(total_element == 'total_fermeture'){
		total_fermeture = totalVal;
		recettes = total_fermeture-total_ouverture;
		//console.log(recettes);
		delta = recettes-ventes;
		$('span#recettes').text( recettes.toFixed(2).replace(".",",") );
		$('span#delta').text( delta.toFixed(2).replace(".",",") );
	
	// compare fond de caisse et previous fond (if applicable)
	}else if(total_element == 'total_ouverture' && previous_fond_de_caisse !== false){
		// debug
		//console.log(previous_fond_de_caisse+' - '+totalVal);
		if( previous_fond_de_caisse !== totalVal){
			$('div#VerifFond').addClass('warning');
		}else{
			$('div#VerifFond').removeClass('warning');
		}
	}
	$('span#fond_de_caisse').text( fond_de_caisse.toFixed(2).replace(".",",") );
	
});

// calculate cheques total & update ouverture/fermeture/depot banque total
$('table.tCheques').on('blur', 'tr input.cheque', function(){
	var $thisTable = $(this).closest('table.amountsDetail');
	if($thisTable.attr('id') == 'of'){
		var verif = 'VerifCaisse';
	}else if($thisTable.attr('id') == 'banque'){
		var verif = 'VerifDepot';
		total_element = 'total_depot_banque';
	}
	var $totalEspeces = $thisTable.find('input.totalEspeces');
	var $totalCheques = $thisTable.find('input.totalCheques');
	var $totalSpans = $thisTable.closest('form').find('span.'+total_element);
	var chequeTotal = 0;
	var $allCheq = $thisTable.find('input.cheque');
	$allCheq.each(function(){
		var v = $(this).val().replace(",",".");
		if(v == ''){
			v = 0;
		}
		chequeTotal += parseFloat(v);
		var s = chequeTotal.toFixed(2);
		$totalCheques.val( s.replace(".",",") );
		$totalCheques.attr('value', s);
	});
	totalVal = chequeTotal+ parseFloat( $totalEspeces.val().replace(",",".") );
	if(verif == 'VerifCaisse'){
		if(totalVal>0){
			$('div#'+verif).css('display','none');
		}else{
			$('div#'+verif).css('display','block');
		}
		total_fermeture = totalVal;
		fond_de_caisse = total_fermeture-total_depot_banque;
	}else if(verif == 'VerifDepot'){
		if(totalVal>0 && $('input#porteur_depot_banque').val() == ''){
			$('div#VerifDepot').css('display','block');
		}else if(totalVal == 0){
			$('div#VerifDepot').css('display','none');
		}
		total_depot_banque = totalVal;
		fond_de_caisse = total_fermeture-total_depot_banque;
	}

	// compare fond de caisse et previous fond (if applicable)
	if(total_element == 'total_ouverture' && previous_fond_de_caisse !== false){
		// debug
		//console.log(previous_fond_de_caisse+' - '+totalVal);
		if( previous_fond_de_caisse !== totalVal){
			$('div#VerifFond').addClass('warning');
		}else{
			$('div#VerifFond').removeClass('warning');
		}
	}
	var v = totalVal.toFixed(2);
	$totalSpans.text( v.replace(".",",") );
	// calculs
	$('span#fond_de_caisse').text( fond_de_caisse.toFixed(2).replace(".",",") );
});

// validate caisse on page load
if( (total_element == 'total_ouverture' && total_ouverture > 0) || (total_element == 'total_fermeture' && total_fermeture > 0) ){
	$('div#VerifCaisse').css('display','none');
}

// validate porteur depot banque
if( $('form[name="caisse"]').find( $('input#porteur_depot_banque') ).length ){
	if( $('input#porteur_depot_banque').val().length ){
		$('div#VerifDepot').css('display','none');
	}
}
$('input#porteur_depot_banque').on('change', function(){
	if($(this).val() !== ''){
		$('div#VerifDepot').css('display','none');
	}else if($('span.total_depot_banque').text() !== '0,00'){
		$('div#VerifDepot').css('display','block');
	}
});

// validate referents
if( $('input#referents').val().length ){
	$('div#VerifReferents').css('display','none');
}
$('input#referents').on('change', function(){
	if($(this).val() !== ''){
		$('div#VerifReferents').css('display','none');
	}else{
		$('div#VerifReferents').css('display','block');
	}
});

// validate passages format
if( $('form[name="caisse"]').find( $('input#passages') ).length ){
	if( $('input#passages').val().length ){
		$('div#VerifPassages').css('display','none');
	}
}
$('input#passages').on('change', function(){
	if($(this).val() !== ''){
		$('div#VerifPassages').css('display','none');
	}else{
		$('div#VerifPassages').css('display','block');
	}
});

// validate horaires format (diff. validation if Ouverture or Fermeture)
<?php 
if( $caisse_statut == 'Ouverture'){
?>$

// Ouverture: either horaire_am_start or horaire_pm_start must be filled
if($('input#horaire_am_start').val().match(<?php echo $horaire_valid; ?>) !== null || $('input#horaire_pm_start').val().match(<?php echo $horaire_valid; ?>) !== null ){
	// hide horaires verification div
	$('div#VerifHoraires').css('display','none');
}
$('input#horaire_am_start, input#horaire_pm_start').on('change', function(){
	//alert('changed');
	if( $(this).val().match(<?php echo $horaire_valid; ?>) !== null ){
		// hide horaires verification div and format error div
		$('div#VerifHoraires, div#horVal').css('display','none');
	}else{
		$('div#VerifHoraires, div#horVal').css('display','block');
	}
});

<?php
}elseif( $caisse_statut == 'Ouverte'){
?>
// Fermeture: either horaire_am_start & horaire_am_end, or horaire_pm_start & horaire_pm_end must be filled
var $am_start = $('input#horaire_am_start');
var $am_end = $('input#horaire_am_end');
var $pm_start = $('input#horaire_pm_start');
var $pm_end = $('input#horaire_pm_end');
if( ( $am_start.val().match(<?php echo $horaire_valid; ?>) !== null && $am_end.val().match(<?php echo $horaire_valid; ?>) !== null ) || ( $pm_start.val().match(<?php echo $horaire_valid; ?>) !== null && $pm_end.val().match(<?php echo $horaire_valid; ?>) !== null ) ){
	// hide horaires verification div
	$('div#VerifHoraires, div#horVal').css('display','none');
}
$('input#horaire_am_start, input#horaire_am_end, input#horaire_pm_start, input#horaire_pm_end').on('change', function(){
	var thisVal = $(this).val();
	var thisId = $(this).attr('id');
	var startEnd = thisId.slice(-3);
	if(startEnd == 'end'){
		var otherId = thisId.replace("_end", '_start');
	}else if(startEnd == 'art'){
		var otherId = thisId.replace("_start", '_end');
	}
	var otherVal = $('input#'+otherId).val();
	if( thisVal.match(<?php echo $horaire_valid; ?>) !== null && otherVal.match(<?php echo $horaire_valid; ?>) !== null){
		// hide horaires verification div
		$('div#VerifHoraires, div#horVal').css('display','none');
	}else{
		$('div#VerifHoraires, div#horVal').css('display','block');
	}
});
<?php
}
?>

// stopUnload global var. On click form submit and if valdation passes, stopUnload will be set to false
var stopUnload = true;

// validate form on submit
$('button[name="caisseSubmit"]').on('click', function(e){
	e.preventDefault();
	var warn = '';
	$('div#verification div:visible').each(function(){
		warn += "\n"+$(this).text();
	});
	if( warn.length ){
		alert('Il manque les informations suivantes:\n'+warn);
	}else{
		stopUnload = false;
		$('form[name="caisse"]').submit();
	}
});

// refresh ventes on window focus
$(window).on('focus', function(){
	if($('span#ventes').length){
		refresh_ventes('<?php echo $caisse_date; ?>');
	}
});

// warn user before page unload 
window.onbeforeunload = function(e){
	if(stopUnload){
		var e = e || window.event;
		// For IE and Firefox
		if (e) {
			e.returnValue = 'La Caisse n\'est pas sauvegardée!';
		}
		// For Safari
		return 'La Caisse n\'est pas sauvegardée!';
	}
};

/* window open memory between admin home to this page */
/*
// save the fact that this child window is open, into opener page
window.reconnect = function(){
	var i =0;
	var timer = setInterval(function(){
		i++;
		if(window.opener && window.opener.saveChildReference){
			window.opener.saveChildReference(window);
			clearTimeout(timer);
		}
		if(i > 100){
			clearTimeout(timer);// stop trying as parent may be closed.
		}
	}, 1000);
};
*/
</script>

</body>
</html>