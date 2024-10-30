<?php
require_once( '../../../wp-config.php');
global $wpdb,$user_ID,$blog_id;
$cccc=print_r($_POST,true);
file_put_contents('/tmp/location2.sql', $cccc."\n\n", FILE_APPEND);
$pref_id=$_GET['config'];
$system=$_GET['system'];
$sql2 =$wpdb->prepare("SELECT pref_value FROM ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='canalkey'",$blog_id);
$r = $wpdb->get_results($sql2);
$pref_db=$r[0]->pref_value;
$dbids=explode('|',$r[0]->pref_value);
$username=$_POST['username'];
$password=$_POST['password']; 
$url = get_bloginfo('url');
$url.='/wp-content/plugins/canalplan-ac/cp_location.php';
$config_url=$url;
if($system=='backitude'){
if ($pref_id==$pref_db) {
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Content-disposition: attachment; filename=backitude.prefs");
	header("Content-Type: text/xml, application/xml");
	echo "<?xml version='1.0' encoding='UTF-8'?>
<preferences>
<preference>
<id>lastErrorMsg2</id>
<value>gaugler.backitude.service.BackitudeException: Custom Server POST failure: HTTP/1.1 200 OK</value>
</preference>
<preference>
<id>lastErrorMsg1</id>
<value></value>
</preference>
<preference>
<id>offlineSync_flag</id>
<value>true</value>
</preference>
<preference>
<id>savedLocation_time</id>
<value>Apr 05, 2014 11:09:35 AM</value>
</preference>
<preference>
<id>max_steals_interval</id>
<value>300000</value>
</preference>
<preference>
<id>server_key_latitude</id>
<value>latitude</value>
</preference>
<preference>
<id>timezone</id>
<value>false</value>
</preference>
<preference>
<id>server_key_longitude</id>
<value>longitude</value>
</preference>
<preference>
<id>lastErrorTime</id>
<value></value>
</preference>
<preference>
<id>update_toast</id>
<value>true</value>
</preference>
<preference>
<id>steals_enabled</id>
<value>true</value>
</preference>
<preference>
<id>savedLocation_type</id>
<value>Fire</value>
</preference>
<preference>
<id>offline_sync</id>
<value>true</value>
</preference>
<preference>
<id>server_success</id>
<value>201</value>
</preference>
<preference>
<id>server_key_bearing</id>
<value>direction</value>
</preference>
<preference>
<id>min_accuracy</id>
<value>true</value>
</preference>
<preference>
<id>wifi_lock</id>
<value>true</value>
</preference>
<preference>
<id>resync_interval</id>
<value>0</value>
</preference>
<preference>
<id>offlineSync_toast</id>
<value>true</value>
</preference>
<preference>
<id>server_key_custom2</id>
<value></value>
</preference>
<preference>
<id>server_custom1</id>
<value></value>
</preference>
<preference>
<id>minimum_distance</id>
<value>100</value>
</preference>
<preference>
<id>server_key_custom1</id>
<value></value>
</preference>
<preference>
<id>server_custom2</id>
<value></value>
</preference>
<preference>
<id>lastErrorTime2</id>
<value>Apr 05, 2014 12:08:38 PM</value>
</preference>
<preference>
<id>polledLocation_long</id>
<value>-2.0471606</value>
</preference>
<preference>
<id>server_user_name</id>
<value>$dbids[1]</value>
</preference>
<preference>
<id>fallbackOptions</id>
<value>2</value>
</preference>
<preference>
<id>realtime</id>
<value>true</value>
</preference>
<preference>
<id>savedLocation_UpdateTime</id>
<value>Apr 05, 2014 11:09:35 AM</value>
</preference>
<preference>
<id>realtime_interval</id>
<value>300000</value>
</preference>
<preference>
<id>isAlarmRunning</id>
<value>false</value>
</preference>
<preference>
<id>appEnabled</id>
<value>false</value>
</preference>
<preference>
<id>syncOptions</id>
<value>3</value>
</preference>
<preference>
<id>interval</id>
<value>300000</value>
</preference>
<preference>
<id>server_url</id>
<value>$config_url</value>
</preference>
<preference>
<id>wifi_mode_interval</id>
<value>1800000</value>
</preference>
<preference>
<id>wake_lock</id>
<value>true</value>
</preference>
<preference>
<id>server_key_req_timestamp</id>
<value>req_timestamp</value>
</preference>
<preference>
<id>server_request_type</id>
<value>1</value>
</preference>
<preference>
<id>server_password</id>
<value>$dbids[0]</value>
</preference>
<preference>
<id>wifi_mode_timeout_interval</id>
<value>15000</value>
</preference>
<preference>
<id>server_key_accuracy</id>
<value>accuracy</value>
</preference>
<preference>
<id>display_error</id>
<value>true</value>
</preference>
<preference>
<id>wifi_mode</id>
<value>false</value>
</preference>
<preference>
<id>server_key_password</id>
<value>password</value>
</preference>
<preference>
<id>isReSyncRunning</id>
<value>false</value>
</preference>
<preference>
<id>savedLocation_lat</id>
<value>51.90309</value>
</preference>
<preference>
<id>wifiOnly_enabled</id>
<value>false</value>
</preference>
<preference>
<id>savedLocation_long</id>
<value>-2.0471606</value>
</preference>
<preference>
<id>minGpsAccuracy</id>
<value>50</value>
</preference>
<preference>
<id>export</id>
<value>3</value>
</preference>
<preference>
<id>server_key_username</id>
<value>username</value>
</preference>
<preference>
<id>server_key_altitude</id>
<value>altitude</value>
</preference>
<preference>
<id>gpsOption</id>
<value>1</value>
</preference>
<preference>
<id>server_key_speed</id>
<value>speed</value>
</preference>
<preference>
<id>realtimeRunning</id>
<value>false</value>
</preference>
<preference>
<id>polledLocation_accur</id>
<value>29.037</value>
</preference>
<preference>
<id>polledLocation_lat</id>
<value>51.90309</value>
</preference>
<preference>
<id>server_key_timezone</id>
<value>offset</value>
</preference>
<preference>
<id>server_key_loc_timestamp</id>
<value>loc_timestamp</value>
</preference>
<preference>
<id>accountName</id>
<value>canalplan@gmail.com</value>
</preference>
<preference>
<id>polledLocation_date</id>
<value>1396696175571</value>
</preference>
<preference>
<id>authentication</id>
<value>2</value>
</preference>
<preference>
<id>server_key_account</id>
<value></value>
</preference>
<preference>
<id>realtime_timeout_interval</id>
<value>15000</value>
</preference>
<preference>
<id>timeout_interval</id>
<value>15000</value>
</preference>
<preference>
<id>minWifiAccuracy</id>
<value>100</value>
</preference>
<preference>
<id>statusBar</id>
<value>2</value>
</preference>
<preference>
<id>savedLocation_accur</id>
<value>29.037</value>
</preference>
<preference>
<id>isCharging</id>
<value>false</value>
</preference>
</preferences>
";
}
}
if($system=='gpslogger'){
if ($pref_id==$pref_db) {	
		$objDateTime = new DateTime('NOW');
	$timecreated=$objDateTime->format(DateTime::RFC2822); 
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Content-disposition: attachment; filename=CanalplanBlog.properies");
	header('Content-Type: text/html; charset=utf-8');
	$config_url=str_replace(":","\:",$config_url);
	echo "#Warning: This file can contain server names, passwords, email addresses and other sensitive information.
#$timecreated
log_geojson=false
log_gpx=false
autoftp_useftps=false
log_customurl_headers=Content-Type\: application/x-www-form-urlencoded
gdocs_enabled=false
last_version_seen=102
hide_notification_buttons=false
current_profile_name=CanalplanBlog
new_file_custom_name=gpslogger
opengts_server_communication_method=
osm_enabled=false
autosend_frequency_whenstoppressed=false
distance_before_logging=100
smtp_username=
owncloud_directory=gpslogger
startonapplaunch=false
log_gpx_11=false
opengts_server_port=
keep_fix=false
osm_description=
absolute_timeout=120
hide_notification_from_status_bar=false
log_customurl_url=$config_url
new_file_creation=onceaday
new_file_custom_each_time=true
autoftp_directory=GPSLogger
autosend_sendzip=true
log_kml=false
autoopengts_enabled=false
autoftp_port=21
log_satellite_locations=true
startonbootup=false
osm_visibility=private
accuracy_before_logging=40
autoftp_implicit=false
opengts_accountname=
gdocs_foldername=GPSLogger for Android
dropbox_enabled=false
autoemail_enabled=false
log_customurl_basicauth_password=eibblrcvgg
GDRIVE_ACCOUNT_NAME=
new_file_custom_keep_changing=true
log_customurl_method=POST
owncloud_enabled=false
autoftp_ssltls=
autosend_wifionly=false
useImperial=false
log_opengts=false
log_network_locations=false
altitude_subtractoffset=0
autosend_enabled=false
latlong_display_format=DEGREES_MINUTES_SECONDS
new_file_prefix_serial=true
log_customurl_enabled=true
log_customurl_body=lat\=%LAT&lon\=%LON&time\=%TIMESTAMP&username\=$dbids[1]&password\=$dbids[0]&accuracy\=%ACC&speed\=%SPD&altitude\=%ALT&prov\=GPStracker
autoftp_username=
smtp_ssl=true
retry_time=60
autosend_frequency_minutes=60
autoftp_enabled=false
time_before_logging=60
activityrecognition_dontlogifstill=false
";
}
}
$x=print_r($_POST,true);
//file_put_contents('/tmp/2.txt', $x);
$providerlist = array("Backitude"=>"Backitude", "GPStracker"=>"GPS Tracker for Android", "Joe"=>"43");

