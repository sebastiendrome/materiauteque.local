<?php
/* 
 * Page Voir Caisses du Mois
 * Recettes = fermeture - ouverture
 * Delta = Recettes - Ventes
 * Fond de caisse = total_fermeture - total_depot_banque
 * */
require('../php/first_include.php');
$title = 'Caisses au Mois';
require(ROOT.'c/php/doctype.php');
if( isset($_SESSION['article_id']) ){
	unset($_SESSION['article_id']);
}

function time_diff($start, $end){
	if(!empty($start) && !empty($end) && $start !== '00:00:00' && $end !== '00:00:00'){
		$time1 = strtotime('2012-09-13 '.$start);
		$time2 = strtotime('2012-09-13 '.$end);
		// debug
		//echo ' $time1: '.$time1;
		$seconds = $time2-$time1;
		return $seconds;
	}else{
		return 0;
	}
}

function show_caisses($caisse_array){
	$output = '<table>';
	$i = $passages_tot = $recettes_tot = $ventes_tot = $seconds_tot = $delta_tot = 0;
	foreach($caisse_array as $c){
		$caisse_output = $thead = '';
		
		/*
		$total_ouverture = $c['especes_ouverture']+$c['cheques_ouverture'];
		$total_fermeture = $c['especes_fermeture']+$c['cheques_fermeture'];
		$total_fermeture = $c['especes_fermeture']+$c['cheques_fermeture'];
		$total_depot_banque = $c['depot_especes']+$c['depot_cheques'];
		$fond_de_caisse = $total_fermeture-$total_depot_banque;
		$ventes = $ventes = get_ventes_total($c['date']);
		$recettes = $total_ouverture-$total_fermeture;
		$delta = $recettes-$ventes;
		*/

		$c['total_ouverture'] = $c['especes_ouverture']+$c['cheques_ouverture'];
		$c['total_fermeture'] = $c['especes_fermeture']+$c['cheques_fermeture'];
		$c['total_fermeture'] = $c['especes_fermeture']+$c['cheques_fermeture'];
		$c['total_depot_banque'] = $c['depot_especes']+$c['depot_cheques'];
		$c['fond_de_caisse'] = $c['total_fermeture']-$c['total_depot_banque'];
		$c['ventes'] = get_ventes_total($c['date']);
		$c['recettes'] = $c['total_fermeture']-$c['total_ouverture'];
		$c['delta'] = $c['recettes']-$c['ventes'];

		$seconds = time_diff($c['horaire_am_start'], $c['horaire_am_end'])+time_diff($c['horaire_pm_start'], $c['horaire_pm_end']);
		//$c['heures'] = gmdate("H:i:s", $seconds);
		$c['heures'] = $seconds / ( 60 * 60 );
		
		foreach($c as $k => $v){
			// initialize td default class
			$class = 'norm';
			// ignore these fields
			if($k !== 'id' && $k !== 'horaire_am_start' && $k !== 'horaire_am_end' && $k !== 'horaire_pm_start' && $k !== 'horaire_pm_end' && $k !== 'especes_ouverture' && $k !== 'especes_fermeture' && $k !== 'cheques_ouverture' && $k !== 'cheques_fermeture' && $k !== 'depot_especes' && $k !== 'depot_cheques' ){
				
				// start output
				if($i == 0){
					$thead .= '<th>'.str_replace(array('total_','_id','_'),array('','',' '), $k).'</th>';
				}

				// formatting exceptions:
				// totals
				if(  $k == 'total_ouverture' || $k == 'total_fermeture' || $k == 'total_depot_banque'){
					if($k == 'total_ouverture'){
						if(isset($prev_fond_de_caisse) && strval($v) !== strval($prev_fond_de_caisse)){
							$class = 'error';
						}
						$v_prime = 'espèces:'.$c['especes_ouverture'].'<br>chèques:'.$c['cheques_ouverture'];
					}elseif($k == 'total_fermeture'){
						$v_prime = 'espèces:'.$c['especes_fermeture'].'<br>chèques:'.$c['cheques_fermeture'];
					}else{
						$v_prime = 'espèces:'.$c['depot_especes'].'<br>chèques:'.$c['depot_cheques'];
					}
					$v = '<div class="short">'.$v.'<div class="long">'.$v_prime.'</div></div>';
				
				// recettes, delta
				}elseif( ($k == 'recettes' || $k == 'delta') && $v < 0){
					$class = 'error';
				
				// remarques
				}elseif( $k == 'remarques' && !empty($v) ){
					$less = substr($v, 0, 15);
					if($less !== $v){
						$v = '<div class="short">'.$less.'…<div class="long">'.$v.'</div></div>';
					}else{
						$v = $less;
					}
				
				// statut
				}elseif($k == 'statut_id'){
					if($v == 1){
						$class = 'success';
						$v = 'Ouverte';
					}else if($v == 2){
						$v = 'Fermée';
					}
				
				// heures
				}elseif($k == 'heures'){
					$v = '<div class="short">'.$v.'<div class="long">('.$c['horaire_am_start'].'-'.$c['horaire_am_end'].'<br>'.$c['horaire_pm_start'].'-'.$c['horaire_pm_end'].')</div></div>';
				}

				$caisse_output .= '<td class="'.$class.'">'.$v.'</td>';
			}
		}

		if($i == 0){
			$output .= '<thead><tr>'.$thead.'</tr></thead>
			<tr>'.$caisse_output.'</tr>';
		}else{
			$output .= '<tr>'.$caisse_output.'</tr>';
		}
		
		$i++;
		$prev_fond_de_caisse = $c['fond_de_caisse'];

		// add to totals
		$passages_tot += $c['passages'];
		$ventes_tot += $c['ventes'];
		$recettes_tot += $c['recettes'];
		$delta_tot += $c['delta'];
		$seconds_tot += $seconds;
		$heures_total = $seconds_tot / ( 60 * 60 );
	}

	// month totals
	$output .= '<tr class="totals">
	<td colspan="4" style="text-align:left;">'.$i.' Jours Ouverts &horbar; Total &rarr;</td>
	<td>'.$passages_tot.'</td>
	<td colspan="5"></td>
	<td>'.$ventes_tot.'</td>
	<td>'.$recettes_tot.'</td>
	<td>'.$delta_tot.'</td>
	<td>'.$heures_total.'</td>
	</tr>';
	$output .= '</table>';

	return $output;
}


