<?php
if( !isset($categories) ){
	$categories = get_table('categories', 'visible=1 AND id_parent=0');
}

/// check and clean up user input
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
	$search_result = search($keywords, $categories_id, /*visible-only=*/TRUE, /*vendus=*/FALSE);
}
?>

<form name="search" class="searchForm" action="/recherche/" method="post">
<input type="text" name="keywords" value="<?php echo $keywords; ?>" placeholder="Que recherchez-vous?" autofocus><select name="categories_id">
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
</select><button type="submit" name="searchSubmit"><img src="/_code/images/search-white.svg" style="width:14px; vertical-align:middle;"></button>
</form>