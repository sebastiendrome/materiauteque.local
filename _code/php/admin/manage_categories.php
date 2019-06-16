<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
	require(ROOT.'_code/php/admin/not_logged_in.php');
	require(ROOT.'_code/php/admin/admin_functions.php');
}

// make sure we get the needed data, if we don't have it already
if( !isset($categories) || empty($categories) ){
	$categories = get_table('categories');
}

$message='';


// UPDATE CATEGORIES
if(isset($_POST['update'])){
	unset($_POST['update']);
	
	foreach($_POST as $k=>$v){
		// checkboxes
		if(!isset($v['avail_ecommerce'])){
			$v['avail_ecommerce'] = 0;
		}
		if(!isset($v['avail_wholesale'])){
			$v['avail_wholesale'] = 0;
		}
		if(!isset($v['avail_distributor'])){
			$v['avail_distributor'] = 0;
		}
		if(!isset($v['avail_distributor_UK'])){
			$v['avail_distributor_UK'] = 0;
		}
		if(!isset($v['avail_ecommerce'])){
			$v['avail_ecommerce'] = 0;
		}
		$_POST[$k] = $v;
	}
	
	//print_r($_POST); exit;
	$skip = array();
	
	foreach($_POST as $k=>$v){
		
		$dir_name = str_replace(' ','-',$v['old_name']);
		// delete (remove) category dir
		if( $v['is_active'] == '0' && !empty($dir_name) ){
			rmdirr(ROOT.DYNO.'shop/'.$dir_name);
		}else{
		// create (activate) dir 
			if(!is_dir(ROOT.DYNO.'shop/'.$dir_name)){
				if(mkdir(ROOT.DYNO.'shop/'.$dir_name)){
					if($fp = fopen(ROOT.DYNO.'shop/'.$dir_name.'/index.php','w')){
						fwrite($fp,'<?php
require($_SERVER[\'DOCUMENT_ROOT\'].\'/inc/first_include.php\');
require(ROOT.DYNO.INCLUDES.\'index_categories.php\');
?>');
						fclose($fp);
					}
				}
			}else{
				$message .= '0|A directory named: <b>'.$dir_name.'</b> already exists!<br>
				The category was NOT created.';
			}
		}
		
		// rename category dir (if status is not removed)
		if($v['name'] !== $v['old_name']){
			// clean name
			$v['name'] = cleanXXS($v['name']);
			// if valid, rename dir
			if(!preg_match('/(:|,|;|\/|\||\\|&|#|\+|®|™)/', $v['name'])){
				if(rename(ROOT.DYNO.'shop/'.str_replace(' ','-',$v['old_name']),ROOT.DYNO.'shop/'.str_replace(' ','-',$v['name']))){
					$message .= '1|'.str_replace(' ','-',$v['old_name']).' has been renamed to '.str_replace(' ','-',$v['name']);
				}else{
					$skip['name'] = $v['name'];
					$message .= '0|<b>'.str_replace(' ','-',$v['old_name']).'</b> could not be renamed to '.str_replace(' ','-',$v['name']);
				}
			// else, echo error message
			}else{
				$skip['name'] = $v['name'];
				$message .= '0|One or more categories could not be renamed, because they contain forbidden characters: <span style="color:#000;font-weight:normal;"> / \ | + , ; : & ® ™</span>';
				
			}
			
		}
		
		// update database
		foreach($v as $key=>$value){
			if($key !== 'old_name'){
				if(!isset($skip[$key][$value])){
					if($value !== ''){
						$value = filter($value);
						$query = mysqli_query( $db,"UPDATE product_type SET $key = '$value' WHERE id = $k") or die(mysqli_error($db));
						$database_message = '1|The database has been updated.';
					}else{
						$message .= '0|<b>'.$key.'</b> cannot be blank!';
					}
				}
			}
		}
		if(!isset($database_message)){
			$database_message = '0|The database has not been updated.';
		}
	}
}



//print_r($categories);//exit;
$c_count = count($categories);
//echo $c_count;


// CREATE CATEGORY
if(isset($_POST['create']) && !empty($_POST['newSection'])){
	$error = false;
	
	$newSection = trim($_POST['newSection']);
	if(preg_match('/(:|,|;|\/|\||\\|&|#|\+|®|™)/',$newSection, $matches)){ // check section format
		$error = true;
		$m = implode(',',$matches);
		$message .= '0|Section name contains forbidden characters: '.$m;
	}
	foreach($categories as $se){ // avoid overwritting existing section
		if($newSection == id_to_name($se,'product_type')){
			$error = true;
			$message .= '0|A section named <strong>'.$newSection.'</strong> already exists!';
		}
	}
	
	if($error == false){
		if(create_category($newSection,$c_count+1)){
			$message = '1|The new category <b>'.$newSection.'</b> has been created.';
			unset($categories);
			$categories = get_categories_admin();
			$c_count = count($categories);
		}else{
			$message = '0|ERROR - The new category <b>'.$newSection.'</b> could not be created.';
		}
	}
}


?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>MANAGE SECTIONS</title>
<link href="/scripts/css/css.css?v=1" rel="stylesheet" type="text/css">
<link href="/admin/admin.css" rel="stylesheet" type="text/css">


</head>

<body>
<div id="tools">
<a href="main.php">ADMIN MAIN</a>
<a href="main.php?logout" class="logout">logout</a>
</div>



<div style="padding:10px;">


<?php echo $message; if(isset($database_message)){echo $database_message;}; ?>

<h1>Categories</h1>

<form action="" name="updateNamesForm" method="post" class="inlineBlock">
<h3 style="margin-bottom:0;">Update:</h3>
<table cellspacing=1 cellpadding="4">
<tr>
<td colspan="3"></td>
<td colspan="4"><img src="/images/arrowDown.png"> <strong>Availability</strong></td>
</tr>

<tr>
<td><img src="/images/arrowDown.png"> <strong>Name</strong></td>
<td><img src="/images/arrowDown.png"> <strong>position</strong></td>
<td><img src="/images/arrowDown.png"> <strong>status</strong></td>
<td style="padding-left:10px; font-size:11px;">ecommerce</td>
<td style="padding-left:10px; font-size:11px;">wholesale</td>
<td style="padding-left:10px; font-size:11px;">distributor</td>
<td style="padding-left:10px; font-size:11px;">distrib. UK</td></tr>
<?php
foreach($categories as $cat){
	
	//print_r($cat).'<br>';
	$c = get_category_data($cat);
	//print_r($c).'<br>';
	
	echo '<tr>
	<td>
	<input name="'.$cat.'[name]" type="text" value="'.str_replace('-',' ',$c['name']).'">
	<input name="'.$cat.'[old_name]" type="hidden" value="'.str_replace('-',' ',$c['name']).'">
	</td> 
	<td><input name="'.$cat.'[position]" type="text" value="'.$c['position'].'" size="2"></td> 
	
	<td class="status'.$c['is_active'].'" style="vertical-align:middle"><select name="'.$cat.'[is_active]">
	<option value="1"';if($c['is_active']=='1'){echo' selected';}echo'>active</option>
	<option value="0"';if($c['is_active']=='0'){echo' selected';}echo'>removed</option>
	</select></td>
		
	<td style="vertical-align:middle; text-align:center;"><input name="'.$cat.'[avail_ecommerce]" type="checkbox" value="1"';if($c['avail_ecommerce']=='1'){echo' checked';}echo'></td>
	
	<td style="vertical-align:middle; text-align:center;"><input name="'.$cat.'[avail_wholesale]" type="checkbox" value="1"';if($c['avail_wholesale']=='1'){echo' checked';}echo'></td>
	
	<td style="vertical-align:middle; text-align:center;"><input name="'.$cat.'[avail_distributor]" type="checkbox" value="1"';if($c['avail_distributor']=='1'){echo' checked';}echo'></td>
	
	<td style="vertical-align:middle; text-align:center;"><input name="'.$cat.'[avail_distributor_UK]" type="checkbox" value="1"';if($c['avail_distributor_UK']=='1'){echo' checked';}echo'></td>
	</tr>';
}
?>
</table>

<div class="clearBoth" style="padding:10px;"></div>

<button name="update" type="submit" style="float:right;">SAVE CHANGES</button>

</form>


<div class="clearBoth" style="padding:10px;"></div>


<form action="" name="createForm" method="post" class="inlineBlock">
<h3>Create new:</h3>
<strong>Name:</strong> <input name="newSection" type="text" value=""> 
<button name="create" type="submit">CREATE</button>
</form>



</div>


</body>
</html>
