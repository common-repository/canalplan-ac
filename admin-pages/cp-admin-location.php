<?php
/*
Extension Name: Canalplan Home Mooring
Extension URI: http://blogs.canalplan.org.uk/canalplanac/canalplan-plug-in/
Version: 3.0
Description: Home Mooring admin page for the Canalplan AC Plugin
Author: Steve Atty
*/

require_once ('admin.php');
$title = __('Home Mooring');
$this_file =  'canalplan-home.php';
$parent_file = 'canalplan-manager.php';
$base_dir=dirname(__FILE__);
global $blog_id,$wpdb;
echo '<script type="text/javascript"> var linktype=1; cplogid='.$blog_id.'</script>';

if(isset($_POST['_submit_check'])  )
{
//	 var_dump($_POST['dataset']);
  if ($_POST["lati"]<>"Not Set"){
	$sql=$wpdb->prepare("SELECT place_name,canalplan_id,lat,`long`,ST_Length(LineString(lat_lng_point, ST_GeomFromText('Point(".$_POST["lati"]." ".$_POST["longi"].")'))) AS distance FROM ".CANALPLAN_CODES." where attributes != %s ORDER BY distance ASC LIMIT 1", 'm' );
//	print $sql;
	$res = $wpdb->get_results($sql,ARRAY_A);
	//var_dump($res);
	$row=$res[0];
	//var_dump($row);
	}
	switch($_POST['location']) {
	case 'none':
        $dataset="None";
        break;
    case 'Browser':
        $dataset='Browser|'.$_POST["lati"].'|'.$_POST["longi"].'|'.current_time('timestamp').'|0|'.$row['canalplan_id'].'|'.$row['place_name'];
        $cp_lat=$_POST["lati"];
		$cp_long=$_POST["longi"];
        @$cp_user=$_POST['CanalPlanuser'];
        if ($cp_user!='No Key is set') {
			$domain=CANALPLAN_BASE ;
			$url=$domain."/boats/location.php?locat=$cp_user|$cp_lat|$cp_long|0|".current_time('timestamp').'|0';
				$params = array(
				'redirection' => 0,
				'httpversion' => '1.1',
				'timeout' => 200,
				'user-agent' => apply_filters( 'http_headers_useragent', 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) . ';canalplan-' . CANALPLAN_CODE_RELEASE ),
				'headers' => array( 'Expect:' ),
				'sslverify' => false
				);
				echo "<br />";
				$opentype='';
			//$response = wp_remote_get($url,$params);
			//$wp_get_error= is_wp_error( $response ) ;
		//	if ( $wp_get_error) {
			//$error_string = $response->get_error_message();
			//echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
	//	}
	//	if ( !$wp_get_error ) {
	//				$fcheck = $response['body'];
	//	}
		$sql=$wpdb->prepare("Delete from ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='location_error'",$blog_id);
		$res = $wpdb->query($sql);
	//	$sql=$wpdb->prepare("insert into ".CANALPLAN_OPTIONS." set blog_id=%d ,pref_code='location_error', pref_value=%s",$blog_id,$fcheck.'|'.current_time( 'timestamp' ) );
	//	$res = $wpdb->query($sql);
		}
        break;
    case 'Canalplan':
		$dataset="None";
       if (strlen($_POST['dataset'])>4 )
       {
		$dataset='Canalplan|'.$_POST['dataset'].'|'.current_time('timestamp').'|0';
		$values=explode('|',$_POST['dataset']);
		//$values[0]=substr($values[0],1,9999);
		  if (strlen($values[0]) >=5 && substr($values[0],0,1)=="X") $values[0] =  substr($values[0],1,99);
        $sql=$wpdb->prepare("select lat,`long` from ".CANALPLAN_CODES." where canalplan_id=%s",$values[0]);
      //  print $sql;
		$res = $wpdb->get_results($sql,ARRAY_A);
	    $row = $res[0];
	    $cp_lat=$row['lat'];
		$cp_long=$row['long'];
      //  $cp_user=$_POST['CanalPlanuser'];
/*
        if ($cp_user!='No Key is set') {
			$domain=CANALPLAN_BASE ;
			$url=$domain."/boats/location.php?locat=$cp_user|$cp_lat|$cp_long|0|".current_time('timestamp').'|0';
		$params = array(
		'redirection' => 0,
		'httpversion' => '1.1',
		'timeout' => 200,
		'user-agent' => apply_filters( 'http_headers_useragent', 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) . ';canalplan-' . CANALPLAN_CODE_RELEASE ),
		'headers' => array( 'Expect:' ),
		'sslverify' => false
	);
        echo "<br />";
        $opentype='';
	$response = wp_remote_get($url,$params);
	$wp_get_error= is_wp_error( $response ) ;
	if ( $wp_get_error) {
    $error_string = $response->get_error_message();
    echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
}
if ( !$wp_get_error ) {
			$fcheck = $response['body'];
}
			$sql=$wpdb->prepare("Delete from ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='location_error'",$blog_id);
			$res = $wpdb->query($sql);
		//	$sql=$wpdb->prepare("insert into ".CANALPLAN_OPTIONS." set blog_id=%d ,pref_code='location_error', pref_value=%s",$blog_id,$fcheck.'|'.current_time( 'timestamp' ) );
		//	$res = $wpdb->query($sql);
		} */
	   }
        break;
   	case 'Mobile':
        $dataset="From Mobile|".$_POST["lati2"].'|'.$_POST["longi2"].'|'.$_POST["time"] .'|'.$_POST["tz"].'|'.$row['canalplan_id'].'|'.$row['place_name'];
        break;
	default :
	$dataset="None";
	}
	//$dataset.='|'.$_POST['Passthrough'].'|'.$_POST['CanalPlanuser'];

