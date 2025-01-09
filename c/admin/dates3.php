<?php
if( !defined("ROOT") ){
	require('../php/first_include.php');
}
// format numbers european style
function nf($num){
	return str_replace(".",",",$num);
}
// set default date range (=today)
$date_debut = array(date('d'),date('m'),date('Y'));
$date_fin = array(date('d'),date('m'),date('Y'));

if (isset($_GET['jour'])){
    $date_debut[0] = $_GET['jour'];
    if (isset($_GET['jour2'])) $date_fin[0] = $_GET['jour2'];
    else $date_fin[0] = $_GET['jour'];
}
if (isset($_GET['mois'])){
    $date_debut[1] = $_GET['mois'];
    if (isset($_GET['mois2'])) $date_fin[1] = $_GET['mois2'];
    else $date_fin[1] = $_GET['mois'];
}
if (isset($_GET['an'])){
    $date_debut[2] = $_GET['an'];
    if (isset($_GET['an2'])) $date_fin[2] = $_GET['an2'];
    else $date_fin[2] = $_GET['an'];
}
$time_start = mktime(0,0,0,$date_debut[1],$date_debut[0],$date_debut[2]);
$time_end = mktime(23,59,59,$date_fin[1],$date_fin[0],$date_fin[2]);  

// SQL QUERIES

// calculate ventes and poids totals from paniers table
$ventes_poids_query = mysqli_query($db,'SELECT SUM(total) AS `Ventes`, SUM(poids) AS `Poids` FROM `paniers` WHERE statut_id = 4 AND `date_vente`>'.$time_start.' AND `date_vente`<'.$time_end.'') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);

// get matieres table
$matieres_query = mysqli_query($db,'SELECT * FROM `matieres` WHERE `id_parent`=0') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);

$participations_id = name_to_id('Participations', 'categories'); 
if($participations_id == false){
	echo '<p class="error">ERREUR:<br>La catégorie "Participations" n\'existe plus dans la base de donnée!</p>';
}

$matiere_autre_id = name_to_id('(hors matériaux)', 'matieres'); // matériauthèque
if($matiere_autre_id == false){
	echo '<p class="error">ERREUR:<br>La catégorie "(hors matériaux)" n\'existe plus dans la base de donnée!</p>';
}

