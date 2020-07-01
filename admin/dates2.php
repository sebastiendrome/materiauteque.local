<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}
$date_debut = array(13,1,2020);
$date_fin = array(13,1,2020);

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
?>
Debut : <?php echo $date_debut[0] ?>/<?php echo $date_debut[1] ?>/<?php echo $date_debut[2] ?></br>
Fin : <?php echo $date_fin[0] ?>/<?php echo $date_fin[1] ?>/<?php echo $date_fin[2] ?></br>
Time : <?php echo time() ?></br>
Début hier : <?php echo mktime(0,0,0,$date_debut[1],$date_debut[0],$date_debut[2]) ?></br>
Fin hier : <?php echo mktime(23,59,59,$date_fin[1],$date_fin[0],$date_fin[2]) ?></br>
</br>

<?php
$query = mysqli_query($db,'SELECT SUM(total) AS `Ventes`, SUM(poids) AS `Poids` FROM `paniers` WHERE `date_vente`>'.mktime(0,0,0,$date_debut[1],$date_debut[0],$date_debut[2]).' AND `date_vente`<'.mktime(23,59,59,$date_fin[1],$date_fin[0],$date_fin[2]).'') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);
	while( $row = mysqli_fetch_assoc($query) ){
		$data[] = $row;
	}
	if( !empty($data) ){
		echo 'Ventes : '.$data[0]['Ventes'].' €</br>';
		echo 'Poids : '.$data[0]['Poids'].' Kg</br>';
	}else{
		echo 'Aucune vente';
	}
?>
</br>
</br>
<table>
    <tr>
        <td>CATEGORIE</td>
        <td>VENTES</td>
        <td>POIDS</td>
    </tr>
<?php
$query = mysqli_query($db,'SELECT * FROM `categories` WHERE `id_parent`=0') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);
while( $row = mysqli_fetch_assoc($query) ){$somme2 = array(0,0);
    $somme = array(0,0);    
    if($query3 = mysqli_query($db,'SELECT SUM(articles.prix) AS `Ventes`, SUM(articles.poids) AS `Poids` FROM `paniers`,`articles` WHERE `paniers`.`statut_id`=4 AND categories_id='.$row['id'].' AND paniers_id=paniers.id AND paniers.`date_vente`>'.mktime(0,0,0,$date_debut[1],$date_debut[0],$date_debut[2]).' AND paniers.`date_vente`<'.mktime(23,59,59,$date_fin[1],$date_fin[0],$date_fin[2]).'') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__)){
        $somme = mysqli_fetch_assoc($query3);
    }
    echo '<tr>';
    echo '<td>';
    echo '<b>'.$row['nom'].'</b>';
    echo '</td>';
    echo '<td>';
    echo '<b>'.$somme['Ventes'].'</b>';
    echo '</td>';
    echo '<td>';
    echo '<b>'.$somme['Poids'].'</b>';
    echo '</td>';
		$query2 = mysqli_query($db,'SELECT * FROM `categories` WHERE `id_parent`='.$row['id'].'') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__);
    while( $row2 = mysqli_fetch_assoc($query2) ){
        $somme2 = array(0,0);
        if($query4 = mysqli_query($db,'SELECT SUM(articles.prix) AS `Ventes`, SUM(articles.poids) AS `Poids` FROM `paniers`,`articles` WHERE `paniers`.`statut_id`=4 AND sous_categories_id='.$row2['id'].' AND paniers_id=paniers.id AND paniers.`date_vente`>'.mktime(0,0,0,$date_debut[1],$date_debut[0],$date_debut[2]).' AND paniers.`date_vente`<'.mktime(23,59,59,$date_fin[1],$date_fin[0],$date_fin[2]).'') or log_db_errors( mysqli_error($db), 'Function: '.__FUNCTION__)){
            $somme2 = mysqli_fetch_assoc($query4);
        }
        echo '<tr>';
        echo '<td>';
        echo ''.$row2['nom'].'';
        echo '</td>';
        echo '<td>';
        echo ''.$somme2['Ventes'].'';
        echo '</td>';
        echo '<td>';
        echo ''.$somme2['Poids'].'';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tr>';
}
?>
</table>