//	var_dump($dataset);
	parse_data($dataset,$blog_id);
}
	echo '<script type="text/javascript"> var linktype=1; cplogid='.$blog_id.'</script>';
	echo '<script type="text/javascript"> var wpcontent="'.plugins_url().'"</script>';

$radcp=" ";
$radbro=" ";
$radnon=" ";
$default_values = array('No Location Source',0,0,time(),0,0,0,'off','No Key is set');
$values=$default_values;
$cploc=" ( No Location Set )";
$lat=$values[1];
$long=$values[2];
$cp_key=$values[8];
$cp_pass=$values[7];
$radback='';

$sql=$wpdb->prepare("select * from ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='Location'",$blog_id);
$res = $wpdb->get_results($sql,ARRAY_A);
if (isset($res[0]))
{ $values=explode('|',$res[0]['pref_value']);
//print_r($values);
//print_r($default_values);
//$values = array_merge($default_values, $values);
$values=$values + $default_values;
}
//print_r($values);
if (strlen($values[4])==0) $values[4]=0;
	switch($values[0]) {
	case 'none':
        $radnon='checked="checked"';
        $cploc=" ( No Location Set )";
        $lat="Not Set";
        $long="Not Set";
        break;
    case 'Browser':
        $radbro='checked="checked"';
        $cploc=" ( No Location Set )";
        $lat=$values[1];
        $long=$values[2];
        $cp_key=$values[8];
        $cp_pass=$values[7];
        break;
    case 'Canalplan':
        $radcp ='checked="checked"';
        $cploc=" (Currently Set to ".stripslashes($values[2])." )";
        $lat="Not Set";
        $long="Not Set";
        $cp_key=$values[6];
        $cp_pass=$values[5];
        break;
    case 'Backitude':
        $radback='checked="checked"';
        $cploc=" ( No Location Set )";
        $lat=$values[1];
        $long=$values[2];
        $cp_key=$values[8];
        $cp_pass=$values[7];
         break;
    case 'GPS Tracker for Android':
        $radback='checked="checked"';
        $cploc=" ( No Location Set )";
        $lat=$values[1];
        $long=$values[2];
        $cp_key=$values[8];
        $cp_pass=$values[7];
        break;
        case 'From Mobile':
        $radback='checked="checked"';
        $cploc=" ( No Location Set )";
        $lat=$values[1];
        $long=$values[2];
        $cp_key=$values[8];
        $cp_pass=$values[7];
        break;
	default :
	$dataset="None";
	}
?>
<script type="text/javascript" src="../wp-content/plugins/canalplan-ac/canalplan/javascript/plan.js"></script>
<script type="text/javascript" src="../wp-content/plugins/canalplan-ac/canalplan/javascript/canalplan_actb.js"></script>
<script type="text/javascript" src="../wp-content/plugins/canalplan-ac/canalplan/javascript/canalplanfunctions.js" DEFER></script>
<script language="JavaScript" type="text/javascript"><!--

async function getCanalPlan2(tag)
{
 code_id= await Canalplan_Download_Code(tag);
 document.getElementById("CanalPlanText").value=tag
 showValue(CanalPlanText.value,code_id);
}

function GetLocation() {
	var options = {
  enableHighAccuracy: false,
  timeout: 2500,
  maximumAge: 10000
};
if("geolocation" in navigator) {
	document.getElementById("lati").value='';
	document.getElementById("longi").value='';
	navigator.geolocation.getCurrentPosition(function(position) {
		document.getElementById("lati").value=position.coords.latitude;
		document.getElementById("longi").value=position.coords.longitude;
		});
	}
 else {
	document.getElementById("geobut").disabled = true;
	document.getElementById("geobut").value="Get from Browser - Disabled";
 }
}
 function showValue(cptext,cpid)
{
  document.getElementById("dataset").value=cpid+"|"+cptext;
}
</script>
<div class="wrap">

