
var sep = 'qQq'; // custom separator to concatenate many cols/vals queries that will be retreived by php as array
var wW = $(window).width();
var wH = $(window).height();
var new_vrac_id = 0;
var oldVal = ''; // global var used to update on element focus and compared to new value on blur

// js equivalent to php time(), will be used throughout functions
var unix_time = Math.round((new Date()).getTime()/1000);

/***** functions *****************************************************/

// save sql data ( ajax call, uses php function update_table() ) 
// uses 'sep' global var declared above to pass more than one value for 'col' and 'value'
function updateTable(table, id, col, value){
	$.ajax({
		// Server script to process the upload
		url: '/_code/php/admin/admin_ajax.php?updateTable&table='+table+'&id='+id+'&col='+col+'&value='+value,
		type: 'GET',
		// on success show message
		success : function(msg) {
			var pre = msg.substr(0,2);
			var mes = msg.substr(2);
			var message;
			if(pre == '0|'){
				message = '<p class="error">'+mes+'</p>';
			}else if(pre == '1|'){
				message = '<p class="success">'+mes+'</p>';
			}else if(pre == '2|'){
				message = '<p class="note">'+mes+'</p>';
			}
			$('#done').html(message);
			return true;
			//return message;
		}
	});
}

// remove article images directory when a panier is sold containg this article
function removeDirs(imgDirs){
	$.ajax({
		// Server script to process the upload
		url: '/_code/php/admin/admin_ajax.php?removeDirs='+imgDirs,
		type: 'GET',
		// on success show message
		success : function(msg) {
			var pre = msg.substr(0,2);
			var mes = msg.substr(2);
			var message;
			if(pre == '0|'){
				message = '<p class="error">'+mes+'</p>';
			}else if(pre == '1|'){
				message = '<p class="success">'+mes+'</p>';
			}else if(pre == '2|'){
				message = '<p class="note">'+mes+'</p>';
			}
			//$('#done').html(message);
			//alert(message);
			//return true;
			//return message;
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
	var t5;
	$('#done').show();
	t5 = setTimeout(function(){
		$('#done').fadeOut(800, function(){
			$('#done').html('');
		});
	}, 2700);
}

// delete item from table (ajax)
function deleteItem(table, item_id){
	$.ajax({
		url: '/_code/php/admin/admin_ajax.php?deleteItem&table='+table+'&id='+item_id,
		type: 'GET',
		success : function(msg){
			return msg;
		},
		error: function() {
			alert('Error with deleteItem ajax call');
		}
	});
}

// create article (ajax)
function create_article(fields){
	var query_string = '';
	var updatePaniers = true;

	// debug
	//console.log(fields);

	// let's build our query string for admin_ajax.php?create_article
	jQuery.each(fields, function( i, field ){
		// let's omit the panier_statut_id from query, but let's also find out if we need to update paniersModal after article is created
		if( field.name == 'panier_statut_id' ){
			if(field.value == statut_table['vendu']){
				updatePaniers = false;
			}
		}else{
			query_string += '&'+field.name+'='+field.value;
		}
	});

	// ajax call that will use db_function insert_new()
	$.ajax({
		// Server script to process the upload 
		url: '/_code/php/admin/admin_ajax.php?create_article'+query_string,
		type: 'GET',

		// on success
		success : function(msg) {
			var pre = msg.substr(0,2);
			var mes = msg.substr(2);
			var message;
			if(pre == '0|'){
				message = '<p class="error">'+mes+'</p>';
			}else if(pre == '2|'){
				message = '<p class="note">'+mes+'</p>';

			// success, create panier (once created, it will update the article paniers_id with panier new id)
			}else if(pre == '1|'){
				var article_id = mes;
				message = '<p class="success">Article créé ID:'+article_id+'</p>';
				
				// if we're in nouvelle-vente.php let's show meaningful message and scroll to top of page
				if(basename(window.location.href) == 'nouvelle-vente.php'){
					//alert('WE\'RE IN nouvelle-vente.php, js function create_article, admin_ajax.php?create_article');
					window.scrollTo(0, 0);
					//$('div#adminContainer div#msg').remove();
					//$('div#adminContainer').prepend('<div id="msg"><p class="success">Article vendu, ID:'+article_id+' <a href="javascript:;" class="closeBut">&times;</a></p></div>');
					// reset new article form
					$('form#newArticle').trigger("reset");
				}

				if(updatePaniers){
					// debug
					//alert('HERE!');

					setTimeout(function(){
						updatePaniersModal();
					}, 1000);
				}
			}
			// show message
			$('#done').html(message);

		},

		// Custom XMLHttpRequest
		xhr: function() {
			var myXhr = $.ajaxSettings.xhr();
			//alert(myXhr);
			return myXhr;
		}
	});
}

// ajax call (that will use PHP db_function duplicate_vrac_article() to duplicate article vendu)
function duplicate_vrac_article(id, old_poids, old_prix){
	$.ajax({
		// Server script to process the upload 
		url: '/_code/php/admin/admin_ajax.php?vrac_vente&original_id='+id+'&old_poids='+old_poids+'&old_prix='+old_prix,
		type: 'GET',

		// on success, message is either "(0|2)|[error or note string]", or "1|[new_id]" (success, new id of duplicated vrac article)
		success : function(msg) {
			// see if message is error or note (0| or 2|)
			var pre = msg.substr(0,2);
			var mes = msg.substr(2);
			var message;
			if(pre == '1|'){
				// new_vrac_id global scope var is set on top of this file
				new_vrac_id = mes; // "new_id", i.e. 126
				message = '<p class="success">'+new_vrac_id+'</p>';
			}else if(pre == '0|'){
				message = '<p class="error">'+mes+'</p>';
			}else if(pre == '2|'){
				message = '<p class="note">'+mes+'</p>';
			}
		},

		// Custom XMLHttpRequest
		xhr: function() {
			var myXhr = $.ajaxSettings.xhr();
			//alert(myXhr);
			return myXhr;
		}
	});
}


/******** paniers functions ********/
// update paniers modal
function updatePaniersModal(){
	$.ajax({
		url: '/_code/php/admin/admin_ajax.php?updatePaniersModal',
		type: 'GET',
		success : function(msg){
			// msg is: "1234|paniers html output" the number before | is the number of paniers
			var paniersCount = msg.match(/^\d*/); // get number of paniers (used to update span#paniersCount)
			var len = paniersCount.length;
			var paniersOutput = msg.substr(len+1); // get the html output minus the paniers number and pipe

			// update panier modal container with paniersOutput (html output) if found
			if( $('#paniersContainer').length ){
				$('#paniersContainer #panierAjaxTarget').html(paniersOutput);
				$('div#paniersContainer').show();
				// memory for showing or hiding paniers modal from page to page
				document.cookie = "paniersModalDisplay=block; path=/";
			}
			
			// refresh (reload) vente-paniers in container div#vpLoader, if found
			if($('body div#vpLoader').length !== 0){
				var context = window.location;
				$('body div#vpLoader').html('');
				$('body div#vpLoader').load('/_code/php/forms/vente-paniers.php?context='+encodeURIComponent(context));
			}

			// update paniers count in span#paniersCount if found
			if($('span#paniersCount').length){
				$('span#paniersCount').each( function(){
					$(this).text(paniersCount);
				});
			}
			return true;
		}
	});
}

// save panier changes
function savePanierChanges(panierId){

	var myForm = document.forms.namedItem(panierId);

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
			var pre = msg.substr(0, 2);
			var mes = msg.substr(2);
			var message = '';
			if(pre == '0|'){
				message = '<p class="error">'+mes+'</p>';
			}else if(pre == '2|'){
				message = '<p class="note">'+mes+'</p>';
			
			// SUCCESS, msg = new panier id
			}else if(pre == '1|'){
				message = '<p class="success">'+mes+'</p>';
				display_panier(panierId, 'ventesPaniersAjaxTarget');
			}
			// show message
			$('#done').html(message);
		}
	});
}

