<?php
require('../_code/php/first_include.php');
require(ROOT.'_code/php/admin/not_logged_in.php');
require(ROOT.'_code/php/admin/admin_functions.php');
require(ROOT.'_code/php/doctype.php');
if( isset($_SESSION['article_id']) ){
    unset($_SESSION['article_id']);
}


// make sure we get the needed data, if we don't have it already
if( !isset($categories) || empty($categories) ){
    $categories = get_table('categories');
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
    if($keywords !== '' || $categories_id !== ''){
		$ids = search($keywords, $categories_id, /*visible-only=*/FALSE, /*vendus=*/FALSE);
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
$next = $p+1;
$prev = $p-1;
if($next >$pages){
	$next = 1;
}
if($prev ==0){
	$prev = $pages;
}

$fields = array('date', 'titre', 'descriptif', 'categories_id', 'observations', 'statut_id', 'visible');
$articles = get_items_data($fields, 'all', FALSE, 'all', 'date DESC', $limit, $offset);

$items_table = items_table_output($articles);

?>

<!-- admin css -->
<link href="/_code/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">

<!-- adminHeader start -->
<div class="adminHeader">
<h1>Admin</h1>

<a href="/_code/php/forms/newArticle.php" class="button add left">Nouvel article</a> <!--<a href="/_code/php/forms/findArticle.php" class="button edit">Rechercher un article</a> --><a href="/_code/php/forms/ventes.php" class="button">€ Nouvelle vente</a> <a href="/admin/manage_categories.php" class="button edit">Catégories</a> <a href="/admin/manage_dechet_categories.php" class="button edit">Matières</a> <!--<a href="/admin/manage_adhesions.php" class="button edit">Adhésions</a> -->


<div class="clearBoth"></div>
</div>
<!-- adminHeader end -->

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
</select><button type="submit" name="searchSubmit">Rechercher</button> <a href="/_code/php/forms/findArticle.php">&nbsp;>Recherche détaillée</a>
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


<?php
if( isset($_GET['upload_result']) ){
    echo urldecode($_GET['upload_result']);
}
?>


<div id="working">working...</div>
<div id="done"></div>



<div id="result"></div>

<div class="clearBoth" style="margin-top:35px;">

<h2 style="display:inline-block;">Articles</h2> 

&nbsp;&nbsp;&nbsp;Voir <input type="text" name="limit" value="<?php echo $limit; ?>" style="min-width:25px; width:25px; text-align:right;" onClick="this.select();" onChange="window.location.href='?limit='+this.value;"> articles par page.&nbsp;&nbsp;Sauter à la page <input type="text" name="p" value="" style="min-width:25px; width:25px; text-align:right;" onClick="this.select();" onChange="window.location.href='?limit=<?php echo $limit; ?>&p='+this.value;">
<a name="pp"></a>
<?php 
$navigation_output = '';

if($pages >1){
	$navigation_output .= '<div class="pagination" style="text-align:center; margin:10px 0;">'.PHP_EOL;
	$navigation_output .= '<a href="?limit='.$limit.'&p='.$prev.'#pp" class="butLink navPrev">❮&nbsp;&nbsp;&nbsp;</a>';

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
	$navigation_output .= '<a href="?limit='.$limit.'&p='.$next.'#pp" class="butLink navNext">&nbsp;&nbsp;&nbsp;❯</a>'.PHP_EOL;
	
	$navigation_output .= '</div>'.PHP_EOL;

}


echo '<div style="max-width:100%; overflow:auto;">';
echo $navigation_output;
echo $items_table;
echo $navigation_output;
echo '</div>';



?>

</div>


</div><!-- end admin container -->

<?php
require(ROOT.'/_code/php/admin/admin_footer.php');
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