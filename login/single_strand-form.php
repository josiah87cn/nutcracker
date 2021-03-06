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
require_once('../conf/header.php');
// index.php
require("../effects/read_file.php");
$segment_array=array();
/*echo "<pre>";
print_r($_GET);
echo "</pre>";*/
// http://localhost/nutcracker/login/single_strand-form.php?user=f?total_strings=3
// 
//
// QUERY_STRING] => user=f?total_strings=6?object_name=A0
//
//
//$tokens=explode("?model=",$REQUEST_URI);
extract($_GET);
set_time_limit(0);
if(isset($_GET['submit'])===false or $_GET['submit']==null ) // First time here? Called by member-index.php
{ // yes
	$pixel_array=get_strands($username,$object_name);
	$segment_array=get_segments($username,$object_name);
	/*echo "<pre>";
	echo "<pre>segment_array</pre>\n";
	print_r($segment_array);
	echo "<pre>pixel_array</pre>\n";
	print_r($pixel_array);
	echo "</pre>";*/
	$number_segments_arr=get_number_segments($username,$object_name);
	$number_segments=$number_segments_arr[0];
	$gif_model=$number_segments_arr[1];
	$first_time=1;
}
else
{ // no, so this self submit has values for us to update
	$first_time=0;
	extract($_GET);
	if(isset($pixel_array)) update_strands($username,$object_name,$pixel_array);
	$c=count($segment_array);
	if(isset($gif_model)) update_number_segments($username,$object_name,$number_segments,$gif_model);
	else $gif_model="roof_line";
	$c=count($segment_array);
	/*echo "<pre>";
	print_r($segment_array);
	echo "</pre>";*/
	if($c>0) update_segments($username,$object_name,$segment_array);
	/*	POST:
	Array
	(
	[pixel_array] => Array
	(
	[1] => 2
	[2] => 22
	[3] => 6
	[4] => 11
	[5] => 11
	[6] => 33
	)
		[number_segments] => 4
	[submit] => Submit Form to create your target model
	)*/
}
/*echo "<pre>Tokens:\n";
echo "gif_model=$gif_model\n";
echo "number_segments=$number_segments\n";
echo "total_strings=$total_strings\n";
echo "number_segments=$number_segments\n";
echo "</pre>";*/
//
//
echo "<h1>Single Strand</h1>";
$self=$_SERVER['PHP_SELF'];
echo "<form action=\"$self?username=$username\" method=\"GET\">\n";
$model_name=$object_name;
?>
<input type="submit" name="submit" value="Submit Form to create your target model" />
<table border="1">
<input type="hidden" name="username" value="<?php echo "$username"; ?>"/>
<input type="hidden" name="total_strings" value="<?php echo "$total_strings"; ?>"/>
<input type="hidden" name="object_name" value="<?php echo "$object_name"; ?>"/>
<?php
for($string=1;$string<=$total_strings;$string++)
{
	echo "<tr><td>Enter the number of pixels in String #$string</td>";
	if(isset($pixel_array[$string]))
		$maxPixel=$pixel_array[$string];
	else
	$maxPixel=0;
	echo "<td><input type=\"text\" STYLE=\"background-color: #ABE8EC;\" size=\"5\" maxlength=\"6\" 
	value=\"$maxPixel\" name=\"pixel_array[$string]\"/></td>\n";
	echo "</tr>\n";
}
?>
<tr>
<td>Number of Segments</td>
<td><input type="text" style="background-color: #ABE8EC;" size="8" maxlength="" 
<?php echo "value=\"$number_segments\""; ?> name="number_segments"/><br/>
</td>
</tr>
<tr>
<td>What Type of Gif Model to preview with</td>
<?php $checked_roof_line=$checked_window=$checked_arch="";
if($gif_model=="roof_line") $checked_roof_line="checked"; 
if($gif_model=="window") $checked_window="checked"; 
if($gif_model=="arch") $checked_arch="checked"; ?>
<td>
<input type="radio" name="gif_model" value="arch"   
<?php echo "$checked_arch "; ?> />Arch (Each segment makes an arch)<br/>
<input type="radio" name="gif_model" value="roof_line" 
<?php echo "$checked_roof_line "; ?> />Roof Line<br/>
<input type="radio" name="gif_model" value="window" 
<?php echo "$checked_window "; ?> />Window (Assumes four segments)<p>
</td>
<td><img src="../images/single_strand.png" /></td>
</tr>
</table>
<?php
$c=count($segment_array);
if(isset($pixel_array) and ($first_time==0 or $c>0   )) // if not first time, then we have data we can show
{
	echo "<table border=1>";
	for ($loop=1;$loop<=5;$loop++) // loop1=virtual pixel, loop2=string,loop3=pixel, loop4=blank line, loop5=segment
	{
		echo "<tr>";
		$pixel=0;
		for($string=1;$string<=$total_strings;$string++)
		{
			$maxPixel=$pixel_array[$string];
			for($p=1;$p<=$maxPixel;$p++)
			{
				$pixel++;
				if($string%2==0) $color="#AAFFAA";
				else $color="#AAAAFF";
				if($loop==1)
				{
					if($pixel==1)
					{
						echo "<th>Virtual<br/>Pixel</th>";
					}
					echo "<th>$pixel</th>";
				}
				if($loop==2)
				{
					if($pixel==1)
					{
						echo "<td>String</td>";
					}
					echo "<td bgcolor=$color>$string</td>";
				}
				if($loop==3)
				{
					if($pixel==1)
					{
						echo "<td>Pixel</td>";
					}
					echo "<td bgcolor=$color>$p</td>";
					$s=1;
					$target_array[$s][$pixel]['string']=$string;
					$target_array[$s][$pixel]['user_pixel']=$p;
				}
				if($loop==4)
				{
					if($pixel==1)
					{
						echo "<td>----</td>";
					}
					echo "<td >&nbsp;</td>";
				}
				if($loop==5)
				{
					$current_segment=$number_segments;
					for ($segment=1;$segment<$number_segments;$segment++)
					{
						if(isset($segment_array[$segment]))
						{
							$segment1=$segment+1;
							if($pixel >= $segment_array[$segment] and $pixel < $segment_array[$segment1])
								$current_segment = $segment;
						}
					}
					if($pixel==1)
					{
						echo "<td>Segment</td>";
					}
					echo "<td >$current_segment</td>";
				}
			}
		}
		echo "</tr>";
	}
	echo "</table>";
	echo "<br/><h3>Your virtual strand is $pixel Pixels long</h3>\n";
	$maxPixels=$pixel;
	echo "<table border=1>";
	if($number_segments==null) $number_segments=1;
	for ($segment=1;$segment<=$number_segments;$segment++)
	{
		$pixels_per_segment= intval($pixel/$number_segments);
		$start_pixel = ($segment-1)*$pixels_per_segment + 1;
		if(isset($segment_array[$segment])) $start_pixel=$segment_array[$segment];
		echo "<tr>";
		echo "<td>Segment $segment starts at virtual pixel#</td>";
		echo "<td>$start_pixel</td>";
		echo "<td><input type=\"text\" STYLE=\"background-color: #ABE8EC;\" size=\"5\" maxlength=\"6\" 
		value=\"$start_pixel\" name=\"segment_array[$segment]\"></td>\n";
		echo "</tr>\n";
	}
	single_strand($maxPixels,$gif_model,$username,$model_name,$target_array,$number_segments,$segment_array); // let us actually write the targets/member_id/file.dat
}
?>
</form>
<?php
echo "</body>\n";
echo "</html>\n";