?>
<style>
body, table td, input{font-family: 'Courier New', Courier, monospace;}
html{background-color:#eee;}
form input[type=text]{width:30px; border:1px solid #ccc; font-size:15px;}
form input.an{width:50px;}
table{background-color:#eee; border-spacing:1px; float:left;}
tr.main td{font-weight:bold;}
table td{padding:1px 10px 1px 0; background-color:#fff;}
td.right{text-align: right;}
.button{background-color: rgb(1, 156, 27);
color: #fff;
border-color: #1c3b14;display: inline-block;
padding: 3px 10px;
border: 1px solid #888;
border-radius: 3px;
margin: 2px 0 2px 4px;
cursor: pointer;
font-size:medium;
}
</style>
<form name="daterange" method="GET" action="">
<table>
<tr>
<td>Date début :<td><input type="text" name="jour" onClick="this.select();" value="<?php echo $date_debut[0] ?>">/<input type="text" name="mois" onClick="this.select();" value="<?php echo $date_debut[1] ?>">/<input type="text" name="an" class="an" onClick="this.select();" value="<?php echo $date_debut[2] ?>"><td></tr>
<tr>
<td>Date fin &nbsp;&nbsp;:<td><input type="text" name="jour2" onClick="this.select();" value="<?php echo $date_fin[0] ?>">/<input type="text" name="mois2" onClick="this.select();" value="<?php echo $date_fin[1] ?>">/<input type="text" name="an2" onClick="this.select();" class="an" value="<?php echo $date_fin[2] ?>"><td><input type="submit" value="CALCULER" class="button"></tr>
</table>
</form>
<div style="clear:both;">&nbsp;</div>
	
<?php
// output ventes / poids query
while( $row = mysqli_fetch_assoc($ventes_poids_query) ){
	$ventes_poids_data[] = $row;
}
if( !empty($ventes_poids_data) ){
	echo '<b>Ventes : '.nf($ventes_poids_data[0]['Ventes']).' &nbsp;€</b></br>';
	echo '<b>Poids &nbsp;: '.nf($ventes_poids_data[0]['Poids']).' kg</b></br>';
}else{
	echo 'Aucune vente';
}
?>
</br>
</br>

<!-- MATIERES TABLE -->
<table>
    <tr>
        <td><u>MATIÈRE</u></td>
		<td class="right"><u>UNITÉS</u></td>
        <td class="right"><u>VENTES</u></td>
        <td class="right"><u>POIDS</u></td>
    </tr>
<?php
// output details from matieres query 
while( $matieres_row = mysqli_fetch_assoc($matieres_query) ){
    $detail_matieres_data_array = array(0,0);    
    if($detail_matieres_query = mysqli_query($db, 'SELECT COUNT(articles.id) AS `unites`, SUM(articles.prix) AS `Ventes`, SUM(articles.poids) AS `Poids` FROM `paniers`,`articles` WHERE `paniers`.`statut_id`=4 AND matieres_id='.$matieres_row['id'].' AND paniers_id=paniers.id AND paniers.`date_vente`>'.$time_start.' AND paniers.`date_vente`<'.$time_end) or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__)){
        $detail_matieres_data_array = mysqli_fetch_assoc($detail_matieres_query);
    }
    echo '<tr class="main">';
    echo '<td>';
    echo $matieres_row['nom'];
    echo '</td>';
	echo '<td class="right">';
    if(isset($detail_matieres_data_array['unites'])){echo $detail_matieres_data_array['unites'];}
    echo '</td>';
    echo '<td class="right">';
	if(isset($detail_matieres_data_array['Ventes'])){echo nf($detail_matieres_data_array['Ventes']);}
    echo '</td>';
    echo '<td class="right">';
	if(isset($detail_matieres_data_array['Poids'])){echo nf($detail_matieres_data_array['Poids']);}
    echo '</td>';
		$sous_matieres_query = mysqli_query($db, 'SELECT * FROM `matieres` WHERE `id_parent`='.$matieres_row['id'].'') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);
    while( $sous_matieres_row = mysqli_fetch_assoc($sous_matieres_query) ){
        $sous_matieres_array = array(0,0);
        if($sous_matieres_detail_query = mysqli_query($db, 'SELECT COUNT(articles.id) AS `unites`, SUM(articles.prix) AS `Ventes`, SUM(articles.poids) AS `Poids` FROM `paniers`,`articles` WHERE `paniers`.`statut_id`=4 AND sous_matieres_id='.$sous_matieres_row['id'].' AND paniers_id=paniers.id AND paniers.`date_vente`>'.$time_start.' AND paniers.`date_vente`<'.$time_end.'') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__)){
            $sous_matieres_array = mysqli_fetch_assoc($sous_matieres_detail_query);
        }
        echo '<tr class="sub">';
        echo '<td>';
        echo $sous_matieres_row['nom'];
        echo '</td>';
		echo '<td class="right">';
        if(isset($sous_matieres_array['unites'])){echo $sous_matieres_array['unites'];}
        echo '</td>';
        echo '<td class="right">';
        if(isset($sous_matieres_array['Ventes'])){echo nf($sous_matieres_array['Ventes']);}
        echo '</td>';
        echo '<td class="right">';
        if(isset($sous_matieres_array['Poids'])){echo nf($sous_matieres_array['Poids']);}
        echo '</td>';
        echo '</tr>';
    }
    echo '</tr>';
}
?>
</table>


<!-- CATÉGORIES TABLE -->
<table style="margin-left:20px;">
    <tr>
        <td><u>CATÉGORIE</u></td>
		<td class="right"><u>UNITÉS</u></td>
        <td class="right"><u>VENTES</u></td>
    </tr>
<?php
// output participations query

$detail_categories_data_array = array(0,0); 
if($detail_categories_query = mysqli_query($db, 'SELECT COUNT(articles.id) AS `unites`, SUM(articles.prix) AS `Ventes` FROM `paniers`,`articles` WHERE `paniers`.`statut_id`=4 AND categories_id='.$participations_id.' AND paniers_id=paniers.id AND paniers.`date_vente`>'.$time_start.' AND paniers.`date_vente`<'.$time_end.'') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__)){
	$detail_categories_data_array = mysqli_fetch_assoc($detail_categories_query);
}
echo '<tr class="main">';
echo '<td>';
echo 'Participations';
echo '</td>';
echo '<td class="right">';
if(isset($detail_categories_data_array['unites'])){echo $detail_categories_data_array['unites'];}
echo '</td>';
echo '<td class="right">';
if(isset($detail_categories_data_array['Ventes'])){echo nf($detail_categories_data_array['Ventes']);}
echo '</td>';
$sous_categories_query = mysqli_query($db, 'SELECT * FROM categories WHERE id_parent=50') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);
while( $sous_categories_row = mysqli_fetch_assoc($sous_categories_query) ){
	$sous_categories_array = array(0,0);
	if($sous_categories_detail_query = mysqli_query($db, 'SELECT COUNT(articles.id) AS `unites`, SUM(articles.prix) AS `Ventes` FROM `paniers`,`articles` WHERE `paniers`.`statut_id`=4 AND sous_categories_id='.$sous_categories_row['id'].' AND paniers_id=paniers.id AND paniers.`date_vente`>'.$time_start.' AND paniers.`date_vente`<'.$time_end.'') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__)){
		$sous_categories_array = mysqli_fetch_assoc($sous_categories_detail_query);
	}
	echo '<tr class="sub">';
	echo '<td>';
	echo $sous_categories_row['nom'];
	echo '</td>';
	echo '<td class="right">';
	if(isset($sous_categories_array['unites'])){echo $sous_categories_array['unites'];}
	echo '</td>';
	echo '<td class="right">';
	if(isset($sous_categories_array['Ventes'])){echo nf($sous_categories_array['Ventes']);}
	echo '</td>';
	echo '</tr>';
}
echo '</tr>';

?>
</table>

<div style="clear:both; margin-bottom:70px;">&nbsp;</div>
