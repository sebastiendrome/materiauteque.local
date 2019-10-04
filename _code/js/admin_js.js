
var sep = 'qQq'; // separator to concatenate many cols/vals querys that will be retreived  by php as array
var wW = $(window).width();
var wH = $(window).height();

// js equivalent to php time(), will be used throughout functions
var unix_time = Math.round((new Date()).getTime()/1000);


/***** functions *****************************************************/

// save sql data ( ajax call, uses php function update_table() ) 
/*  !!!!! allows only one update per call - should pass arrays in col(s) and value(s)? */
function updateTable(table, id, col, value){
	$.ajax({
		// Server script to process the upload
		url: '/_code/php/admin/admin_ajax.php?updateTable&table='+table+'&id='+id+'&col='+col+'&value='+value,
		type: 'GET',
		// on success show message
		success : function(msg) {
			var er = msg.substr(0, 2);
			if(er == '0|'){
				msg = '<p class="error">'+msg.substr(2)+'</p>';
			}else if(er == '1|'){
				msg = '<p class="success">'+msg.substr(2)+'</p>';
			}else if(er == '2|'){
				msg = '<p class="note">'+msg.substr(2)+'</p>';
			}
			$('#done').html(msg);
			return true;
			//return msg;
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

// update paniers modal
function updatePaniersModal(){
	$.ajax({
		// Server script to process the upload
		url: '/_code/php/admin/admin_ajax.php?updatePaniersModal',
		type: 'GET',
		// on success show message
		success : function(msg) {
			$('#paniersContainer #panierAjaxTarget').html(msg);
			return true;
		}
	});
}

// ajax call to create panier (and create article, OR update article)
function create_panier(article_id_or_fields, nom, poids, prix, paiement_id, panier_statut_id){
	// ajax call that will use db_function create_panier()
	$.ajax({
		// Server script to process the upload 
		url: '/_code/php/admin/admin_ajax.php?create_panier&nom='+nom+'&poids='+poids+'&prix='+prix+'&paiement_id='+paiement_id+'&statut='+panier_statut_id,
		type: 'GET',

		// on success; msg is either new panier ID, or an error message
		success : function(msg) {

			var message;
			// see if message is error (0|) ou note (2|)
			var er = msg.substr(0,2);
			if(er == '0|'){
				message = '<p class="error">'+msg.substr(2)+'</p>';
			}else if(er == '2|'){
				message = '<p class="note">'+msg.substr(2)+'</p>';
			
			}else{
				// success, 
				// If article_id_or_fields is article id, update article.
				// Else, we're creating a panier + an article at the same time, and the article has not been created yet, so article_id_or_fields is a js object containing the fields needed for the creation of the article
				
				var panier_id = msg;
				//alert(panier_id);

				if( typeof article_id_or_fields !== 'object' ){
					var message = '<p class="success">Article updated, panier crée ID:'+panier_id+'</p>';
					var article_statut_id = statut_table['vendu'];
					// update article
					updateTable(
						'articles', 
						article_id_or_fields, 
						'panier_id'+sep+'prix'+sep+'poids'+sep+'statut_id'+sep+'date_vente', 
						panier_id +sep+ prix +sep+ poids+sep+article_statut_id+sep+unix_time
					);
					
					// hide article in table (if within admin/index.php)
					var $tr = $('table.data').find('tr[data-id="'+article_id_or_fields+'"]');
					if($tr){$tr.hide(1000);}
					// or hide div#formsContainer (if within scinderArticle.php)
					var $formsContainer = $('body div#formsContainer');

					// debug
					//alert($formsContainer.attr('id'));

					if($formsContainer.length){
						$formsContainer.hide();
						$('div#adminContainer').append('<a class="button" href="/admin/">&lt; Retour</a>'+message);
					}

					message = '<p class="success">Vente enregistrée, Panier créé; ID:'+msg+'</p>';

					$('#done').html(message);

					// update panierModal
					if( panier_statut_id == 0 ){ // show updated paniers only if panier was not directly sold
						setTimeout(function(){
							updatePaniersModal();
							$('div#paniersTarget').show();
						}, 1000);
					}

				}else{
					// update panier_id field, if already in 
					var panier_in_fields = false;
					var panier_statut_in_fields = false;
					jQuery.each(article_id_or_fields, function( i, field ){
						if(field.name == 'panier_id'){
							field.value = panier_id;
							panier_in_fields = true;
						}
						if(field.name == 'panier_statut_id'){
							field.value = panier_statut_id;
							panier_statut_in_fields = true;
						}
					});
					// if not already there, add panier_id to article_id_or_fields object
					if(!panier_in_fields){
						article_id_or_fields[article_id_or_fields.length] = {name:"panier_id", value:panier_id};
					}
					// we'll use panier_statut_id to determine if we need to update and show paniersModal
					if(!panier_statut_in_fields){
						article_id_or_fields[article_id_or_fields.length] = {name:"panier_statut_id", value:panier_statut_id};
					}
					create_article(article_id_or_fields);
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

// create article
function create_article(fields){
	var query_string = '';
	//var updatePaniers = false;
	var updatePaniers = true;

	// debug
	 console.log(fields);

	// let's build our query string for admin_ajax.php?create_article
	jQuery.each(fields, function( i, field ){
		// let's omit the panier_statut_id from query, but let's also find out if we need to update paniersModal after article is created
		if( field.name == 'panier_statut_id' ){
			if(field.value == 1){
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
			var error = false;
			var message;
			// see if message is success (1|) ou error (0|)
			var er = msg.substr(0,2);
			if(er == '0|'){
				message = '<p class="error">'+msg.substr(2)+'</p>';
				error = true;
			
			// success, create panier (once created, it will update the article panier_id with panier new id)
			}else{
				message = '<p class="success">Article créé ID:'+msg+'</p>';
				
				// if we're in ventes.php, no need to updatePaniers, instead let's show meaningful message and scroll to top of page
				if(basename(window.location.href) == 'ventes.php'){
					//updatePaniers = false;
					window.scrollTo(0, 0);
					$('div#adminContainer div#msg').remove();
					$('div#adminContainer').prepend('<div id="msg"><p class="success">Article vendu, ID:'+msg+' <a href="javascript:;" class="closeBut">&times;</a></p></div>');
				}
				if(updatePaniers){
					// debug
					//alert('HERE!');

					setTimeout(function(){
						updatePaniersModal();
						$('div#paniersTarget').show();
					}, 1000);
				}
			}
			// show message
			$('#done').html(message);

			// reset the form
			if(!error){
				$('form#newArticle').trigger("reset");
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

// ajax call (that will use db_function create_vrac_article() to duplicate article vendu)
function duplicate_vrac_article(id, old_poids, old_prix){
	$.ajax({
		// Server script to process the upload 
		url: '/_code/php/admin/admin_ajax.php?vrac_vente&original_id='+id+'&old_poids='+old_poids+'&old_prix='+old_prix,
		type: 'GET',

		// on success
		success : function(msg) {
			// see if message is success (1|) ou error (0|)
			var er = msg.substr(0,2);
			if(er == '0|'){
				msg = '<p class="error">'+msg.substr(2)+'</p>';
			}else if(er == '2|'){
				msg = '<p class="note">'+msg.substr(2)+'</p>';
			}
			$('#done').html(msg);
		},

		// Custom XMLHttpRequest
		xhr: function() {
			var myXhr = $.ajaxSettings.xhr();
			//alert(myXhr);
			return myXhr;
		}
	});
}


/***** behavior targets/calls *****************************************/

// disable .disabled links
$('body').on('click', 'a.disabled', function(e){
	e.preventDefault();
	alert('Ce bouton ne peut pas encore être cliqué parcequ\'il manque des informations au formulaire');
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

// show/hide moreOptions statutActions in paniersModal !!!!!!!!! other actions not implemented yet
$('body').on('click', 'div.moreOptions', function(){
	var $ul = $(this).parent().find('ul.statutActions');
	if( $ul.is(":visible") ){
		$ul.hide();
	}else{
		$ul.show();
	}
})
// hide statutActions on mouse leave in paniersModal
$('body').on('mouseleave', 'ul.statutActions', function(){
	$(this).hide();
})

/* VENTE DE PANIER */
$('body').on('click', 'a.button.ventePanierSubmit', function(e){
	e.preventDefault();
	if( $(this).hasClass('disabled') ){
		alert('Merci de remplir le champ "Prix"');
		return false;
	}
	var $container = $(this).parent();
	var id = $container.attr('data-panierid');
	var total = $container.find('input#prixVentePanier').val();
	var $paiement_cheque = $container.find('input[name="paiement_id"]');
	if($paiement_cheque.prop('checked') == true){
		var paiement_id = paiement_table['chèque'];
	}else{
		var paiement_id = paiement_table['espèces'];
	}
	var poids = $container.attr('data-poids');
	var statut = 1;

	updateTable(
		'paniers', 
		id, 
		'total'+sep+'paiement_id'+sep+'statut'+sep+'date_vente'+sep+'poids', 
		 total +sep+ paiement_id +sep+ statut +sep+ unix_time + sep+ poids
	);
	
	// we want to retreive the result of updateTable above, which wraps an asynchronous call. When it's done, the function sets the html of div#done. So we can check for that and know the result, but let's wait a half second
	setTimeout(function(){
		var result = $('#done').html();
		//alert(result);
		if( result.substr(0,15) !== '<p class="error' ){ // no error message
			$container.animate({'height':'30px'}, 500, function(){
				$(this).replaceWith('<div class="success" style="padding-right:35px; margin-top:5px;"><a href="javascript:;" class="closeBut">&times;</a>Panier vendu</div>');
			});
		}
	}, 300);

});

/** when article is sold via prixVenteModal.php */ 
// 1. button #directeVenteSubmit
$("body").on('click', 'button#directeVenteSubmit', function(e){
	e.preventDefault();
	var $form = $(this).parents('form');
	var id = $form.find('input[name="id"]').val(); // article[id]
	var $paiement_cheque = $form.find('input[name="paiement_id"]');
	if($paiement_cheque.prop('checked') == true){
		var paiement_id = paiement_table['chèque'];
	}else{
		var paiement_id = paiement_table['espèces'];
	}
	var prix = $form.find('input[name="prix"]').val();
	prix = prix.replace(',','.');
	var poids = $form.find('input[name="poids"]').val();
	poids = poids.replace(',','.');

	/* these vars will be used for vrac, to duplicate item with pre-sale prix and poids */ 
	var vrac = $form.find('input[name="vrac"]').val();
	var old_prix = $form.find('input[name="old_prix"]').val();
	var old_poids = $form.find('input[name="old_poids"]').val();
	old_poids = old_poids.replace(',','.');
	var new_poids = parseFloat(old_poids)-parseFloat(poids);
	var nom = $form.find('input[name="titre"]').val();

	// if vrac, duplicate article vendu ( update original minus poids vente? ...)
	if(vrac == 1/* && new_poids > 0*/){
		// ajax call (that will use db_function create_vrac_article() to duplicate article vendu)
		duplicate_vrac_article(id, old_poids, old_prix);
	}

	// hide modal
	hideModal($(this).parents('div.modal'));

	/* 
	CHANGES START NOW 
	up to here, directeVenteSubmit (this) and ajoutPanierSubmit (below) are strictly the same 
	*/

	// create panier
	// ajax call that will use db_function create_panier()
	create_panier(id, nom, poids, prix, paiement_id, 1);
	
});

// 2. button #ajoutPanierSubmit
$("body").on('click', 'button#ajoutPanierSubmit', function(e){
	e.preventDefault();
	var $form = $(this).parents('form');
	var id = $form.find('input[name="id"]').val(); // article[id]
	var $paiement_cheque = $form.find('input[name="paiement_id"]');
	if($paiement_cheque.prop('checked') == true){
		var paiement_id = paiement_table['chèque'];
	}else{
		var paiement_id = paiement_table['espèces'];
	}
	var prix = $form.find('input[name="prix"]').val();
	prix = prix.replace(',','.');
	var poids = $form.find('input[name="poids"]').val();
	poids = poids.replace(',','.');

	/* these vars will be used for vrac, to duplicate item with pre-sale prix and poids */ 
	var vrac = $form.find('input[name="vrac"]').val();
	var old_prix = $form.find('input[name="old_prix"]').val();
	var old_poids = $form.find('input[name="old_poids"]').val();
	old_poids = old_poids.replace(',','.');
	var new_poids = parseFloat(old_poids)-parseFloat(poids);
	var nom = $form.find('input[name="titre"]').val();

	// if vrac, duplicate article vendu, ( update original minus poids vente? ...)
	if(vrac == 1/* && new_poids > 0*/){
		duplicate_vrac_article(id, old_poids, old_prix);
	}

	// hide modal
	hideModal($(this).parents('div.modal'));

	/* 
	CHANGES START NOW 
	up to here, ajoutPanierSubmit (this) and directeVenteSubmit (above) are strictly the same 
	*/

	var article_statut_id = statut_table['vendu'];

	/**  are we creating a new panier, or using a 'panier en cours' ? **/
	// If 'nouveau panier' option was selected, we can get new panier nom from panierNom field
	var $panierNomInput = $form.find('input[name="panierNom"]');
	if( $panierNomInput.val().length ){ // we are creating a new panier
		var createPanier = true;
		var nom = $panierNomInput.val();
	
	// else, we are using a 'panier en cours', don't need its name but its ID
	}else{
		var createPanier = false;
		var panier_id = $form.find('select[name="panier_id"]').val();
	}

	var panier_statut_id = 0;

	if(createPanier){
		create_panier(id, nom, poids, prix, paiement_id, panier_statut_id);

	// attribute panier_id to article and update article to sold
	}else{
		// update article
		updateTable(
			'articles', 
			id, 
			'panier_id'+sep+'prix'+sep+'poids'+sep+'statut_id'+sep+'date_vente', 
			 panier_id +sep+ prix +sep+ poids+sep+article_statut_id+sep+unix_time
		);
		// and update panier date, so that it goes on top in the list (order date DESC)
		updateTable('paniers', panier_id, 'date', unix_time);

		// hide div#formsContainer (if within scinderArticle.php?..&vendre)
		// debug
		//alert('HERE');
		var $formsContainer = $('body div#formsContainer');

		// debug
		//alert($formsContainer.attr('id'));

		if($formsContainer.length){
			$formsContainer.hide();
			$('div#adminContainer').append('<a class="button" href="/admin/">&lt; Retour</a>');
		}
		
		setTimeout(function(){
			updatePaniersModal();
			$('div#paniersTarget').show();
		}, 1000);
	}
});


// 3. button #newArticleDirectVenteSubmit
$("body").on('click', 'button#newArticleDirectVenteSubmit', function(e){
	e.preventDefault();
	error = false;
	var $form = $(this).parents('form');
	
	// make sure all required fields have a value
	$required = $form.find('input, select, textarea').filter('[required]');
	$required.each(function( index ) {
		if( !$(this).val().length ){
			error = true;
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
	var poids, prix, nom, paiement_id;

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
	});
	// if checkbox was not checked, there's no field['paiement_id'] so paiement_id is undefined
	if(typeof paiement_id === "undefined"){
		paiement_id = 1;
	}

	// create panier. fields (first param) will pass the info needed to create the article once the panier has been created. (If the article exsisted already the value of 'fields' would be the article id)
	create_panier(fields, nom, poids, prix, paiement_id, 1);

});


// 4. button #newArticleAjoutPanierSubmit
$("body").on('click', 'button#newArticleAjoutPanierSubmit', function(e){
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
	var panier_id, nom, poids, prix, paiement_id;

	// now we can update their values through jQuery.each
	jQuery.each(fields, function( i, field ){
		if(field.name == 'panier_id'){
			panier_id = field.value;
		}
		if(field.name == 'panierNom'){
			nom = field.value;
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
	});
	// if checkbox was not checked, there's no field['paiement_id'] so paiement_id is undefined
	if(typeof paiement_id === "undefined"){
		paiement_id = 1;
	}
	
	// are we creating a new article to put it in a selected panier, or are we also creating a new panier?
	if(nom == '' && panier_id != ''){ // we've selected an existing panier_id
		// so let's just create the article, panier_id is in fields
		create_article(fields);

	}else if( (panier_id == '' || panier_id == 'undefined') && nom != ''){ //we're creating a new panier 
		// let's create the new panier, and pass the new article fields as 1st param so that once the panier is created, the article is also created
		create_panier(fields, nom, poids, prix, paiement_id, 0);
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
	var $target = $(this).parents('form').find($("select[name='"+sous_table+"']"));
	// if select name is 'categories_id', we want to look into 'categories' SQL table, if it is 'matieres_id', we want to look into 'matieres' table...
	var table = select_input.replace("_id",'');
	// call to _code/js/js.js: function get_children(), that will request via ajax call to _code/php/admin/admin_ajax.php?get_children the children of id_parent in table, and insert them as html <option> markup to $target
	get_children($target, table, id_parent);
});
// jump to next select from sous_categories selected 
$("select[name='sous_categories_id']").on('change', function(){
	$("select[name='matieres_id']").focus();
});
// jump to next input from sous_matieres selected 
$("select[name='sous_matieres_id']").on('change', function(){
	$("input[name='poids']").focus();
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
$('body').on('click', 'a#newArticleAjoutPanier', function(e){
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
	$('a#newArticleDirectVente').removeClass('selected').addClass('discarded');
});
$('body').on('click', 'a#newArticleDirectVente', function(e){
	e.preventDefault();
	$('div#paniers select, div#paniers input').prop('disabled', true);
	$('div#paniers').hide();
	$('div#direct').show();
	$('input#prixVente').prop('required', true);
	$('input#prixVente').focus();
	$(this).removeClass('discarded').addClass('selected');
	$('a#newArticleAjoutPanier').removeClass('selected').addClass('discarded');
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
/* not used: a#aNP is an extra 'nouveau panier' button commented out in 'vente-panier.php'
$('body').on('click', 'a#aNP', function(e){
	e.preventDefault();
	$('select#paniers').val('');
	$('p#pPN').show();
	$('input#panierNom').focus();
	$('button#ajoutPanierSubmit').prop('disabled', true);
	$('button#newArticleAjoutPanierSubmit').prop('disabled', true);
});
*/
$('.showPaniers').on('click', function(){
	$('div#paniersTarget').show();
});

/* paniers */
$('body').on('keyup', 'input#prixVentePanier', function(){
	var $venteBut = $(this).parents('div.pCont').find('a.button.vente');
	if( $(this).val() !== '' ){
		$venteBut.removeClass('disabled');
	}else{
		$venteBut.addClass('disabled');
	}
});
$('body').on('change', 'input#prixVentePanier', function(){
	var $venteBut = $(this).parents('div.pCont').find('a.button.vente');
	if( $(this).val() !== '' ){
		$venteBut.removeClass('disabled');
		$venteBut.focus();
	}else{
		$venteBut.addClass('disabled');
	}
});

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

/* end upload behaviors */
