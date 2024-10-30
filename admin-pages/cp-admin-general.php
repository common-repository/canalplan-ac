<?php
/*
Extension Name: Canalplan General Settings
Extension URI: http://blogs.canalplan.org.uk/canalplanac/canalplan-plug-in/
Version: 3.0
Description: General Settings for the Canalplan AC Plugin
Author: Steve Atty
*/

$title = __('CanalPlan Options');
global $blog_id;
$do_update=0;
echo '<script type="text/javascript"> var linktype=1; cplogid='.$blog_id.'</script>';
echo '<script type="text/javascript" src="../wp-content/plugins/canalplan-ac/canalplan/javascript/canalplanfunctions.js" DEFER></script>';
nocache_headers();
?>
<script language="JavaScript" type="text/javascript"><!--
        function set_value(param,listID)
        {
var x=document.getElementById("general_options");
x.options[param].text=listID;
        }
        function showValue(listID)
        {
    var list = document.getElementById(listID);
    var items = list.getElementsByTagName("option");
    var itemsString = "";
    var itemsString2 = "";
    for (var i = 0; i < items.length; i++) {
        if (itemsString.length > 0) itemsString += ":";
        itemsString += items[i].value;
 	itemsString += '|';
        itemsString += items[i].innerHTML;
    }
document.getElementById("dataset").value=itemsString;
        }
	//-->
	</script>
<?php
nocache_headers();
if(isset($_POST['_submit_check']))
	{
		parse_data($_POST['dataset'],$blog_id);
	}
?>

<div class="wrap">
<h2><?php _e('General CanalPlan Options') ?> </h2>
<br>
<h3><?php _e('CanalPlan Data') ?></h3>
<?php
if (isset($_POST["canalkey"]) && isset($_POST['SCK'])){
	$api=preg_replace("/[^a-zA-Z0-9|\s\p{P}]/", "", $_POST['canalkey']);
	$sql2 =$wpdb->prepare("SELECT pref_value FROM ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='canalkey'",$blog_id);
	$sql=$wpdb->prepare("update ".CANALPLAN_OPTIONS." set pref_value=%s where blog_id=%d and pref_code='canalkey'",$api,$blog_id);
	$r = $wpdb->get_results($sql2);
	if ($wpdb->num_rows==0) {
		$sql=$wpdb->prepare("insert into ".CANALPLAN_OPTIONS." (pref_value,blog_id,pref_code) values (%s,%d,'canalkey')",$api,$blog_id);
	}
	$wpdb->query($sql);
}

if (isset($_POST["canalkey"]) && isset($_POST['RCK'])){
	$sql = $wpdb->prepare("Delete FROM ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='canalkey'",$blog_id);
	$r = $wpdb->query($sql);
}

if (isset($_POST["routeslug"])){
	$routeslug=preg_replace("/[^a-zA-Z0-9\s\p{P}]/", "", $_POST['routeslug']);
	$sql=$wpdb->prepare("update ".CANALPLAN_OPTIONS." set pref_value='".$routeslug."'  where blog_id=%d and pref_code='routeslug'",$blog_id);
	$sql2 =$wpdb->prepare("SELECT pref_value FROM ".CANALPLAN_OPTIONS." where  blog_id=%d and pref_code='routeslug'",$blog_id);
	$r =$wpdb->get_results($sql2);
	$sql=$wpdb->prepare("update ".CANALPLAN_OPTIONS." set pref_value=%s where blog_id=%d and pref_code='routeslug'",$routeslug,$blog_id);
	if ($wpdb->num_rows==0) {
		$sql=$wpdb->prepare("insert into ".CANALPLAN_OPTIONS." (pref_value,blog_id,pref_code) values (%s,%d,'routeslug')",$routeslug,$blog_id);
	}
	$wpdb->query($sql);
}