function display_file($full_path)
{
	$lines = file($full_path);
	echo "<pre>";
	// Loop through our array, show HTML source as HTML source; and line numbers too.
	foreach ($lines as $line_num => $line)
	{
		echo "$line";
	}
	echo "</pre>";
}

function single_strand($maxPixels,$gif_model,$username,$model_name,$target_array,$number_segments,$segment_array)
{
	//echo "<pre>single_strand($maxPixels,$gif_model,$username,$model_name)</pre>\n";
	#
	#	output files are created for each segment
	#	8 segment = t1_8.dat output file
	#	16 segment = t1_16.dat output file
	#	32 segment = t1_32.dat output file
	#
	#
	#
	#	Build a mega-Tree with arbitray strands
	#	House is 40' wide, 25' tall
	echo "<pre>function single_strand(maxPixels,gif_model,username,model_name,target_array,number_segments,segment_array)</pre>\n";
	echo "<pre>function single_strand($maxPixels,$gif_model,$username,$model_name,$target_array,$number_segments,$segment_array)</pre>\n";
	$member_id=get_member_id($username);
	$path="../targets/" . $member_id ;
	if (file_exists($path))
	{
		} else {
		echo "The directory $path does not exist, creating it";
		mkdir($path, 0777);
	}
	##	passed in now thru runtime arg, 	strands=16;
	$dat_file = $path . "/" . $model_name . ".dat";
	echo "<pre>dat_file=$dat_file  </pre>\n";
	$fh = fopen($dat_file, 'w') or die("can't open file $fh");
	fwrite($fh,"#    $dat_file\n");
	fwrite($fh,"#    Col 1: Your TARGET_MODEL_NAME\n");
	fwrite($fh,"#    Col 2: Strand number.\n");
	fwrite($fh,"#    Col 3: Nutcracker Pixel#\n");
	fwrite($fh,"#    Col 4: X location in world coordinates\n");
	fwrite($fh,"#    Col 5: Y location in world coordinates\n");
	fwrite($fh,"#    Col 6: Z location in world coordinates\n");
	fwrite($fh,"#    Col 7: Single Strand Segment number\n");
	fwrite($fh,"#    Col 8: User string\n");
	fwrite($fh,"#    Col 9: User pixel\n");
	fwrite($fh,"# \n");
	$s=1;
	$p=0;
	$number_segments=count($segment_array);
	$segment_max=$number_segments+1;
	$segment_array[$segment_max]=$maxPixels+1;
	$x=$y=$z=0;
	/*echo "<pre>segment_array:";
	print_r($segment_array);
	echo "</pre>";*/
	for ($segment=1;$segment<$segment_max;$segment++)
	{
		if(isset($segment_array[$segment]))
		{
			$segment1=$segment+1;
			$pixels_segment[$segment]=$segment_array[$segment1]-$segment_array[$segment];
			$PI=3.1415926;
			$diameter_array[$segment] = 2*($pixels_segment[$segment]/$PI);
			///
			/*echo "<pre>segment=$segment";
			print_r($pixels_segment);
			print_r($diameter_array);
			echo "</pre>";*/
			//
			printf ("<pre>%6d  %6d-%6d  dia=%7.2f pix_per-seg=%6d</pre>\n",
			$segment,$segment_array[$segment],$segment_array[$segment1]-1,
			$diameter_array[$segment],$pixels_segment[$segment]);
			for($i=$segment_array[$segment]; $i< $segment_array[$segment1]; $i++)
			{
				$p++;
				$start_pixel=$segment_array[$segment];
				$xyz = get_xyz($segment,$start_pixel,$pixels_segment,
				$diameter_array,$p,$x,$y,$z,$gif_model);
				$x=$xyz['x'];
				$y=$xyz['y'];
				$z=$xyz['z'];
				fwrite($fh,sprintf ("%s %3d %3d %7.3f %7.3f %7.3f %5d %5d %5d %s %s\n", $model_name,$s,$p,$x,$y,$z,
				$segment,$target_array[$s][$p]['string'] ,$target_array[$s][$p]['user_pixel'], 
				$username ,$model_name));
				/*	printf ("<pre>seg=%3d %s %3d %3d %7.3f %7.3f %7.3f %5d %5d %5d %s %s</pre>\n", 
				$segment,$model_name,$s,$p,$x,$y,$z,
				$segment,$target_array[$s][$p]['string'] ,$target_array[$s][$p]['user_pixel'],
				$username ,$model_name);*/
			}
		}
	}
	fwrite($fh, "\n" );
	fclose($fh);
	echo "</pre>\n";
	return $dat_file;
}

