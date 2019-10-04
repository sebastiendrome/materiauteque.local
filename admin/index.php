<?php
require('../_code/php/first_include.php');
require(ROOT.'_code/php/admin/not_logged_in.php');
require(ROOT.'_code/php/admin/admin_functions.php');
require(ROOT.'_code/php/doctype.php');
if( isset($_SESSION['article_id']) ){
	unset($_SESSION['article_id']);
}

$paniers = get_table('paniers', 'statut=0', 'date DESC');

// make sure we get the needed data, if we don't have it already
if( !isset($categories) || empty($categories) ){
	$categories = get_table('categories', 'visible=1 AND id_parent=0');
}

// process form POST data (simple search)
if( isset($_POST['simpleSearch']) ){

	// check and clean up user input
	if( isset($_POST['keywords'])  && !empty($_POST['keywords']) ){
		$keywords = trim($_POST['keywords']);
		$keywords = normalize($keywords);
		$keywords = cleanXXS($keywords);
	}else{
		$keywords = '';
	}
	if( isset($_POST['categories_id']) && is_numeric($_POST['categories_id']) ){
		$categories_id = trim($_POST['categories_id']);
	}else{
		$categories_id = '';
	}
	if( isset($_POST['sous_categories_id']) && is_numeric($_POST['sous_categories_id']) ){
		$sous_categories_id = trim($_POST['sous_categories_id']);
	}else{
		$sous_categories_id = '';
	}
	if($keywords !== '' || $categories_id !== ''){
		$ids = search($keywords, $categories_id, $sous_categories_id, /*visible-only=*/FALSE, /*vendus=*/FALSE);
		if( !empty($ids) ){
			foreach($ids as $id){
				//echo 'Article #'.$key.'<br>';
				$search_items[] = get_item_data($id);
			}
		}
	}
}else{
	$keywords = $categories_id = '';
}



// pagination
// get all ids just to count the number of articles
$all_articles = get_items_data('id', 'all');
$count = count($all_articles);
if(isset($_GET['limit']) && !empty($_GET['limit'])){
	// validate
	if(preg_match('/^\d*?$/',$_GET['limit']) && $_GET['limit']<=$count){
		$limit = $_GET['limit'];
		$_SESSION['limit'] = $limit;
		$p = 1;
		$_SESSION['p'] = $p;
	}
}elseif( isset($_SESSION['limit']) ){
	$limit = $_SESSION['limit'];

}else{
	$limit = 20;
}
$pages = ceil($count/$limit);

if(isset($_GET['p'])){
	// validate
	if(preg_match('/^\d*?$/',$_GET['p'])){
		$p = $_GET['p'];
		if($_GET['p']>$pages){
			$p = 1;
		}elseif($_GET['p']<1){
			$p = $pages;
		}
		$_SESSION['p'] = $p;
	}
}elseif( isset($_SESSION['p']) ){
	$p = $_SESSION['p'];
}else{
	$p = 1;
}

$offset = ($p-1)*$limit;
$w_end = $offset+$limit;
if($w_end > $count){
	$w_end = $count;
}
$next = $p+1;
$prev = $p-1;
if($next >$pages){
	$next = 1;
}
if($prev ==0){
	$prev = $pages;
}

$fields = array('date', 'titre', 'descriptif', 'categories_id', 'observations', 'statut_id'/*, 'visible'*/);
$articles = get_items_data($fields, 'all', FALSE, 'all', 'date DESC', $limit, $offset);

$items_table = items_table_output($articles);

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

<a href="/_code/php/forms/newArticle.php" class="button add left" title="créer un article">Nouvel article</a> <!--<a href="/_code/php/forms/findArticle.php" class="button edit">Rechercher un article</a> --><a href="/_code/php/forms/ventes.php" class="button vente" title="rechercher ou créer un article à vendre">€ Nouvelle vente</a> <span style="font-size:20px; display:inline-block; margin-left:10px; margin-right:6px;">•</span> <a href="/admin/manage_categories.php?table=categories" class="button edit" title="gérer les catégories">Catégories</a> <a href="/admin/manage_categories.php?table=matieres" class="button edit" title="gérer les matières">Matières</a><!-- <a href="/admin/manage_adhesions.php" class="button edit">Adhésions</a> -->

<div class="clearBoth"></div>
</div>
<!-- adminHeader end -->

<?php 
include(ROOT.'_code/php/forms/paniersModal.php');
?>


<!-- start admin container -->
<div id="adminContainer">

<!-- recherche simple start -->
<form name="search" class="searchForm" action="#top" method="post"> Rechercher un article:
<input type="hidden" name="simpleSearch" value="simpleSearch">
<input type="text" name="keywords" value="<?php echo $keywords; ?>" placeholder="Titre, descriptif..." style="background-image:none;"><select name="categories_id" style="min-width:auto;">
<option value="">Toutes catégories</option>
<?php 
foreach($categories as $c){
	echo '<option value="'.$c['id'].'"';
	if( $categories_id == $c['id'] ){
		echo ' selected';
	}
	echo '>'.$c['nom'].'</option>'.PHP_EOL;
}
?>
</select>
<!--
<select name="sous_categories_id" style="min-width:auto;" disabled>
<option value="">Toutes matières</option>
</select>
-->
<button type="submit" name="searchSubmit">Rechercher</button> <a href="/_code/php/forms/findArticle.php">&nbsp;>Recherche détaillée</a>
</form>
<!-- recherche simple end -->

