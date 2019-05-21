<?php
require('_code/php/first_include.php');
require(ROOT.'_code/php/doctype.php');

$items = get_items_data('*', 1);

$output = '';

if(!empty($items)){
	foreach($items as $item){
		$output .= show_article($item);
	}
	
}else{
	$output .= '<p>Aucun article...</p>';
}

?>


<?php require(ROOT.'/_code/inc/header.php'); ?>

<!-- start #container -->
<div id="container">

<?php
echo $output;
//echo '<pre>';echo print_r($articles); echo '</pre>';
?>

</div><!-- end #container -->

<?php require(ROOT.'/_code/inc/footer.php'); ?>