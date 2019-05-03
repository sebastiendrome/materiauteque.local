
// make sure nav height never desappears below page bottom (it is positioned fixed...)
function limitNavHeight(){
	var nH = $('#nav').height(); // recalculate nav height
	//alert(nH);
	if(nH > wH){
		//alert('too high!');
		if($('#nav').hasClass('collapsible')){
			$('#nav').removeClass('collapsible');
		}
		$('#nav').css({'height':wH+'px', 'overflow':'auto'});
		$('#nav ul').css('margin-right', 0);
	}
}

/* cookie functions */
function setCookie(c_name,value,exdays){
	var exdate=new Date();exdate.setDate(exdate.getDate()+exdays);
	var c_value=escape(value)+((exdays==null) ? "" : "; expires="+exdate.toUTCString()+"; path=/");
	document.cookie=c_name+"="+c_value;
}
/*
function getCookie(c_name){
	var c_value=document.cookie;var c_start=c_value.indexOf(" "+c_name+"=");
	if(c_start==-1){c_start=c_value.indexOf(c_name+"=");}
	if(c_start==-1){c_value=null;}
	else{c_start=c_value.indexOf("=",c_start)+1;
		var c_end=c_value.indexOf(";",c_start);
		if(c_end==-1){c_end=c_value.length;}
		c_value=unescape(c_value.substring(c_start,c_end));}
	return c_value;
}
*/

// get window width and height
var wW = $(window).width();
var wH = $(window).height();

// set cookies of window width and height for later use
setCookie('wW', wW, 2);
setCookie('wH', wH, 2);

$(document).ready(function(){

	// get footer height
	var fH = $('#footer').outerHeight();
	// gte nav height
	var navH = $('#nav').outerHeight();

	limitNavHeight();

	// this var will detremine where the footer stands, when content container is empty
	var contentMinHeight = wH-fH-87;

	// if viewport width is less than 980px, 
	if (document.documentElement.clientWidth < 980) {
		contentMinHeight = wH-fH-60;
	}

	// show/hide navigation for small screens
	$('#nav').on('click', function(e){
		// if viewport width is less than 720px, 
		if (document.documentElement.clientWidth < 720) {

			contentMinHeight = wH-fH-100;
			if($(this).hasClass('collapsible')){
				$(this).removeClass('collapsible').removeAttr("style");
			}else if($(this).height() == wH){ // collaspible class has been removed by limitNavHeight function, so just look for nav_height = window_height
				$(this).css({'height':navH+'px', 'overflow':'hidden'});
				$('#nav ul').css('margin-right', '10px');
			}else{
				$(this).addClass('collapsible').removeAttr("style");
				limitNavHeight();
			}
		
			// avoid propagation of nav click if click on site title (#nav h1 a)
			$('#nav h1 a').click(function(event){
				event.stopPropagation();
			});
		}
	});

	// position footer at bottom of page even if no content
	$('#content').css('min-height', contentMinHeight+'px');


	// underline '.aMore' link when mouse over '.imgMore' (for sub-sections)
	$('div.divItem').on('mouseenter', 'a.imgMore', function(){
		$(this).closest('.divItem').children('.title').children('.aMore').css('text-decoration', 'underline');
	});
	// un-underline '.aMore' link when mouse over '.imgMore' (for sub-sections)
	$('div.divItem').on('mouseleave', 'a.imgMore', function(){
		$(this).closest('.divItem').children('.title').children('.aMore').css('text-decoration', '');
	});

	// sub-section should load via ajax (and scroll orizontally) if set so by user
	$('div.divItem').on('click', 'a.aMore.orizontal, a.imgMore.orizontal', function(e){
		var path = $(this).attr("href");
		var elem = $(this).closest('div.divItem');
		//alert(path);
		loadSubSection(path, elem);
		e.preventDefault();
	});
	// close sub-section
	$('div.divItem').on('click', '.aLess.orizontal', function(e){
		var closeId = $(this).data("close");
		//alert(closeId);
		$('#'+closeId).hide();
		e.preventDefault();
	});

});

// show tooltip below the span.question element on click (native title tooltip will show on moue hover)
// reposition it if it is below the bottom window edge
$('body').on('click', 'span.question', function(){
	var msg = $(this).attr("title");
	if($(this).children('span.tooltip').length == 0){
		$(this).append('<span class="tooltip">'+msg+'</span>');
	}
	var $tooltip = $(this).find('span.tooltip');
	// calculate verticaly
	var offsetTop = $tooltip.offset().top;
	var sTop = $(window).scrollTop();
	var tH = $tooltip.outerHeight();
	// calculate orizontaly
	var offsetLeft = $tooltip.offset().left;
	var sLeft = $(window).scrollLeft();
	var tW = $tooltip.outerWidth();
	/*
	// alerts for left and top values
	alert('offsetLeft: '+offsetLeft+' tW: '+tW+' sLeft: '+sLeft+' calcul: '+(offsetLeft+tW-sLeft)+' wW:'+wW);
	alert('offsetTop: '+offsetTop+' tH: '+tH+' sTop: '+sTop+' calcul: '+(offsetTop+tH-sTop)+' wH:'+wH);
	*/
	// reposition verticaly
	if(offsetTop+tH-sTop > wH){
		$tooltip.css('top', (-tH-10)+'px');
	}
	// reposition orizontaly
	if(offsetLeft+tW-sLeft > wW){
		$tooltip.css('left', (-tW)+'px');
	}
}).on('mouseleave', 'span.question', function(){
	$(this).children('span.tooltip').remove();
});


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