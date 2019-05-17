<?php
echo '<a name="top"></a>';
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}
if( !isset($title) ){
    $title = ' : Nouvelle vente';
    require(ROOT.'_code/php/doctype.php');
    echo '<!-- admin css -->
    <link href="/_code/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">'.PHP_EOL;

    echo '<!-- adminHeader start -->
	<div class="adminHeader">
	<h1><a href="/admin/">Admin</a>'.$title.' </h1>'.PHP_EOL;
	echo '</div><!-- adminHeader end -->'.PHP_EOL;

    echo '<!-- start admin container -->
    <div id="adminContainer">'.PHP_EOL;

    echo '<div id="working">working...</div>
        <div id="done"></div>
        <div id="result"></div>';
    $footer = true;
}else{
    $footer = false;
}

?>


<?php
// make sure we get the needed data, if we don't have it already
if( !isset($categories) || empty($categories) ){
    $categories = get_table('categories');
}
if( !isset($dechette_categories) || empty($dechette_categories) ){
    $dechette_categories = get_table('dechette_categories');
}

// process form POST data (simple search)
if( isset($_POST['simpleSearch']) ){

    // check and clean up user input
    if( isset($_POST['keywords'])  && !empty($_POST['keywords']) ){
        $keywords = trim($_POST['keywords']);
        $keywords = normalize($keywords);
        $keywords = cleanXXS($keywords);
    }else{
        $keywords = '';
    }
    if( isset($_POST['categories_id']) && is_numeric($_POST['categories_id']) ){
        $categories_id = trim($_POST['categories_id']);
    }else{
        $categories_id = '';
    }
    if($keywords !== '' || $categories_id !== ''){
		$ids = search($keywords, $categories_id, /*visible-only=*/TRUE, /*vendus=*/FALSE);
		if( !empty($ids) ){
			foreach($ids as $id){
				//echo 'Article #'.$key.'<br>';
				$items[] = get_item($id);
			}
		}
    }
}else{
    $keywords = $categories_id = '';
}


// process form POST data (detailed search)
if( isset($_POST['findArticleSubmitted']) ){
    $key_val_pairs = array();
    foreach($_POST as $k => $v){
        if($k !== 'findArticleSubmitted' && $k !== 'findArticleSubmit' && $k !== 'types' && $k !== 'sizes' && $v !== '' && $k !== 'date' ){
            $key_val_pairs[$k] = trim($v);
        // for date, value is an array (date[start] and [end]), so don't trim. 
        // Also, make sure that not both are empty
        }elseif( $k == 'date' && ( !empty($v['start']) || !empty($v['end']) ) ){
            $key_val_pairs[$k] = $v;
        }
    }
    /*
    echo '<pre>'.__FILE__.PHP_EOL;
    print_r($key_val_pairs);
    echo '</pre>';
    */
    if( !empty($key_val_pairs) ){
        if( $results = find_articles($key_val_pairs) ){
            foreach($results as $key => $val){
                //echo 'Article #'.$key.'<br>';
                $items[] = get_item($key);
            }
        }
    }
}
?>

<?php
if( isset($items) && !empty($items)){
	echo '<p class="success" style="overflow:auto;">
	<span style="display:block; margin: 10px 0;">';
    if(isset($results)){
        $count = count($results);
        if($count>1){$s='s';}else{$s='';} // plural or singular
        echo '<b>'.$count.' article'.$s.' trouvé'.$s.'.</b> <a href="#recherche" class="button">Nouvelle recherche</a><br>
        Paramètres de recherche: ';
        foreach($key_val_pairs as $rk => $rv){
            if( is_array($rv) ){
                $string = '';
                foreach($rv as $rrk => $rrv){
					if(substr($rrk, -3) == '_id'){
						$table = substr($rrk, 0, -3);
						$rrv = id_to_name($rrv, $table);
					}
                    $string .= $rrk.': '.$rrv.'&nbsp;&nbsp;';
                }
                $rv = $string;
			}
			if(substr($rk, -3) == '_id'){
				$rk = substr($rk, 0, -3);
				$rv = id_to_name($rv, $rk);
			}
            echo $rk.' = '.$rv.'&nbsp;&nbsp;';
        }
    }else{
        $count = count($items);
        if($count>1){$s='s';}else{$s='';} // plural or singular
        echo '<b>'.$count.' article'.$s.' trouvé'.$s.'.</b><br>
        Paramètres de recherche: ';
		echo $keywords;
		if(!empty($categories_id)){
			echo '&nbsp;&nbsp;Catégorie: '.id_to_name($categories_id, 'categories');
		}
    }
	
	echo '</span>';

	$items_table = items_table_output($items);
	echo $items_table;

    echo '</p>';

}elseif(isset($_POST['findArticleSubmitted']) ){
    echo '<p class="note">Aucun article trouvé pour: ';
    foreach($key_val_pairs as $rk => $rv){
        if( is_array($rv) ){
            $string = '';
            foreach($rv as $rrk => $rrv){
                $string .= $rrk.':'.$rrv.'&nbsp;&nbsp;';
            }
            $rv = $string;
        }
        echo $rk.' = '.$rv.'<br>';
    }
    echo '</p>'.PHP_EOL;
        
}elseif( isset($_POST['simpleSearch']) ){
    echo '<p class="note">Aucun résultat...</p>'.PHP_EOL;
}

