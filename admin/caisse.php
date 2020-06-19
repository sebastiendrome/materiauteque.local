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

echo '<div id="working"><div class="note">working...</div></div>';
echo '<div id="done">'.$message.'</div>';
?>

<!-- adminHeader start -->
<div class="adminHeader">
<h1><a href="/admin" class="admin">Admin <span class="home">&#8962;</span></a> CAISSE</h1>
<div class="clearBoth"></div>
</div>
<!-- adminHeader end -->

<!-- start admin container -->
<div id="adminContainer">

<form name="caisee" action="" method="post">
<input type="hidden" name="caisseSubmitted" value="caisseSubmitted">

<button type="submit" name="searchSubmit"> SAUVEGARDER </button>
</form>


</div><!-- end admin container -->

<?php
require(ROOT.'/_code/php/admin/admin_footer.php');
?>

</body>
</html>