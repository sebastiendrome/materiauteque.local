<?php
require('../_code/php/first_include.php');
require(ROOT.'_code/php/admin/not_logged_in.php');
require(ROOT.'_code/php/admin/admin_functions.php');
require(ROOT.'_code/php/doctype.php');
if( isset($_SESSION['article_id']) ){
	unset($_SESSION['article_id']);
}

/*
if(isset($_GET)){
	print_r($_GET);
}
*/

// paniers en cours for paniersModal.php
$paniers = get_table('paniers', 'statut_id=1', 'date DESC');

//$ventes = get_ventes();
//$items_table = ventes_table_output($ventes);


// get paniers vendus today
//$time_start = strtotime('yesterday');
$time_start = strtotime('today');
//$time_start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
$time_end = time();
$paniers_vendus = get_table('paniers', 'statut_id=4 AND date_vente>='.$time_start.' AND date_vente<'.$time_end, 'date DESC');

//echo $time_start.'<br>'.$time_end.'<br>';

// result message passed via query string
if( isset($message) && !empty($message) ){
	$message = str_replace(array('0|', '1|', '2|'), array('<p class="error">', '<p class="success">', '<p class="note">'), $message).'</p>';
	$message_script = '<script type="text/javascript">showDone();</script>';
}else{
	$message = $message_script = '';
}

?>

<!-- admin css -->
<link href="/_code/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">

<?php
if( isset($_GET['upload_result']) ){
	$message = urldecode($_GET['upload_result']);
}elseif( isset($_GET['message']) ){
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
<h1><a href="/admin" class="admin">Admin <span class="home">&#8962;</span></a></h1>

<!--<a href="/admin/caisse.php" class="button caisse edit" title="Caisse (ouverture et fermeture)">Caisse</a> --><a href="/admin/ventes.php" class="button vente edit selected" title="Gérer les ventes">Ventes</a> <!--<a href="/admin/manage_adhesions.php" class="button edit">Adhésions</a> <span style="font-size:20px; display:inline-block; margin-left:10px; margin-right:6px;">•</span>--> <a href="/admin/articles.php" class="button articles edit" title="Gérer les articles">Articles</a><!-- <span style="font-size:20px; display:inline-block; margin-left:10px; margin-right:6px;">•</span> <a href="/admin/manage_categories.php?table=categories" class="button edit" title="gérer les catégories">Catégories</a> <a href="/admin/manage_categories.php?table=matieres" class="button edit" title="gérer les matières">Matières</a> -->
<a href="javascript:;" class="button paniersBut right showPaniers"><img src="/_code/images/panier.svg" style="width:15px;height:15px; margin-bottom:-2px; margin-right:10px;">Paniers en cours</a>

<div class="clearBoth"></div>
</div>
<!-- adminHeader end -->

<?php 
include(ROOT.'_code/php/forms/paniersModal.php');
?>


<!-- start admin container -->
<div id="adminContainer">


<h2 style="display:inline-block; margin-right:10px;">Ventes <?php echo date('d-m-Y'); ?></h2> <a href="/_code/php/forms/nouvelle-vente.php" class="button vente" rel="nouvelle-vente" title="rechercher ou créer un article à vendre">+ Nouvelle vente</a>
<a name="pp"></a>

<?php
echo '<div class="tableContainer" id="ventesPaniersContainer">
<div id="ventesPaniersAjaxTarget" style="padding-bottom:200px;">';

echo display_paniers($paniers_vendus);
echo '</div>
</div>';
?>

</div>


</div><!-- end admin container -->

<?php
require(ROOT.'/_code/php/admin/admin_footer.php');

echo $message_script;
?>
</body>
</html>