// process form POST data
if( isset($_POST['newArticleSubmitted']) ){
    foreach($_POST as $k => $v){
        if($k !== 'newArticleSubmitted' && $k !== 'newArticleSubmit' && $k !== 'types' && $k !== 'sizes'){
            $item_data[$k] = trim($v);
        }
    }
    if($article_id = insert_new('articles', $item_data)){
		$_SESSION['article_id'] = $article_id;
		$new_item[0] = get_item($article_id);
		$items_table = items_table_output($new_item);
        $message = '<p class="success">Nouvel Article créé. ID: '.$article_id.'</p>';
        $path = 'uploads/'.$article_id;
        
    }else{
        $message = '<p class="error">'.mysqli_error($db).'</p>';
    }
}

// if standalone, show result message passed via query string
if( isset($message) ){
    echo $message;
}

?>

<a name="recherche"></a>


<!-- recherche simple start -->
<!--
	<form name="search" class="searchForm" action="#top" method="post" style="display:inline-block; margin-top:20px;">
<h3>Recherche simple:</h3>
<input type="hidden" name="simpleSearch" value="simpleSearch">
<input type="text" name="keywords" value="<?php echo $keywords; ?>" placeholder="Que recherchez-vous?" style="background-image:none;"><select name="categories_id" style="min-width:auto;">
<option value="">Toutes catégories</option>
<?php 
foreach($categories as $c){
    echo '<option value="'.$c['id'].'"';
    if( $categories_id == $c['id'] ){
        echo ' selected';
    }
    echo '>'.$c['nom'].'</option>'.PHP_EOL;
}
?>
</select><button type="submit" name="searchSubmit">Rechercher</button>
</form>
-->
<!-- recherche simple end -->

<!--<div style="margin:10px;"></div>-->




<!-- recherche detail start -->
<form name="findArticle" id="findArticle" action="#top" method="post" style="display:inline-block; float:left; margin-right:20px;">

<?php
if( empty($key_val_pairs) && isset($_POST['findArticleSubmitted']) ){
    echo '<p class="note">Choisir au moins 1 paramètre de recherche...</p>'.PHP_EOL;
}
?>