if ($username==$dbids[1] && $password==$dbids[0]) {
	file_put_contents('/tmp/location2.sql', "Got here\n\n", FILE_APPEND);
	$latitude=0.0;
	$longitude=0.0;
	$loc_timestamp=0;
	$direction=0.0;
	$speed=0.0;
	$altitude=0.0;
	$accuracy=0;
	$offset='+0:00';
	$updated_ok='N';
	if (is_numeric($_POST['latitude'])) $latitude=$_POST['latitude'];
	if (is_numeric($_POST['longitude'])) $longitude=$_POST['longitude'];
	if (is_numeric($_POST['loc_timestamp'])) $loc_timestamp=$_POST['loc_timestamp'];
	if (is_numeric($_POST['direction'])) $direction=$_POST['direction'];
	if (is_numeric($_POST['speed'])) $speed=$_POST['speed'];
	if (is_numeric($_POST['altitude'])) $altitude=$_POST['altitude'];
	if (is_numeric($_POST['accuracy'])) $accuracy=$_POST['accuracy'];
	$providerkey='Backitude';
	
	if (is_numeric($_POST['lat'])) $latitude=$_POST['lat'];
	if (is_numeric($_POST['lon'])) $longitude=$_POST['lon'];
	if (is_numeric($_POST['time'])) $loc_timestamp=$_POST['time']+ ( get_option( 'gmt_offset' ) * 3600 ) ;
		if (is_numeric($_POST['time'])) $loc_timestamp=$_POST['time'] ;
	if (is_numeric($_POST['direction'])) $direction=$_POST['direction'];
	if (is_numeric($_POST['speed'])) $speed=$_POST['speed'];
	if (is_numeric($_POST['altitude'])) $altitude=$_POST['altitude'];
	if (is_numeric($_POST['accuracy'])) $accuracy=$_POST['accuracy'];
	if (strlen($_POST['prov'])>4 ) $providerkey=$_POST['prov'];
    $provider=$providerlist[$providerkey];
	$sql=$wpdb->prepare("select * from ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='Location'",$blog_id);
	$res = $wpdb->get_results($sql,ARRAY_A);
	$values=explode('|',$res[0]['pref_value']);
	$sql=$wpdb->prepare("Delete from ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='Location'",$blog_id);
	$res = $wpdb->query($sql);
	$offset=explode(":",$offset);
	$plus_min=substr($offset[0],0,1);
	$hours=substr($offset[0],1);
	$tz_offset=($hours*3600)+($offset[1]*60);
	if ($plus_min=='-') $tzoff=0-$tz_offset;
	if ($plus_min=='+') $tzoff=0+$tz_offset;
	$sql=$wpdb->prepare("SELECT place_name,canalplan_id,lat,`long`,ST_Length(LineString(lat_lng_point, ST_GeomFromText('Point(".$latitude." ".$longitude.")'))) AS distance FROM ".CANALPLAN_CODES." where attributes != %s ORDER BY distance ASC LIMIT 1", 'm' );
	$res = $wpdb->get_results($sql,ARRAY_A);
	$row=$res[0];
	$data=$provider.'|'.$latitude.'|'.$longitude.'|'.$loc_timestamp.'|'.$tzoff.'|'.$row['canalplan_id'].'|'.$row['place_name'].'|'.$values[7].'|'.$values[8];
	$sql=$wpdb->prepare("insert into ".CANALPLAN_OPTIONS." set blog_id=%d ,pref_code='Location', pref_value=%s",$blog_id,$data);
	$res = $wpdb->query($sql);
	if  ( $res ) $updated_ok='Y';
	if ($values[7]=='on') {
		if ($plus_min=='-') $tzoff=0-$tz_offset;
		if ($plus_min=='+') $tzoff=0+$tz_offset;
		$domain=CANALPLAN_BASE ;
		$url=$domain."/boats/location.php?locat=$values[8]|$latitude|$longitude|$accuracy|$loc_timestamp|$tzoff";
		file_put_contents('/tmp/url.txt', $url);
		$fcheck=file_get_contents($url);
	//	file_put_contents('/tmp/fcheck.txt', $fcheck);
		$sql=$wpdb->prepare("Delete from ".CANALPLAN_OPTIONS." where blog_id=%d and pref_code='location_error'",$blog_id);
		$res = $wpdb->query($sql);
		$sql=$wpdb->prepare("insert into ".CANALPLAN_OPTIONS." set blog_id=%d ,pref_code='location_error', pref_value=%s",$blog_id,$fcheck.'|'.current_time( 'timestamp' ) );
		$res = $wpdb->query($sql);
		 // flush the cache if needed.
		if (function_exists('wp_cache_clear_cache') )    wp_cache_clear_cache();
		if  ( $res ) $updated_ok='Y';
	}
	if  ( $updated_ok=='Y' ) header(' ', true, 201);
}
?>
