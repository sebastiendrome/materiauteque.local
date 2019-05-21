<?php
if( !defined("ROOT") ){
	require($_SERVER['DOCUMENT_ROOT'].'/_code/php/first_include.php');
}

if( isset($_GET['path']) ){
	$path = urldecode($_GET['path']);
}
if(isset($_GET['img'])){
	$img = urldecode($_GET['img']);
}else{
	$img = 0;
}
if(!isset($path)){
	exit;
}
$images_array = get_article_images('', '_L', $path);
$inner_img_output = $img_nav = '';
$n = count($images_array);

$img_selected = $images_array[$img];
if( $n > 1){
	$img_nav .= '<div class="imgNav" style="background-color:#ccc; height:20px;">';
	for($i=0; $i<$n; $i++){
		if($i == 0){
			$extra = 'selected';
		}else{
			$extra = '';
		}
		$img_nav .= '<a href="/'.$images_array[$i].'" class="'.$extra.'" rel="imageGallery?path='.urlencode($path).'&img='.$i.'">•</a>';
	}
	$img_nav .= '</div>';
}
$inner_img_output = $img_nav.'<img src="'.$img_selected.'" style="display:block; width:100%; height:auto; max-width:100%;">';

	
?>

<div class="modal" style="padding:0;">
	<a href="javascript:;" class="closeBut">&times;</a>
<?php
echo $inner_img_output;
?>
</div>