function get_xyz($segment,$start_pixel,$pixels_segment,$diameter_array,$p,$x,$y,$z,$gif_model)
{
	$max_pixels_segment=$pixels_segment[$segment];
	$pixels_segment[0]=0;
	if($gif_model=="roof_line")
	{
		$mod = $segment%2;
		if($mod==0) $mod=2;
		$x=$p*3;
		switch ($mod)
		{
			case 1:
			$z=($p-$start_pixel)*3;
			//$z=($pixels_segment[$segment-1]+($p-$start_pixel))*3;
			break;
			//
			case 2:
			$z=($pixels_segment[$segment-1]-($p-$start_pixel))*3;
			break;
		}
	}
	else if($gif_model=="window")
	{
		$mod=$segment%4;
		if($mod==0) $mod=4;
		switch ($mod)
		{
			case 1:
			$x=$p*3;
			$z=0;
			break;
			//
			case 2:
			$x=$pixels_segment[1]*3;
			$z=($p-$start_pixel)*3;
			break;
			//
			case 3:
			$x=($max_pixels_segment -($p-$start_pixel))*3;
			$z=$pixels_segment[2]*3;
			break;
			//
			case 4:
			$x=0;
			$z=($pixels_segment[4]-($p-$start_pixel))*3;
			break;
		}
	}
	else if($gif_model=="window10")
	{
		$mod=$segment%4;
		if($mod==0) $mod=4;
		switch ($mod)
		{
			case 1:
			$x=$pixels_segment[4]*3;
			$z=($pixels_segment[3]-($p-$start_pixel))*3;
			break;
			case 2:
			$x=($max_pixels_segment -($p-$start_pixel))*3;
			$z=0;
			break;
			case 3:
			$x=0;
			$z=($p-$start_pixel)*3;
			break;
			
			case 4:
			$x=($p-$start_pixel)*3;
			$z=$pixels_segment[4]*3;
			break;
			case 5:
			$x=$p*3;
			$z=0;
			break;
			case 6:
			$x=$p*3;
			$z=0;
			break;
			case 7:
			$x=0;
			$z=($pixels_segment[3]-($p-$start_pixel))*3;
			break;
			case 8:
			$x=($max_pixels_segment -($p-$start_pixel))*3;
			$z=$pixels_segment[2]*3;
			break;
			case 9:
			$x=$pixels_segment[1]*3;
			$z=($p-$start_pixel)*3;
			break;
			case 10:
			$x=($max_pixels_segment -($p-$start_pixel))*3;
			$z=$pixels_segment[2]*3;
			break;
			//
			case 2:
			$x=$pixels_segment[1]*3;
			$z=($p-$start_pixel)*3;
			break;
			//
			case 3:
			$x=($max_pixels_segment -($p-$start_pixel))*3;
			$z=$pixels_segment[2]*3;
			break;
			//
			case 4:
			$x=0;
			$z=($pixels_segment[3]-($p-$start_pixel))*3;
			break;
		}
	}
	else if($gif_model=="arch")
	{
		$mod=$segment%4;
		if($mod==0) $mod=4;
		$PI=3.1415926;
		$radius = $max_pixels_segment/$PI;
		$radian = (($p-$start_pixel)/$max_pixels_segment)*$PI;
		$sum_diamter=$x_offset=$z_offset=0;
		if($segment>1)
		{
			for ($seg=1;$seg<$segment;$seg++)
			{
				$sum_diamter+=$diameter_array[$seg];
			}
		}
		$x_offset=$sum_diamter+$diameter_array[$segment]/2;
		$x=-1 * $radius * cos($radian) + $x_offset;
		$z=$radius * sin($radian) + $z_offset;
	}
	$y=0;
	$xyz['x']=$x;
	$xyz['y']=$y;
	$xyz['z']=$z;
	return $xyz;
}

