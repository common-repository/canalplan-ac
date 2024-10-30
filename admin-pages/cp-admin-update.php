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
	global $blog_id,$wpdb;
	$sql=$wpdb->prepare("SELECT pref_value FROM ".CANALPLAN_OPTIONS." where  blog_id=%s and pref_code='canalkey'",$blog_id);
	$r2 = $wpdb->get_results($sql,ARRAY_A);
	// Only do the update if we've got an canalkey
	if ($wpdb->num_rows>0) {
		$api=explode('|',$r2[0]['pref_value']);
		$query=$wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_status='publish' and (post_type='post' or post_type='page') order by ID desc limit %d",$_POST['plselect']);
		$r = $wpdb->get_results($query,ARRAY_A);
		foreach ($r as $rw) {
			update_canalplan($rw['ID'],$silent,1000,$api);
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
if (strlen($api)>4 ) {
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
