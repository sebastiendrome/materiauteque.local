<?php
require('../_code/php/first_include.php');
require(ROOT.'_code/php/admin/not_logged_in.php');
require(ROOT.'_code/php/admin/admin_functions.php');
require(ROOT.'_code/php/doctype.php');

echo '<!-- admin css -->
<link href="/_code/css/admincss.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css">'.PHP_EOL;

echo '<!-- start admin container -->
<div id="adminContainer">'.PHP_EOL;

echo '<h1>Admin</h1>'.PHP_EOL;

$csv_file = ROOT.'tests/ventes-utf8.csv';
$delimiter = ';';
$csv_array = csv_to_array($csv_file, $delimiter);
echo_table($csv_array);



/****** exemples
 * get categories
 * create article (form) + upload image
 * edit article (form)
 * update item (code)
 * update table (generic)
 * get item
 * get all items
 * get table (generic)
 * delete item (generic)
 * find item(s) (form)
 */


/* GET CATEGORIES */
$categories = get_categories();
//print_r($categories);
echo '<h3>Catégories</h3>'.PHP_EOL;
if($categories){
    echo_table($categories);
}else{
    echo '<p class="note">Pas de résultats...</p>'.PHP_EOL;
}

/* CREATE ARTICLE */
require(ROOT.'_code/php/forms/newArticle.php');


/* EDIT ARTICLE */
$article_id = 24;
require(ROOT.'_code/php/forms/editArticle.php');


/* FIND ITEM */
require(ROOT.'_code/php/forms/findArticle.php');



/* GET TABLE (generic) */
$table = 'ventes';
$data = get_table($table);
echo '<h3>'.ucwords($table).'</h3>'.PHP_EOL;
if($data){
    echo_table($data);
}else{
    echo '<p class="note">Pas de résultats...</p>'.PHP_EOL;
}


/* ADD ITEM */
/*
$item_data = array();
$item_data['vrac'] = '0';
$item_data['categories_id'] = 4;
$item_data['dechette_categories_id'] = 2;
$item_data['descriptif'] = 'this is a nice item';
$item_data['prix'] = 5.2;
$item_data['poids'] = 10.5;
$item_data['visible'] = 1;
$item_data['observations'] = '...Obs... ouais ouais';

$insert_article = insert_article($item_data);
*/



/* UPDATE ITEM */
/*
$article_id = 5;
$update =  array('vrac'=>0, 'prix'=>'0.78');
update_item($article_id, $update);
*/

/* UPDATE TABLE (generic) */
/*
$article_id = 12;
$update =  array('vrac'=>0, 'prix'=>6, 'descriptif'=>'Hello it\'s a great idea!', 'observations'=>"c'est un test de Seb.");
update_table('articles', $article_id, $update);
*/



/* GET ITEM */
$article_id = 24;
echo '<h3>Données de l\'article #'.$article_id.'</h3>'.PHP_EOL;
$item = get_item($article_id);
if(!empty($item)){
    echo_item_table($item);
}else{
    echo '<p class="note">L\'article #'.$article_id.' n\'existe pas...</p>'.PHP_EOL;
}


/* DELETE ITEM */
require(ROOT.'_code/php/forms/deleteArticle.php');


/* GET ITEMS */
//$items = get_items_data(0, 1, 'prix DESC'); // visible['all'|0|1], categories_id['all'|n], ORDER BY 
$items = get_items_data();
echo '<h3>Tous les Articles ('.count($items).')</h3>'.PHP_EOL;
if( $items ){
    echo_table($items);
}else{
    echo '<p class="note">Aucun article trouvé...</p>'.PHP_EOL;
}





echo '</div><!-- end admin container -->'.PHP_EOL;


require(ROOT.'/_code/php/admin/admin_footer.php');

echo '
	</body>
	</html>';