function getx($r,$degree)
{
	$PI = pi();
	$DTOR = $PI/180;
	$RTOD = 180/$PI;
	$radian = $degree * $DTOR;
	$x=$r*sin($radian);
	$y=$r*cos($radian);
	#	print r,degree,radian,x,y;
	return $x;
}

function gety($r,$degree)
{
	$PI = pi();
	$DTOR = $PI/180;
	$RTOD = 180/$PI;
	$radian = $degree * $DTOR;
	$x=$r*sin($radian);
	$y=$r*cos($radian);
	return $y;
}

function drop_and_create($db,$table,$query)
{
	$drop_query = "drop table " . $table;
	//mysql_query($drop_query,$db) or die("Error on '$drop_query'");
	mysql_query($drop_query,$db);
	mysql_query($query,$db) or die ("Error on $query");
}

function insert_target_model($db,$file)
{
	$fh = fopen($file, 'r') or die("can't open file");
	$line=0;
	$row=0;
	echo "<table border=1>";
	while (!feof($fh))
	{
		$line = fgets($fh);
		#echo "<pre>$line<br/></pre>";
		//$tok=preg_split("/ +/", $line);
		$tok=preg_split('/\t/', $line);
		$row++;
		if(strlen($tok[0])>0)
		{
			$insert="INSERT into members (username,role,joined,posts) values ('" . $tok[0] . "','". $tok[1]  . "','FEB-12-2012',0)";
			//echo "<td>$insert</td>";
			mysql_query($insert,$db) or die ("Failed executing $insert");
		}
		//	echo "</tr>";
	}
	echo "</table>";
	fclose($fh);
	$query="SELECT * FROM members";
	$result = mysql_query($query,$db) or die("Failed Query");
	echo "<b><center>Database Output</center></b><br><br>";
	$i=0;
	echo "<table border=1>";
	$row=0;
	while ($myrow = mysql_fetch_row($result))
	{
		$row++;
		printf("<tr><td>$row</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>n", 
		$myrow[0], $myrow[1], $myrow[2], $myrow[3], $myrow[4]);
	}
	echo "</table>";
}