if (isset($_POST["maptype"])){
	$maptype=preg_replace("/[^a-zA-Z0-9\s\p{P}]/", "", $_POST['maptype']);
	$sql=$wpdb->prepare("update ".CANALPLAN_OPTIONS." set pref_value='".$maptype."'  where blog_id=%d and pref_code='maptype'",$blog_id);
	$sql2 =$wpdb->prepare("SELECT pref_value FROM ".CANALPLAN_OPTIONS." where  blog_id=%d and pref_code='maptype'",$blog_id);
	$r =$wpdb->get_results($sql2);
	$sql=$wpdb->prepare("update ".CANALPLAN_OPTIONS." set pref_value=%s where blog_id=%d and pref_code='maptype'",$maptype,$blog_id);
	if ($wpdb->num_rows==0) {
		$sql=$wpdb->prepare("insert into ".CANALPLAN_OPTIONS." (pref_value,blog_id,pref_code) values (%s,%d,'maptype')",$maptype,$blog_id);
	}
	$wpdb->query($sql);
}	

$uploads =  ABSPATH . 'wp-content/uploads/CanalplanPluginDataDownload';
if (!is_dir($uploads)) mkdir($uploads);
$sqlitefile=$uploads.'/canalplan_data.sqlite';
$polylinesfile=$uploads.'/polylines.json';
$cpaliasesfile=$uploads.'/cpaliases.json';
$cpcanalsfile=$uploads.'/cpcanals.json';
$cpplacesfile=$uploads.'/cpplaces.json';
$cpplingplacesfile=$uploads.'/cpplingplaces.json';
$cplinksfile=$uploads.'/cplinks.json';
set_time_limit(20);
if (isset($_POST["update_data"])){
	$overallstart = time(); 

	
set_time_limit(20);
	$data = canalplan_get_url(CANALPLAN_BASE."/utilities/PluginDataDownload/cpaliases.json");
	if ( substr($data,0,4)=='ERRO') {
		echo $data."<br/>";
	} else {
		$handle2=fopen($cpaliasesfile,"w");
		fwrite($handle2, $data);
		fclose($handle2);
	}
	
	set_time_limit(20);
	$data = canalplan_get_url(CANALPLAN_BASE."/utilities/PluginDataDownload/cpcanals.json");
	if ( substr($data,0,4)=='ERRO') {
		echo $data."<br/>";
	} else {
		$handle2=fopen($cpcanalsfile,"w");
		fwrite($handle2, $data);
		fclose($handle2);
	}

	set_time_limit(20);
	$data = canalplan_get_url(CANALPLAN_BASE."/utilities/PluginDataDownload/polylines.json");
	if ( substr($data,0,4)=='ERRO') {
		echo $data."<br/>";
	} else {
		$handle2=fopen($polylinesfile,"w");
		fwrite($handle2, $data);
		fclose($handle2);
	}
set_time_limit(20);	
	echo '<table border="1" cellpadding="10" ><tr><th>Table Name </th><th>Contained (Rows)</th><th>Now Contains (Rows)</th><th>Time Taken</th></tr>';
	$start = time(); 
	$sql="select count(*) from ".CANALPLAN_ALIASES." where substring(canalplan_id,1,1)!='!';";
	$res = $wpdb->get_results($sql,ARRAY_N);
	$res2=$res[0];
		$sql="select count(*) from ".CANALPLAN_ALIASES." where substring(canalplan_id,1,1)='!';";
	$res = $wpdb->get_results($sql,ARRAY_N);
	$res4=$res[0];
	set_time_limit(20);
	$sql= "delete from ".CANALPLAN_ALIASES." where substring(canalplan_id,1,1)!='!';";
	$res = $wpdb->query($sql);
	set_time_limit(20);
	$sql= "delete from ".CANALPLAN_ALIASES." where substring(canalplan_id,1,1)='!';";
	$res = $wpdb->query($sql);
	set_time_limit(20);

	$linefile=$cpaliasesfile;
	$myfile = fopen($linefile, "r") or die("Unable to open file!");
	$rawfile = fread($myfile,filesize($linefile));
	fclose($myfile);
	unlink($linefile);
	$data = json_decode($rawfile,true);
	foreach ($data as $entry) {
		set_time_limit(20);

	   $sql= $wpdb->prepare("INSERT IGNORE INTO ".CANALPLAN_ALIASES." (canalplan_id,place_name) VALUES (%s,%s)",$entry[0],str_replace('"','',$entry[1]));
	   $res = $wpdb->query($sql);
	 $res = $wpdb->query($sql);
	}
	$sql="select count(*) from ".CANALPLAN_ALIASES." where substring(canalplan_id,1,1)!='!';";
	$res = $wpdb->get_results($sql,ARRAY_N);
	$res3=$res[0];
	$sql="select count(*) from ".CANALPLAN_ALIASES." where substring(canalplan_id,1,1)='!';";
	$res = $wpdb->get_results($sql,ARRAY_N);
	$res5=$res[0];
	$timetaken=date('H:i:s',(time() - $start));
	print "<tr><td>Canalplan Aliases</td><td>".$res2[0]."</td><td>".$res3[0]."</td><td>$timetaken</td></tr>";
	print "<tr><td>Canalplan Features</td><td>".$res4[0]."</td><td>".$res5[0]."</td><td>-</td></tr>";

	$start = time(); 
	$sql="select count(*) from ".CANALPLAN_CODES.";";
	$res = $wpdb->get_results($sql,ARRAY_N);
	$res2=$res[0];
	for ($x = 1; $x <= 100; $x++) {
	set_time_limit(20);
//	print "fetching ".CANALPLAN_BASE."/utilities/cpplaces$x.json<br/>";
	$data = canalplan_get_url(CANALPLAN_BASE."/utilities/PluginDataDownload/cpplaces$x.json");
	//print(strlen($data))."<br/>";
	if (strpos($data,'<title>404 ')> 0 ) {
		$x=1000;
		break;
	}
	if ( substr($data,0,4)=='ERRO') {
		echo $data."<br/>";
	} else {
		$handle2=fopen($cpplacesfile.$x,"w");
		fwrite($handle2, $data);
		fclose($handle2);
		$place_files[]=$cpplacesfile.$x;
	}
}
   foreach($place_files as $place_file){
	$linefile=$place_file;
	$myfile = fopen($linefile, "r") or die("Unable to open file!");
	$rawfile = fread($myfile,filesize($linefile));
	fclose($myfile);
	
	$data = json_decode($rawfile,true);

	foreach ($data as $entry) {
		set_time_limit(20);

 $sql= $wpdb->prepare("INSERT INTO ".CANALPLAN_CODES." (canalplan_id,place_name,size,lat,`long`,attributes,lat_lng_point,region)  VALUES (%s,%s,%d,%s,%s,%s, ST_GeomFromText(%s),%s) ON DUPLICATE KEY UPDATE place_name=%s, size=%d, lat=%s, `long`=%s, attributes=%s, lat_lng_point=ST_GeomFromText(%s), region =%s ",$entry[0],str_replace('"','',$entry[1]),$entry[2],$entry[3],$entry[4],$entry[5],"Point(".$entry[3].' '.$entry[4].")",$entry[6],str_replace('"','',$entry[1]),$entry[2],$entry[3],$entry[4],$entry[5],"Point(".$entry[3].' '.$entry[4].")",$entry[6]);

	   $res = $wpdb->query($sql);
	 $res = $wpdb->query($sql);
	}
		unlink($linefile);
}
for ($x = 1; $x <= 100; $x++) {
	set_time_limit(20);

	$data = canalplan_get_url(CANALPLAN_BASE."/utilities/PluginDataDownload/cpplingplaces$x.json");
	if (strpos($data,'<title>404 ')> 0 ) {
		$x=1000;
		break;
	}
	if ( substr($data,0,4)=='ERRO') {
		echo $data."<br/>";
	} else {
		$handle2=fopen($cpplingplacesfile.$x,"w");
		fwrite($handle2, $data);
		fclose($handle2);
		$plingplace_files[]=$cpplingplacesfile.$x;
	}
}
 foreach($plingplace_files as $plingplace_file){
	$linefile=$plingplace_file;
	$myfile = fopen($linefile, "r") or die("Unable to open file!");
	$rawfile = fread($myfile,filesize($linefile));
	fclose($myfile);
	$data = json_decode($rawfile,true);
	foreach ($data as $entry) {
		set_time_limit(20);

 $sql= $wpdb->prepare("INSERT INTO ".CANALPLAN_CODES." (canalplan_id,place_name,size,lat,`long`,attributes,lat_lng_point) VALUES (%s,%s,%d,%s,%s,%s, ST_GeomFromText(%s)) ON DUPLICATE KEY UPDATE place_name=%s, size=%d, lat=%s, `long`=%s, attributes=%s, lat_lng_point=ST_GeomFromText(%s)",$entry[0],$entry[1],$entry[2],$entry[3],$entry[4],$entry[5],"Point(".$entry[3].' '.$entry[4].")",$entry[1],$entry[2],$entry[3],$entry[4],$entry[5],"Point(".$entry[3].' '.$entry[4].")");
	   $res = $wpdb->query($sql);
	 $res = $wpdb->query($sql);
	}
		unlink($linefile);
}
	$sql="select count(*) from ".CANALPLAN_CODES.";";
	$res = $wpdb->get_results($sql,ARRAY_N);
	$res3=$res[0];
	$timetaken=date('H:i:s',(time() - $start));
	print "<tr><td>Canalplan Places</td><td>".$res2[0]."</td><td>".$res3[0]."</td><td>$timetaken</td></tr>";
	$start = time(); 
	$sql="select count(*) from ".CANALPLAN_LINK.";";
	$res = $wpdb->get_results($sql,ARRAY_N);
	$res2=$res[0];

for ($x = 1; $x <= 100; $x++) {
	set_time_limit(20);

	$data = canalplan_get_url(CANALPLAN_BASE."/utilities/PluginDataDownload/cplinks$x.json");

	if (strpos($data,'<title>404 ')> 0 ) {
		$x=1000;
		break;
	}
	if ( substr($data,0,4)=='ERRO') {
		echo $data."<br/>";
	} else {
		$handle2=fopen($cplinksfile.$x,"w");
		fwrite($handle2, $data);
		fclose($handle2);
		$cplink_files[]=$cplinksfile.$x;
	}
}
 foreach($cplink_files as $cplinksfile){
	$linefile=$cplinksfile;
	$myfile = fopen($linefile, "r") or die("Unable to open file!");
	$rawfile = fread($myfile,filesize($linefile));
	fclose($myfile);
	$data = json_decode($rawfile,true);
	foreach ($data as $entry) {
		set_time_limit(20);

$sql= $wpdb->prepare("INSERT INTO ".CANALPLAN_LINK." (place1,place2,metres,locks,waterway) VALUES (%s,%s,%d,%d,%s) ON DUPLICATE KEY UPDATE metres=%d,locks=%d, waterway=%s",$entry[0],$entry[1],$entry[2],$entry[3],$entry[4],$entry[2],$entry[3],$entry[4]);
	 $res = $wpdb->query($sql);
	}
		unlink($linefile);
}
	$sql="select count(*) from ".CANALPLAN_LINK.";";
	$res = $wpdb->get_results($sql,ARRAY_N);
	$res3=$res[0];
		$timetaken=date('H:i:s',(time() - $start));
	print "<tr><td>Canalplan Links</td><td>".$res2[0]."</td><td>".$res3[0]."</td><td>$timetaken</td></tr>";
$start = time(); 
	$sql="select count(*) from ".CANALPLAN_CANALS.";";
	$res = $wpdb->get_results($sql,ARRAY_N);
	$res2=$res[0];
	$linefile=$cpcanalsfile;
	$myfile = fopen($linefile, "r") or die("Unable to open file!");
	$rawfile = fread($myfile,filesize($linefile));
	fclose($myfile);
		unlink($linefile);
	$data = json_decode($rawfile,true);
	foreach ($data as $entry) {
		set_time_limit(20);
  $sql= $wpdb->prepare("INSERT INTO ".CANALPLAN_CANALS." (id,parent,name,fullname) VALUES (%s,%s,%s,%s) ON DUPLICATE KEY UPDATE name=%s,fullname=%s, parent=%s", $entry[0],$entry[1],$entry[2],$entry[3],$entry[2],$entry[3],$entry[1]);

	// echo "SQL = $sql <br />";
	 $res = $wpdb->query($sql);
	}
	$sql="select count(*) from ".CANALPLAN_CANALS.";";
	$res = $wpdb->get_results($sql,ARRAY_N);
	$res3=$res[0];
		$timetaken=date('H:i:s',(time() - $start));
	print "<tr><td>Canalplan Waterways</td><td>".$res2[0]."</td><td>".$res3[0]."</td><td>$timetaken</td></tr>";
$start = time(); 
	$sql="select count(*) from ".CANALPLAN_POLYLINES.";";
	$res = $wpdb->get_results($sql,ARRAY_N);
	$res2=$res[0];
	$linefile=$polylinesfile;
	$myfile = fopen($linefile, "r") or die("Unable to open file!");
	$rawfile = fread($myfile,filesize($linefile));
	fclose($myfile);
		unlink($linefile);
	$data = json_decode($rawfile,true);
	foreach ($data as $entry) {
		set_time_limit(20);
	$sql= $wpdb->prepare("INSERT INTO ".CANALPLAN_POLYLINES." (id,pline,weights) VALUES (%s,%s,%s) ON DUPLICATE KEY UPDATE pline=%s,weights=%s", $entry[0],$entry[1],$entry[2],$entry[1],$entry[2]);
	// echo "SQL = $sql <br />";
	 $res = $wpdb->query($sql);
	}

	$sql="select count(*) from ".CANALPLAN_POLYLINES.";";
	$res = $wpdb->get_results($sql,ARRAY_N);
	$res3=$res[0];
		$timetaken=date('H:i:s',(time() - $start));
	print "<tr><td>Canalplan Polylines</td><td>".$res2[0]."</td><td>".$res3[0]."</td><td>$timetaken</td></tr>";

$region_array=array('ukuk'=>'uk','eueu'=>'europe','ieie'=>'ireland','ozoz'=>'australia','o4sr'=>'north-america');
foreach ($region_array as $key => $value){
	$start = time(); 
	$url=CANALPLAN_BASE."/mapping/geodata/full/$key/canalplan_waterways.geojson";
	//var_dump($url);
	$data = canalplan_get_url($url);
	//print strpos($data,",
//]}");
$data=str_replace(",
]}","]}",$data);
	$geodatas = json_decode($data,true);
	foreach ($geodatas['features'] as $geodata){
		//	var_dump($geodata);
		$waterway_id=$geodata['properties']['cp_id'];
	//	print "$waterway_id<br/><br/>";
		$coords=$geodata['geometry']['coordinates'];
		$coordstring="[";
		foreach ($coords as $coord) {
			$geocord='['.implode(',',$coord).'],';
			//	print "$geocord<br/>";
				$coordstring.=$geocord;
		}
		$coordstring=trim($coordstring,",");
		$coordstring.="]";
		//print "$coordstring<br/><br/>";
		$sql= $wpdb->prepare("update ".CANALPLAN_POLYLINES." set geojson = %s where id = %s",$coordstring,$waterway_id);
		$r = $wpdb->get_results($sql,ARRAY_A);
	//		print "$sql<br/><br/>";
		}
		$timetaken=date('H:i:s',(time() - $start));
	print "<tr><td>Canalplan geojson for $value</td><td></td><td></td><td>$timetaken</td></tr>";	
	}
	$sql="update ".CANALPLAN_OPTIONS." set pref_value='".time()."' where blog_id=-1 and pref_code='update_date'";
	$res = $wpdb->query($sql);
 $end = date('H:i:s',(time() - $overallstart));
	print "</table><br>All Done - elapsed time: $end<br/><br/>";
	$do_update=="no button";
	$sql="update ".CANALPLAN_OPTIONS." set pref_value='".time()."' where blog_id=-1 and pref_code='update_date'";
	$r = $wpdb->query("SELECT pref_value FROM  ".CANALPLAN_OPTIONS." where blog_id=-1 and pref_code='update_date'");
	if ($wpdb->num_rows==0) {
	$sql="insert into  ".CANALPLAN_OPTIONS." (pref_value,blog_id,pref_code) values ('".time()."',-1,'update_date')";
	}
	$wpdb->query($sql);
	sleep(2);
	//var_dump($sql);
}
$r2 = $wpdb->get_results("SELECT (".time()." - pref_value) as age, pref_value FROM  ".CANALPLAN_OPTIONS." where blog_id=-1 and pref_code='update_date'",ARRAY_A);
$do_update="no button";

