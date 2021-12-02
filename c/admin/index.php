<?php
require('../php/first_include.php');
require(ROOT.'c/php/doctype.php');
if( isset($_SESSION['article_id']) ){
	unset($_SESSION['article_id']);
}
?>

<!-- admin css -->
<link href="<?php echo REL; ?>c/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">
<style>
h1.home{color:#000; text-decoration: underline;}
h1.home span.home{transform: rotate(-90deg);}
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
<h1 class="home">Admin <span class="home">&#8962;</span></h1>

<a href="<?php echo REL; ?>c/admin/ventes.php" class="button vente edit venSH" title="Gérer les ventes">Ventes</a> <a href="<?php echo REL; ?>c/admin/articles.php" class="button articles edit artSH" title="Gérer les articles">Articles</a> <a href="<?php echo REL; ?>c/admin/caisse.php" id="caisseOpener" target="caisseWindow" class="button caisse edit caiSH" title="Caisse (ouverture et fermeture)">Caisse</a> <a href="<?php echo REL; ?>c/admin/caisses-au-mois.php" class="button caiSH" title="voir les caisses du mois">Caisse/mois</a> <a href="<?php echo REL; ?>c/admin/manage_categories.php?table=categories" class="button edit catSH" title="gérer les catégories">Catégories</a> <a href="<?php echo REL; ?>c/admin/manage_categories.php?table=matieres" class="button edit catSH" title="gérer les matières">Matières</a> 
<a href="javascript:;" class="button paniersBut right showPaniers venSH"><img src="<?php echo REL; ?>c/images/panier.svg" style="width:15px;height:15px; margin-bottom:-2px; margin-right:10px;">Paniers en cours (<span id="paniersCount"><?php echo $paniers_count; ?></span>)</a>

<div class="clearBoth"></div>
</div>
<!-- adminHeader end -->

<?php 
include(ROOT.'c/php/admin/forms/paniersModal.php');
?>


<!-- start admin container -->
<div id="adminContainer">




</div><!-- end admin container -->

<?php
require(ROOT.'/c/php/admin/admin_footer.php');

echo $message_script;
?>
<script type="text/javascript">
// open child window (caisse) and store the fact
var a = document.getElementById('caisseOpener'), caisseWindow;
a.onclick = function(e){
	e.preventDefault();
	if(!caisseWindow || caisseWindow.closed){
		caisseWindow = window.open(rel+"c/admin/caisse.php","caisseWindow", '', false);
	}else{
		console.log('window is already opened');
	}
	caisseWindow.focus();
};
/*
// reconnect function is defined in child window 
window.onbeforeunload = function(){
	caisseWindow.reconnect();
}

window.saveChildReference = function(ref){
	caisseWindow = ref;
}
*/
</script>
</body>
</html>