<?php
// delete file form
// used inline or loaded via ajax, so check for necessary vars and require files accordingly
if( !defined("ROOT") ){
	require('../../../c/php/first_include.php');
	require(ROOT.'c/php/admin/not_logged_in.php');
	require(ROOT.'c/php/admin/admin_functions.php');
}

// for creating sub-sections, we need the parent section:
if(isset($_GET['article_id']) && !empty($_GET['article_id']) ){
	$article_id = urldecode($_GET['article_id']);
	$item_data = get_article_data($article_id, 'titre');
	$images_array = get_article_images($article_id, '_M');
	if(!empty($images_array)){
		$img = $images_array[0];
		$class = "articleVente";
	}else{
		$class = 'articleVente noImg';
		$img = '';
	}
}else{
	exit;
}

?>
<div class="modal" id="deleteArticleContainer">
	<a href="javascript:;" class="closeBut hideModal">&times;</a>
	<h3 class="first">Êtes vous sûr.e de vouloir supprimer cet article?</h3>
	<?php echo '<div class="'.$class.'" style="background-image:url('.REL.$img.');"><h3>'.$item_data['titre'].'</h3></div>'; ?>
	<p>Article ID: <?php echo $article_id; ?></p>
	
	<form name="deleteArticle" id="deleteArticle" action="<?php echo REL; ?>c/php/forms/deleteArticle.php" method="post">
	<input type="hidden" name="delete_id" id="delete_id" value="<?php echo $article_id; ?>">
	<input type="hidden" name="table" id="table" value="articles">
	<input type="hidden" name="deleteArticleSubmitted" id="deleteArticleSubmitted" value="deleteArticleSubmitted">
	<a href="javascript:;" class="button hideModal left">Non</a> <button type="submit" name="deleteArticleSubmit" id="deleteArticleSubmit" class="right">Supprimer</button>
	</form>

</div>