if ($wpdb->num_rows==0) {
 	    $updated="never";
}
else
{
//	var_dump($r2[0]['age']);
	  $updated=$r2[0]['age']/(3600*24);
	  $updatedate=$r2[0]['pref_value'] + ( get_option( 'gmt_offset' ) * 3600 ) ; 
	  $timestring=date(get_option('date_format'),$updatedate).'  at '.date(get_option('time_format'),$updatedate);
}
// Un comment the following line to force Canalplan to think it's data is rather old
//$updated=22;
//var_dump($timestring);
//var_dump($updated);
if ($updated> 14 && $do_update==0) {
	echo "CanalPlan data was last updated over two weeks ago ( on $timestring ) so its probably very out of date. Click on the button to refresh it";
	$do_update="Get New Data";
}

if ($updated=="never") {
	echo "You've not got any CanalPlan data, click on the button below to connect to the CanalPlan Server and get the data";
	$do_update="Get Data";
}

if ( $do_update=="no button" && $updated>0.5) {
	echo "CanalPlan data was last updated ".round($updated,2)." days ago on $timestring. Click on the button to refresh it";
	$do_update="Refresh Data";
}
if ($do_update!="no button"){
?>
<p> This may take several minutes to complete... please be patient</p>
<form action="" name="data_update" id="data_update" method="post">
<p class="submit"> <input type="submit"  value="<?php echo $do_update;?>" /></p>
<input type="hidden" name="update_data" value="1"/>
</form>
<?php } else { echo "<p>CanalPlan data was last updated ".round($updated*24,2) ." hours ago  ( on $timestring ). You cannot refresh it yet</p>" ; }?>
<p><b>Note: </b> The data used by this plugin is pulled from data extracted regularily from the Canalplan Database rather than the live database itself. This means that the data being used by the plugin can be up to 2 hours old when it is pulled over. So if you've added a new place and want to blog about it (or include it in a imported route) then you need to wait a while before doing the import.</p>
<hr>
<h3><?php _e('Distance Format') ?></h3>
<p>This is the default format that will be used when importing routes from Canalplan AC. It can be overridden on a route by route basis:</p>