// date form submit (form.dateForm)
if( isset($_POST['month']) && isset($_POST['year']) ){
	if( is_numeric($_POST['month']) && is_numeric($_POST['year']) ){
		$month = sprintf('%02d', $_POST['month']);
		$year = $_POST['year'];
		if( empty($year) ){
			$year = '2020';
		}elseif(strlen($year) == 2){
			$year = '20'.$year;
		}
		$caisse_date = $year.'-'.$month;
	}else{
		$caisse_date = date('Y-m'); // = today
		$error = '<p class="error">La date est mal formée (la forme doit être: 12 2020)</p>';
	}
}else{
	$caisse_date = date('Y-m'); // = today
}


list($year, $month) = explode('-', $caisse_date);

// get the date's caisse if it already exists, set required values
if( $caisses_table = get_table('caisse', 'MONTH(date) = '.$month.' AND YEAR(date) = '.$year, 'date') ){
	$output = show_caisses($caisses_table);
}else{
	$output = '<p class="lowkey">Pas de caisses pour cette date: '.$month.' - '.$year.'</p>';
}

?>

<!-- admin css -->
<link href="<?php echo REL; ?>c/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">

<style>
table{background-color:#ccc; border-spacing:1px; border:none;}
table tr:hover td.norm{background-color:#eee;}
table td, table th{ background-color: #fff; border:none; padding:3px;}
table td{text-align:right;}
table th{background-color:#666; color:#fff;}
td.error, td.warning, td.success{display:table-cell; overflow:visible; border-radius:0; border:none; padding:3px;}
table tr.totals td{font-weight:bold; background-color:transparent;}
</style>

<?php
$message = $message_script = '';

echo '<div id="working"><div class="note">working...</div></div>';
echo '<div id="done">'.$message.'</div>';
?>

<!-- adminHeader start -->
<div class="adminHeader">
<h1><a href="<?php echo REL; ?>admin" class="admin">Admin <span class="home">&#8962;</span></a></h1>
<h2>Caisses du mois: <form name="dateVentes" class="dateForm" action="" method="post"><input type="text" name="month" value="<?php echo $month; ?>" size="2" maxlength="2"><input type="text" name="year" value="<?php echo $year; ?>" size="4" maxlength="4"><input type="submit" name="submitDateVentes" value="&gt;" style="position:absolute; top:-100px;"></form></h2>

<div class="clearBoth"></div>
</div>
<!-- adminHeader end -->

<?php 
//include(ROOT.'c/php/admin/forms/paniersModal.php');
?>


<!-- start admin container -->
<div id="adminContainer">

<?php
// show caisse result after ouverture or fermeture - and exit (do not display the form)
if( isset($result) ){
	echo $result;
}
echo $output;
?>



</div><!-- end admin container -->

<?php
require(ROOT.'/c/php/admin/admin_footer.php');
?>

</body>
</html>