<h3>Rechercher l'article à vendre:</h3>
<p class="below">Remplir au moins 1 des champs.</p>

    <table>
        
        <tr>
        <td>Vrac:<td><input type="radio" name="vrac" value="0"><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="vrac" value="1"><label for="1"> oui</label>
        <!--
        <tr style="display:none;">
        <td>etiquette:<td><input type="text" name="etiquette" value="">
        -->

        <tr>
            <td colspan="2">Créé entre le: <input type="text" name="date[start]" id="startDate" value="<?php if(isset($key_val_pairs['date']['start'])){echo $key_val_pairs['date']['start'];} ?>" style="min-width:75px; width:100px;" placeholder="25-12-1970"> et le: <input type="text" name="date[end]" id="endDate" value="<?php if(isset($key_val_pairs['date']['end'])){echo $key_val_pairs['date']['end'];} ?>" style="min-width:75px; width:100px;" placeholder="<?php echo date('d-m-Y'); ?>">

        <tr>
        <td>Catégorie:<td><select name="categories_id">
            <option value="">Toutes catégories</option>
            <?php
            foreach($categories as $cat){
                $sel = '';
                if(isset($key_val_pairs['categories_id']) && $key_val_pairs['categories_id'] == $cat['id']){
                    $sel = ' selected';
                }
                echo '<option value="'.$cat['id'].'"'.$sel.'>'.$cat['id'].' = '.$cat['nom'].'</option>';
            }
            ?>
        </select>
        
        <tr>
        <td>Déchet. Catégorie:<td><select name="dechette_categories_id">
            <option value="">Toutes catégories</option>
            <?php
            foreach($dechette_categories as $cat){
                $sel = '';
                if(isset($key_val_pairs['dechette_categories_id']) && $key_val_pairs['dechette_categories_id'] == $cat['id']){
                    $sel = ' selected';
                }
                echo '<option value="'.$cat['id'].'"'.$sel.'>'.$cat['id'].' = '.$cat['nom'].'</option>';
            }
            ?>
        </select>
        
        <tr>
        <td>Le Titre contient...<td><input type="text" name="titre" value="<?php if(isset($key_val_pairs['titre'])){echo $key_val_pairs['titre'];} ?>">

        <tr>
        <td>Le Descriptif contient...<td><textarea name="descriptif"><?php if(isset($key_val_pairs['descriptif'])){echo $key_val_pairs['descriptif'];} ?></textarea>
		
		<!--
        <tr>
        <td>Prix:<td><input type="number" name="prix" step="any" value="<?php if(isset($key_val_pairs['prix'])){echo $key_val_pairs['prix'];} ?>">
        
        <tr>
		<td>Poids (Kg):<td><input type="number" name="poids" step="any" value="<?php if(isset($key_val_pairs['poids'])){echo $key_val_pairs['poids'];} ?>">
		-->
        
        <tr>
        <td>Statut:<td><select name="statut_id">
        <option value="" selected>Tous statuts</option>
			<?php
			$statut_array = get_table('statut'); // get contents of statut table ('id, nom)
			$options = '';

			foreach($statut_array as $st){ // loop through statut_array to output the options
				if($st['id'] == $key_val_pairs['statut_id']){
					$selected = ' selected';
				}else{
					$selected = '';
				}
				$options .= '<option value="'.$st['id'].'"'.$selected.'>'.$st['nom'].'</option>';
			}
			echo $options;

			?>
        </select>
		
		<!--
        <tr>
        <td>Visible:<td><input type="radio" name="visible" value="0"<?php if(isset($key_val_pairs['visible']) && $key_val_pairs['visible']==0){echo ' checked';} ?>><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="visible" value="1"<?php if(isset($key_val_pairs['visible']) && $key_val_pairs['visible']==1){echo ' checked';} ?>><label for="1"> oui</label>
		</select>
		-->
        
        <tr>
        <td>Les Observations <br>contiennent...<td><textarea name="observations"><?php if(isset($key_val_pairs['observations'])){echo $key_val_pairs['observations'];} ?></textarea>

    
    </table>

    <input type="hidden" name="findArticleSubmitted" id="findArticleSubmitted" value="findArticleSubmitted">
    <a href="" class="button left">Réinitialiser</a>
    <button type="submit" name="findArticleSubmit" id="findArticleSubmit" class="right" >Rechercher</button>

</form>
<!-- recherche detail end -->






<!-- créer article à vendre start -->
<form name="newArticle" id="newArticle" action="" method="post" style="display:inline-block; float:left;">
Si l'article n'existe pas ou est introuvable...
<h3>Créer l'article à vendre:</h3>

    <table>

        
        <tr>
        <td>Vrac:<td><input type="radio" name="vrac" value="0" checked><label for="0"> non</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="vrac" value="1"><label for="1"> oui</label>
        <!--
        <tr>
        <td>etiquette:<td><input type="text" name="etiquette" value="">
        --> 
        <tr>
        <td>Catégorie:<td><select name="categories_id" required>
            <option value="">Choisir...</option>
            <?php
            foreach($categories as $cat){
                echo '<option value="'.$cat['id'].'">'.$cat['id'].' = '.$cat['nom'].'</option>';
            }
            ?>
        </select>
        
        <tr>
        <td>Déchet. Catégorie:<td><select name="dechette_categories_id" required>
            <option value="">Choisir...</option>
            <?php
            foreach($dechette_categories as $cat){
                echo '<option value="'.$cat['id'].'">'.$cat['id'].' = '.$cat['nom'].'</option>';
            }
            ?>
        </select>
        
        <tr>
        <td>Titre:<td><input type="text" name="titre" value="" required>

        <tr>
        <td>Descriptif:<td><textarea name="descriptif"></textarea>
        
        <tr>
        <td>Prix vente:<td><input type="number" name="prix_vente" step="any" value="" required>
        
        <tr>
        <td>Poids (Kg):<td><input type="number" name="poids" step="any" value="" required>
        
        <tr>
        <td>Observations:<td><textarea name="observations"></textarea>



    </table>
	
	<input type="hidden" name="statut_id" value="6">
	<input type="hidden" name="visible" value="0">
	<input type="hidden" name="date_vente" value="<?php echo time(); ?>">
    <input type="hidden" name="newArticleSubmitted" id="newArticleSubmitted" value="newArticleSubmitted">
    <button type="submit" name="newArticleSubmit" id="newArticleSubmit" class="right" >Enregistrer la vente</button>

</form>
<!-- créer qrticle à vendre end -->




<?php
if($footer){
    echo '</div><!-- end admin container -->'.PHP_EOL;
	require(ROOT.'/_code/php/admin/admin_footer.php');
	echo '
	</body>
	</html>';
}
?>