<?php
$sql=$wpdb->prepare("SELECT pref_value FROM  ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='distance_format'",$blog_id);
$r = $wpdb->get_results($sql,ARRAY_A);
if ($wpdb->num_rows==0) {
     $df="f";
}
else
{

  $df=$r[0]['pref_value'];
}
?>
<form action="" name="distform" id="dist_form" method="post">
<select id="DFSelect" name="dfsel" onchange="set_value(0,DFSelect.value);" >

<?php
$arr = array('k'=> "Decimal Kilometres (3.8 kilometres)", 'M' => "Kilometres and Metres (3 kilometres and 798 metres) ", 'm'=>"Decimal miles (2.3 miles)", 'y'=>"Miles and Yards (2 miles and 634 yards) ",'f'=>"Miles and Furlongs (  2 miles , 2 &#190; flg )");
foreach ($arr as $i => $value) {
	if ($i==$df){ print '<option selected="yes" value="'.$i.'" >'.$arr[$i].'</option>';}
	else {print '<option value="'.$i.'" >'.$arr[$i].'</option>';}
}
?>
</select>
<select id="general_options" style="display:none">
<option value="distance_format"></option>
<option value="cplogin"></option>
</select>
<input type="hidden" name="_submit_check" value="1"/>
<input type="hidden" name="dataset" id="dataset" value="" />
<p class="submit"> <input type="submit" onclick="showValue('general_options')"  value="Save Options" /></p>
</form>
</div>
<hr>
<h3><?php _e('Canalplan Key') ?></h3>
This key allows Canalplan to link back to your blog posts.
<form action="" name="canalapi" id="canalapi" method="post">

