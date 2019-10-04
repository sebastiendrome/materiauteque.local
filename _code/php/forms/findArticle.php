<?php
echo '<a name="top"></a>';
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

// set $article_form_context for edit_article_table.php vars
$article_form_context = 'search';

if( !isset($title) ){
	$title = ' Rechercher un Article';
	require(ROOT.'_code/php/doctype.php');
	echo '<!-- admin css -->
	<link href="/_code/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">'.PHP_EOL;
	
	echo '<!-- adminHeader start -->
	<div class="adminHeader">
	<h1><a href="/admin" class="admin">Admin <span class="home">&#8962;</span></a>'.$title.' </h1>'.PHP_EOL;
	echo '</div><!-- adminHeader end -->'.PHP_EOL;

	echo '<!-- start admin container -->
	<div id="adminContainer">'.PHP_EOL;
	
	echo '<div id="working">working...</div>
		<div id="done"></div>
		<div id="result"></div>';

	$footer = true;
}else{
	$footer = false;
}

$paniers = get_table('paniers', 'statut=0', 'date DESC');
include(ROOT.'_code/php/forms/paniersModal.php');
?>

<?php

$keywords = $categories_id = '';


// process form POST data (detailed search)
if( isset($_POST['findArticleSubmitted']) ){
	$key_val_pairs = array();
	foreach($_POST as $k => $v){
		if($k !== 'findArticleSubmitted' && $k !== 'findArticleSubmit' && $k !== 'types' && $k !== 'sizes' && $v !== '' && $k !== 'date' ){
			$key_val_pairs[$k] = trim($v);
		// for date, value is an array (date[start] and [end]), so don't trim. 
		// Also, make sure that not both are empty
		}elseif( $k == 'date' && ( !empty($v['start']) || !empty($v['end']) ) ){
			$key_val_pairs[$k] = $v;
		}
	}
	/*
	echo '<pre>'.__FILE__.PHP_EOL;
	print_r($key_val_pairs);
	echo '</pre>';
	*/
	if( !empty($key_val_pairs) ){
		if( isset($key_val_pairs['statut_id']) && $key_val_pairs['statut_id'] == name_to_id('vendu', 'statut') ){
			$include_vendus = TRUE;
		}else{
			$include_vendus = FALSE;
		}
		if( $results = find_articles($key_val_pairs, $include_vendus) ){
			foreach($results as $key => $val){
				//echo 'Article #'.$key.'<br>';
				$items[] = get_item_data($key);
			}
		}
	}
}
?>

<?php
if( isset($items) && !empty($items)){
	echo '<p class="success" style="overflow:auto;">
	<span style="display:block; margin: 10px 0;">';
	if(isset($results)){
		$count = count($results);
		if($count>1){$s='s';}else{$s='';} // plural or singular
		echo '<b>'.$count.' article'.$s.' trouvé'.$s.'.</b> <a href="#recherche" class="button">Nouvelle recherche</a><br>
		Paramètres de recherche: ';
		foreach($key_val_pairs as $rk => $rv){
			if( is_array($rv) ){
				$string = '';
				foreach($rv as $rrk => $rrv){
					if(substr($rrk, -3) == '_id'){
						$table = substr($rrk, 0, -3);
						$rrv = id_to_name($rrv, $table);
					}
					$string .= $rrk.': '.$rrv.'&nbsp;&nbsp;';
				}
				$rv = $string;
			}
			if(substr($rk, -3) == '_id'){
				$rk = substr($rk, 0, -3);
				$rv = id_to_name($rv, $rk);
			}
			echo $rk.' = '.$rv.'&nbsp;&nbsp;';
		}
	}else{
		$count = count($items);
		if($count>1){$s='s';}else{$s='';} // plural or singular
		echo '<b>'.$count.' article'.$s.' trouvé'.$s.'.</b><br>
		Paramètres de recherche: ';
		echo $keywords;
		if(!empty($categories_id)){
			echo '&nbsp;&nbsp;Catégorie: '.id_to_name($categories_id, 'categories');
		}
	}
	
	echo '</span>';

	$items_table = items_table_output($items);
	echo $items_table;

	echo '</p>';

}elseif(isset($_POST['findArticleSubmitted']) ){
	echo '<p class="note">Aucun article trouvé pour: ';
	foreach($key_val_pairs as $rk => $rv){
		if( is_array($rv) ){
			$string = '';
			foreach($rv as $rrk => $rrv){
				$string .= $rrk.':'.$rrv.'&nbsp;&nbsp;';
			}
			$rv = $string;
		}
		echo $rk.' = '.$rv.'<br>';
	}
	echo '</p>'.PHP_EOL;
		
}

?>

<a name="recherche"></a>




<!-- recherche detail start -->
<form name="findArticle" id="findArticle" action="#top" method="post" style="display:inline-block;">

<?php
if( empty($key_val_pairs) && isset($_POST['findArticleSubmitted']) ){
	echo '<p class="note">Choisir au moins 1 paramètre de recherche...</p>'.PHP_EOL;
}
?>

<!--<h3>Recherche détaillée:</h3>-->
<p class="below">Saisir au moins 1 des champs.</p>
	
	<?php
	require(ROOT.'_code/php/forms/edit_article_table.php');
	?>

	<input type="hidden" name="findArticleSubmitted" id="findArticleSubmitted" value="findArticleSubmitted">
	<a href="" class="button left">Réinitialiser</a>
	<button type="submit" name="findArticleSubmit" id="findArticleSubmit" class="right" >Rechercher</button>

</form>
<!-- recherche detail end -->




<?php
if($footer){
	echo '</div><!-- end admin container -->'.PHP_EOL;
	require(ROOT.'/_code/php/admin/admin_footer.php');
	echo '
	</body>
	</html>';
}
?>