// ajax call to display one panier output (used to refresh single panier container in paniers display)
function display_panier(panierId, context){
	// ajax call that will use db_function create_panier()
	$.ajax({
		// Server script creates the panier
		url: '/_code/php/admin/admin_ajax.php?display_panier&id='+panierId+'&context='+context,
		type: 'GET',

		// on success; msg is either new panier ID, or an error message
		success : function(msg) {
			var pre = msg.substr(0, 2);
			var mes = msg.substr(2);
			var message = '';
			if(pre == '0|'){
				message = '<p class="error">'+mes+'</p>';
			}else if(pre == '2|'){
				message = '<p class="note">'+mes+'</p>';
			
			// SUCCESS, msg = new panier id
			}else if(pre == '1|'){
				message = mes;
			}
			if(context == 'ventesPaniersAjaxTarget'){
				var $target = $('div#ventesPaniersAjaxTarget').find('div.pCont[data-panierid='+panierId+']');
			}else if(context == 'paniersAjaxTarget'){
				var $target = $('div#paniersAjaxTarget').find('div.pCont[data-panierid='+panierId+']');
			}
			// replace the found panier output
			if($target.length){
				$target.replaceWith(message);
			// or prepend it to the main container
			}else if($('div#'+context).length){
				$('div#'+context).prepend(message);
			}/*else{
				alert('nowhere to show new panier');
			}*/
		}
	});
}

// ajax call to create panier (and create article, OR update article)
function create_panier(article_id_or_fields, nom, poids, prix, paiement_id, vrac, panier_statut_id){
	// ajax call that will use db_function create_panier()
	$.ajax({
		// Server script creates the panier
		url: '/_code/php/admin/admin_ajax.php?create_panier&nom='+nom+'&poids='+poids+'&prix='+prix+'&paiement_id='+paiement_id+'&statut_id='+panier_statut_id,
		type: 'GET',

		// on success; msg is either new panier ID, or an error message
		success : function(msg) {

			// message is error (0|[message string]) ou note (2|[message string]) ou success (1|[paniers_id])
			var pre = msg.substr(0, 2);
			var mes = msg.substr(2);
			var message = '';
			if(pre == '0|'){
				message = '<p class="error">'+mes+'</p>';
			}else if(pre == '2|'){
				message = '<p class="note">'+mes+'</p>';
			
			// SUCCESS, msg = new panier id
			}else if(pre == '1|'){
				message = '<p class="success">'+mes+'</p>';

				// If article_id_or_fields is article id, update article.
				// Else, we're creating a panier + an article at the same time, and the article has not been created yet, so article_id_or_fields is a js object containing the fields needed for the creation of the article
				
				var paniers_id = mes;
				//alert(paniers_id);

				// article_id_or_fields is object containing data for creating article
				if( typeof article_id_or_fields == 'object' ){

					// update paniers_id field, if already in 
					var panier_in_fields = false;
					var panier_statut_in_fields = false;
					jQuery.each(article_id_or_fields, function( i, field ){
						if(field.name == 'paniers_id'){
							field.value = paniers_id;
							panier_in_fields = true;
						}
						if(field.name == 'panier_statut_id'){
							field.value = panier_statut_id;
							panier_statut_in_fields = true;
						}
					});
					// if not already there, add paniers_id to article_id_or_fields object
					if(!panier_in_fields){
						article_id_or_fields[article_id_or_fields.length] = {name:"paniers_id", value:paniers_id};
					}
					// we'll use panier_statut_id to determine if we need to update and show paniersModal
					if(!panier_statut_in_fields){
						article_id_or_fields[article_id_or_fields.length] = {name:"panier_statut_id", value:panier_statut_id};
					}
					create_article(article_id_or_fields);

					
				// article_id_or_fields is article id
				}else{
					var article_id = article_id_or_fields;
					message = '<p class="success">Article updated, panier crée ID:'+paniers_id+'</p>';
					var article_statut_id = statut_table['vendu'];
					// update article
					updateTable(
						'articles', 
						article_id, 
						'paniers_id'+sep+'prix'+sep+'poids'+sep+'statut_id'+sep+'date_vente', 
						paniers_id +sep+ prix +sep+ poids+sep+article_statut_id+sep+unix_time
					);
					
					// update html table or form if article is NOT vrac
					if(vrac*1 !== 1){
						//alert('not vrac!');
						var $tr = $('table.data').find('tr[data-id="'+article_id+'"]')
						if( $tr.length ){
							//alert('yes tr!');
							$tr.hide(1000);
						}
						if( $('div#formsContainer').length ){
							//alert('yes formsContainer!');
							$('div#formsContainer').hide();
							$('div#adminContainer').append('<a class="button" href="/admin/">&lt; Retour</a>'+message);
						}
					}

					// show message
					$('#done').html(message);

					// update panierModal
					if( panier_statut_id !== statut_table['vendu'] ){ // show updated paniers only if panier was not directly sold
						setTimeout(function(){
							updatePaniersModal();
						}, 500);
					}
				}
			}
		},

		// Custom XMLHttpRequest
		xhr: function() {
			var myXhr = $.ajaxSettings.xhr();
			//alert(myXhr);
			return myXhr;
		}
	});
}