<?php
$sql= $wpdb->prepare("SELECT pref_value FROM ".CANALPLAN_OPTIONS." where  blog_id=%d and pref_code='canalkey'",$blog_id);
$r = $wpdb->get_results($sql,ARRAY_A);
if ($wpdb->num_rows==0) {
     $api="";
}
else
{
$api=$r[0]['pref_value'];
}
$url=get_home_url();
$sname=get_bloginfo('name');
if (strlen($api)<4) {

	$x=CANALPLAN_URL.'api.cgi?mode=register_blogger&domain='.$url.'&title='.urlencode($sname);
	$response = canalplan_get_url($x);
	if ( substr($response,0,4)=='ERRO') {
    echo '<div id="message" class="error"><p>' . $response . '</p></div>';
} else {
	$fcheck = $response;
	$cp_register=json_decode($fcheck,true);
	$api=$cp_register['key'];
	$uid=$cp_register['id'];
	echo "<br/>API Key has been set to : <i> ".$api." </i> and is valid for the blog titled:<b> '".$sname."' </b> on the following url : <b> ".$url.'</b><br />';
}	
	echo '<p class="submit"> <input type="submit" name="SCK"  value="Save Canalplan Key" /></p>';
}

else {
	$api=explode("|",$api);
	$api=$api[0];
	$uid=$api[1];
	echo "<br/>API Key currently set to : <i> ".$api." </i> and is valid for the blog titled:<b> '".$sname."' </b> on the following url : <b> ".$url.'</b><br />';
	echo '<p class="submit"><input type="submit" name="RCK" value="Reset Canalplan Key" /></p>';
}

