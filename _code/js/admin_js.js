
/***** behavior functions *****************************************************/

// tables should be sortable
$("table.data").tablesorter();


// assign behavior to .closeMessage (close parent on click)
$('body').on('click', '.closeMessage', function(e){
	var parent = $(this).parent();
	parent.hide();
	//window.location.search = '';
    e.preventDefault();
});

// display 'working' div while processing ajax requests
$(document).ajaxStart(function(){
	$('#working').show();
}).ajaxStop(function(){
	//setTimeout(function(){
		$('#working').hide();
	//}, 2000);
	if($('#done').html() != ''){
		$('#done').show();
		setTimeout(function(){
			$('#done').hide();
			$('#done').html('');
		}, 2000);
	}
});


// if (article) statut_id is changed to 6='vendu' (from within 'Modifier un article' page only), show prix_vente field, set visible = 0
$("select[name='statut_id']").on('change', function(){
	var $table = $(this).parent().parent().parent();
	if($table !== false){
		var $tr = $table.find('tr#prixVente');
		if($tr !== false){
			var $prixVente = $table.find($("input[name='prix_vente']"));
			if($(this).val() == 6){
				$tr.show();
				var $visibleZero = $table.find($("input[name='visible']#visibleZero"));
				$prixVente.prop("required","required");
				$prixVente.focus();
				$visibleZero.prop("checked", true);
			}else{
				$prixVente.val('');
				$tr.hide();
			}
		}
	}
});


// for 'statut_id' select inputs, store previous value before change, empty it on blur.
var previous_id;
$("table.data").on('keydown', "select[name='statut_id']", function(){
	previous_id = $(this).val();
	//alert(previous);
}).on('blur', "select[name='statut_id']", function(){
	previous_id = '';
});

// handles all select drop-downs change via ajax, including article sale (opens prixVenteModal) if statut_id changed to 6 (vendu)
$("table.data").on('change', 'select.ajax', function(){
	var $table = $(this).parents('table');
	var table = $table.data('id'); // 'articles'
	var id = $(this).parents('tr').data('id'); // '167'
	var value = $(this).val();
	var col = $(this).attr('name'); // 'statut_id'

	//alert(table);
	//alert(id);
	//alert(previous);

	// select[name='statut_id'] can be used to change statut_id to 6 = 'vendu', in this case, show prixVenteModal
	if(value == 6){ // = vendu
		var prix = $(this).parents('tr').find('td.prix').html();
		showModal('prixVenteModal?article_id='+id+'&prix='+encodeURIComponent(prix)+'&previous_id='+previous_id);
		
	}else{
		//alert('ID:'+id+' modifié pour "'+value+'" dans tableau '+table+', column '+col);
		updateTable(table, col, id, value);
	}
});


$("body").on('click', '#prixVenteSubmit', function(e){
	e.preventDefault();
	var $form = $(this).parents('form');
	var id = $form.find('input[name="id"]').val();
	var prix_vente = $form.find('input[name="prix_vente"]').val();
	var $payement = $form.find('input[name="payement_cheque"]');
	//alert('id='+id+' prix_vente='+prix_vente);
	hideModal($(this).parents('div.modal'));
	updateTable('articles', 'prix_vente', id, prix_vente);
	if($payement.prop('checked') == true){
		//alert('checked');
		updateTable('articles', 'payement_id', id, '2');
	}
});


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

// show/hide longer text on mouse enter short text (for descriptif and observations)
$("div.short").on('mouseenter', function(){
	//alert('mouseenter');
	$(this).children().show();
});

$("div.short").on('mouseleave', function(){
	$(this).children().hide();
});


// add .closeMessage to messages, so they can be closed (hidden)
$('<a class="closeMessage">&times;</a>').appendTo('p.error, p.note, p.success, div.success');


/* UPLOAD FUNCTIONS */

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


// save sql data ( ajax call, uses php function update_table() )
function updateTable(table, col, id, value){
	$.ajax({
		// Your server script to process the upload
		url: '/_code/php/admin/admin_ajax.php?updateTable&table='+table+'&col='+col+'&id='+id+'&value='+value,
		type: 'GET',
		// on success show message
		success : function(msg) {
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
/* end upload functions */




/*
function is_touch_device() {
	//return true;
	return 'ontouchstart' in window        // works on most browsers 
		|| 'onmsgesturechange' in window;  // works on IE10 with some false positives
};
*/
