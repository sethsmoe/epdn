<?php
/* change this shit */
use GDText\Box;
use GDText\Color;
header('content-type: image/jpeg');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
/* teamspeak likes cache, this fixes that */
$glob=glob('./pngs/*');/**/
$font='./font/font.ttf';
$fsize=100;
$fxa=0;/*text adjust x y*/
$fya=0;
$fst='[epdn]';
$bpad=30;/*padding for the black box on grey images*/
$by=100; /* banner height */
$pad=10; /* warning: processing time is o(n+n^2), it can get really slow */
$banner=false;
$avgdark=100;
$avglight=200;



/* grab random from dir and alloc */
$r=$glob[array_rand($glob)];
$png=@imagecreatefrompng($r);
list($pngx,$pngy)=getimagesize($r);
$r=NULL;unset($r);

/* add banner */
if($banner){
	$pngb=imagecrop($png,['x'=>0,'y'=>0,'width'=>$pngx,'height'=>$pngy+$by]);
	$pngy+=$by;
	$png=$pngb;
	imagedestroy($pngb); /*ualloc memory*/
}

/* create text mask */
/*$mask=@imagecreatetruecolor($pngx,$pngy);
imagettftext($mask,12,0,$fax,$fay,$fcolor*-1,$font,"[epdn] $pngy");
	UNNEEDED UNLESS BITMAP FONT
	(ITS BROKEN ANYWAY)
*/

/* calculate bounding box for $fst (actually draws it like a class). */
$b=imagettfbbox($fsize,0,$font,$fst);
$th=abs($b[1]-$b[7]);
$tw=abs($b[0]-$b[2]);
$xoff=($pngx-$tw)/2;
$yoff=($pngy-$th)/2;
/* change text colour based on center greying */
$center=imagecreatetruecolor($tw+($pad*2),$th+($pad*2));
imagecopy($center, $png, 0,0, $xoff+$fxa-($pad/1),$yoff+$fya-($pad/1), $tw+($pad*2),$th+($pad*2));
/* pad is like padding:64px; or whatever, it increases the area of average colour

/* get average colour behind text */
$r=$g=$b=0;
for($y=0; $y<($th+($pad*2)); $y++){
	for($x=0; $x<($tw+($pad*2)); $x++){
		$pix=imagecolorat($center, $x,$y);
		/* im sorry for the funky floating point shit, but its faster */
		$r+=$pix >> 16;
		$g+=$pix >> 8 & 255;
		$b+=$pix & 255;
	}
}
$pixels=($th+($pad*2))*($tw+($pad*2));
$r=round($r/$pixels);
$g=round($g/$pixels);
$b=round($b/$pixels);
$avg=round(($r+$g+$b)/3);


/* add [epdn] text */
$border=FALSE;
if($avg<$avgdark) {
	$fcolor=imagecolorallocate($png,255,255,255);
}elseif($avg<$avglight){
	$fcolor=imagecolorallocate($png,255,255,255);
	$border=TRUE;
}else{
	$fcolor=imagecolorallocate($png,0,0,0);
}
$black=imagecolorallocate($png,0,0,0);
$white=imagecolorallocate($png,255,255,255);


$bfixx=5;/* font adjust for bg */
$bfixy=0;
if($border){imagefilledrectangle($png,$bfixx+$xoff-$bpad,$bfixy+$yoff-$bpad,$bfixx+$xoff+$tw+$bpad,$bfixy+$yoff+$th+$bpad,$bcolor);}
imagettftext($png,$fsize,0,$xoff+$fxa,$yoff+$fya+100,$fcolor,$font,"[epdn]");

//$r=$g=$b=$x=$y=NULL;unset($r,$g,$b,$x,$y);


/* debug, dont touch, it looks nice. */
if($_GET['debug']) {
	$dlist = array(
		"pix:$pixels",
		"r:$r",
		"g:$g",
		"b:$b",
		"avg:$avg",
		"border:$border"
	);
	imagefilledrectangle($png, 0,0, 100,18+sizeof($dlist)*12, $black);
	for($a=0;$a<=sizeof($dlist);$a++) {
		imagettftext($png,9,0,0,9+($a*12),$white,'./font/debug.ttf',">".$dlist[$a]);
	}
}
function sendimg($img){
	imagepng($img);
	imagedestroy($img);
}
sendimg($png);


?>