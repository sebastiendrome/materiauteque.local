<?php
echo '<a name="top"></a>';
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

$paniers = get_table('paniers', 'statut_id=1', 'date DESC');

/**** !!!!!!! SEARCH ARTICLE can be hidden by changing this to false... */
$show_findArticleForm = true;

/*
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
		$ids = search($keywords, $categories_id, TRUE, FALSE);
		if( !empty($ids) ){
			foreach($ids as $id){
				//echo 'Article #'.$key.'<br>';
				$items[] = get_article_data($id);
			}
		}
	}
}else{
	$keywords = $categories_id = '';
}
*/

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
		if( $results = find_articles($key_val_pairs) ){
			foreach($results as $key => $val){
				//echo 'Article #'.$key.'<br>';
				$items[] = get_article_data($key);
			}
		}
	}
}

// process form POST data (create article)
if( isset($_POST['form2Submitted']) ){
	foreach($_POST as $k => $v){
		if($k !== 'form2Submitted' && $k !== 'types' && $k !== 'sizes'){
			$new_item_data[$k] = trim($v);
		}
	}
	if($article_id = insert_new('articles', $new_item_data)){
		$_SESSION['article_id'] = $article_id;
		$new_item[0] = get_article_data($article_id);
		$items_table = items_table_output($new_item);
		$message = '1|Nouvel Article créé. ID: '.$article_id;
		$path = 'uploads/'.$article_id;
		
	}else{
		$message = '0|'.mysqli_error($db);
	}
}


// result message passed via query string
if( isset($message) && !empty($message) ){
	$message = str_replace(array('0|', '1|', '2|'), array('<p class="error">', '<p class="success">', '<p class="note">'), $message).'</p>';
	$message_script = '<script type="text/javascript">showDone();</script>';
}else{
	$message = $message_script = '';
}

if( !isset($title) ){
	$title = ' Nouvelle Vente';
	require(ROOT.'_code/php/doctype.php');
	echo '<!-- admin css -->
	<link href="/_code/css/admincss.css?v='.$version.'" rel="stylesheet" type="text/css">'.PHP_EOL;

	echo '<div id="working"><div class="note">working...</div></div>';
	echo '<div id="done">'.$message.'</div>';

	echo '<!-- adminHeader start -->
	<div class="adminHeader">
	<h1><a href="/admin" class="admin">Admin <span class="home">&#8962;</span></a></h1> <a href="/admin/ventes.php" class="button vente edit selected" title="Gérer les ventes">Ventes</a> <a href="/admin/articles.php" class="button articles edit" title="Gérer les articles">Articles</a> <a href="javascript:;" class="button paniersBut right showPaniers"><img src="/_code/images/panier.svg" style="width:15px;height:15px; margin-bottom:-2px; margin-right:10px;">Paniers en cours</a>'.PHP_EOL;
	echo '</div><!-- adminHeader end -->'.PHP_EOL;

	
	include(ROOT.'_code/php/forms/paniersModal.php');


	echo '<!-- start admin container -->
	<div id="adminContainer">'.PHP_EOL;

	echo '<h2>Nouvelle vente</h2>';
		
	$footer = true;
}else{
	echo $message;
	$footer = false;
}

?>


<?php
// search results
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

// search was done but with no results
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

	
// simple search (not used at the moment, syill here just in case...)
}elseif( isset($_POST['simpleSearch']) ){
	echo '<p class="note">Aucun résultat...</p>'.PHP_EOL;
}

?>

<a name="recherche"></a>


<?php
// show find Article Form?
if($show_findArticleForm === true){ ?>

<!-- recherche detail start -->
<form name="findArticle" id="findArticle" action="#top" method="post" style="display:inline-block; float:left; margin-right:20px;">

<?php
if( empty($key_val_pairs) && isset($_POST['findArticleSubmitted']) ){
	echo '<p class="note">Choisir au moins 1 paramètre de recherche...</p>'.PHP_EOL;
}
?>

<h3>Rechercher l'article à vendre:</h3>
<span class="below">Saisir au moins 1 des champs.</span>

	<?php
	$article_form_context = 'search';
	require(ROOT.'_code/php/forms/edit_article_table.php');
	?>

	<input type="hidden" name="findArticleSubmitted" id="findArticleSubmitted" value="findArticleSubmitted">
	<a href="" class="button left">Réinitialiser</a>
	<button type="submit" name="findArticleSubmit" id="findArticleSubmit" class="right" >Rechercher</button>

</form>
<!-- recherche detail end -->

<?php } ?>






<!-- créer article à vendre start -->
<form name="newArticle" id="newArticle" action="" method="post" style="display:inline-block; float:left;">
<?php if($show_findArticleForm === true){ ?>
Si l'article n'existe pas ou est introuvable...
<?php } ?>
<h3>Créer l'article à vendre:</h3>

	<?php
	$article_form_context = 'vente';
	require(ROOT.'_code/php/forms/edit_article_table.php');
	?>
	<input type="hidden" name="visible" value="0">
	<input type="hidden" name="date_vente" value="<?php echo time(); ?>">
	
	<div id="vpLoader">
	<?php require(ROOT.'_code/php/forms/vente-paniers.php'); ?>
	</div>
	

</form>
<!-- créer article à vendre end -->




<?php
if($footer){
	echo '</div><!-- end admin container -->'.PHP_EOL;
	require(ROOT.'/_code/php/admin/admin_footer.php');
	echo $message_script;
	echo '</body></html>';
}else{
	echo $message_script;
}
?>

<script type="text/javascript">

// highlight/dim forms
var $NaForm = $('form#newArticle');
var $SaForm = $('form#findArticle');
$NaForm.on('mouseenter', function(){
	$(this).css('opacity', 1);
	$SaForm.css('opacity', .5);
}).on('mouseleave', function(){
	$SaForm.css('opacity', 1);
});
$SaForm.on('mouseenter', function(){
	$(this).css('opacity', 1);
	$NaForm.css('opacity', .5);
}).on('mouseleave', function(){
	$NaForm.css('opacity', 1);
});

</script>