// compare panier total and sum of articles prix (in HTML FORM)
function compareTotal($this){
	var $cont = $this.closest('div.pCont');
	var total = 0;
	var totalVal = $cont.find('input.prixVentePanier').val();
	totalVal = parseFloat(totalVal);
	// get and add value of each article prix
	var total = article_sum($cont, 'prix');
	// compare sum of articles with panier total
	if( totalVal !== total ){
		// show warning sign next to total
		$cont.find('p.n a.warning').css('visibility','visible');
	}else{
		$cont.find('p.n a.warning').css('visibility','hidden');
	}
}

// add up articles prix ou poids (item_to_add) within a panier (panier_id) (in HTML FORM)
// $cont is the html container (typically, div.pCont)
function article_sum($cont, item_to_add){
	var total = 0;
	if(item_to_add == 'prix'){
		var cssTarget = '.currency';
	}else if(item_to_add == 'poids'){
		var cssTarget = '.weight';
	}else{
		alert('Error: invalid item_to_add: '+item_to_add);
		return false;
	}
	// find all input.(currency|weight) that are are not to be removed, and only in a div.particle container (so as to exclude the panier total input in main container).
	$cont.find('div.particle:not(.removeConfirm) input'+cssTarget).each( function(){
		if($(this).val().length){
			var v = parseFloat( $(this).val() );
			total += v;
		}
	});
	return total;
}

// remove article from panier
function remove_article_from_panier(article_id){
	// update article: remove paniers_id, date_vente, set statut_id = 1 (disponible)
	updateTable(
		'articles', 
		article_id, 
		'paniers_id'+sep+'date_vente'+sep+'statut_id', 
		'' +sep+ '' +sep+ '1'
	);
	// update paniers modal
	setTimeout(function(){
		updatePaniersModal();
	}, 500);
}


/*************** PANIERS behaviors START **************/

/* PANIERS EN COURS */
$('div#paniersContainer').on('click', 'a.button.ventePanierSubmit', function(e){
	e.preventDefault();
	var t4;
	var t5;
	if( $(this).hasClass('disabled') ){
		alert('Merci de remplir le champ "Total"');
		return false;
	}
	var $container = $(this).closest('div.pCont');
	var id = $container.attr('data-panierid');
	var nom = $container.attr('data-paniernom');
	var total = $container.find('input.prixVentePanier').val();
	var $paiement_cheque = $container.find('input[name="paiement_id"]');
	if($paiement_cheque.prop('checked') == true){
		var paiement_id = paiement_table['chèque'];
	}else{
		var paiement_id = paiement_table['espèces'];
	}
	var poids = $container.attr('data-poids');
	var statut_id = statut_table['vendu']; // 4

	updateTable(
		'paniers', 
		id, 
		'total'+sep+'paiement_id'+sep+'statut_id'+sep+'date_vente'+sep+'poids', 
		 total +sep+ paiement_id +sep+ statut_id +sep+ unix_time + sep+ poids
	);

	var imgDirs = '';
	
	// update each article prix inside panier
	$container.find('div.particle').each( function(){
		var article_id = $(this).attr('data-articleid');
		var $a_prix_input = $(this).find('input[name="aPrix"]');
		var $a_poids_input = $(this).find('input[name="aPoids"]');
		if($a_prix_input.val().length){
			var article_prix = $a_prix_input.val();
		}else{
			var article_prix = '0';
		}
		if($a_poids_input.val().length){
			var article_poids = $a_poids_input.val();
		}else{
			var article_poids = '0';
		}
		alert('artcielID: '+article_id+', prix: '+article_prix+', poids: '+article_poids);
		updateTable(
			'articles', 
			article_id, 
			'prix'+sep+'poids', 
			article_prix+sep+article_poids
		);
		// create string of article_id(s) to delete article img directory later
		imgDirs += article_id+sep;

	});

	// delete image directory for each article in sold panier
	removeDirs(imgDirs);
	
	// we want to retreive the result of updateTable above, which wraps an asynchronous call. When it's done, the function sets the html of div#done. So we can check for that and know the result, but let's wait to make sure it's done
	t4 = setTimeout(function(){
		var result = $('#done').html();
		//alert(result);
		if( result.substr(0,15) !== '<p class="error' ){ // no error message = success
			updatePaniersModal();
			var venteMsg = '<div class="success" style="padding-right:35px; margin-top:5px;">Panier vendu: '+nom+'<br><a href="javascript:;" class="undoVentePanier" data-panierid="'+id+'" title="rouvrir ce panier">Annuler la vente<span class="undo"></span></a> <a href="javascript:;" class="remove" style="position:absolute; top:0; right:0;" onclick="$(this).parent().hide();" title="hide"></a></div>';
			t5 = setTimeout(function(){
				$('div#panierAjaxTarget').prepend(venteMsg);
				display_panier(id, 'ventesPaniersAjaxTarget');
			}, 200);
			
		}else{
			$container.append(result);
		}
	}, 350);

});


