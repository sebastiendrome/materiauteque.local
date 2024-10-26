<?php
echo '
<!-- generated via js/js.php -->
<script type="text/javascript">
// define dir to add to all relative path
const rel = "'.REL.'";
</script>
';
if($online){
	?>
<!-- generated via js/js.php, if online -->
<script type="text/javascript">
// check if internet connection is on or off
window.addEventListener("offline", (e) => {
	$('#done').html('<p class="error" style="padding:20px; padding-bottom:40px;"><b style="font-size:larger">Plus de connection internet<br>!</b><br><br>Ce logiciel ne fonctionne pas sans internet. Assurez-vous de rétablir la connection avant de continuer...</p>');
	clearTimeout(hideDoneMessage);
	showDone();
	if($('div.overlay').length == 0){
		$overlayDiv = $('<div class="overlay"/>');
		$('body').append($overlayDiv);
	}else{
		$overlayDiv = $('div.overlay');
	}
	$overlayDiv.fadeIn();
	//alert("La connection internet ne fonctionne plus!\r\n\r\nCe logiciel ne fonctionne pas sans inernet; assurez-vous de restaurer la connection internet de cet ordinateur...");
});
window.addEventListener("online", (e) => {
	$('#done').html('<p class="success" style="padding:20px; padding-bottom:40px;"><b style="font-size:larger">Super!</b><br><br>Connection rétablie.</p>');
	showDone();
	if($('div.overlay').length > 0){
		$overlayDiv.fadeOut();
	}
	//alert("Super, \r\nLa connection internet est restaurée. :) ");
});
</script>
	<?php 
	}
?>