function update_strands($username,$object_name,$pixel_array)
{
	require_once('../conf/config.php');
	//Connect to mysql server
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if(!$link)
	{
		die('Failed to connect to server: ' . mysql_error());
	}
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db)
	{
		die("Unable to select database");
	}
	$delete = "delete from models_strands where username='$username' and object_name='$object_name'";
	//	echo "<pre>update_strands: delete=$delete</pre>\n";
	mysql_query($delete) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $delete . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
	//
	//
	$total_pixels=0;
	foreach ($pixel_array as $string => $maxPixel)
	{
		$total_pixels+=$maxPixel;
		$insert = "insert into models_strands( username,object_name,string,pixels,last_updated)
			values ('$username','$object_name',$string,$maxPixel,now())";
		//	echo "<pre>update_strands: query=$insert</pre>\n";
		mysql_query($insert) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $insert . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
	}
	$update = "update models set total_pixels=$total_pixels
	where username='$username' and object_name='$object_name'";
	//	echo "<pre>update_strands: delete=$delete</pre>\n";
	mysql_query($update) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $update . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
}

function get_strands($username,$object_name)
{
	require_once('../conf/config.php');
	//Connect to mysql server
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if(!$link)
	{
		die('Failed to connect to server: ' . mysql_error());
	}
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db)
	{
		die("Unable to select database");
	}
	//
	//
	$query = "select * from models_strands where username='$username' and  object_name='$object_name'
	order by string";
	//echo "<pre>update_strands: query=$query</pre>\n";
	$result=mysql_query($query) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $query . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
	//
	$pixel_array=array();
	while ($row = mysql_fetch_assoc($result))
	{
		extract($row);
		$pixel_array[$string]=$pixels;
	}
	return $pixel_array;
}

function update_segments($username,$object_name,$segment_array)
{
	require_once('../conf/config.php');
	//Connect to mysql server
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if(!$link)
	{
		die('Failed to connect to server: ' . mysql_error());
	}
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db)
	{
		die("Unable to select database");
	}
	$delete = "delete from models_strand_segments where username='$username' and object_name='$object_name'";
	//echo "<pre>update_segments: delete=$delete</pre>\n";
	mysql_query($delete) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $delete . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
	//
	//
	//echo "<pre>update_segments: segment array:";
	//print_r($segment_array);
	//echo "</pre>\n";
	foreach ($segment_array as $segment => $starting_pixel)
	{
		$insert = "insert into models_strand_segments( username,object_name,segment,starting_pixel,last_updated)
			values ('$username','$object_name',$segment,$starting_pixel,now())";
		//echo "<pre>update_segments: query=$insert</pre>\n";
		mysql_query($insert) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $insert . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
	}
}

function update_number_segments($username,$object_name,$number_segments,$gif_model)
{
	require_once('../conf/config.php');
	//Connect to mysql server
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if(!$link)
	{
		die('Failed to connect to server: ' . mysql_error());
	}
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db)
	{
		die("Unable to select database");
	}
	//
	$update = "update models set number_segments=$number_segments,gif_model='$gif_model',last_updated=now()
		where username='$username' and  object_name='$object_name'";
	//echo "<pre>update_number_segments: query=$update</pre>\n";
	mysql_query($update) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $update . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
}

function get_number_segments($username,$object_name)
{
	require_once('../conf/config.php');
	//Connect to mysql server
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if(!$link)
	{
		die('Failed to connect to server: ' . mysql_error());
	}
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db)
	{
		die("Unable to select database");
	}
	//
	$query = "select * from  models where username='$username' and  object_name='$object_name'";
	//echo "<pre>get_number_segments: query=$query</pre>\n";
	$result=mysql_query($query) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $query . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
	//
	$number_segments=-1;
	while ($row = mysql_fetch_assoc($result))
	{
		extract($row);
	}
	/*echo "<pre>get_number_segments:";
	print_r($row);
	echo "</pre>\n";*/
	$number_segments_arr[0]=$number_segments;
	$number_segments_arr[1]=$gif_model;
	return $number_segments_arr;
}
