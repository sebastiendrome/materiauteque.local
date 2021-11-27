<?php
require('../_code/php/first_include.php');
require(ROOT.'_code/php/admin/not_logged_in.php');
require(ROOT.'_code/php/admin/admin_functions.php');
$title = 'VENTES';
require(ROOT.'_code/php/doctype.php');
if( isset($_SESSION['article_id']) ){
	unset($_SESSION['article_id']);
}

// date
if(isset($_POST['day']) && isset($_POST['month']) && isset($_POST['year']) ){
	if( is_numeric($_POST['day']) && is_numeric($_POST['month']) && is_numeric($_POST['year']) ){
		$day = sprintf('%02d', $_POST['day']);
		$month = sprintf('%02d', $_POST['month']);
		$year = $_POST['year'];
		if( empty($year) ){
			$year = '2020';
		}elseif(strlen($year) == 2){
			$year = '20'.$year;
		}
		$date = $day.'-'.$month.'-'.$year;
		$_SESSION['dateVentes'] = $date;
	}else{
		$date = date('d-m-Y'); // = today
		$error = '<p class="error">La date est mal formée (la forme doit être: 01 01 2020)</p>';
	}
}elseif( isset($_SESSION['dateVentes']) ){
	// if session date = yesterday, change to today
	if( $_SESSION['dateVentes'] == date('d-m-Y', strtotime('yesterday')) ){
		$_SESSION['dateVentes'] = date('d-m-Y'); // = today
	}
	$date = $_SESSION['dateVentes'];
}else{
	$date = date('d-m-Y'); // = today
}



list($day, $month, $year) = explode('-', $date);


// get paniers vendus by date
$time_start = mktime(0, 0, 0, $month, $day, $year); // = today
$time_end = $time_start+86400; // ce jour là seulement
//$time_end = $time_start+time(); // depuis ce jour jusqu'à aujourd'hui
$vendu = name_to_id('vendu', 'statut');
$paniers_vendus = get_table('paniers', 'statut_id='.$vendu.' AND date_vente>='.$time_start.' AND date_vente<'.$time_end, 'date DESC');

?>

<!-- admin css -->
<link href="/_code/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">

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
<h1><a href="/admin" class="admin">Admin <span class="home">&#8962;</span></a></h1>

<h2>Ventes <form name="dateVentes" class="dateForm" action="" method="post"><input type="text" name="day" value="<?php echo $day; ?>" size="2" maxlength="2"><input type="text" name="month" value="<?php echo $month; ?>" size="2" maxlength="2"><input type="text" name="year" value="<?php echo $year; ?>" size="4" maxlength="4"><input type="submit" name="submitDateVentes" value="&gt;" style="position:absolute; top:-100px;"></form></h2> 
<a href="/_code/php/forms/nouvelle-vente.php" class="button vente" rel="nouvelle-vente" title="rechercher ou créer un article à vendre">+ Nouvelle vente</a>

<!--<a href="/admin/manage_adhesions.php" class="button edit">Adhésions</a> <span style="font-size:20px; display:inline-block; margin-left:10px; margin-right:6px;">•</span>--> <a href="/admin/articles.php" class="button articles edit" title="Gérer les articles">Articles</a><!-- <span style="font-size:20px; display:inline-block; margin-left:10px; margin-right:6px;">•</span> <a href="/admin/manage_categories.php?table=categories" class="button edit" title="gérer les catégories">Catégories</a> <a href="/admin/manage_categories.php?table=matieres" class="button edit" title="gérer les matières">Matières</a> -->
<a href="javascript:;" class="button paniersBut right showPaniers"><img src="/_code/images/panier.svg" style="width:15px;height:15px; margin-bottom:-2px; margin-right:10px;">Paniers en cours (<span id="paniersCount"><?php echo $paniers_count; ?></span>)</a>

<div class="clearBoth"></div>
</div>
<!-- adminHeader end -->

<?php 
include(ROOT.'_code/php/forms/paniersModal.php');
?>


<!-- start admin container -->
<div id="adminContainer">

<?php if(isset($error)){echo $error;} ?>

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