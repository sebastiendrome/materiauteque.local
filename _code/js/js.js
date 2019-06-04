
// show Modal window
function showModal(modal, callback){
	var $newDiv,
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
	$newdiv.load('/_code/php/forms/'+modal+'.php'+query);
	$newdiv.show();
	checkModalHeight('#'+modal);
	if(callback !== undefined && typeof callback === 'function') {
		callback();
	}
}


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
		url: '/_code/php/admin/admin_ajax.php?get_children&table='+table+'&id_parent='+id_parent,
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

// assign behavior to .closeBut et .hideModal (close parent div on click)
$('body').on('click', '.closeBut, .hideModal', function(e){
	hideModal($(this));
	e.preventDefault();
});