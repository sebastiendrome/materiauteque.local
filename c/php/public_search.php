<?php
require('first_include.php');
require(ROOT.'c/php/doctype.php'); 
// header.php includes search.php, which returns $search_result if $_POST[keywords] and/or [categories_id] are set
require(ROOT.'/c/inc/header.php'); 
?>

<!-- start #container -->
<div id="container">

<?php
$output = '';
if( isset($search_result) ){ // $search_result: see above (header include)
	if(!$search_result){
		$output .= '<p class="warning">Aucun résultat...</p>';
	}else{
		// debug
		//echo '<pre>'; print_r($search_result); echo '</pre>';
		
		foreach($search_result as $id){
			$items[] = get_article_data($id);
		}
		foreach($items as $item){
			$output .= show_article($item);
		}
	}
	echo $output;
}
?>

</div><!-- end #container -->

<?php require(ROOT.'/c/inc/footer.php'); ?>