/* UPDATE PANIER STATUT (from paniersModal.php, ul.statutActions drop-down) */
$('div#paniersContainer, div#ventesPaniersContainer').on('click', 'ul.statutActions a', function(e){
	e.preventDefault();

	var t6;

	var $container = $(this).parents('div.pCont');
	var id = $container.attr('data-panierid');
	var new_statut_id = $(this).attr('data-statut');

	updateTable(
		'paniers', 
		id, 
		'statut_id', 
		new_statut_id
	);
	
	// we want to retreive the result of updateTable above, which wraps an asynchronous call. When it's done, the function sets the html of div#done. So we can check for that and know the result, but let's wait a little to make sure its done
	t6 = setTimeout(function(){
		var result = $('#done').html();
		//alert(result);
		if( result.substr(0,15) !== '<p class="error' ){ // no error message
			$container.animate({'height':'30px'}, 500, function(){
				$(this).replaceWith('<div class="success" style="padding-right:35px; margin-top:5px;">Panier statut mis à jour  <a href="javascript:;" class="remove" style="position:absolute; top:0; right:0;" onclick="$(this).parent().hide();" title="hide"></a></div>');
			});
		}
	}, 500);

});


// update panier en cours when individual article is removed
$('div#paniersContainer').on('click', 'div.particle a.remove', function(){
	// get article id
	var article_id = $(this).parents('div.particle').attr('data-articleid');
	//alert(article_id);
	remove_article_from_panier(article_id);
});

// "enregistrer la vente" button is enabled or disabled depending on total set or empty
$('div#paniersContainer').on('keyup', 'input.prixVentePanier', function(){
	var $venteBut = $(this).parents('div.pCont').find('a.button.vente');
	if( $(this).val() !== '' ){
		$venteBut.removeClass('disabled');
	}else{
		$venteBut.addClass('disabled');
	}
});
// same as above BUT on change, AND focus on submit vente button (cannot be combined with above)
$('div#paniersContainer').on('change', 'input.prixVentePanier', function(){
	var $venteBut = $(this).parents('div.pCont').find('a.button.vente');
	if( $(this).val() !== '' ){
		$venteBut.removeClass('disabled');
		$venteBut.focus();
	}else{
		$venteBut.addClass('disabled');
	}
});
$('div#paniersContainer').on('click', 'a.undoVentePanier', function(){
	var id = $(this).attr('data-panierid');
	updateTable('paniers', id, 'statut_id', 1);
	var $panier_displayed = $('div#ventesPaniersContainer').find('div.pCont[data-panierid='+id+']');
	if($panier_displayed.length){
		$panier_displayed.hide();
	}
	setTimeout(function(){
		updatePaniersModal();
	},150);
});




/* ALL PANIERS (EN COURS + VENDUS) */
// auto-select inputs (.currency and .weight) on click
$('div#paniersContainer, div#ventesPaniersContainer').on('click', 'div.pCont input.currency, div.pCont input.weight', function(){
	$(this).select();
});
// auto-select dateVentes inputs
$('form[name="dateVentes"] input[type="text"]').on('click', function(){
	$(this).select();
});
// show warning next to panier total when individual articles do not add to its value
$('div#paniersContainer, div#ventesPaniersContainer').on('change', 'div.pCont input.currency', function(){
	compareTotal( $(this) );
});
// auto adjust the height of textarea via tAreaContainer hidden div
$('div#paniersContainer, div#ventesPaniersContainer').on('keyup', 'textarea.notes', function(e){
	var cont = this.parentElement;
	var text_to_change = cont.childNodes[0];
	if(e.which == 13){
		text_to_change.nodeValue += '\n&nbsp;';
		return false;
	}
	if(this.value.length){
		text_to_change.nodeValue = this.value;
	}else{
		text_to_change.nodeValue = '&nbsp;';
	}
});
// update global var 'oldVal' when textarea.notes is on focus
$('div#paniersContainer, div#ventesPaniersContainer').on('focus', 'textarea.notes', function(){
	oldVal = $(this).val();
});
// save panier notes when loosing focus on textarea.notes
// oldVal is set as global var at top of page, and updated on focus (above)
$('div#paniersContainer, div#ventesPaniersContainer').on('blur', 'textarea.notes', function(){
	var $this = $(this);
	var newVal = $this.val();
	//alert(newVal);
	if( newVal !== oldVal){
		var id = $this.closest('div.pCont').attr('data-panierid');
		updateTable('paniers', id, 'notes', encodeURIComponent(newVal) );
	}
	// hide textarea if value is empty - show 'add note' button
	if(newVal == ''){
		setTimeout( function(){
			var $cont = $this.closest('div.pCont');
			$cont.find('div.tAreaResizer').css('display','none');
			$cont.find('a.addNote').css('display','inline-block');
		}, 150);
	}
});
// show and focus on notes textarea when a.addNote is clicked
$('div#paniersContainer, div#ventesPaniersContainer').on('click', 'div.pCont a.addNote', function(e){
	e.preventDefault();
	var $cont = $(this).closest('div.pCont');
	$cont.find('div.tAreaResizer').css('display','block');
	$cont.find('textarea.notes').focus();
	$cont.find('a.addNote').css('display','none');
});

// update panier total when individual articles prix are changed
/***** !!!!!!!! supprimé jusqu'à nouvel ordre
$('div#paniersContainer, div#ventesPaniersContainer').on('change', 'div.paActions input.currency', function(){
	// get and add value of each article prix
	var $cont = $(this).parents('div.pCont');
	var total = article_sum($cont, 'prix');
	var $submit = $cont.find('a.ventePanierSubmit');
	$cont.find('input.prixVentePanier').val(total.toFixed(2));
	if( $submit.hasClass('disabled') ){
		$submit.removeClass('disabled');
	}
	//alert('changed');
});
*/