echo '<input type="hidden" name="canalkey" value="'.$api.'|'.$uid.'">';
?>

</form>
<hr>
<h3><?php _e('Route Page Slug') ?></h3>
 The Route Page Slug is the name of the page you are using for your Route Handling page. The page needs to contain the following code to work : {BLOGGEDROUTES}. <br/> <br/>
<?php
if (!defined('CANALPLAN_ROUTE_SLUG')) { ?>
<form action="" name="routeslug" id="routeslug" method="post">

<?php
$sql = $wpdb->prepare("SELECT pref_value FROM  ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='routeslug'",$blog_id);
$r = $wpdb->get_results($sql,ARRAY_A);
if ($wpdb->num_rows==0) {
     $routeslug="UNDEFINED!";
}
else
{
	$routeslug=$r[0]['pref_value'];
}
echo '<input type="text" name="routeslug" maxlength="20" size="20" value="'.$routeslug.'">';
?>
<input type="hidden" name="routes_slug" value="1"/>
<p class="submit"> <input type="submit"  value="Save Route Page Slug" /></p>

</form>
Your current page slug for blogged routes is
<?php
if ($routeslug=="UNDEFINED!") { echo " <b> currently not defined </b> so please set one";} else {

echo "'". $routeslug."' so you need to make sure that <a href='".get_home_url()."/".$routeslug."'>".get_home_url()."/".$routeslug."</a> exists";
}}
else {
?>
The Site Administrator has set the page slug for blogged routes to be  '
<?php
echo CANALPLAN_ROUTE_SLUG."' so you need to make sure that <a href='".get_home_url()."/".CANALPLAN_ROUTE_SLUG."'>".get_home_url()."/".CANALPLAN_ROUTE_SLUG."</a> exists ";
}

