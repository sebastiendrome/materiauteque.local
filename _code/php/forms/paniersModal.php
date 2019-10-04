<?php
if($paniers){
	$output = display_paniers($paniers);
}else{
	$output = '<p style="color:#aaa;">Pas de paniers...</p>';
}
?>
<div id="paniersContainer"><a href="javascript:;" class="showPaniers">
<img src="/_code/images/panier.svg" style="width:15px;height:15px; margin-bottom:-2px; margin-right:10px;">Paniers en cours</a>
<div id="paniersTarget" style="display:none;">
<a class="closeBut">&times;</a>
<div id="panierAjaxTarget">
<?php echo $output; ?>
</div>
</div>
</div>