
// show Modal window
function showModal(modal, callback){
	var $newdiv,
		$overlayDiv,
		query = '';
		
	// create overlay if it does not exist
	if($('div.overlay').length == 0){
		$overlayDiv = $('<div class="overlay"/>');
		$('body').append($overlayDiv);
	}else{
		$overlayDiv = $('div.overlay');
	}
	$overlayDiv.fadeIn();
	// parse and check for query string (rel="zoomModal?img=/path/to/image.jpg") will append query string to loading modal.
	if(modal.indexOf('?') !== -1){
		var splitRel = modal.split("?");
		modal = splitRel[0];
		query =  '?'+splitRel[1];
		//alert(query);
	}
	// create modalContainer if it does not exist
	if($('div#'+modal).length == 0){
		$newdiv = $('<div class="modalContainer" id="'+modal+'"/>');
		$('body').append($newdiv);
	}else{
		$newdiv = $('div#'+modal);
	}
	$newdiv.load(rel+'c/php/admin/forms/'+modal+'.php'+query);
	$newdiv.show();
	checkModalHeight('#'+modal);
	if(callback !== undefined && typeof callback === 'function') {
		callback();
	}
}
// hide modal
function hideModal($elem){
	var n = $('div.modalContainer:visible').length;
	if(n > 0){
		$elem.closest('div.modalContainer').hide();
		n = n-1;
	}else{
		$elem.closest('div').hide();
	}
	//alert(n);
	if(n < 1){
		$('div.overlay').fadeOut();
	}
}
// change positioning of modals to account for scrolling down window!
function checkModalHeight(elem){
	var scroltop = parseInt($(window).scrollTop());
	var newtop = scroltop+50;
	if(newtop<100){
		newtop =  100;	
	}
	//alert(newtop);
	$(elem).animate({top: newtop}, 100, function() {
		// calback function to focus on first txt input but exclude newFile modal
		var elId = $(elem).attr("id");
		//alert(elId);
		if(elId != 'newFile'){
			$(elem).find('input[type=text]').eq(0).focus();
		}
	  });
}

// get children of id_parent (for categories and matieres hierarchical SQL tables)
function get_children($target, table, id_parent){
	$.ajax({
		// Server script to process the upload
		url: rel+'c/php/admin/admin_ajax.php?get_children&table='+table+'&id_parent='+id_parent,
		type: 'GET',
		// on success show message
		success : function(msg){
			if($target.prop('disabled') == true){
				$target.prop('disabled', false);
			}
			$target.html(msg).focus()/*.attr('size', 5)*/;
		}
	});
}


// hide all modalContainer(s) and overlay
$('body').on('click', 'div.overlay', function(){
	$(this).fadeOut();
	$('div.modalContainer').hide();
	return false;
});

// assign behavior to .showModal
$('body').on('click', '.showModal', function(e){
	var modal = $(this).attr("rel");
	var nextpage = $(this).attr("href");
	if(nextpage !== 'javascript:;' && nextpage !== '#'){
		if(modal.indexOf('?') !== -1){
			modal = modal+'&redirect='+encodeURIComponent(nextpage);
		}else{
			modal = modal+'?redirect='+encodeURIComponent(nextpage);
		}
	}
	showModal(modal);
	e.preventDefault();
});

// assign behavior to .hideModal (close parent div on click)
$('body').on('click', '.hideModal', function(e){
	hideModal($(this));
	e.preventDefault();
});

/**** cookie functions *******/
function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires="+ d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

// get cookie value from cookie name
function getCookie(cname){
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++){
		var c = ca[i];
		while(c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if(c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}