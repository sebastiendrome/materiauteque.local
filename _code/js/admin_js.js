
/***** functions *****************************************************/

// save sql data ( ajax call, uses php function update_table() )
function updateTable(table, col, id, value){
	$.ajax({
		// Your server script to process the upload
		url: '/_code/php/admin/admin_ajax.php?updateTable&table='+table+'&col='+col+'&id='+id+'&value='+value,
		type: 'GET',
		// on success show message
		success : function(msg) {
			var ty = msg.substr(0, 2);
			if(ty == '0|'){
				msg = '<p class="error">'+msg.substr(2)+'</p>';
			}else if(ty == '1|'){
				msg = '<p class="success">'+msg.substr(2)+'</p>';
			}else if(ty == '2|'){
				msg = '<p class="note">'+msg.substr(2)+'</p>';
			}
			$('#done').html(msg);
			return true;
		}
	});
}

// get upload fileName without 'fake' path
function basename(path){
	return path.replace(/\\/g,'/').replace( /.*\//, '' );
}

// return file size in bytes
function getFileSize(){
	if(window.ActiveXObject){	// old IE
		var fso = new ActiveXObject("Scripting.FileSystemObject");
		var filepath = document.getElementById('fileUpload').value;
		var thefile = fso.getFile(filepath);
		var sizeinbytes = thefile.size;
	}else{						// modern browsers
		var sizeinbytes = document.getElementById('fileUpload').files[0].size;
	}
	return sizeinbytes;
}

// return bytes size to human readable size
function bytesToReadbale(sizeInBytes){
	var fSExt = new Array('Bytes', 'KB', 'MB', 'GB');
	fSize = sizeInBytes; i=0;
	while(fSize>900){
		fSize/=1024;
		i++;
	}
	var humanSize = (Math.round(fSize*100)/100)+' '+fSExt[i];
	return humanSize;
}

// show #done div to display message
function showDone(){
	$('#done').show();
	setTimeout(function(){
		$('#done').fadeOut(800, function(){
			$('#done').html('');
		});
	}, 2700);
}


// tooltip show/hide functions
if( $('span#tooltip').length == 0 ){
	$('body').append('<span id="tooltip"></span>');
}
var $tooltip = $('span#tooltip');
var timerLeave;
var timerEnter;
function hideToolTip(){
	timerLeave = setTimeout(function(){
		$tooltip.stop().fadeOut(500);
	}, 1000);
}
function showToolTip(){
	timerEnter = setTimeout(function(){
		$tooltip.stop().fadeIn(20);
	}, 200);
}
var wW = $(window).width();
var wH = $(window).height();


/***** behavior targets/calls *****************************************/

// tables should be sortable
$("table.data").tablesorter();


// assign behavior to .closeMessage (close parent on click)
$('body').on('click', '.closeMessage', function(e){
	var parent = $(this).parent();
	parent.hide();
	//window.location.search = '';
	e.preventDefault();
});

// display 'working' div while processing ajax requests, display 'done' div if message
$(document).ajaxStart(function(){
	$('#working').show();
}).ajaxStop(function(){
	$('#working').hide();
	if($('#done').html() != ''){
		showDone();
	}
});


// if (article) statut_id is changed to 4 ='vendu' (from within 'Modifier un article' page only), show prix_vente field, set visible = 0
$("select[name='statut_id']").on('change', function(){
	var $table = $(this).parent().parent().parent();
	if($table !== false){
		var $tr = $table.find('tr#prixVente');
		if($tr !== false){
			var $prixVente = $table.find($("input[name='prix_vente']"));
			if($(this).val() == 4){
				$tr.show();
				var pageName = document.location.pathname.match(/[^\/]+$/)[0];
				//alert(pageName);
				// make prix_vente required, only if NOT in search mode (findArticle.php)
				if(pageName !== 'findArticle.php'){
					var $visibleZero = $table.find($("input[name='visible']#visibleZero"));
					$prixVente.prop("required","required");
					$visibleZero.prop("checked", true);
					$prixVente.focus();
				}
			}else{
				$prixVente.val('');
				$tr.hide();
			}
		}
	}
});


// for 'statut_id' select inputs, store previous value before change, empty it on blur.
var previous_id;
$("table.data, table.editArticle").on('keydown', "select[name='statut_id']", function(){
	previous_id = $(this).val();
	//alert(previous_id);
}).on('blur', "select[name='statut_id']", function(){
	previous_id = '';
});

// handles all select drop-downs change via ajax, including article sale (opens prixVenteModal) if statut_id changed to 4 (vendu)
$("table.data").on('change', 'select.ajax', function(){
	var $table = $(this).parents('table');
	var table = $table.data('id'); // 'articles'
	var id = $(this).parents('tr').data('id'); // '167'
	var value = $(this).val();
	var col = $(this).attr('name'); // 'statut_id'

	//alert(table);
	//alert(id);
	//alert(previous);

	// select[name='statut_id'] can be used to change statut_id to 4 = 'vendu', in this case, show prixVenteModal
	if(col == 'statut_id' && value == 4){ // = vendu
		var prix = $(this).parents('tr').find('td.prix').html();
		showModal('prixVenteModal?article_id='+id+'&prix='+encodeURIComponent(prix)+'&previous_id='+previous_id);
		
	}else{
		//alert('ID:'+id+' modifié pour "'+value+'" dans tableau '+table+', column '+col);
		$(this).parents('tr').removeClass('vendu');
		updateTable(table, col, id, value);
	}
});

// handle vente via 'vendre' button
$("table.data").on('click', 'a.vendre', function(e){
	e.preventDefault();
	var id = $(this).parents('tr').data('id'); // '167'
	var prix = $(this).parents('tr').find('td.prix').html();
	showModal('prixVenteModal?article_id='+id+'&prix='+encodeURIComponent(prix));
});

// when article is sold via prixVenteModal.php
$("body").on('click', '#prixVenteSubmit', function(e){
	e.preventDefault();
	var $form = $(this).parents('form');
	var id = $form.find('input[name="id"]').val();
	var prix_vente = $form.find('input[name="prix_vente"]').val();
	var $payement_cheque = $form.find('input[name="payement_cheque"]');
	
	//alert('id='+id+' prix_vente='+prix_vente);
	hideModal($(this).parents('div.modal'));

	updateTable('articles', 'prix_vente', id, prix_vente);
	// record payment by cheque if it is the case
	if($payement_cheque.prop('checked') == true){
		updateTable('articles', 'payement_id', id, '2');
	}

	// change select statut_id selection, visible statut selection if article is vendu via table.data 
	var $tableData = $('body table.data');
	if($tableData.length){
		//alert('table.data found');
		var $tr = $tableData.find("tr[data-id='" + id + "']");
		if($tr.length){
			$tr.find('select[name="statut_id"]').val('4');
			$tr.find('select[name="visible"]').val('0');
			$tr.addClass('vendu');
		}
	}else{
		// change select statut_id selection if edit_article_table.php was included
		var $tableEdit = $('table.editArticle');
		if($tableEdit.length){
			//alert('YES table.editArticle');
			$select_input = $tableEdit.find('select[name="statut_id"]');
			if($select_input.length){
				//alert('$select_input found!');
				$select_input.val('4');
				// show prix_vente tr (set to display:none)
				var $prix_vente_tr = $tableEdit.find('tr#prixVente');
				$prix_vente_tr.show();
				// set its value to the prix_vente used above
				var $prix_vente_input = $prix_vente_tr.find('input[name="prix_vente"]');
				//alert(prix_vente);
				$prix_vente_input.val(parseFloat(prix_vente.replace(",", ".")));
			}
		}
	}
});

// modifier un article on tr.pair et tr.impair click
$("table.data").on('click', 'tr.pair td, tr.pair td div, tr.impair td, tr.impair td div', function(e){
	var id = $(this).parents('tr').data('id');
	var $this = $(this);
	var $origin = e.originalEvent.srcElement;
	console.log($origin);
	console.log($this[0]);
	if($this[0] == $origin){
		window.location.href="/_code/php/forms/editArticle.php?article_id="+id;
	}
});

/*
// if (article) action is selected, redirect to desired form or show desired modal
$("select[name='actions']").on('change', function(){
	var id = $(this).parent().data("id");
	var action = $(this).val();
	if(action == 'vendu'){
		showModal('venteModal?id='+id);
	}else if(action == 'images'){
		showModal('newFile?path='+encodeURIComponent('/uploads/'+id));
	}else if(action == 'modifier'){
		window.location.href = '/_code/php/forms/editArticle.php?article_id='+id;
	}
});
*/


// categories and matieres select inputs on change must repopulate next select input in form with children correseponding sub-categories/matieres:
$("select[name='categories_id'], select[name='matieres_id']").on('change', function(){
	var id_parent = $(this).val();
	var select_input = $(this).attr("name");
	var sous_table = 'sous_'+select_input;
	// $target: the next select input to be populated depending on the selected option. Make sure it belongs to the same form, hence going through the DOM parents(form)...
	var $target = $(this).parents('form').find($("select[name='"+sous_table+"']"));
	// if select name is 'categories_id', we want to look into 'categories' SQL table, if it is 'matieres_id', we want to look into 'matieres' table...
	var table = select_input.replace("_id",'');
	// call to _code/js/js.js: function get_children(), that will request via ajax call to _code/php/admin/admin_ajax.php?get_children the children of id_parent in table, and insert them as html <option> markup to $target
	get_children($target, table, id_parent);
});


// show/hide longer text on mouse enter short text (for descriptif and observations)
$("div.short").on('mouseenter', function(){
	//alert('mouseenter');
	$(this).children().show();
}).on('mouseleave', function(){
	$(this).children().hide();
});

// add .closeMessage to messages, so they can be closed (hidden)
$('<a class="closeMessage">&times;</a>').appendTo('p.error, p.note, p.success, div.success');

/*
// show tooltip below the span.question element on click (native title tooltip will show on moue hover)
$('a, button, select, input, tr.pair, tr.impair').on('mouseenter', function(e){

	// get tooltip message
	//var msg = $(this).attr("title");
	var msg = $(this).data("tooltip");
	// if present,
	if(msg.length){
		clearTimeout(timerLeave);
		// get mouse position
		//var x = e.clientX;
		//var y = e.clientY;
		var off = $(this).offset();
		var x = off.top;
		var y = off.left;
		var h = $(this).outerHeight();
		var w = $(this).outerWidth();
	
		$tooltip.html(msg);
		// calculate tooltip position relative element position
		var tW = $tooltip.outerWidth();
		//alert(tW);
		if(x > (wH/2)){
			$tooltip.css('top',(x-h)+'px');
		}else{
			$tooltip.css('top',(x+h)+'px');
		}
		if(y > (wW/2)){
			$tooltip.css('left',(y-tW+20)+'px');
		}else{
			$tooltip.css('left',(y-20)+'px');
		}
		showToolTip(x, y, h, w);
	}
}).on('mouseleave', function(){
	// get tooltip message
	var msg = $(this).attr("title");
	//var msg = $(this).data("tooltip");
	// if present,
	if(msg.length){
		clearTimeout(timerEnter);
		hideToolTip();
	}
});
*/



/* UPLOAD BEHAVIORS */

// #chooseFileLink onclick triggers #fileUpload click
$('body').on('click', '#chooseFileLink', function(){
	$('input#fileUpload').trigger('click');
	return false;
});

// #fileUpload click validates file size and extension, then triggers #uploadFileSubmit click
$("body").on("change", '#fileUpload', function(){
	var upVal = this.value;
	if(upVal != ''){

		var error = false;
		var file = this.files[0];
		var fileSize = file.size;
		//var fileType = file.type;
		var fileName = file.name;
		
		// validate file extension
		var ext = fileName.split('.').pop().toLowerCase();
		var dotExt = '.'+ext;
		var extMatch = dotExt.match(supported_types);
		if(extMatch == null){
			error = true;
			alert('Sorry, this file type is not supported: .'+ext+'\n\nThe file has not been uploaded.');
		}
		
		// validate file size
		if(fileSize > max_upload_bytes) {
			var readableSize = bytesToReadbale(fileSize);
			error = true;
			alert('The file is too large: '+readableSize+'\n\nThe maximum upload size is '+max_upload_size);
		}
		
		if(!error){
			$('.hideUp').hide();
			$('#uploadFileSubmit').trigger('click');
		}
	}
});

// #uploadFileSubmit onchange sets #chooseFileLink innerHTML to #fileUpload value (fileName)
// AND initiates ajax call to upload via /_code/php/admin/admin_ajax.php -> upload_file()
$('body').on('click', '#uploadFileSubmit', function(e){
	e.preventDefault();
	var path = $('#fileUpload').val();
	var fileName = basename(path);
	var myForm = document.forms.namedItem("uploadFileForm");
	
	$('a#chooseFileLink').html('Uploading: '+fileName+'...').removeClass('submit');
	// show upload progress bar
	$('div.progress').css('display','block');
	
	
	$.ajax({
		// Your server script to process the upload
		url: '/_code/php/admin/admin_ajax.php',
		type: 'POST',

		// Form data
		data: new FormData(myForm),

		// Tell jQuery not to process data or worry about content-type
		// You *must* include these options!
		cache: false,
		contentType: false,
		processData: false,

		// on success, reload page with upload_result message
		success : function(msg) {
			var url = window.location.protocol+'//'+window.location.hostname+window.location.pathname;
			
			// for inserting uploaded image in edit_text.php, call insertImg function and hide Modal
			/*if(window.location.pathname == '/_code/php/admin/article_id.php'){
				var error = msg.match(/^0\|/);
				if(error == null){
					insertImg(msg);
					hideModal($('#uploadFileInsertContainer'));
				}else{
					msg = msg.replace("0|", 'Error: ');
					$('#result').html('<p class="error">'+msg+'</p>');
				}
				$('button#uploadFileSubmit').css({'opacity':1,'cursor':'pointer'}); 
				$('div.progress').hide();

				return true;
			
			// for uploading file (both in manage_contents and preferences-bg-image), reload page with message
			}else{*/
				window.location = url+'?upload_result='+encodeURIComponent(msg);
			//}
		},

		// Custom XMLHttpRequest
		xhr: function() {
			var myXhr = $.ajaxSettings.xhr();
			if (myXhr.upload) {
				// For handling the progress of the upload
				myXhr.upload.addEventListener('progress', function(e) {
					if (e.lengthComputable) {
						var t = e.total;
						var l = e.loaded;
						var percent = (100.0 / t * l).toFixed(2);
						//var lastWidth = $('.bar').width();
						if(percent > 95){
							$('a#chooseFileLink').html('Processing (almost done) ...');
						}
						$('.bar').stop().animate({width: percent+'%'}, 1500);
					}
				} , false);
			}
			//alert(myXhr);
			return myXhr;
		}
	});
	
});

/* end upload behaviors */
