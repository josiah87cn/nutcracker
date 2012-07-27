<?php
/*
Nutcracker: RGB Effects Builder
Copyright (C) 2012  Sean Meighan
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once('../conf/auth.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Nutcracker: RGB Effects Builder</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="last-modified" content=" 24 Feb 2012 09:57:45 GMT"/>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8"/>
<meta name="robots" content="index,follow"/>
<meta name="googlebot" content="noarchive"/>
<link rel="shortcut icon" href="barberpole.ico" type="image/x-icon"/> 
<meta name="description" content="RGB Sequence builder for Vixen, Light-O-Rama and Light Show Pro"/>
<meta name="keywords" content="DIY Light animation, Christmas lights, RGB Sequence builder, Vixen, Light-O-Rama or Light Show Pro"/>
<link href="../css/loginmodule.css" rel="stylesheet" type="text/css" />
</head>
<body>
<h1>Welcome <?php echo $_SESSION['SESS_FIRST_NAME'];?></h1>
<?php $menu="effect-form"; require "../conf/menu.php"; ?>
<?php
//
require("read_file.php");
$username=$_SESSION['SESS_LOGIN'];
$member_id=$_SESSION['SESS_MEMBER_ID'];
echo "<h2>Nutcracker: RGB Effects Builder for user $username<br/>
On this page you build an animation of the spiral class and create an animated GIF</h2>"; 
echo "<pre>\n";
ini_get('max_execution_time'); 
set_time_limit(250);
ini_get('max_execution_time'); 
echo "</pre>\n";
//show_array($_POST,"_POST");
//show_array($_SERVER,"_SERVER");
//show_array($_SESSION,"_SESSION");
///*
/*
SESSION
Array
(
[SESS_MEMBER_ID] => 2
[SESS_FIRST_NAME] => sean
[SESS_LAST_NAME] => MEIGHAN
[SESS_LOGIN] => f
)
	FLY_0_0_TEST?username=f?effect_class=butterfly?user_targets=AA
*/ 
$array_to_save=$_POST;
$array_to_save['OBJECT_NAME']='life';
extract ($array_to_save);
$effect_name = strtoupper($effect_name);
$effect_name = rtrim($effect_name);
$username=str_replace("%20"," ",$username);
$effect_name=str_replace("%20"," ",$effect_name);
$array_to_save['effect_name']=$effect_name;
$array_to_save['username']=$username;
if(!isset($show_frame)) $show_frame='N';
$array_to_save['show_frame']=$show_frame;
$frame_delay = $_POST['frame_delay'];
$frame_delay = intval((5+$frame_delay)/10)*10; // frame frame delay to nearest 10ms number_format
extract ($array_to_save);
save_user_effect($array_to_save);
show_array($array_to_save,"Effect Settings");
$path="../targets/". $member_id;
list($usec, $sec) = explode(' ', microtime());
$script_start = (float) $sec + (float) $usec;
$t_dat = $user_target . ".dat";
$arr=read_file($t_dat,$path); //  target megatree 32 strands, all 32 being used. read data into an array
$member_id=get_member_id($username);
$path ="workspaces/" . $member_id;
$directory=$path;
if (file_exists($directory))
{
	} else {
	echo "The directory $directory does not exist, creating it";
	mkdir($directory, 0777);
}
$x_dat = $user_target . "+" . $effect_name . ".dat";
$minStrand =$arr[0];  // lowest strand seen on target
$minPixel  =$arr[1];  // lowest pixel seen on skeleton
$maxStrand =$arr[2];  // highest strand seen on target
$maxPixel  =$arr[3];  // maximum pixel number found when reading the skeleton target
$maxI      =$arr[4];  // maximum number of pixels in target
$tree_rgb  =$arr[5];
$tree_xyz  =$arr[6];
$file      =$arr[7];
$min_max   =$arr[8];
$strand_pixel=$arr[9];
$maxCellsToStart=$number_seed_cells;
if($maxCellsToStart<1) $maxCellsToStart=10;
$maxFrame=80;
// $tree_rgb[$strand][$p]=$rgb_val;
if(empty($seed)) $seed=rand(1,3000);
srand($seed);
$base = $user_target . "+" . $effect_name;
for($s=1;$s<=$maxStrand;$s++)
	for($p=1;$p<=$maxPixel;$p++)
{
	$tree_rgb[$s][$p]=0;
}
$zero_tree_rgb=$tree_rgb;
for ($i=1;$i<=$maxCellsToStart;$i++)
{
	$p=intval(rand(1,$maxPixel));
	$s=intval(rand(1,$maxStrand));
	//echo "<pre>Seeding cell at $s,$p</pre>\n";
	$tree_rgb[$s][$p]=hexdec('#00FF00');
}
$prev_tree_rgb=$tree_rgb;
$numberSpirals=2;	// just filling in a dummy value
$seq_number=0;
for($frame=1;$frame<=$maxFrame;$frame++)
{
	$x_dat = $base . "_d_". $frame . ".dat"; // for spirals we will use a dat filename starting "S_" and the tree model
	$dat_file[$frame] = $path . "/" .  $x_dat;
	$dat_file_array[]=$dat_file[$frame];
	//	echo "<pre>$frame $dat_file[$frame]</pre>\n";
	$fh_dat [$frame]= fopen($dat_file[$frame], 'w') or die("can't open file");
	$tree_rgb=$zero_tree_rgb;
	for($s=1;$s<=$maxStrand;$s++)
		for($p=1;$p<=$maxPixel;$p++)
	{
		$neighbors=count_neighbors($prev_tree_rgb,$s,$p,$maxStrand,$maxPixel);
		/*
		Any live cell with fewer than two live neighbours dies, as if caused by under-population.
		Any live cell with two or three live neighbours lives on to the next generation.
		Any live cell with more than three live neighbours dies, as if by overcrowding.
		Any dead cell with exactly three live neighbours becomes a live cell, as if by reproduction.*/
		if($neighbors<2 or $neighbors>3) $tree_rgb[$s][$p]=0; // we died
		else if($neighbors>=2 and $neighbors<=3) $tree_rgb[$s][$p]=hexdec('#FF0000'); // we live
		else if($neighbors==3 and $tree_rgb[$s][$p]<>0) $tree_rgb[$s][$p]=hexdec('#0000FF'); // we are born
		$xyz=$tree_xyz[$s][$p];
		$rgb_val=$tree_rgb[$s][$p];
		$string=$user_pixel=0;
		if($rgb_val<0 or $rgb_val>0)
		{
			if($p%2==0)
				$color_HSV=color_picker($p,$maxPixel,$numberSpirals,$start_color,$end_color);
			else
			$color_HSV=color_picker($maxPixel-$p,$maxPixel,$numberSpirals,$start_color,$end_color);
			$H=$color_HSV['H'];
			$S=$color_HSV['S'];
			$V=$color_HSV['V'];
			$rgb_val=HSV_TO_RGB ($H, $S, $V);
			$seq_number++;
			fwrite($fh_dat[$frame],sprintf ("t1 %4d %4d %9.3f %9.3f %9.3f %d %d %d %d %d\n",$s,$p,$xyz[0],$xyz[1],$xyz[2],$rgb_val,$string, $user_pixel,$strand_pixel[$s][$p][0],$strand_pixel[$s][$p][1],$frame,$seq_number));
			//	printf ("<pre>n%d %4d %4d %9.3f %9.3f %9.3f %d %d %d %d %d</pre>\n",$neighbors,$s,$p,$xyz[0],$xyz[1],$xyz[2],$rgb_val,$string, $user_pixel,$strand_pixel[$s][$p][0],$strand_pixel[$s][$p][1],$frame,$seq_number);
		}
	}
	$prev_tree_rgb=$tree_rgb;
}
echo "<pre>";
//print_r($tree_rgb);
echo "</pre>";
for ($frame=1;$frame<=$maxFrame;$frame++)
{
	//	echo "<pre>closing $fh_dat[$frame]</pre>\n";
	fclose($fh_dat[$frame]);
}
$x_dat_base="life";
$amperage=array();
make_gp($arr,$path,$x_dat_base,$t_dat,$dat_file_array,$min_max,$username,$frame_delay,$script_start,$amperage,$seq_duration,$show_frame);
$filename_buff=make_buff($username,$member_id,$base,$frame_delay,$seq_duration); 

function count_neighbors($tree_rgb,$s,$p,$maxStrand,$maxPixel)
{
	//     2   3   4
	//     1   X   5
	//     0   7   6
	$n_x=array(-1,-1,-1,0,1,1,1,0);
	$n_y=array(-1,0,1,1,1,0,-1,-1);
	$neighbors=$j=0;
	for($i=0;$i<=7;$i++)
	{
		$x=$s + $n_x[$i];
		if($x<1) $x=$maxStrand;
		if($x>$maxStrand) $x=1;
		$y=$p + $n_y[$i];
		if($y<1) $y=$maxPixel;
		if($y>$maxPixel) $y=1;
		if($tree_rgb[$x][$y]==0)
		{
			$j=1;
		}
		else
		$neighbors++;
	}
	return $neighbors;
}
?>
