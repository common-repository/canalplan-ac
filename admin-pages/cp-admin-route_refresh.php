<?php
/*
Extension Name: Canalplan Bulk update
Extension URI: http://blogs.canalplan.org.uk/canalplanac/canalplan-plug-in/
Version: 3.0
Description: Bulk notifier page for the Canalplan AC Plugin
Author: Steve Atty
*/

$parent_file = 'canalplan-manager.php';
$title = __('Bulk Link notify');
$this_file = 'cp-admin-update.php';
global $blog_id,$wpdb;
?>
<div class="wrap">
<h2><?php _e('Bulk Link Notifier') ?> </h2>
<?php
//var_dump($_POST);

function bulk_update ($silent) {
	return;
	global $blog_id,$wpdb;
	$sql=$wpdb->prepare("SELECT route_id,totalroute,total_coords FROM ".CANALPLAN_ROUTES." where  blog_id=%d limit 5",$blog_id);
	print $sql;
	$r2 = $wpdb->get_results($sql,ARRAY_A);
	if ($wpdb->num_rows>0) {
		foreach ($r2 as $row) {
			$places=explode(',',$row['totalroute']);
			$coords=explode('|',$row['total_coords']);
			$originalcoords=explode('|',$row['total_coords']);
			print "================================".$row['route_id']."===============================<br/>";
			foreach ($places as $key => $place){
				$sql=$wpdb->prepare("SELECT lat,`long`,place_name FROM ".CANALPLAN_CODES." where canalplan_id=%s",$place);
				$r3 = $wpdb->get_results($sql,ARRAY_A);
				$lat=$r3[0]['lat'];
				$lon=$r3[0]['long'];
				$placename=$r3[0]['place_name'];
				$stored=explode(',',$coords[$key]);
				$storedlat=$stored[0];
				$storedlon=$stored[1];
				$coords[$key]="$lat,$lon";
			//	print "$key $place replacing $storedlat, $storedlon with $lat, $lon <br/>";
				//if ($key > 0 ) {
				//	$latoff = abs($storedlat - $lat);
				//	$longoff = abs($storedlon - $lon);
				//	if ($latoff > 0.01 or $longoff > 0.01) {
					//	print "$placename $place ($lat,$lon) [ $storedlat, $storedlon] is off by $latoff , $longoff <br/>";
					//}
				//}
			}
			$newcoords=implode("|",$coords);

			$coords=explode('|',$newcoords);
			
			foreach ($coords as $key => $place){
				$stored=explode(',',$place);
				$storedlat=$stored[0];
				$storedlon=$stored[1];
				If ($key == 0){
					$lastlat=$storedlat;
					$lastlon=$storedlon;
				}
				if ($key > 0 ) {
					$latoff = abs($storedlat - $lastlat);
					$longoff = abs($storedlon - $lastlon);
					if ($latoff > 0.04 or $longoff > 0.04) {
				//		print "$key ($storedlat,$storedlon) [ $lastlat, $lastlon] is off by $latoff , $longoff <br/>";
						$storedlat=$lastlat;
						$storedlon=$lastlon;
					}
				//	print "replacing $key ".print_r($coords[$key],true)." with $storedlat,$storedlon<br/>";
					$coords[$key]="$storedlat,$storedlon";
					$lastlat=$storedlat;
					$lastlon=$storedlon;
				}
			}
			$newcoords=implode("|",$coords);
		//	print count($coords);
			//print "!!!".count ($originalcoords);
		//	foreach ($originalcoords as $key => $place){
			//	print "$key - $place ".$coords[$key]."<br/>";
			//}
			$sql=$wpdb->prepare("UPDATE ".CANALPLAN_ROUTES." set total_coords =%s where  blog_id=%d and route_id=%s",$newcoords,$blog_id,$row['route_id']);
			$r2 = $wpdb->get_results($sql,ARRAY_A);
			print $sql;
		}
	}
	if ($silent =='N') echo "<br /> <b>All Done !</b><br /><br />";
}

if (isset($_POST["bulkprocess"])){ bulk_update('N'); }

$sql=$wpdb->prepare("SELECT pref_value FROM ".CANALPLAN_OPTIONS." where  blog_id=%s and pref_code='canalkey'",$blog_id);
$r2 = $wpdb->get_results($sql,ARRAY_A);
//var_dump($r2[0]['pref_value']);	
$api='';
if ($wpdb->num_rows>0) {
		$api=$r2[0]['pref_value'];
	}
 if (!isset($_POST["bulkprocess"])){
?>
<br>
Normally Canalplan AC will find out about links into its gazetteer entries from your blog automatically. However if you've just added posts with a lot of Canalplan Links in them you might want to push a list of these links to Canalplan.
<br />
<?php
if (strlen($api)>-1 ) {
?>
<form action="" name="bulkform" id="bulk_form" method="post">
<p>Number of posts to Process :
<select id="plselect" name="plselect">
<?php
for ($i = 1; $i <= CANALPLAN_MAX_POST_PROCESS; $i++) {
echo '<option ';
echo ($i==10) ? 'selected="yes"' : '';
echo ' value="'.$i.'">'.$i.'</option>';
}
?>
</select>
<input type="hidden" name="bulkprocess" id="bulkprocess" value="<?php echo $api ?>" />
</p><p class="submit"> <input type="submit"   value="Bulk Notify" /></p>
<?php
	}
	else
	{
		echo "<br><i>You have not obtained an API Key from Canalplan so you cannot use this option. Go to the General Settings page and obtain one.</i>"; }
}
?>