?>
<br/><hr><h3><?php _e('Map Type') ?></h3>
 This allows you to chose which map type you use. At the moment there are two map types : <p> Google : Uses Google Map Services<br/>and<br/>Canalplan : Uses the Canalplan Tile Server<br/></p>.


<form action="" name="maptype" id="maptype" method="post">

<?php
$sql = $wpdb->prepare("SELECT pref_value FROM  ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='maptype'",$blog_id);
$r = $wpdb->get_results($sql,ARRAY_A);
if ($wpdb->num_rows==0) {
     $maptype="UNDEFINED!";
}
else
{
	$maptype=$r[0]['pref_value'];
}
$gchecked = '';
$cpchecked = '';
if ($maptype=='google') $gchecked='CHECKED';
if ($maptype=='cpmap') $cpchecked='CHECKED';
print  "<input type='radio' id='google' name='maptype' value='google' $gchecked >";
 print  '<label for="google">Google</label><br>';
print  "<input type='radio' id='cpmap' name='maptype' value='cpmap' $cpchecked >";
 print  '<label for="cpmap">Canalplan</label><br>';
 ?>
<input type="hidden" name="maptype2" value="1"/>
<p class="submit"> <input type="submit"  value="Save Map Type" /></p>

</form>
<hr>
<?php

function parse_data($data,$blid)
{$i=1;
global $wpdb;
  $containers = explode(":", $data);
  foreach($containers AS $container)
  {
      $values = explode("|", $container);
      if ( strlen($values[1])> 0) {
       $sql=$wpdb->prepare("Delete from ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code=%s",$blid,$values[0]);
	 $res = $wpdb->query($sql);
     $sql=$wpdb->prepare("insert into ".CANALPLAN_OPTIONS." set blog_id=%d ,pref_code=%s, pref_value=%s",$blid,$values[0],$values[1]);
     $res = $wpdb->query($sql);
        }
  }
}
?>