// delete panier when it has been emptied 
$('div#paniersContainer, div#ventesPaniersContainer').on('click', 'a.deletePanier', function(){
	var $container = $(this).closest('div.pCont');
	var paniers_id = $container.attr('data-panierid');
	var table = 'paniers';
	$.ajax({
		// Server script to process the upload
		url: '/_code/php/admin/admin_ajax.php?deleteItem&table='+table+'&id='+paniers_id,
		type: 'GET',
		// on success show message
		success : function(msg) {
			var pre = msg.substr(0, 2);
			var mes = msg.substr(2);
			var message = '';
			if(pre == '1|'){
				message = '<p class="success">'+mes+'</p>';
			}else if(pre == '0|'){
				message = '<p class="error">'+mes+'</p>';
			}else if(pre == '2|'){
				message = '<p class="note">'+mes+'</p>';
			}
			$('#done').html(message);
			//return msg;
			/*if($container.parent().attr('id') == 'panierAjaxTarget'){
				// update paniers modal
				setTimeout(function(){
					updatePaniersModal();
				}, 400);
			}else if($container.parent().attr('id') == 'ventesPaniersAjaxTarget'){
				// update paniers modal
				setTimeout(function(){
					$container.hide();
				}, 150);
			}*/
			setTimeout(function(){
				$container.hide();
			}, 150);
			
			return true;
		},
		error: function() {
			alert('Error with deleteItem ajax call');
		}
	});
});


/* PANIERS VENDUS (VENTES) */
// save panier changes
$('div#ventesPaniersContainer').on('click', 'button.savePanierChanges', function(e){
	e.preventDefault();
	//var panierId = $(this).closest('div.pCont').attr('data-panierid');
	var panierId = $(this).closest('form').attr('name');
	savePanierChanges(panierId);
});
// compare articles prix and panier total on form reset and show warning if not equal
$('div#ventesPaniersContainer').on('click', 'div.pCont button.reset', function(){
	var $this = $(this);
	setTimeout(function(){
		compareTotal( $this );
	}, 150);
});
// highlight changed value and show save/cancel buttons in paniers Form
$('div#ventesPaniersContainer').on('change', 'div.pCont input, div.pCont select', function(){
	var $container = $(this).closest('div.pCont');
	$(this).addClass('changed');
	//$(this).css({'background-color':'#fff', 'border':'1px dashed #000'});
	$container.find('div.changes').css('display', 'block');
});
// highlight change when individual article is removed and show save/cancel buttons
$("div#ventesPaniersContainer").on('click', 'div.particle a.remove', function(){
	var $container = $(this).closest('div.pCont');
	var $particle = $(this).closest('div.particle');
	$particle.addClass('removeConfirm');
	$particle.find('select.statut_id').val('1');
	$container.find('div.changes').css('display', 'block');
	compareTotal( $(this) );
});
// cancel changes in panier vendu form
$('div#ventesPaniersContainer').on('click', 'div.pCont button.reset', function(){
	var $this = $(this);
	var $container = $(this).closest('div.pCont');
	//$container.find('input, select').removeAttr('style');
	$container.find('input, select').removeClass('changed');
	$container.find('div.particle').removeClass('removeConfirm');
	setTimeout(function(){
		$container.find('div.changes').css('display', 'none');
	}, 100);
});
// quick cancel article removal from panier
$('div#ventesPaniersContainer').on('click', 'div.particle a.undo', function(){
	var $container = $(this).closest('div.pCont');
	var $particle = $(this).closest('div.particle');
	$particle.removeClass('removeConfirm');
	$particle.find('select.statut_id').val('4'); // vendu
	compareTotal( $(this) );
	// hide 'annuler'/'enregistrer' buttons if nothing else was changed
	if( $container.find('input.changed, select.changed').length === 0){
		setTimeout(function(){
			$container.find('div.changes').css('display', 'none');
		}, 100);	
	}
});

/*************** PANIERS behaviors END **************/




/*********** ARTICLE VENTE (ADD TO PANIER) behaviors START **************/

/** when article is sold via prixVenteModal.php */ 
// 1. button #directeVenteSubmit
$('body').on('click', 'button#directeVenteSubmit', function(e){
	e.preventDefault();

	var $form = $(this).parents('form');
	var $poids = $form.find('input[name="poids"]');
	var $paiement_cheque = $form.find('input[name="paiement_id"]');
	if($paiement_cheque.prop('checked') == true){
		var paiement_id = paiement_table['chèque'];
	}else{
		var paiement_id = paiement_table['espèces'];
	}
	var article_id = $form.find('input[name="id"]').val(); // article[id]
	var prix = $form.find('input[name="prix"]').val().replace(',','.');
	var poids = $poids.val().replace(',','.');

	if(poids.length == 0){
		$poids.focus();
		alert('Merci de remplir le champ "poids"');
		return false;
	}

	// used as nom du panier if new panier is to be created
	var panier_nom = $form.find('input[name="titre"]').val();

	// hide modal
	hideModal( $(this).parents('div.modal') );

	/* vrac? */ 
	var vrac = $form.find('input[name="vrac"]').val();

	// if vrac, duplicate article vendu ( update original minus poids vente? ...)
	if(vrac*1 == 1/* && new_poids > 0*/){
		// these vars will be used to duplicate item with pre-sale prix and poids
		var old_prix = $form.find('input[name="old_prix"]').val();
		var old_poids = $form.find('input[name="old_poids"]').val().replace(',','.');
		//var new_poids = parseFloat(old_poids)-parseFloat(poids);
		// ajax call (that will use php db_function duplicate_vrac_article() to duplicate vrac article)
		duplicate_vrac_article(article_id, old_poids, old_prix);
		// ↑ when completed and if successfull, the above ajax call will update the value of global js var new_vrac_id to id of newly created vrac article. In which case, this new_vrac_id should be used as article_id in create_panier call below

		/* 
		CHANGES START NOW 
		up to here, directeVenteSubmit (this) and ajoutPanierSubmit (below) are strictly the same 
		*/

		var t1;
	
		t1 = setTimeout( function isReady(){

			if(new_vrac_id !== 0){ // ready!
				article_id = new_vrac_id;
				// reset new_vrac_id!
				new_vrac_id = 0;
				// create panier
				create_panier(article_id, panier_nom, poids, prix, paiement_id, vrac, statut_table['vendu']);
			
			}else{ // not ready yet, new timeout
				t1 = setTimeout(isReady, 300);
			}
		}, 300);
	
	// if not vrac, just create the panier using article_id already set
	}else{
		create_panier(article_id, panier_nom, poids, prix, paiement_id, vrac, statut_table['vendu']);
	}

});

