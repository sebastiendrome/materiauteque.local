<?php
echo '<a name="top"></a>';
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

// paniers en cours for paniersModal.php
$paniers = get_table('paniers', 'statut_id=1', 'date DESC');

// all paniers
$all_paniers = get_table('paniers', '', 'date DESC');




// result message passed via query string
if( isset($message) && !empty($message) ){
	$message = str_replace(array('0|', '1|', '2|'), array('<p class="error">', '<p class="success">', '<p class="note">'), $message).'</p>';
	$message_script = '<script type="text/javascript">showDone();</script>';
}else{
	$message = $message_script = '';
}

if( !isset($title) ){
	$title = ' Paniers';
	require(ROOT.'_code/php/doctype.php');
	echo '<!-- admin css -->
	<link href="/_code/css/admincss.css?v='.$version.'" rel="stylesheet" type="text/css">'.PHP_EOL;

	echo '<div id="working"><div class="note">working...</div></div>';
	echo '<div id="done">'.$message.'</div>';

	echo '<!-- adminHeader start -->
	<div class="adminHeader">
	<h1><a href="/admin" class="admin">Admin <span class="home">&#8962;</span></a>'.$title.' </h1>'.PHP_EOL;
	echo '</div><!-- adminHeader end -->'.PHP_EOL;

	// display paniers en cours
	include(ROOT.'_code/php/forms/paniersModal.php');

	// display all paniers
	if( !empty($all_paniers) ){
		$output_all = display_paniers($all_paniers);
	}else{
		$output = '<p style="color:#aaa;">Pas de paniers...</p>';
	}
	echo $output_all;


	echo '<!-- start admin container -->
	<div id="adminContainer">'.PHP_EOL;
		
	$footer = true;
}else{
	echo $message;
	$footer = false;
}

?>




<a name="recherche"></a>



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



</script>