<?php

if( isset($search_items) && !empty($search_items)){
	echo '<div class="success clearBoth" style="overflow:auto;">';
	if(isset($results)){
		$search_count = count($results);
		if($search_count>1){$s='s';}else{$s='';} // plural or singular
		echo '<p><b>'.$search_count.' article'.$s.' trouvé'.$s.'.</b><br> 
		Paramètres de recherche: ';
		foreach($key_val_pairs as $rk => $rv){
			if( !empty($rv) ){
				if( is_array($rv) ){
					$string = '';
					foreach($rv as $rrk => $rrv){
						$string .= $rrk.':'.$rrv.'&nbsp;&nbsp;';
					}
					$rv = $string;
				}
				echo $rk.' = '.$rv;
			}
		}
		echo '</p>';

	}else{
		$search_count = count($search_items);
		if($search_count>1){$s='s';}else{$s='';} // plural or singular
		echo '<p><b>'.$search_count.' article'.$s.' trouvé'.$s.'.</b></br> 
		Paramètres de recherche: ';
		echo $keywords;
		if(!empty($categories_id)){
			echo '&nbsp;&nbsp;Catégorie: '.id_to_name($categories_id, 'categories');
		}
		echo '</p>';
	}

	$search_table = items_table_output($search_items);
	echo $search_table;

	echo '</div>';

}elseif( isset($_POST['simpleSearch']) ){
	echo '<p class="note">Aucun résultat...</p>';
}
?>


<div class="clearBoth" style="margin-top:35px;">

<h2 style="display:inline-block; margin-right:10px;">Articles</h2> 
<span style="white-space:nowrap;"><?php echo ($offset+1).'—'.$w_end.' sur '.$count; ?>&nbsp;&nbsp;</span>
<span style="white-space:nowrap;">Voir <input type="text" name="limit" value="<?php echo $limit; ?>" style="min-width:25px; width:25px; text-align:right;" onClick="this.select();" onChange="window.location.href='?limit='+this.value;"> articles par page.&nbsp;&nbsp;</span>
<span style="white-space:nowrap;">Sauter à la page <input type="text" name="p" value="" style="min-width:25px; width:25px; text-align:right;" onClick="this.select();" onChange="window.location.href='?limit=<?php echo $limit; ?>&p='+this.value;"></span>
<a name="pp"></a>
<?php 
$navigation_output = '';

if($pages >1){
	$navigation_output .= '<div class="pagination" style="text-align:center; margin:10px 0;">'.PHP_EOL;
	$navigation_output .= '<a href="?limit='.$limit.'&p='.$prev.'#pp" class="butLink navPrev" title="Préc.">❮&nbsp;&nbsp;&nbsp;</a>';

	$p_loop = 1;
	while( $p_loop <= $pages ){
		$class= '';
		if( $p_loop == $p ){
			$class=' selected';
		}
		
		if($pages > 30){
			$r_start = $p-7;
			$r_end = $p+7 ;
			
			if( ($p_loop > $r_start && $p_loop < $r_end) || $p_loop > $pages-7 || $p_loop < 8 ){
				$navigation_output .= '<a href="?limit='.$limit.'&p='.$p_loop.'#pp" class="butLink'.$class.'">'.$p_loop.'</a>';
			}else{
				if( !isset($done) ){
					$navigation_output .= '•••';
					$done = '';
				}elseif( !isset($redone) && $p_loop > $r_end ){
					$navigation_output .= '•••';
					$redone = '';
				}
			}
			
		}else{
			$navigation_output .= '<a href="?limit='.$limit.'&p='.$p_loop.'#pp" class="butLink'.$class.'">'.$p_loop.'</a>';
		}
		$p_loop++;
	}
	$navigation_output .= '<a href="?limit='.$limit.'&p='.$next.'#pp" class="butLink navNext" title="Suiv.">&nbsp;&nbsp;&nbsp;❯</a>'.PHP_EOL;
	
	$navigation_output .= '</div>'.PHP_EOL;

}


echo '<div class="tableContainer">';
echo $navigation_output;
echo $items_table;
echo $navigation_output;
echo '</div>';



?>

</div>


</div><!-- end admin container -->

<?php
require(ROOT.'/_code/php/admin/admin_footer.php');

echo $message_script;
?>

<script type="text/javascript">
$(function(){
	// adjust width of nav div to width of articles table
	var tW = $("table.data").outerWidth();
	//alert(tW);
	$('div.pagination').css('max-width',tW+'px');

	// go to next/previous page on arrow-right and arrow-left key down
	$(document).keydown(function(e) {
		//console.log(e.keyCode);
		// make sure no input is in focus
		if( !$("input").is(":focus") ){
			if(e.keyCode == 37){
				window.location.href = '?limit=<?php echo $limit; ?>&p=<?php echo $prev; ?>#pp';
			}else if(e.keyCode == 39){
				window.location.href = '?limit=<?php echo $limit; ?>&p=<?php echo $next; ?>#pp';
			}
		}
	});
});
</script>
</body>
</html>