// 2. button #ajoutPanierSubmit
$('body').on('click', 'button#ajoutPanierSubmit', function(e){
	e.preventDefault();

	var $form = $(this).parents('form');
	var $poids = $form.find('input[name="poids"]');
	var $paiement_cheque = $form.find('input[name="paiement_id"]');
	if($paiement_cheque.prop('checked') == true){
		var paiement_id = paiement_table['chèque'];
	}else{
		var paiement_id = paiement_table['espèces'];
	}
	var article_id = $form.find('input[name="id"]').val(); // article[id]
	var prix = $form.find('input[name="prix"]').val().replace(',','.');
	var poids = $form.find('input[name="poids"]').val().replace(',','.');

	if(poids.length == 0){
		$poids.focus();
		alert('Merci de remplir le champ "poids"');
		return false;
	}

	// used as nom du panier if new panier is to be created
	var panier_nom = $form.find('input[name="titre"]').val();

	// hide modal
	hideModal( $(this).parents('div.modal') );

	/* vrac? */ 
	var vrac = $form.find('input[name="vrac"]').val();

	// if vrac, duplicate article vendu, ( update original minus poids vente? ...)
	if(vrac*1 == 1){
		//these vars will be used to duplicate item with pre-sale prix and poids
		var old_prix = $form.find('input[name="old_prix"]').val();
		var old_poids = $form.find('input[name="old_poids"]').val().replace(',','.');
		// ajax call (that will use php db_function duplicate_vrac_article() to duplicate vrac article)
		duplicate_vrac_article(article_id, old_poids, old_prix);
		// ↑ when completed and if successfull, the above ajax call will update the value of global js var new_vrac_id to id of newly created vrac article. In which case, this new_vrac_id should be used as article_id in create_panier call below

		/* 
		CHANGES START NOW 
		up to here, ajoutPanierSubmit (this) and directeVenteSubmit (above) are strictly the same 
		*/
	}

	// we'll use setTimeout, better declare var to hold it
	var t1;
	var t2;

	/**  are we creating a new panier, or using a 'panier en cours' ? **/
	// If 'nouveau panier' option was selected, we can get new panier nom from panierNom field
	var $panierNomInput = $form.find('input[name="panierNom"]');
	if( $panierNomInput.val().length ){ // we are creating a new panier
		var createPanier = true;
		var panier_nom = $panierNomInput.val();
		panier_nom = panier_nom.replace(/("|')/g, ''); // sanitize
	
	// else, we are using a 'panier en cours', don't need its name but its ID
	}else{
		var createPanier = false;
		var paniers_id = $form.find('select[name="paniers_id"]').val();
	}

	// if new panier needs to be created
	if(createPanier){
		// If vrac, set time out to make sure we catch the updated new_vrac_id via duplicate_vrac_article ajax call above
		if(vrac*1 == 1){ 
			t1 = setTimeout( function isReady(){
				
				if(new_vrac_id !== 0){ // ready!
					article_id = new_vrac_id;
					// reset new_vrac_id!
					new_vrac_id = 0;
					// create panier - ajax call that will use db_function create_panier()
					create_panier(article_id, panier_nom, poids, prix, paiement_id, vrac, statut_table['disponible']);

				}else{ // not ready yet, new timeout
					t1 = setTimeout(isReady, 300);
				}
			}, 300);

		// if not vrac, just create the panier using article_id already set
		}else{
			create_panier(article_id, panier_nom, poids, prix, paiement_id, vrac, statut_table['disponible']);
		}

	// just update article to sold with existing paniers_id
	}else{

		// If vrac, set time out to make sure we catch the updated new_vrac_id via duplicate_vrac_article ajax call above
		if(vrac*1 == 1){
			t1 = setTimeout( function isReady(){

				if(new_vrac_id !== 0){ // ready!
					article_id = new_vrac_id;
					// reset new_vrac_id!
					new_vrac_id = 0;
					// update article
					updateTable(
						'articles', 
						article_id, 
						'paniers_id'+sep+'prix'+sep+'poids'+sep+'statut_id'+sep+'date_vente', 
						paniers_id +sep+ prix +sep+ poids+sep+statut_table['vendu']+sep+unix_time
					);
					// and update panier date, so that it goes to top of the list (order date DESC)
					updateTable(
						'paniers', 
						paniers_id, 
						'date', 
						unix_time
					);

					// we need to give time to the 2 updateTable ajax calls above to happen
					t2 = setTimeout( function(){
						updatePaniersModal();
					}, 500);

				}else{ // not ready yet, timeout again
					t1 = setTimeout(isReady, 300);
				}
			}, 300);

		// if not vrac, just update article and panier
		}else{
			// update article
			updateTable(
				'articles', 
				article_id, 
				'paniers_id'+sep+'prix'+sep+'poids'+sep+'statut_id'+sep+'date_vente', 
				paniers_id +sep+ prix +sep+ poids+sep+statut_table['vendu']+sep+unix_time
			);
			// and update panier date, so that it goes to top of the list (order date DESC)
			updateTable(
				'paniers', 
				paniers_id, 
				'date', 
				unix_time
			);

			// hide/update relevent html table or form
			var $tr = $('table.data').find('tr[data-id="'+article_id+'"]')
			if( $tr.length ){
				//alert('yes tr!');
				$tr.hide(1000);
			}
			if( $('div#formsContainer').length ){
				//alert('yes formsContainer!');
				$('div#formsContainer').hide();
				$('div#adminContainer').append('<a class="button" href="/admin/">&lt; Retour</a>');
			}

			// we need to give time to the 2 updateTable ajax calls above to happen
			t2 = setTimeout( function(){
				updatePaniersModal();
			}, 500);
		}
	}
});


// 3. button #newArticleDirectVenteSubmit
$('body').on('click', 'button#newArticleDirectVenteSubmit', function(e){
	e.preventDefault();
	error = false;
	var $form = $(this).parents('form');
	
	// make sure all required fields have a value
	$required = $form.find('input, select, textarea').filter('[required]');
	$required.each(function( index ) {
		if( !$(this).val().length ){
			error = true;
			alert( 'Il reste un champ à remplir: '+$(this).attr('name') );
			$(this).focus();
			return false; // break the loop
		}
	});
	
	if(error){
		return false; // stop the execution of the function
	}

	var fields = $form.serializeArray();
	// debug
	//console.log( fields );

	// these need to be declared here because declaring them within jQuery object does not work
	var poids, prix, nom, paiement_id, vrac;

	// now we can update their values through jQuery.each
	jQuery.each(fields, function( i, field ){
		if(field.name == 'poids'){
			poids = field.value;
		}
		if(field.name == 'prix'){
			prix = field.value;
		}
		if(field.name == 'titre'){
			// debug
			//alert('field.name = titre');
			nom = field.value;
		}
		if(field.name == 'paiement_id'){
			paiement_id = field.value;
		}
		if(field.name == 'vrac'){
			vrac = field.value;
		}
	});
	// if checkbox was not checked, there's no field['paiement_id'] so paiement_id is undefined
	if(typeof paiement_id === "undefined"){
		paiement_id = 1;
	}

	// create panier. fields (first param) will pass the info needed to create the article once the panier has been created. (If the article exsisted already the value of 'fields' would be the article id)
	create_panier(fields, nom, poids, prix, paiement_id, vrac, statut_table['vendu']);

});


// 4. button #newArticleAjoutPanierSubmit
$('body').on('click', 'button#newArticleAjoutPanierSubmit', function(e){
	e.preventDefault();
	error = false;
	var $form = $(this).parents('form');
	
	// make sure all required fields have a value
	$required = $form.find('input, select, textarea').filter('[required]');
	$required.each(function( index ) {
		if( !$(this).val().length ){
			error = true;
			alert( 'Il reste un champ à remplir: '+$(this).attr('name') );
			$(this).focus();
			return false; // break the loop
		}
	});
	
	if(error){
		return false; // stop the execution of the function
	}
	
	var fields = $form.serializeArray();
	// debug
	//console.log( fields );

	// these need to be declared here because declaring them within jQuery.each loop does not work
	var paniers_id, nom, poids, prix, paiement_id, vrac;

	// now we can update their values through jQuery.each
	jQuery.each(fields, function( i, field ){
		if(field.name == 'paniers_id'){
			paniers_id = field.value;
		}
		if(field.name == 'panierNom'){
			nom = field.value;
			nom = nom.replace(/("|')/g, '');
		}
		if(field.name == 'poids'){
			poids = field.value;
		}
		if(field.name == 'prix'){
			prix = field.value;
		}
		if(field.name == 'paiement_id'){
			paiement_id = field.value;
		}
		if(field.name == 'vrac'){
			vrac = field.value;
		}
	});
	// if checkbox was not checked, there's no field['paiement_id'] so paiement_id is undefined
	if(typeof paiement_id === "undefined"){
		paiement_id = 1;
	}
	
	// are we creating a new article to put it in a selected panier, or are we also creating a new panier?
	if(nom == '' && paniers_id != ''){ // we've selected an existing paniers_id
		// so let's just create the article, paniers_id is in fields
		create_article(fields);

	}else if( (paniers_id == '' || paniers_id == 'undefined') && nom != ''){ //we're creating a new panier 
		// let's create the new panier, and pass the new article fields as 1st param so that once the panier is created, the article is also created
		create_panier(fields, nom, poids, prix, paiement_id, vrac, statut_table['disponible']);
	}

});

/*********** ARTICLE VENTE (ADD TO PANIER) behaviors END **************/





/***** behavior targets/calls *****************************************/

// show/hide moreOptions statutActions in paniersModal
$('body').on('click', 'div.moreOptions', function(){
	var $ul = $(this).parent().find('ul.statutActions');
	if( $ul.is(":visible") ){
		$ul.hide();
	}else{
		$ul.show();
	}
});
// hide statutActions on mouse leave in paniersModal
$('body').on('mouseleave', 'ul.statutActions', function(){
	$(this).hide();
});

// disable .disabled links
$('body').on('click', 'a.disabled', function(e){
	e.preventDefault();
	//alert('Ce bouton ne peut pas encore être cliqué parcequ\'il manque des informations au formulaire');
	return false;
});

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

// updates article DB champ when select is changed within table.data
$("table.data").on('change', 'select.ajax', function(){
	var $table = $(this).parents('table');
	var table = $table.data('id'); // 'articles'
	var id = $(this).parents('tr').data('id'); // '167'
	var value = $(this).val();
	var col = $(this).attr('name'); // 'statut_id'
	
	updateTable(table, id, col, value);
});

// handles vente via 'vendre' button within table.data
$("table.data").on('click', 'a.vendre', function(e){
	e.preventDefault();
	var id = $(this).parents('tr').data('id'); // '167'
	var prix = $(this).parents('tr').find('td.prix').html();
	showModal('prixVenteModal?article_id='+id+'&prix='+encodeURIComponent(prix));
});


/* format number inputs (on blur) to currency or weight depending on their class name */
$('body').on('blur', 'input.currency, input.weight', function(){
	var v = parseFloat( $(this).val() );
	if( $(this).hasClass('currency') ){
		$(this).val( v.toFixed(2) ); // 0,00 $
	}else if( $(this).hasClass('weight') ){
		$(this).val( v.toFixed(3) ); // 0,000 kg
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


// categories and matieres select inputs on change must repopulate next select input in form with children correseponding sub-categories/matieres:
$("select[name='categories_id'], select[name='matieres_id']").on('change', function(){
	var id_parent = $(this).val();
	var select_input = $(this).attr("name");
	var sous_table = 'sous_'+select_input;
	// $target: the next select input to be populated depending on the selected option. Make sure it belongs to the same form, hence going through the DOM parents(form)...
	var $target = $(this).closest('form').find($("select[name='"+sous_table+"']"));
	// if select name is 'categories_id', we want to look into 'categories' SQL table, if it is 'matieres_id', we want to look into 'matieres' table...
	var table = select_input.replace("_id",'');
	// call to _code/js/js.js: function get_children(), that will request via ajax call to _code/php/admin/admin_ajax.php?get_children the children of id_parent in table, and insert them as html <option> markup to $target
	get_children($target, table, id_parent);
});
// jump to next select from sous_categories selected 
$("select[name='sous_categories_id']").on('change', function(){
	$(this).closest('form').find("select[name='matieres_id']").focus();
});
// jump to next input from sous_matieres selected 
$("select[name='sous_matieres_id']").on('change', function(){
	$(this).closest('form').find("input[name='poids']").focus();
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


/* vente end of form user behavior */
// show/hide last steps of form, depending on choice between 'vendre directement' et 'ajouter au panier'
$('body').on('click', 'a#ajoutPanier', function(e){
	e.preventDefault();
	$('div#paniers select, div#paniers input').prop('disabled', false);
	$('input#prixVente').prop('required', false);
	$('div#direct').hide();
	$('div#paniers').show();
	$(this).removeClass('discarded').addClass('selected');
	if(!paniers){
		$('input#panierNom').focus();
	}else{
		// debug
		//alert('should focus on select');
		$('select#paniers').focus();
	}
	$('a#directVente').removeClass('selected').addClass('discarded');
});
$('body').on('click', 'a#directVente', function(e){
	e.preventDefault();
	$('div#paniers select, div#paniers input').prop('disabled', true);
	$('div#paniers').hide();
	$('div#direct').show();
	$('input#prixVente').prop('required', true);
	$('input#prixVente').focus();
	$(this).removeClass('discarded').addClass('selected');
	$('a#ajoutPanier').removeClass('selected').addClass('discarded');
});

// enable disabled submit buttons at end of each form : directeVenteSubmit, and ajoutPanierSubmit
$('body').on('keyup', 'input#prixVente', function(){
	if( $(this).val() !== '' ){
		$('button#directeVenteSubmit').prop('disabled', false);
		$('button#newArticleDirectVenteSubmit').prop('disabled', false);
		
	}else{
		$('button#directeVenteSubmit').prop('disabled', true);
		$('button#newArticleDirectVenteSubmit').prop('disabled', true);
	}
}).on('change', 'input#prixVente', function(){
	if( $(this).val() !== '' ){
		$('button#directeVenteSubmit').prop('disabled', false).focus();
		$('button#newArticleDirectVenteSubmit').prop('disabled', false).focus();
	}else{
		$('button#directeVenteSubmit').prop('disabled', true);
		$('button#newArticleDirectVenteSubmit').prop('disabled', true);
	}
});

$('body').on('keyup', 'input#panierNom', function(){
	if( $(this).val().length ){
		$('button#ajoutPanierSubmit').prop('disabled', false);
		$('button#newArticleAjoutPanierSubmit').prop('disabled', false);
	}else{
		$('button#ajoutPanierSubmit').prop('disabled', true);
		$('button#newArticleAjoutPanierSubmit').prop('disabled', true);
	}
});
$('body').on('change', 'select#paniers', function(){
	if( $(this).val().length ){
		$('input#panierNom').val('');
		$('p#pPN').hide();
		$('button#ajoutPanierSubmit').prop('disabled', false);
		$('button#newArticleAjoutPanierSubmit').prop('disabled', false);
	}else{
		$('p#pPN').show();
		$('input#panierNom').focus();
		$('button#ajoutPanierSubmit').prop('disabled', true);
		$('button#newArticleAjoutPanierSubmit').prop('disabled', true);
	}
});
$('body').on('change', 'input#panierNom', function(){
	if( $(this).val().length ){
		$('select#paniers').val('');
		$('button#ajoutPanierSubmit').prop('disabled', false);
		$('button#newArticleAjoutPanierSubmit').prop('disabled', false);
	}else{
		$('button#ajoutPanierSubmit').prop('disabled', true);
		$('button#newArticleAjoutPanierSubmit').prop('disabled', true);
	}
});

$('.showPaniers').on('click', function(){
	$('div#paniersContainer').show();
	// memory for showing or hiding paniers modal from page to page
	document.cookie = "paniersModalDisplay=block; path=/";
});

$('a.warning').on('click', function(){
	alert( $(this).attr('title') );
});

/********** UPLOAD BEHAVIORS START ************/

// #chooseFileLink onclick triggers #fileUpload click
$('body').on('click', '#chooseFileLink', function(){
	$('input#fileUpload').trigger('click');
	return false;
});

// #fileUpload click validates file size and extension, then triggers #uploadFileSubmit click
$('body').on("change", '#fileUpload', function(){
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
			window.location = url+'?upload_result='+encodeURIComponent(msg);
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

/********** UPLOAD BEHAVIORS END ************/