<h2><?php _e('Set Location') ?> </h2>
<br />
Current location
<?php
// Put in to fix some problems in multisite when some blogs got a different TZ even though they were all set the same
date_default_timezone_set('UTC');
//var_dump($values);
//var_dump($lat);
//var_dump($long);
echo " ( Set by ".$values[0]." at  ".date("l, j. M. Y, H:i:s", $values[3]+$values[4])." ) "; ?> is : <br /><br />
<b>Latitude : </b> <?php echo $lat; ?> <br />
<b>Longitude :</b> <?php echo $long; ?> <br />
<?php
$checked_flag=array('on'=>'checked','off'=>'');
$location_status=array('0'=>'Error','1'=>'Success ');
$status_colour=array(0=>'red',1=>'green');
if ($lat=='Not Set') {
	print "<b>Canalplan location is set to :</b> <a href='".CANALPLAN_GAZ_URL.$values[1]."' target='_new' > ".stripslashes($values[2])."</a> <br />";
} else {
		$sql=$wpdb->prepare("SELECT place_name,canalplan_id,lat,`long`,ST_Length(LineString(lat_lng_point, ST_GeomFromText('Point(".$lat." ".$long.")'))) AS distance FROM ".CANALPLAN_CODES." where attributes != %s ORDER BY distance ASC LIMIT 1", 'm' );
		$res = $wpdb->get_results($sql,ARRAY_A);
		if(count($res)>0){
			$row=$res[0];
			print "<b>Nearest Canalplan location is :</b> <a href='".CANALPLAN_GAZ_URL.$row['canalplan_id']."' target='_new' > ".$row['place_name']."</a> <br />";
		}
			}
		$sql2 =$wpdb->prepare("SELECT pref_value FROM ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='canalkey'",$blog_id);
		$r = $wpdb->get_results($sql2);
				$url = get_bloginfo('url');
		$img_url=$url.'/wp-content/plugins/canalplan-ac/canalplan/';
       if (isset($r[0])) {
		$url.='/wp-content/plugins/canalplan-ac/cp_location.php?config='.$r[0]->pref_value;
		$config_url=$url; }
		//var_dump($config_url);
?>
<br />
<form action="" name="flid" id="fav_list" method="post">
<input type="hidden" name="tagtypeID" value="ZED" />
<input type="radio" name="location" value="Browser" <?php echo $radbro; ?> >Manually Set :&nbsp;
Lat : <input type="text" name="lati" id="lati" value="<?php echo $lat; ?>" maxlength="12" size="12"> / Long : <input type="text" name="longi" id="longi" value="<?php echo $long; ?>" maxlength="12" size="12">
&nbsp;&nbsp;&nbsp;<input type="button" onclick="GetLocation();" id="geobut" value="Get from Browser"><br />
<input type="radio" name="location" value="Canalplan" <?php echo $radcp; ?> > Use Canalplan :&nbsp; <?php echo $cploc; ?> <input type="text" name="CanalPlanID" ID="CanalPlanID" align="LEFT" size="40" maxlength="90"/>
<INPUT TYPE="button" name="CPsub" VALUE="Set CanalPlan Location"  onclick="getCanalPlan2(CanalPlanID.value);"/><br />
<input type="radio" name="location" value="None" <?php echo $radnon; ?> >Don't Set a Location<br />

<br />
<input type="hidden" name="_submit_check" value="1"/>
<input type="hidden" name="lati2" value="<?php echo $lat; ?>"/>
<input type="hidden" name="longi2" value="<?php echo $long; ?>"/>
<input type="hidden" name="tz" value="<?php echo $values[6]; ?>"/>
<input type="hidden" name="time" value="<?php echo $values[3]; ?>"/>
<input type="hidden" name="dataset" id="dataset" value="" />
<input type="hidden" name="CanalPlanText" ID="CanalPlanText" align="LEFT" size="40" maxlength="90"/>
<br />
<input type="submit"  value="Save Changes" />
</form>
<br />
<hr />
<script language="JavaScript" type="text/javascript">
canalplan_actb(document.getElementById("CanalPlanID"),new Array());
</script>
</div>
<?php

function parse_data($data,$blid) {
	$i=1;
	global $wpdb;
     $sql=$wpdb->prepare("Delete from ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='Location'",$blid);
	 $res = $wpdb->query($sql);
     $sql=$wpdb->prepare("insert into ".CANALPLAN_OPTIONS." set blog_id=%d ,pref_code='Location', pref_value=%s",$blid,$data);
      $res = $wpdb->query($sql);
   // flush the cache if needed.
   if (function_exists('wp_cache_clear_cache') )    wp_cache_clear_cache();
}
?>
