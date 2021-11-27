<?php
if($paniers){
	$output = display_paniers_en_cours($paniers);
}else{
	$output = '';
}
if( isset($_COOKIE['paniersModalDisplay']) && ( $_COOKIE['paniersModalDisplay'] == 'none' || $_COOKIE['paniersModalDisplay'] == 'block') ){
	$paniers_modal_display = $_COOKIE['paniersModalDisplay'];
}else{
	$paniers_modal_display = 'none';
}
?>
<div id="paniersContainer" style="display:<?php echo $paniers_modal_display; ?>;">
<a class="closeBut">&times;</a>
<img src="/_code/images/panier.svg" style="width:15px;height:15px; margin-bottom:-2px; margin-right:10px;">Paniers en cours

<div id="paniersAjaxTarget">
<?php echo $output; ?>
</div>

</div>