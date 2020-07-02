<?php
require('../_code/php/first_include.php');
require(ROOT.'_code/php/admin/not_logged_in.php');
require(ROOT.'_code/php/admin/admin_functions.php');
require(ROOT.'_code/php/doctype.php');
if( isset($_SESSION['article_id']) ){
	unset($_SESSION['article_id']);
}
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

<a href="/admin/caisse.php" class="button caisse edit selected" title="Caisse (ouverture et fermeture)">Caisse</a> <a href="/admin/ventes.php" class="button vente edit" title="Gérer les ventes">Ventes</a> <!--<a href="/admin/manage_adhesions.php" class="button edit">Adhésions</a> <span style="font-size:20px; display:inline-block; margin-left:10px; margin-right:6px;">•</span>--> <a href="/admin/articles.php" class="button articles edit" title="Gérer les articles">Articles</a><!-- <span style="font-size:20px; display:inline-block; margin-left:10px; margin-right:6px;">•</span> <a href="/admin/manage_categories.php?table=categories" class="button edit" title="gérer les catégories">Catégories</a> <a href="/admin/manage_categories.php?table=matieres" class="button edit" title="gérer les matières">Matières</a> -->
<a href="javascript:;" class="button paniersBut right showPaniers"><img src="/_code/images/panier.svg" style="width:15px;height:15px; margin-bottom:-2px; margin-right:10px;">Paniers en cours (<span id="paniersCount"><?php echo $paniers_count; ?></span>)</a>

<div class="clearBoth"></div>
</div>
<!-- adminHeader end -->

<?php 
include(ROOT.'_code/php/forms/paniersModal.php');
?>


<!-- start admin container -->
<div id="adminContainer">

<form name="caisse" action="" method="post">
<input type="hidden" name="caisseSubmitted" value="caisseSubmitted">



<button type="submit" name="searchSubmit"> SAUVEGARDER </button>
</form>


</div><!-- end admin container -->

<?php
require(ROOT.'/_code/php/admin/admin_footer.php');
?>

</body>
</html>