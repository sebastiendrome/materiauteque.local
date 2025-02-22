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
?>
<style>
body, table td, input{font-family: 'Courier New', Courier, monospace;}
form input[type=text]{width:30px; border:1px solid #ccc; font-size:15px;}
form input.an{width:50px;}
table{background-color:#eee; border-spacing:1px;}
tr.main td{font-weight:bold;}
table td{padding:1px 10px 1px 0; background-color:#fff;}
td.right{text-align: right;}
</style>
<form name="daterange" method="GET" action="">
<table>
<tr>
<td>Date début :<td><input type="text" name="jour" value="<?php echo $date_debut[0] ?>">/<input type="text" name="mois" value="<?php echo $date_debut[1] ?>">/<input type="text" name="an" class="an" value="<?php echo $date_debut[2] ?>"><td></tr>
<tr>
<td>Date fin &nbsp;&nbsp;:<td><input type="text" name="jour2" value="<?php echo $date_fin[0] ?>">/<input type="text" name="mois2" value="<?php echo $date_fin[1] ?>">/<input type="text" name="an2" class="an" value="<?php echo $date_fin[2] ?>"><td><input type="submit" value="CALCULER"></tr>
</table>
</form>
<!--
	Time : <?php echo time() ?></br>
Début hier : <?php echo mktime(0,0,0,$date_debut[1],$date_debut[0],$date_debut[2]) ?></br>
Fin hier : <?php echo mktime(23,59,59,$date_fin[1],$date_fin[0],$date_fin[2]) ?></br>
</br>
-->
	
<?php
// calculate totals (ventes and poids)
$query = mysqli_query($db,'SELECT SUM(total) AS `Ventes`, SUM(poids) AS `Poids` FROM `paniers` WHERE `date_vente`>'.$time_start.' AND `date_vente`<'.$time_end.'') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);
	while( $row = mysqli_fetch_assoc($query) ){
		$data[] = $row;
	}
	if( !empty($data) ){
		echo '<b>Ventes : '.nf($data[0]['Ventes']).' &nbsp;€</b></br>';
		echo '<b>Poids &nbsp;: '.nf($data[0]['Poids']).' kg</b></br>';
	}else{
		echo 'Aucune vente';
	}
?>
</br>
</br>
<table>
    <tr>
        <td><u>CATEGORIE</u></td>
		<td class="right"><u>UNITÉS</u></td>
        <td class="right"><u>VENTES</u></td>
        <td class="right"><u>POIDS</u></td>
    </tr>
<?php
$query = mysqli_query($db,'SELECT * FROM `categories` WHERE `id_parent`=0') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);
while( $row = mysqli_fetch_assoc($query) ){
	$somme2 = array(0,0);
    $somme = array(0,0);    
    if($query3 = mysqli_query($db,'SELECT COUNT(articles.id) AS `unites`, SUM(articles.prix) AS `Ventes`, SUM(articles.poids) AS `Poids` FROM `paniers`,`articles` WHERE `paniers`.`statut_id`=4 AND categories_id='.$row['id'].' AND paniers_id=paniers.id AND paniers.`date_vente`>'.$time_start.' AND paniers.`date_vente`<'.$time_end.'') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__)){
        $somme = mysqli_fetch_assoc($query3);
    }
    echo '<tr class="main">';
    echo '<td>';
    echo $row['nom'];
    echo '</td>';
	echo '<td class="right">';
    if(isset($somme['unites'])){echo $somme['unites'];}
    echo '</td>';
    echo '<td class="right">';
	if(isset($somme['Ventes'])){echo nf($somme['Ventes']);}
    echo '</td>';
    echo '<td class="right">';
	if(isset($somme['Poids'])){echo nf($somme['Poids']);}
    echo '</td>';
		$query2 = mysqli_query($db,'SELECT * FROM `categories` WHERE `id_parent`='.$row['id'].'') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);
    while( $row2 = mysqli_fetch_assoc($query2) ){
        $somme2 = array(0,0);
        if($query4 = mysqli_query($db,'SELECT COUNT(articles.id) AS `unites`, SUM(articles.prix) AS `Ventes`, SUM(articles.poids) AS `Poids` FROM `paniers`,`articles` WHERE `paniers`.`statut_id`=4 AND sous_categories_id='.$row2['id'].' AND paniers_id=paniers.id AND paniers.`date_vente`>'.$time_start.' AND paniers.`date_vente`<'.$time_end.'') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__)){
            $somme2 = mysqli_fetch_assoc($query4);
        }
        echo '<tr class="sub">';
        echo '<td>';
        echo $row2['nom'];
        echo '</td>';
		echo '<td class="right">';
        if(isset($somme2['unites'])){echo $somme2['unites'];}
        echo '</td>';
        echo '<td class="right">';
        if(isset($somme2['Ventes'])){echo nf($somme2['Ventes']);}
        echo '</td>';
        echo '<td class="right">';
        if(isset($somme2['Poids'])){echo nf($somme2['Poids']);}
        echo '</td>';
        echo '</tr>';
    }
    echo '</tr>';
}
?>
</table>

