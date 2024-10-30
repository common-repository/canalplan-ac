<?php
/*
Plugin Name: CanalPlan Integration
Plugin URI: https://blogs.tty.org.uk/canalplan-plug-in/
Description: Provides features to integrate your blog with <a href="https://www.canalplan.org.uk">Canalplan AC</a> - the Canal Route Planner.
Version: 5.31
Author: Steve Atty
Author URI: https://steve.tty.org.uk
 *
 *
 * Copyright 2011 - 2024 Steve Atty (email : posty@tty.org.uk)
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (file_exists(WP_CONTENT_DIR . "/uploads/canalplan_multisite.php")) {
	@include (WP_CONTENT_DIR . "/uploads/canalplan_multisite.php");
}
defined('CANALPLAN_BASE') or define('CANALPLAN_BASE', 'https://canalplan.uk');
define('CANALPLAN_URL', CANALPLAN_BASE . '/cgi-bin/');
define('CANALPLAN_GAZ_URL', CANALPLAN_BASE . '/place/');
define('CANALPLAN_WAT_URL', CANALPLAN_BASE . '/waterway/');
define('CANALPLAN_FEA_URL', CANALPLAN_BASE . '/feature/');
define('CANALPLAN_MAX_POST_PROCESS', 100);
define('CANALPLAN_CODE_RELEASE', '5.31 r00');
defined('MAPSERVER_BASE') or define('MAPSERVER_BASE', 'https://maps.tty.org.uk');
define('CANALPLAN_DB_VERSION', 3);
define('MAPLIBRE_VERSION', "4.1.3");

global $table_prefix, $wp_version, $wpdb, $db_prefix, $canalplan_run_canal_link_maps, $canalplan_run_canal_route_maps, $canalplan_run_canal_place_maps, $maptype;
$canalplan_run = array();
# Determine the right table prefix to use
$cp_table_prefix = $wpdb->base_prefix;
if (isset ($db_prefix)) {
	$cp_table_prefix = $db_prefix;
}

define('CANALPLAN_OPTIONS', $cp_table_prefix . 'canalplan_options');
define('CANALPLAN_ALIASES', $cp_table_prefix . 'canalplan_aliases');
define('CANALPLAN_CODES', $cp_table_prefix . 'canalplan_codes');
define('CANALPLAN_FAVOURITES', $cp_table_prefix . 'canalplan_favourites');
define('CANALPLAN_LINK', $cp_table_prefix . 'canalplan_link');
define('CANALPLAN_CANALS', $cp_table_prefix . 'canalplan_canals');
define('CANALPLAN_ROUTES', $cp_table_prefix . 'canalplan_routes');
define('CANALPLAN_POLYLINES', $cp_table_prefix . 'canalplan_polylines');
define('CANALPLAN_ROUTE_DAY', $cp_table_prefix . 'canalplan_route_day');

function between($x, $lim1, $lim2)
{
	if ($lim1 < $lim2) {
		$lower = $lim1;
		$upper = $lim2;
	} else {
		$lower = $lim2;
		$upper = $lim1;
	}
	return (($x >= $lower) && ($x <= $upper));
}

function canalplan_get_url($get_url)
{
	global $wp_version;
	$params = array(
		'redirection' => 0,
		'httpversion' => '1.1',
		'timeout' => 2000,
		'read_timeout' => 2000,
		'connect_timeout' => 2000,
		'user-agent' => apply_filters('http_headers_useragent', 'WordPress/' . $wp_version . '; ' . get_bloginfo('url') . ';canalplan-' . CANALPLAN_CODE_RELEASE),
		'headers' => array('Expect:'),
		'sslverify' => false
	);
	$response = wp_remote_get($get_url, $params);
	$wp_get_error = is_wp_error($response);
	if ($wp_get_error) {
		$error_string = $response->get_error_message();
		echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
		$data = 'ERROR: ' . $error_string;
	}
	if (!$wp_get_error) {
		$data = $response['body'];
	}
	return $data;
}
function ascii_encode($numb)
{
	//echo $numb . "<br>";
	$numb = $numb << 1;
	if ($numb < 0) {
		$numb = ~$numb;
	}
	return ascii_encode_helper($numb);
}

function ascii_encode_helper($numb)
{
	$string = "";
	$count = 0;
	while ($numb >= 0x20) {
		$count++;
		$string .= (pack("C", (0x20 | ($numb & 0x1f)) + 63));
		$numb = $numb >> 5;
	}
	$string .= pack("C", $numb + 63);
	return str_replace("\\", "\\\\", $string);
}

function canalplan_mobile()
{
	if (function_exists('jetpack_is_mobile'))
		return jetpack_is_mobile();
	return wp_is_mobile();
}

function isMobileBrowser()
{
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match('/(android|bbd+|meego).+mobile|avantgo|bada/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)/|plucker|pocket|psp|series(4|6)0|symbian|treo|up.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw-(n|u)|c55/|capi|ccwa|cdm-|cell|chtm|cldc|cmd-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc-s|devi|dica|dmob|do(c|p)o|ds(12|-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(-|_)|g1 u|g560|gene|gf-5|g-mo|go(.w|od)|gr(ad|un)|haie|hcit|hd-(m|p|t)|hei-|hi(pt|ta)|hp( i|ip)|hs-c|ht(c(-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i-(20|go|ma)|i230|iac( |-|/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |/)|klon|kpt |kwc-|kyo(c|k)|le(no|xi)|lg( g|/(k|l|u)|50|54|-[a-w])|libw|lynx|m1-w|m3ga|m50/|ma(te|ui|xo)|mc(01|21|ca)|m-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|-([1-8]|c))|phil|pire|pl(ay|uc)|pn-2|po(ck|rt|se)|prox|psio|pt-g|qa-a|qc(07|12|21|32|60|-[2-7]|i-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55/|sa(ge|ma|mm|ms|ny|va)|sc(01|h-|oo|p-)|sdk/|se(c(-|0|1)|47|mc|nd|ri)|sgh-|shar|sie(-|m)|sk-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h-|v-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl-|tdg-|tel(i|m)|tim-|t-mo|to(pl|sh)|ts(70|m-|m3|m5)|tx-9|up(.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas-|your|zeto|zte-/i', substr($useragent, 0, 4)))
		return true;
}

function format_distance($distance, $locks, $format, $short)
{
	$totalfeet = ($distance * 3.2808399);
	$wholemiles = floor((($totalfeet) / 5280));
	$wholekm = floor($distance / 1000);
	$decmiles = round((($distance * 3.2808399) / 5280), 2);
	$remmeters = ((($distance / 1000) - floor($distance / 1000)) * 1000);
	$remyards = ceil((((($totalfeet) / 5280) - $wholemiles) * 1760));
	if ($remyards == 1760) {
		$remyards = 0;
	}
	$remfurls = round((((($totalfeet) / 5280) - $wholemiles) * 8), 2);
	$wholefurls = floor($remfurls);
	$fractfurls = $remfurls - $wholefurls;
	$furltext = ", ";
	$fracttext = "";
	$miletext = " miles";
	$yfractext = "";
	$ytext = " yards";
	$ktext = " kilometres";
	$mfractext = "";
	$mtext = " metres";
	if ($wholemiles == 1) {
		$miletext = " mile";
	}
	if ($remyards > 0) {
		$yfractext = ", ";
	}
	if ($remyards == 0) {
		$remyards = "";
		$ytext = "";
	}
	if ($remyards == 1) {
		$ytext = " yard";
	}

	if ($wholekm == 1) {
		$ktext = " kilometer";
	}
	if ($remmeters > 0) {
		$mfractext = ", ";
	}
	if ($remmeters == 0) {
		$remmeters = "";
		$mtext = "";
	}
	if ($remmeters == 1) {
		$mtext = " meter";
	}

	if ($wholefurls == 0) {
		$wholefurls = "";
	}
	if ($fractfurls < 0.25) {
		$fractfurls = 0;
	}
	if (($fractfurls >= 0.25) && ($fractfurls < 0.5)) {
		$fracttext = "&#188;";
	}
	if (($fractfurls >= 0.5) && ($fractfurls < 0.75)) {
		$fracttext = "&#189;";
	}
	if (($fractfurls >= 0.75) && ($fractfurls < 1)) {
		$fracttext = "&#190;";
	}
	$fracttext .= " flg";
	if ($wholefurls == 8) {
		$wholemiles = $wholemiles + 1;
		$wholefurls = 0;
	}
	if (($fractfurls == 0) && ($wholefurls == 0)) {
		$fracttext = "";
		$furltext = "";
	}
	if ($wholefurls == 0) {
		$wholefurls = "";
	}
	$dist_text = "";
	$furltext .= $wholefurls . $fracttext;
	if ($short != 1)
		$dist_text = "a distance of ";
	if ($short == 3)
		$dist_text = "A total distance of ";
	switch ($format) {
		case "k":
			$dist_text .= round($distance / 1000, 2) . $ktext;
			break;
		case "M":
			$dist_text .= $wholekm . $ktext . $mfractext . $remmeters . $mtext;
			break;
		case "m":
			$dist_text .= $decmiles . $miletext;
			break;
		case "y":
			$dist_text .= $wholemiles . $miletext . $yfractext . $remyards . $ytext;
			break;
		default:
			$dist_text .= $wholemiles . $miletext . $furltext;
	}
	if ($locks == 1) {
		$dist_text .= " and 1 lock";
	}
	if ($locks > 1) {
		$dist_text .= " and " . $locks . " locks";
	}
	return $dist_text;
}

function update_canalplan($post_id, $silent, $sleep, $api)
{
	global $blog_id, $wpdb;
	$blog_url = get_bloginfo('url');
	$bulkpost = get_post($post_id);
	$date = date("Ymd", strtotime($bulkpost->post_date));
	$link = urlencode(str_replace($blog_url, "", get_permalink($post_id)));
	if ($silent == 'N')
		echo "<br />Processing Post <i>" . $bulkpost->post_title . "</i><br />";
	$postcontent = $bulkpost->post_content;
	$postcontent = canal_stats($postcontent, $blog_id, $post_id);
	if (preg_match_all('/' . preg_quote('[[CP:') . '(.*?)' . preg_quote(']]') . '/', $postcontent, $matches)) {
		$places_array = $matches[1];
		foreach ($places_array as $place) {
			set_time_limit(20);
			$placeinfo = explode('|', $place);
			$x = CANALPLAN_URL . 'api.cgi?mode=add_bloglink&id=' . $api[1] . '&key=' . $api[0] . '&title=' . urlencode($bulkpost->post_title) . '&placeid=' . $placeinfo[1];
			$x .= '&url=' . $link . '&date=' . $date;
			$fcheck = canalplan_get_url($x);
			$cp_bulk = json_decode($fcheck, true);
			$refresh = CANALPLAN_BASE . '/utilities/enqueue_refresh.php?cpid=' . $placeinfo[1];
			$fcheck2 = canalplan_get_url($refresh);
			if ($silent == 'N')
				echo "&nbsp;&nbsp;&nbsp;Found link to <i>" . $placeinfo[0] . "</i>";
			if ($cp_bulk['status'] == 'OK') {
				if ($silent == 'N') {
					echo " and ", $cp_bulk['detail'] . ' the link ';
					echo ($cp_bulk['detail'] == 'added') ? "to" : "in";
					echo ' CanalPlan AC<br />';
				}
			} else {
				if ($silent == 'N')
					echo "&nbsp;&nbsp;&nbsp;<b>A problem occurred : " . $cp_bulk['status'] . " - " . $cp_bulk['detail'] . "</b><br />";
			}
			#Sleep for 10ms just to stop us swamping the server.
			usleep($sleep);
		}
	} else {
		if ($silent == 'N')
			echo "&nbsp;&nbsp;&nbsp;No Canalplan Links Found<br />";
	}
}


function recalculate_route_day($blog_id, $route_id, $day_id)
{
	global $wpdb;
	//	echo "<br /> Doing $day_id <br />";
	$sql = $wpdb->prepare("Select totalroute from " . CANALPLAN_ROUTES . " where blog_id=%d and route_id=%d", $blog_id, $route_id);
	$r = $wpdb->get_results($sql, ARRAY_A);
	$totalroute = $r[0]["totalroute"];
	$sql = $wpdb->prepare("Select start_id,end_id,flags from " . CANALPLAN_ROUTE_DAY . " where blog_id=%d and route_id=%d and day_id=%d", $blog_id, $route_id, $day_id);
	$r = $wpdb->get_results($sql, ARRAY_A);
	$rw = $r[0];
	$route = explode(",", $totalroute);
	$dayroute = array_slice($route, $rw['start_id'], ($rw['end_id'] - $rw['start_id']) + 1);
	$stopafterlocktoday = $rw['flags'];
	$stopafterlockyesterday = 'X';
	$newlocks = 0;
	$newdistance = 0;
	if ($day_id > 1) {
		$sql = $wpdb->prepare("Select start_id,end_id,flags from " . CANALPLAN_ROUTE_DAY . " where blog_id=%d and route_id=%d and day_id=%d", $blog_id, $route_id, $day_id - 1);
		$r = $wpdb->get_results($sql, ARRAY_A);
		$rw = $r[0];
		$stopafterlockyesterday = $rw['flags'];
	}
	if (strlen($stopafterlocktoday) == 0)
		$stopafterlocktoday = 'X';
	if (strlen($stopafterlockyesterday) == 0)
		$stopafterlockyesterday = 'X';
	for ($placeindex = 1; $placeindex < count($dayroute); $placeindex += 1) {
		$p1 = $dayroute[$placeindex];
		$p2 = $dayroute[$placeindex - 1];
		$sql = $wpdb->prepare("select distinct metres,locks from " . CANALPLAN_LINK . " where (place1=%s and place2=%s) or  (place1=%s and place2=%s )", $p1, $p2, $p2, $p1);
		//		 print "<br/>$sql<br/>";
		$r = $wpdb->get_results($sql, ARRAY_A);
		//	print_r($r);
		if (count($r) > 0) {
			$rl = $r[0];
			if (is_null($rl['locks']))
				$rl['locks'] = 0;
			if (is_null($rl['metres']))
				$rl['metres'] = 10;
			$newlocks = $newlocks + $rl['locks'];
			$newdistance = $newdistance + $rl['metres'];
		}
	}
	for ($placeindex = 0; $placeindex < count($dayroute); $placeindex += 1) {
		$x = $dayroute["$placeindex"];
		$sql = $wpdb->prepare("select attributes from " . CANALPLAN_CODES . " where canalplan_id=%s", $x);
		$r = $wpdb->get_results($sql, ARRAY_A);
		$rw = $r[0];

		if (strpos($rw['attributes'], 'L') !== false) {
			if ($stopafterlockyesterday == 'L' and $placeindex == 0)
				$newlocks = $newlocks;
			if ($stopafterlockyesterday == 'X' and $placeindex == 0)
				$newlocks = $newlocks + 1;
			if ($stopafterlocktoday == 'X' and $placeindex == count($dayroute) - 1)
				$newlocks = $newlocks;
			if ($stopafterlocktoday == 'L' and $placeindex == count($dayroute) - 1)
				$newlocks = $newlocks + 1;
			if ($placeindex > 0 and $placeindex < count($dayroute) - 1)
				$newlocks = $newlocks + 1;
		}
		preg_match_all('!\d+!', $rw['attributes'], $matches);
		$lock_count = 0;
		if (isset ($matches[0][0]))
			$lock_count = $matches[0][0];

		if (!is_null($lock_count)) {
			if ($stopafterlockyesterday == 'L' and $placeindex == 0)
				$newlocks = $newlocks;
			if ($stopafterlockyesterday == 'X' and $placeindex == 0)
				$newlocks = $newlocks + $lock_count;
			if ($stopafterlocktoday == 'X' and $placeindex == count($dayroute) - 1)
				$newlocks = $newlocks;
			if ($stopafterlocktoday == 'L' and $placeindex == count($dayroute) - 1)
				$newlocks = $newlocks + $lock_count;
			if ($placeindex > 0 and $placeindex < count($dayroute) - 1)
				$newlocks = $newlocks + $lock_count;
		}


		if (!isset ($rw['locks'])) {
			$rw['locks'] = 0;
		}
		$newlocks = $newlocks + $rw['locks'];
	}
	$all_coords = '';
	foreach ($dayroute as $oneplace) {
		$sql = $wpdb->prepare("select lat,`long` from " . CANALPLAN_CODES . " where canalplan_id = %s ", $oneplace);
		$r = $wpdb->get_results($sql, ARRAY_A);
		$rx = $r[0];
		$all_coords .= '|' . $rx['lat'] . ',' . $rx['long'];
	}
	$all_coords = trim($all_coords, '|');
	$sql = $wpdb->prepare("update " . CANALPLAN_ROUTE_DAY . " set distance=%d , locks=%d, day_coords = %s where blog_id=%d and route_id=%d and day_id=%d", $newdistance, $newlocks, $all_coords, $blog_id, $route_id, $day_id);
	$r = $wpdb->query($sql);
}

function recalculate_route($blog_id, $route_id)
{
	global $wpdb;
	$sql = $wpdb->prepare("select totalroute from " . CANALPLAN_ROUTES . " where blog_id=%d and route_id=%d ", $blog_id, $route_id);
	$res = $wpdb->get_results($sql, ARRAY_A);
	$row = $res[0];
	$places = explode(",", $row['totalroute']);
	$all_coords = '';
	foreach ($places as $oneplace) {
		$sql = $wpdb->prepare("select lat,`long` from " . CANALPLAN_CODES . " where canalplan_id = %s ", $oneplace);
		$r = $wpdb->get_results($sql, ARRAY_A);
		$rx = $r[0];
		$all_coords .= '|' . $rx['lat'] . ',' . $rx['long'];
	}
	$all_coords = trim($all_coords, '|');
	$sql = $wpdb->prepare("update " . CANALPLAN_ROUTES . " set total_coords=%s where route_id=%d and blog_id=%d", $all_coords, $route_id, $blog_id);
	$r2 = $wpdb->query($sql);
}

function canalplan_add_custom_box()
{
	add_meta_box(
		'canalplan_sectionid',
		__('CanalPlan Tags', 'canalplan_textdomain'),
		'canalplan_inner_custom_box',
		'post',
		'advanced'
	);
	add_meta_box(
		'canalplan_sectionid',
		__('CanalPlan Tags', 'canalplan_textdomain'),
		'canalplan_inner_custom_box',
		'page',
		'advanced'
	);
}

/* Prints the inner fields for the custom post/page section */
function canalplan_inner_custom_box()
{
	echo '<input type="hidden" name="canalplan_noncename" id="canalplan_noncename" value="' .
		wp_create_nonce(plugin_basename(__FILE__)) . '" />';
	global $wpdb, $blog_id;
	echo '<script type="text/javascript"> var linktype=1; cplogid=' . $blog_id . '</script>';
	echo '<script type="text/javascript"> var wpcontent="' . plugins_url() . '"</script>';
	echo '<script type="text/javascript" src="../wp-content/plugins/canalplan-ac/canalplan/javascript/canalplanfunctions.js" DEFER></script>';
	echo '<script type="text/javascript" src="../wp-content/plugins/canalplan-ac/canalplan/javascript/canalplan_actb.js"></script>';
	echo "Insert : ";
	$sql = $wpdb->prepare("SELECT place_name FROM " . CANALPLAN_FAVOURITES . " where blog_id=%d order by place_order asc", $blog_id);
	$blog_favourites = $wpdb->get_results($sql);
	if (count($blog_favourites) > 0) {
		print '<select name="blogfav" onchange="CanalPlanID.value=blogfav.options[blogfav.selectedIndex].value">';
		print '<option value="" selected>Select Favourite</option>';
		foreach ($blog_favourites as $fav) {
			print '<option value="' . stripslashes($fav->place_name) . '">' . stripslashes($fav->place_name) . '</option>';
		}
		print "</select>";
	}
	$wp_version = get_bloginfo('version');

	print ' <input type="text" ID="CanalPlanID" align="LEFT" size="50" maxlength="100" autocomplete="off"/> as';
	print '  <select name="tagtype" ID="tagtypeID"> <option value="CP" selected>Gazetteer Tag</option> <option value="CPGM">Google Map Tag</option> </select>';

	print ' <INPUT TYPE="button" name="CPsub" VALUE="Insert tag"  onclick="getCanalPlan(CanalPlanID.value);"/>';
	echo "<br />Insert : ";
	$sql = $wpdb->prepare("SELECT route_id,title FROM " . CANALPLAN_ROUTES . " where blog_id=%d order by route_id desc", $blog_id);
	$blog_routes = $wpdb->get_results($sql);
	if (count($blog_favourites) > 0) {
		print '<select name="blogroute" onchange="CanalRouteID.value=blogroute.options[blogroute.selectedIndex].text">';
		print '<option value="" selected>Select Route</option>';
		foreach ($blog_routes as $route) {
			print '<option value="' . $route->route_id . '" name="' . $route->title . '">' . $route->title . '</option>';
		}
		print "</select>";
	}
	print ' <input type="text" disabled="disabled" ID="CanalRouteID" align="LEFT" size="50" maxlength="100"/> as';
	print '  <select name="routetagtype" ID="routetagtypeID"> <option value="CPTS" selected>Trip Statistics </option> <option value="CPTD" selected>Trip Details (Overnight Stops) </option> <option value="CPTM">Trip Map</option> <option value="CPTO">Trip Map (Overnight Stops)</option> <option value="CPTL">List of Links to Trip Blog Posts</option></select>';
	print ' <INPUT TYPE="button" name="CPsub2" VALUE="Insert tag"  onclick="getCanalRoute(blogroute.options[blogroute.selectedIndex].value);"/>';
	print ' <br> Update Canalplan when publishing this post : <INPUT TYPE="checkbox" name="UpdateCanalplan" VALUE="UpdateCanalplan" />';
	print '<script>canalplan_actb(document.getElementById("CanalPlanID"),new Array());</script>';
}

function canal_init()
{
	global $blog_id;
	add_filter('the_content', 'canal_stats');
	add_filter('the_content', 'canal_trip_maps');
	add_filter('the_content', 'canal_trip_stats');
	add_filter('the_content', 'canal_route_maps');
	add_filter('the_content', 'canal_place_maps');
	add_filter('the_content', 'canal_link_maps');
	add_filter('the_content', 'canal_linkify');
	add_filter('the_content', 'canal_blogroute_insert');
	add_filter('the_excerpt', 'canal_stats');
	add_filter('the_excerpt', 'canal_trip_stats');
	add_filter('network_the_content', 'canal_stats');
	add_filter('network_the_content', 'canal_trip_maps');
	add_filter('network_the_content', 'canal_trip_stats');
	add_filter('network_the_content', 'canal_route_maps');
	add_filter('network_the_content', 'canal_place_maps');
	add_filter('network_the_content', 'canal_link_maps');
	add_filter('network_the_content', 'canal_linkify');
	add_filter('network_the_content', 'canal_blogroute_insert');
	add_filter('the_content_feed', 'canal_stats');
	add_filter('the_content_feed', 'canal_trip_maps');
	add_filter('the_content_feed', 'canal_trip_stats');
	add_filter('the_content_feed', 'canal_route_maps');
	add_filter('the_content_feed', 'canal_place_maps');
	add_filter('the_content_feed', 'canal_link_maps');
	add_filter('the_content_feed', 'canal_linkify');
	add_filter('the_content_feed ', 'canal_blogroute_insert');
	add_filter('network_the_content_feed', 'canal_stats');
	add_filter('network_the_content_feed', 'canal_trip_maps');
	add_filter('network_the_content_feed', 'canal_trip_stats');
	add_filter('network_the_content_feed', 'canal_route_maps');
	add_filter('network_the_content_feed', 'canal_place_maps');
	add_filter('network_the_content_feed', 'canal_link_maps');
	add_filter('network_the_content_feed', 'canal_linkify');
	add_filter('network_the_content_feed ', 'canal_blogroute_insert');
	add_filter('the_excerpt', 'canal_stats');
	add_filter('the_excerpt', 'canal_trip_maps');
	add_filter('the_excerpt', 'canal_trip_stats');
	add_filter('the_excerpt', 'canal_route_maps');
	add_filter('the_excerpt', 'canal_place_maps');
	add_filter('the_excerpt', 'canal_link_maps');
	add_filter('the_excerpt', 'canal_linkify');
	add_filter('the_excerpt', 'canal_blogroute_insert');
	add_filter('the_excerpt_rss', 'canal_stats');
	add_filter('the_excerpt_rss', 'canal_trip_maps');
	add_filter('the_excerpt_rss', 'canal_trip_stats');
	add_filter('the_excerpt_rss', 'canal_route_maps');
	add_filter('the_excerpt_rss', 'canal_place_maps');
	add_filter('the_excerpt_rss', 'canal_link_maps');
	add_filter('the_excerpt_rss', 'canal_linkify');
	add_filter('the_excerpt_rss', 'canal_blogroute_insert');
	add_filter('network_the_excerpt_rss', 'canal_stats');
	add_filter('network_the_excerpt_rss', 'canal_trip_maps');
	add_filter('network_the_excerpt_rss', 'canal_trip_stats');
	add_filter('network_the_excerpt_rss', 'canal_route_maps');
	add_filter('network_the_excerpt_rss', 'canal_place_maps');
	add_filter('network_the_excerpt_rss', 'canal_link_maps');
	add_filter('network_the_excerpt_rss', 'canal_linkify');
	add_filter('network_the_excerpt_rss', 'canal_blogroute_insert');


	add_filter('document_title_parts', 'canalplan_wp_title', 999, 1);


	add_action('wp_head', 'canalplan_header');
	add_action('wp_footer', 'canalplan_footer');

	// Makes sure Jetpack OG description tag has place names in it rather than tags
	add_filter(
		'jetpack_open_graph_tags',
		function ($tags) {
			global $blog_id;
			$tags['og:description'] = canal_trip_stats($tags['og:description'], $blog_id, null, 'N', 1);
			$tags['og:description'] = canal_trip_maps($tags['og:description'], $blog_id, null, 'Y');
			$tags['og:description'] = canal_stats($tags['og:description'], $blog_id, null, 1);
			$tags['og:description'] = canal_route_maps($tags['og:description'], $blog_id, null, 'Y');
			$tags['og:description'] = strip_tags(canal_blogroute_insert($tags['og:description']));
			return $tags;
		}
	);

	global $dogooglemap;
	$dogooglemap = 0;
}

function canal_trip_maps($content, $mapblog_id = NULL, $post_id = NULL, $search = 'N')
{
	global $wpdb, $post, $blog_id, $google_map_code, $canalplan_map_code, $dogooglemap, $canalplan_run_canal_route_maps;
	$tripdetail = 'N';
	$tripsumm = 'N';
	$triplink = 'N';
	$pid = $post->ID;
	if (is_null($pid))
		return $content;
	$places_array = '';
	$places_array2 = '';
	$places_array3 = '';
	if (preg_match_all('/' . preg_quote('[[CPTM:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[1];
		$tripsumm = 'Y';
	}
	if (preg_match_all('/' . preg_quote('[[CPTO:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches2)) {
		$places_array2 = $matches2[1];
		$tripdetail = 'Y';
		$overnight = 'Y';
	}
	if (preg_match_all('/' . preg_quote('[[CPTL:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches2)) {
		$places_array3 = $matches2[1];
		$triplink = 'Y';
	}
	if (get_query_var('feed') || $search == 'Y' || is_feed() || is_tag()) {
		$names = array();
		$links = array();
		if (is_array($places_array)) {
			foreach ($places_array as $place_code) {
				$words = explode(":", $place_code);
				$names[] = "[[CPTM:" . $place_code . "]]";
				$links[] = "<b>[ Route Map embedded here ]</b>";
			}
		}
		if (is_array($places_array2)) {
			foreach ($places_array2 as $place_code) {
				$words = explode(":", $place_code);
				$names[] = "[[CPTO:" . $place_code . "]]";
				$links[] = "<b>[ Route Map embedded here ]</b>";
			}
		}
		if (is_array($places_array3)) {
			foreach ($places_array3 as $place_code) {
				$words = explode(":", $place_code);
				$names[] = "[[CPTL:" . $place_code . "]]";
				$links[] = "<b>[ Route Map embedded here ]</b>";
			}
		}
		return str_replace($names, $links, $content);
	}
	if ($tripsumm == 'Y') {
		$names = array();
		$links = array();
		foreach ($places_array as $place_code) {
			$names[] = "[[CPTM:" . $place_code . "]]";
			$links[] = canal_bloggedroute($place_code, "N");
		}
		$content = str_ireplace($names, $links, $content);
	}
	if ($tripdetail == 'Y') {
		$names = array();
		$links = array();
		foreach ($places_array2 as $place_code) {
			$names[] = "[[CPTO:" . $place_code . "]]";
			$links[] = canal_bloggedroute($place_code, "Y");
		}
		$content = str_ireplace($names, $links, $content);
	}
	if ($triplink == 'Y') {
		$format_type = array('B' => 'ul', 'N' => 'ol');
		foreach ($places_array3 as $place_code) {
			$x = explode(':', $place_code);
			$routeid = addslashes($x[0]);
			$format = strtoupper($x[1]);
			if (!in_array($format, array("B", "N")))
				$format = 'N';
			$list_type = $format_type[$format];
			$sql = "select id, post_title from " . $wpdb->posts . " where id in (select post_id from " . CANALPLAN_ROUTE_DAY . " where blog_id=" . $wpdb->blogid . " and  route_id=" . $routeid . " and post_id <> $pid order by day_id asc ) and post_status='publish' order by id asc";
			$res = $wpdb->get_results($sql, ARRAY_A);
			$blroute = "<$list_type>";
			foreach ($res as $row) {
				$link = get_blog_permalink($blog_id, $row['id']);
				$blroute .= "<li><a href=\"$link\" target=\"_new\">$row[post_title]</a> </li>";
			}
			$blroute .= "</$list_type>";
			$links[] = $blroute;
			$names[] = "[[CPTL:" . $place_code . "]]";
		}
		$content = str_ireplace($names, $links, $content);
	}
	return $content;
}

function canal_trip_stats($content, $mapblog_id = NULL, $post_id = NULL, $search = 'N', $names_only = 0)
{
	global $wpdb, $post, $blog_id, $google_map_code, $canalplan_map_code, $dogooglemap, $canalplan_run_canal_route_maps, $network_post;
	if (isset ($network_post)) {
		$post_id = $network_post->ID;
		$mapblog_id = $network_post->BLOG_ID;
	}
	if (!isset ($mapblog_id)) {
		$mapblog_id = $blog_id;
	}
	$tripdetail = 'N';
	$tripsumm = 'N';
	if (preg_match_all('/' . preg_quote('[[CPTS:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[1];
		$tripsumm = 'Y';
	}
	if (preg_match_all('/' . preg_quote('[[CPTD:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches2)) {
		$places_array2 = $matches2[1];
		$tripdetail = 'Y';
	}
	if ($tripsumm == 'Y') {
		$names = array();
		$links = array();
		foreach ($places_array as $place_code) {
			$sql = $wpdb->prepare("select totalroute,uom,total_distance,total_locks from " . CANALPLAN_ROUTES . " cpr where cpr.route_id=%d and cpr.blog_id=%d", $place_code, $mapblog_id);
			$res1 = $wpdb->get_results($sql, ARRAY_A);
			$row1 = $res1[0];
			$dformat = $row1['uom'];
			$troute = explode(',', $row1['totalroute']);
			$startp = $troute[0];
			$endp = array_pop($troute);
			$sql = $wpdb->prepare("select distinct place_name from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d union select place_name from " . CANALPLAN_CODES . " where canalplan_id=%s and canalplan_id not in (select canalplan_id from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d)", $startp, $mapblog_id, $startp, $startp, $mapblog_id);
			;
			$res2 = $wpdb->get_results($sql, ARRAY_A);
			$row2 = $res2[0];
			$sql = $wpdb->prepare("select distinct place_name from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d union select place_name from " . CANALPLAN_CODES . " where canalplan_id=%s and canalplan_id not in (select canalplan_id from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d)", $endp, $mapblog_id, $endp, $endp, $mapblog_id);
			$res3 = $wpdb->get_results($sql, ARRAY_A);
			$row3 = $res3[0];
			$names[] = "[[CPTS:" . $place_code . "]]";
			if ($names_only == 1) {
				$links[] = "From " . $row2['place_name'] . " to " . $row3['place_name'] . ", " . format_distance($row['total_distance'], $row['total_locks'], $dformat, 2) . ".";
			} else {
				$links[] = "From [[CP:" . $row2['place_name'] . "|" . $startp . "]] to [[CP:" . $row3['place_name'] . "|" . $endp . "]], " . format_distance($row1['total_distance'], $row1['total_locks'], $dformat, 3) . ".";
			}
		}
		$content = str_ireplace($names, $links, $content);
	}
	if ($tripdetail == 'Y') {
		$names2 = array();
		$links2 = array();
		foreach ($places_array2 as $place_code) {
			$sql = $wpdb->prepare("select totalroute,uom,total_distance,total_locks from " . CANALPLAN_ROUTES . " cpr where cpr.route_id=%d and cpr.blog_id=%d", $place_code, $mapblog_id);
			$res1 = $wpdb->get_results($sql, ARRAY_A);
			$row1 = $res1[0];
			$dformat = $row1['uom'];
			$troute = explode(',', $row1['totalroute']);
			$sql = $wpdb->prepare("select distance,`locks`,start_id,end_id, day_id from " . CANALPLAN_ROUTE_DAY . " where blog_id=%d and  route_id=%d", $mapblog_id, $place_code);
			$res = $wpdb->get_results($sql, ARRAY_A);
			foreach ($res as $dayresult) {
				$startp = $troute[$dayresult['start_id']];
				$endp = $troute[$dayresult['end_id']];
				$sql = $wpdb->prepare("select distinct canalplan_id, place_name from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d union select canalplan_id, place_name from " . CANALPLAN_CODES . " where canalplan_id=%s and canalplan_id not in (select canalplan_id from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d)", $startp, $mapblog_id, $startp, $startp, $mapblog_id);
				$res2 = $wpdb->get_results($sql, ARRAY_A);
				$startplaces[] = $res2[0];
				$sql = $wpdb->prepare("select distinct canalplan_id, place_name from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d union select canalplan_id, place_name from " . CANALPLAN_CODES . " where canalplan_id=%s and canalplan_id not in (select canalplan_id from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d)", $endp, $mapblog_id, $endp, $endp, $mapblog_id);
				$res3 = $wpdb->get_results($sql, ARRAY_A);
				if ($dayresult['day_id'] >= 1)
					$endplaces[] = $res3[0];
			}
			$endplace = array_pop($endplaces);
			$penultimateplace = array_pop($endplaces);
			$names[] = "[[CPTS:" . $place_code . "]]";
			if ($names_only == 1) {
				$stat_text = "Starting at " . $startplaces[0]['place_name'] . " and finishing at " . $endplace['place_name'] . " with overnight stops at :";
			} else {
				$stat_text = "Starting at [[CP:" . $startplaces[0]['place_name'] . "|" . $startplaces[0]['canalplan_id'] . "]] and finishing at [[CP:" . $endplace['place_name'] . "|" . $endplace['canalplan_id'] . " ]] with overnight stops at :";
			}
			foreach ($endplaces as $nightplace) {
				if ($names_only == 1) {
					$stat_text .= " " . $nightplace['place_name'] . ",";
				} else {
					$stat_text .= " [[CP:" . $nightplace['place_name'] . "|" . $nightplace['canalplan_id'] . "]],";
				}
			}
			rtrim($stat_text, ",");
			if ($names_only == 1) {
				$stat_text .= " and " . $penultimateplace['place_name'] . ".";
			} else {
				$stat_text .= " and [[CP:" . $penultimateplace['place_name'] . "|" . $penultimateplace['canalplan_id'] . "]].";
			}

			$stat_text .= " " . format_distance($row1['total_distance'], $row1['total_locks'], $dformat, 3) . ".";
			$names2[] = "[[CPTD:" . $place_code . "]]";
			$links2[] = $stat_text;
		}
		$content = str_ireplace($names2, $links2, $content);
	}
	return $content;
}

function canal_stats($content, $mapblog_id = NULL, $post_id = NULL, $names_only = 0)
{
	global $blog_id, $wpdb, $post, $network_post;
	if (preg_match_all('/' . preg_quote('[[CPRS') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[0];
	}
	if (!isset ($places_array)) {
		return $content;
	}
	if (count($places_array) == 0) {
		return $content;
	}
	if (isset ($mapblog_id)) {
	} else {
		$mapblog_id = $blog_id;
	}
	if (isset ($post_id)) {
	} else {
		$post_id = $post->ID;
		if ($post_id <= 1) {
			$post_id = $post->ID;
		}
		if (isset ($post->blog_id)) {
			$mapblog_id = $post->blog_id;
		}
	}
	if (isset ($network_post)) {
		$post_id = $network_post->ID;
		$mapblog_id = $network_post->BLOG_ID;
	}
	if (!isset ($post_id)) {
		return;
	}
	if (!isset ($mapblog_id)) {
		return;
	}
	$sql = $wpdb->prepare("select distance,`locks`,start_id,end_id from " . CANALPLAN_ROUTE_DAY . " where blog_id=%d and  post_id=%d", $mapblog_id, $post_id);
	$res = $wpdb->get_results($sql, ARRAY_A);
	$row = $res[0];
	$sql = $wpdb->prepare("select totalroute,uom from " . CANALPLAN_ROUTES . " cpr, " . CANALPLAN_ROUTE_DAY . " crd where cpr.route_id= crd.route_id and cpr.blog_id=crd.blog_id and crd.blog_id=%d and  crd.post_id=%d", $mapblog_id, $post_id);
	$res3 = $wpdb->get_results($sql, ARRAY_A);
	$row3 = $res3[0];
	$dformat = $row3['uom'];
	$places = explode(",", $row3['totalroute']);
	$sql = $wpdb->prepare("select distinct place_name from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d union select place_name from " . CANALPLAN_CODES . " where canalplan_id=%s and canalplan_id not in (select canalplan_id from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d)", $places[$row['start_id']], $mapblog_id, $places[$row['start_id']], $places[$row['start_id']], $mapblog_id);
	$res2 = $wpdb->get_results($sql, ARRAY_A);
	$row2 = $res2[0];
	$start_name = $row2['place_name'];
	$sql = $wpdb->prepare("select distinct place_name from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d union select place_name from " . CANALPLAN_CODES . " where canalplan_id=%s and canalplan_id not in (select canalplan_id from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d)", $places[$row['end_id']], $mapblog_id, $places[$row['end_id']], $places[$row['end_id']], $mapblog_id);
	$res2 = $wpdb->get_results($sql, ARRAY_A);
	$row2 = $res2[0];
	$end_name = $row2['place_name'];
	$names = array();
	$links = array();
	foreach ($places_array as $place_code) {
		$words = explode(":", $place_code);
		$names[] = $place_code;
		if ($names_only == 1) {
			$links[] = "From " . $start_name . " to " . $end_name . ", " . format_distance($row['distance'], $row['locks'], $dformat, 2) . ".";
		} else {
			$links[] = "From [[CP:" . $start_name . "|" . $places[$row['start_id']] . "]] to [[CP:" . $end_name . "|" . $places[$row['end_id']] . "]], " . format_distance($row['distance'], $row['locks'], $dformat, 2) . ".";
		}
	}
	return str_ireplace($names, $links, $content);
}

function canal_linkify($content)
{
	global $post, $blog_id, $wpdb, $network_post;

	if (isset ($network_post)) {
		$post_id = $network_post->ID;
		$mapblog_id = $network_post->BLOG_ID;
		$date = date("Ymd", strtotime($network_post->post_date));
		$title = urlencode($network_post->post_title);
		switch_to_blog($network_post->BLOG_ID);
		$blog_url = get_bloginfo('url');
		$link = urlencode(str_replace($blog_url, "", get_permalink($network_post->ID)));
		restore_current_blog();
	}
	if (!isset ($mapblog_id)) {
		$blog_url = get_bloginfo('url');
		$date = date("Ymd", strtotime($post->post_date));
		$link = urlencode(str_replace($blog_url, "", get_permalink($post->ID)));
		$title = urlencode($post->post_title);
		$mapblog_id = $blog_id;
	}
	// First we check the content for tags:
	if (preg_match_all('/' . preg_quote('[[CP') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[1];
	}
	// If the array is empty then we've no links so don't do anything!}
	$names = array();
	$links = array();
	if (preg_match_all('/' . preg_quote('[[CP:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[1];
		$gazstring = CANALPLAN_GAZ_URL;
		$sql = $wpdb->prepare("SELECT pref_value FROM " . CANALPLAN_OPTIONS . " where blog_id=%d and pref_code='canalkey'", $mapblog_id);
		$r2 = $wpdb->get_results($sql, ARRAY_A);
		if ($wpdb->num_rows == 0) {
			$api = "";
		} else {
			$rw = $r2[0];
			$api = explode("|", $rw['pref_value']);

		}
		foreach ($places_array as $place_code) {
			$words = explode("|", $place_code);
			$names[] = "[[CP:" . $place_code . "]]";
			if (isset ($api[0])) {
				$links[] = "<a href='" . CANALPLAN_GAZ_URL . trim($words[1]) . "?blogkey=" . $api[0] . "&title=" . $title . "&blogid=" . $api[1] . "&date=" . $date . "&url=" . $link . "' target='gazetteer' title=\"Link to " . trim($words[0]) . " on Canalplan\">" . trim($words[0]) . "</a>";

			} else {
				$links[] = "<a href='" . CANALPLAN_GAZ_URL . trim($words[1]) . "' target='gazetteer'  title=\"Link to " . trim($words[0]) . " on Canalplan \">" . trim($words[0]) . "</a>";
			}
		}
	}
	if (preg_match_all('/' . preg_quote('[[CPW:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[1];
		$gazstring = CANALPLAN_WAT_URL;
		foreach ($places_array as $place_code) {
			$words = explode("|", $place_code);
			$names[] = "[[CPW:" . $place_code . "]]";
			$links[] = "<a href='" . $gazstring . trim($words[1]) . "' target='gazetteer'  title='Link to " . trim($words[0]) . "'>" . trim($words[0]) . "</a>";
		}
	}

	if (preg_match_all('/' . preg_quote('[[CPF:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[1];
		$gazstring = CANALPLAN_FEA_URL;
		foreach ($places_array as $place_code) {
			$words = explode("|", $place_code);
			$names[] = "[[CPF:" . $place_code . "]]";
			if ($api[0] == "") {
				$links[] = "<a href='" . $gazstring . trim($words[1]) . "' target='gazetteer'  title=\"Link to " . trim($words[0]) . " on Canalplan \">" . htmlspecialchars(trim($words[0])) . "</a>";
			} else {
				$links[] = "<a href='" . $gazstring . trim($words[1]) . "?blogkey=" . $api[0] . "&title=" . $title . "&blogid=" . $api[1] . "&date=" . $date . "&url=" . $link . "' target='gazetteer' title=\"Link to " . trim($words[0]) . " on Canalplan\">" . htmlspecialchars(trim($words[0])) . "</a>";
			}
		}
	}
	return str_ireplace($names, $links, $content);
}

function canal_linkify_name($content)
{
	global $post, $blog_id, $wpdb;
	// First we check the content for tags:
	if (preg_match_all('/' . preg_quote('[[CP') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[1];
	}
	// If the array is empty then we've no links so don't do anything!
	$names = array();
	$links = array();
	if (preg_match_all('/' . preg_quote('[[CP:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[1];
		foreach ($places_array as $place_code) {
			$words = explode("|", $place_code);
			$names[] = "[[CP:" . $place_code . "]]";
			$links[] = trim($words[0]);
		}
	}
	if (preg_match_all('/' . preg_quote('[[CPW:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[1];
		foreach ($places_array as $place_code) {
			$words = explode("|", $place_code);
			$names[] = "[[CPW:" . $place_code . "]]";
			$links[] = trim($words[0]);
		}
	}
	if (preg_match_all('/' . preg_quote('[[CPF:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[1];
		foreach ($places_array as $place_code) {
			$words = explode("|", $place_code);
			$names[] = "[[CPF:" . $place_code . "]]";
			$links[] = trim($words[0]);
		}
	}
	return str_ireplace($names, $links, $content);
}

function wp_canalplan_admin_pages()
{
	global $maptype;
	$base_dir = dirname(__FILE__) . '/admin-pages/';
	//	settings_fields('canalplan_options');
	$hook = add_menu_page('CanalPlan AC Overview', 'CanalPlan AC', 'edit_pages', $base_dir . 'cp-admin-menu.php');
	add_submenu_page($base_dir . 'cp-admin-menu.php', 'CanalPlan Options: General Options', 'General Options', 'activate_plugins', $base_dir . 'cp-admin-general.php');
	add_submenu_page($base_dir . 'cp-admin-menu.php', 'CanalPlan Options: Home Mooring', 'Home Mooring', 'activate_plugins', $base_dir . 'cp-admin-home.php');
	add_submenu_page($base_dir . 'cp-admin-menu.php', 'CanalPlan Options: Favourites', 'Favourites', 'activate_plugins', $base_dir . 'cp-admin-fav.php');
	if ($maptype == 'google')
		add_submenu_page($base_dir . 'cp-admin-menu.php', 'CanalPlan Options: Google Maps', 'Google Maps', 'activate_plugins', $base_dir . 'cp-admin-google.php');
	if ($maptype == 'cpmap')
		add_submenu_page($base_dir . 'cp-admin-menu.php', 'CanalPlan Options: Canalplan Maps', 'Canalplan Maps', 'activate_plugins', $base_dir . 'cp-admin-cpmaps.php');
	add_submenu_page($base_dir . 'cp-admin-menu.php', 'CanalPlan Options: Import Route', 'Import Routes', 'activate_plugins', $base_dir . 'cp-import_route.php');
	add_submenu_page($base_dir . 'cp-admin-menu.php', 'CanalPlan Options: Manage Routes', 'Manage Routes', 'activate_plugins', $base_dir . 'cp-manage_route.php');
	add_submenu_page($base_dir . 'cp-admin-menu.php', 'CanalPlan Options: Set Location', 'Set Location', 'activate_plugins', $base_dir . 'cp-admin-location.php');
	add_submenu_page($base_dir . 'cp-admin-menu.php', 'CanalPlan Options: Diagnostics', 'Diagnostics', 'activate_plugins', $base_dir . 'cp-admin-diagnostics.php');
	add_submenu_page($base_dir . 'cp-admin-menu.php', 'CanalPlan Options: Bulk Notify', 'Bulk Notify', 'activate_plugins', $base_dir . 'cp-admin-update.php');
}

function canalplan_header($blah)
{
	global $blog_id, $wpdb, $google_map_code, $canalplan_map_code, $maptype;
	$canalplan_options = get_option('canalplan_options');
	if (isset ($canalplan_options['supress_google'])) {
		$google_map_code = '';
		return;
	}
	$header = '<meta name="viewport" content="initial-scale=1.0, user-scalable=no" /> ';
	$header = $header . "<script type='text/javascript'>var cp_info = {}; cp_info.language = 'en'; const steveqa = (selector, base = document) => {let elements = base.querySelectorAll('#'+selector);return (elements.length == 1) ? elements[0] : elements;};</script>";
	if ($maptype=='cpmap') {
	$header = $header . "<script src='" . MAPSERVER_BASE . "/maplibre/maplibre-gl.js?vers=".MAPLIBRE_VERSION."'></script> <link href='" . MAPSERVER_BASE . "/maplibre/maplibre-gl.css?vers=".MAPLIBRE_VERSION."' rel='stylesheet' />";
	$plugurl = plugin_dir_url(__FILE__);
	$header = $header . "<script src='" . $plugurl . "canalplan/javascript/output_osm_map.js'></script>";
	$header = $header . "<script src='" . $plugurl . "canalplan/javascript/maplibre_mapstuff.js'></script>";
	}
	echo $header;
	$api_key = '';
	$canalplan_map_code = '<script>';
	if (isset ($canalplan_options['canalplan_rm_key']))
		$api_key = $canalplan_options['canalplan_rm_key'];
	$google_map_code = ' <script type="text/javascript" src="//maps.google.com/maps/api/js?key=' . $api_key . '&libraries=geometry"> </script>  <script  async type="text/javascript"> google.maps.visualRefresh = true; function initialize() {  ';
	return $blah;
}

function canalplan_footer($blah)
{
	global $google_map_code, $canalplan_map_code, $maptype;
	$google_map_code .= '  } </script> ';
	$canalplan_map_code .= ' </script> ';
	if ($maptype == 'google') {
		echo $google_map_code;
	}
	if ($maptype == 'cpmap') {
		echo $canalplan_map_code;
	}
	echo "\n<!-- Canalplan AC code revision : " . CANALPLAN_CODE_RELEASE . " -->\n";
	if ($maptype == 'google') {
		$canalplan_options = get_option('canalplan_options');
		if (isset ($canalplan_options['supress_google'])) {
			return;
		}
		echo "<script> google.maps.event.addDomListener(window, 'load', initialize); </script> ";
		?>
		<script async type='text/javascript'>
			function CPResizeControl(e) { this.startUp(e) } CPResizeControl.RESIZE_BOTH = 0; CPResizeControl.RESIZE_WIDTH = 1; CPResizeControl.RESIZE_HEIGHT = 2; CPResizeControl.prototype.startUp = function (e) { var t = this; this._map = e; this.resizing = false; this.mode = CPResizeControl.RESIZE_BOTH; this.minWidth = 150; this.minHeight = 150; this.maxWidth = 0; this.maxHeight = 0; this.diffX = 0; this.diffY = 0; google.maps.event.addListenerOnce(e, "tilesloaded", function () { var n = new CPResizeControl.ResizeControl(t, e); n.index = 1 }) }; CPResizeControl.ResizeControl = function (e, t) { var n = document.createElement("div"); n.style.width = "20px"; n.style.height = "20px"; n.style.backgroundImage = "url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUBAMAAAB/pwA+AAAAAXNSR0IArs4c6QAAAA9QTFRFMBg0f39/0dDN7eri/v7+XsdLVAAAAAF0Uk5TAEDm2GYAAABNSURBVAjXRcpBDcAwDEPRKAymImghuCUw/qTWJI7nk/X0zXquZ+tH6E5df3TngPBA+ELY7UW2gWwDq02sNjHbwmwLoyVGS7ytbw62tA8zTA85AeAv2wAAAABJRU5ErkJggg%3D%3D)"; n.style.position = "absolute"; n.style.right = "0px"; n.style.bottom = "0px"; google.maps.event.addDomListener(n, "mousedown", function () { e.resizing = true }); google.maps.event.addDomListener(document, "mouseup", function () { if (e.resizing) { e.resizing = false; if (typeof e.doneCallBack == "function") e.doneCallBack(e._map) } }); google.maps.event.addDomListener(document, "mousemove", function (t) { e.mouseMoving(t) }); var r = t.getDiv(); r.appendChild(n); var i = r.firstChild.childNodes[2]; i.style.marginRight = "25px"; return n }; CPResizeControl.prototype.changeMapSize = function (e, t) { var n = this._map.getDiv().style; var r = parseInt(n.width); var i = parseInt(n.height); var s = r, o = i; r += e; i += t; if (this.minWidth) { r = Math.max(this.minWidth, r) } if (this.maxWidth) { r = Math.min(this.maxWidth, r) } if (this.minHeight) { i = Math.max(this.minHeight, i) } if (this.maxHeight) { i = Math.min(this.maxHeight, i) } var u = false; if (this.mode != CPResizeControl.RESIZE_HEIGHT) { n.width = r + "px"; u = true } if (this.mode != CPResizeControl.RESIZE_WIDTH) { n.height = i + "px"; u = true } if (u) { if (typeof this.changeCallBack == "function") this.changeCallBack(this._map, r, i, r - s, i - o); google.maps.event.trigger(this._map, "resize") } }; CPResizeControl.prototype.mouseMoving = function (e) { var t = window.scrollX || document.documentElement.scrollLeft || 0; var n = window.scrollY || document.documentElement.scrollTop || 0; if (!e) e = window.event; var r = e.clientX + t; var i = e.clientY + n; if (this.resizing) { this.changeMapSize(r - this.diffX, i - this.diffY) } this.diffX = r; this.diffY = i; return false }
		</script>
		<?php
	}
	return $blah;
}

function canal_blogroute_insert($content)
{

	$string = '{BLOGGEDROUTES}';
	$tag_key = NULL;
	if (preg_match_all('/{BLOGGEDROUTES|[a-zA-Z0-9]{1,30}}/', $content, $matches)) {
		$string = $matches[0][0];
		if (isset ($matches[0][1])) {
			$string = $matches[0][0] . '|' . $matches[0][1];
			$tag_key = rtrim($matches[0][1], '}');
		}
		if (strlen($tag_key) == 0) {
			$string = '{BLOGGEDROUTES}';
			$tag_key = NULL;
		}

	}
	;
	$content = str_replace($string, canal_bloggedroute(0, 'N', $tag_key), $content);
	return $content;
}

function canal_deactivate()
{
	wp_clear_scheduled_hook('canalplan_update_by_cron');
}

function canal_uninstall()
{
	global $wpdb, $table_prefix;
	$sql = 'DROP TABLE if exists ' . CANALPLAN_ALIASES;
	$result = $wpdb->query($sql);
	$sql = 'DROP TABLE ' . CANALPLAN_CODES . ';';
	$result = $wpdb->query($sql);
	$sql = 'DROP TABLE ' . CANALPLAN_FAVOURITES . ';';
	$result = $wpdb->query($sql);
	$sql = 'DROP TABLE ' . CANALPLAN_LINK . ';';
	$result = $wpdb->query($sql);
	$sql = 'DROP TABLE ' . CANALPLAN_OPTIONS . ';';
	$result = $wpdb->query($sql);
	$sql = 'DROP TABLE ' . CANALPLAN_ROUTES . ';';
	$result = $wpdb->query($sql);
	$sql = 'DROP TABLE ' . CANALPLAN_CANALS . ';';
	$result = $wpdb->query($sql);
	$sql = 'DROP TABLE ' . CANALPLAN_POLYLINES . ';';
	$result = $wpdb->query($sql);
	$sql = 'DROP TABLE ' . CANALPLAN_ROUTE_DAY . ';';
	$result = $wpdb->query($sql);
}

function canal_activate()
{
	global $wpdb, $table_prefix;
	wp_cache_flush();
	wp_schedule_event(time(), 'daily', 'canalplan_update_by_cron');
	$errors = array();

	$sql = 'CREATE TABLE IF NOT EXISTS ' . CANALPLAN_ALIASES . ' (
  canalplan_id varchar(10) NOT NULL,
  place_name varchar(250) NOT NULL,
  PRIMARY KEY  (canalplan_id,place_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8';
	$result = $wpdb->query($sql);
	if ($result === false)
		$errors[] = __('Failed to create ') . CANALPLAN_ALIASES;

	$sql = 'CREATE TABLE IF NOT EXISTS ' . CANALPLAN_CODES . ' (
  canalplan_id varchar(10) NOT NULL,
  place_name varchar(250) NOT NULL,
  size tinyint(1) DEFAULT NULL,
  lat float NOT NULL DEFAULT 0,
  `long` float NOT NULL DEFAULT 0,
  type tinyint(2) unsigned DEFAULT NULL,
  attributes varchar(20) DEFAULT NULL,
  lat_lng_point point NOT NULL,
  region varchar(100) NULL,
  PRIMARY KEY  (canalplan_id),
  KEY place_name (place_name),
  SPATIAL INDEX (lat_lng_point)
) ENGINE=MyISAM DEFAULT CHARSET=utf8';
	$result = $wpdb->query($sql);
	if ($result === false)
		$errors[] = __('Failed to create ') . CANALPLAN_CODES;

	$sql = 'CREATE TABLE IF NOT EXISTS ' . CANALPLAN_FAVOURITES . ' (
  blog_id bigint(20) NOT NULL DEFAULT 0,
  place_order int(4) NOT NULL DEFAULT 0,
  canalplan_id varchar(10) NOT NULL,
  place_name varchar(250) NOT NULL,
  PRIMARY KEY  (blog_id,canalplan_id,place_order),
  KEY canalplan_idx (canalplan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8';
	$result = $wpdb->query($sql);
	if ($result === false)
		$errors[] = __('Failed to create ') . CANALPLAN_FAVOURITES;

	$sql = 'CREATE TABLE IF NOT EXISTS ' . CANALPLAN_LINK . ' (
  place1 varchar(4) NOT NULL,
  place2 varchar(4) NOT NULL,
  metres bigint(10) DEFAULT NULL,
  locks bigint(10) DEFAULT NULL,
  waterway varchar(4) DEFAULT NULL,
  PRIMARY KEY  (place1,place2),
  KEY waterway (waterway)
) ENGINE=InnoDB DEFAULT CHARSET=utf8';
	$result = $wpdb->query($sql);
	if ($result === false)
		$errors[] = __('Failed to create ') . CANALPLAN_LINK;

	$sql = 'CREATE TABLE IF NOT EXISTS ' . CANALPLAN_OPTIONS . ' (
  blog_id bigint(20) NOT NULL DEFAULT 0,
  pref_code varchar(20) NOT NULL,
  pref_value varchar(240) NOT NULL,
  PRIMARY KEY  (blog_id,pref_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8';
	$result = $wpdb->query($sql);
	if ($result === false)
		$errors[] = __('Failed to create ') . CANALPLAN_OPTIONS;

	$sql = 'CREATE TABLE IF NOT EXISTS ' . CANALPLAN_ROUTES . ' (
  route_id bigint(10) NOT NULL DEFAULT 0,
  blog_id bigint(20) NOT NULL DEFAULT 0,
  cp_route_id varchar(20) DEFAULT NULL,
  title varchar(100) DEFAULT NULL,
  description varchar(240) DEFAULT NULL,
  start_date date DEFAULT NULL,
  duration int(3) DEFAULT NULL,
  UOM char(1) DEFAULT NULL,
  total_distance float DEFAULT NULL,
  status int(1) DEFAULT NULL,
  total_locks bigint(10) DEFAULT NULL,
  totalroute text NOT NULL,
  total_coords longtext NULL,
  routetag varchar(100) NULL,
  PRIMARY KEY  (route_id,blog_id),
  KEY status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8';
	$result = $wpdb->query($sql);
	if ($result === false)
		$errors[] = __('Failed to create ') . CANALPLAN_ROUTES;

	$sql = 'CREATE TABLE IF NOT EXISTS ' . CANALPLAN_CANALS . ' (
  id varchar(4) NOT NULL,
  parent varchar(4) DEFAULT NULL,
  name varchar(40) NOT NULL,
  fullname varchar(120) NOT NULL,
  PRIMARY KEY  (id),
  KEY parent (parent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8';
	$result = $wpdb->query($sql);
	if ($result === false)
		$errors[] = __('Failed to create ') . CANALPLAN_CANALS;

	$sql = 'CREATE TABLE IF NOT EXISTS ' . CANALPLAN_POLYLINES . ' (
  id varchar(5) NOT NULL,
  pline longtext,
  weights longtext,
  PRIMARY KEY  (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8';
	$result = $wpdb->query($sql);
	if ($result === false)
		$errors[] = __('Failed to create ') . CANALPLAN_POLYLINES;

	$sql = 'CREATE TABLE IF NOT EXISTS ' . CANALPLAN_ROUTE_DAY . ' (
  route_id bigint(10) NOT NULL DEFAULT 0,
  day_id int(3) NOT NULL DEFAULT 0,
  blog_id bigint(20) NOT NULL DEFAULT 0,
  post_id bigint(20) DEFAULT NULL,
  route_date date DEFAULT NULL,
  start_id int(4) DEFAULT NULL,
  end_id int(4) DEFAULT NULL,
  distance int(10) DEFAULT NULL,
  locks int(4) DEFAULT NULL,
  flags varchar(10) DEFAULT NULL,
  day_coords longtext NULL,
  PRIMARY KEY  (route_id,day_id,blog_id),
  KEY post_blog_idx (blog_id,post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8';
	$result = $wpdb->query($sql);
	if ($result === false)
		$errors[] = __('Failed to create ') . CANALPLAN_POLYLINES;


	if ($errors) {
		return;
	}
	$sql = "update " . CANALPLAN_OPTIONS . " set pref_value=" . CANALPLAN_DB_VERSION . " where blog_id=-1 and pref_code='database_version'";
	$r = $wpdb->query("SELECT pref_value FROM  " . CANALPLAN_OPTIONS . " where blog_id=-1 and pref_code='dbv'");
	if ($wpdb->num_rows == 0) {
		$sql = "insert into  " . CANALPLAN_OPTIONS . " (pref_value,blog_id,pref_code) values ('" . CANALPLAN_DB_VERSION . "',-1,'database_version')";
	}
	$result = $wpdb->query($sql);
}

function canalplan_option_init()
{
	register_setting('canalplan_options', 'canalplan_options', 'canalplan_validate_options');
	//	print "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!@";
}

function canalplan_validate_options($options)
{
	# Do they want to reset? If so we reset the options and let WordPress do the business for us!

	if (isset ($_POST["RSD"])) {
		$options["canalplan_pm_type"] = 'H';
		$options["canalplan_pm_zoom"] = 14;
		$options["canalplan_pm_height"] = 200;
		$options["canalplan_pm_width"] = 200;
		$options["canalplan_rm_type"] = 'H';
		$options["canalplan_rm_zoom"] = 9;
		$options["canalplan_rm_height"] = 600;
		$options["canalplan_rm_width"] = 500;
		$options["canalplan_rm_r_hex"] = "00";
		$options["canalplan_rm_g_hex"] = "00";
		$options["canalplan_rm_b_hex"] = "ff";
		$options["canalplan_rm_weight"] = 4;
		$options["canalplan_cppm_type"] = 'H';
		$options["canalplan_cppm_zoom"] = 14;
		$options["canalplan_cppm_height"] = 200;
		$options["canalplan_cppm_width"] = 200;
		$options["canalplan_cprm_type"] = 'H';
		$options["canalplan_cprm_zoom"] = 9;
		$options["canalplan_cprm_height"] = 600;
		$options["canalplan_cprm_width"] = 500;
		$options["canalplan_cprm_r_hex"] = "00";
		$options["canalplan_cprm_g_hex"] = "00";
		$options["canalplan_cprm_b_hex"] = "ff";
		$options["canalplan_cprm_weight"] = 4;
		$options["cp-place-mooring"] = 'none';
		$options["cp-place-fixedbridges"] = 'none';
		$options["cp-place-smallplaces"] = 'none';
		$options["cp-place-bigplaces"] = 'none';
		$options["cp-place-movebridges"] = 'none';
		$options["cp-place-winding"] = 'none';
		$options["cp-place-locks"] = 'none';
		$options["cp-place-junctions"] = 'none';
		$options["cp-place-facilities"] = 'none';
	}
	return $options;
}



add_action('admin_init', 'canalplan_option_init');

function save_error()
{
	update_option('plugin_error', ob_get_contents());
}


function canal_update($dbv)
{
	global $wpdb;
	$updated = 0;
	if ($dbv == 0) {
		$sql = 'ALTER TABLE ' . CANALPLAN_ROUTE_DAY . ' ADD  `day_coords` longtext NULL';
		$result = $wpdb->query($sql);
		if ($result === false)
			$errors[] = __('Failed to update ') . CANALPLAN_ROUTE_DAY;
		if ($result === true)
			$updated = 1;

		$sql = 'ALTER TABLE ' . CANALPLAN_ROUTES . ' ADD `total_coords` longtext NULL';
		$result = $wpdb->query($sql);
		if ($result === false)
			$errors[] = __('Failed to update ') . CANALPLAN_ROUTES;
		if ($result === true)
			$updated = 1;
	}

	if ($dbv == 1) {

		$sql = 'ALTER TABLE ' . CANALPLAN_ROUTES . ' ADD `routetag` varchar(100) NULL';
		$result = $wpdb->query($sql);
		if ($result === false)
			$errors[] = __('Failed to update ') . CANALPLAN_ROUTES;
		if ($result === true)
			$updated = 1;
	}

	if ($dbv == 2) {

		$sql = 'ALTER TABLE ' . CANALPLAN_CODES . ' ADD `region` varchar(100) NULL';
		$result = $wpdb->query($sql);
		if ($result === false)
			$errors[] = __('Failed to update ') . CANALPLAN_CODES;
		if ($result === true)
			$updated = 1;

		$sql = 'ALTER TABLE ' . CANALPLAN_POLYLINES . ' ADD `geojson` longtext NULL';
		$result = $wpdb->query($sql);
		if ($result === false)
			$errors[] = __('Failed to update ') . CANALPLAN_CODES;
		if ($result === true)
			$updated = 1;
	}

	$r = $wpdb->get_results("select count(1) tot_routes from  " . CANALPLAN_ROUTES . " where total_coords is null", ARRAY_A);
	$tot_routes = $r[0]['tot_routes'];
	$r = $wpdb->get_results("select count(1) tot_days from  " . CANALPLAN_ROUTE_DAY . " where day_coords is  null and day_id > 0", ARRAY_A);
	$tot_days = $r[0]['tot_days'];

	if ($tot_routes >= 1) {
		$sql = $wpdb->prepare("select blog_id,route_id,totalroute from " . CANALPLAN_ROUTES . " where total_coords is null", '');
		$r = $wpdb->get_results($sql, ARRAY_A);
		foreach ($r as $row) {
			$total_route = explode(",", $row['totalroute']);
			$all_coords = '';
			foreach ($total_route as $place) {
				$sql = $wpdb->prepare("select lat,`long` from " . CANALPLAN_CODES . " where canalplan_id=%s", $place);
				$r = $wpdb->get_results($sql, ARRAY_A);
				$rw = $r[0];
				$all_coords .= '|' . $rw['lat'] . ',' . $rw['long'];
			}
			$all_coords = trim($all_coords, '|');
			$sql = $wpdb->prepare("update " . CANALPLAN_ROUTES . " set total_coords=%s where route_id=%d and blog_id=%d", $all_coords, $row['route_id'], $row['blog_id']);
			$r2 = $wpdb->query($sql);
		}
	}
	if ($tot_days >= 1) {

		$sql = $wpdb->prepare("select blog_id,route_id,day_id,start_id,end_id from " . CANALPLAN_ROUTE_DAY . " where day_coords is null and day_id > 0 order by blog_id,route_id,day_id", '');
		$r = $wpdb->get_results($sql, ARRAY_A);
		foreach ($r as $row) {
			$sql2 = $wpdb->prepare("select totalroute from " . CANALPLAN_ROUTES . " where blog_id=%d and route_id=%d", $row['blog_id'], $row['route_id']);
			$r2 = $wpdb->get_results($sql2, ARRAY_A);
			$row2 = $r2[0];
			$first = $row['start_id'];
			$last = $row['end_id'];
			$all_coords = '';
			$total_route = explode(',', $row2['totalroute']);
			$dayroute = array_slice($total_route, $first, ($last - $first) + 1);
			foreach ($dayroute as $oneplace) {
				$sql = $wpdb->prepare("select lat,`long` from " . CANALPLAN_CODES . " where canalplan_id = %s ", $oneplace);
				$r = $wpdb->get_results($sql, ARRAY_A);
				$rx = $r[0];
				$all_coords .= '|' . $rx['lat'] . ',' . $rx['long'];
			}
			$all_coords = trim($all_coords, '|');
			$sql3 = $wpdb->prepare("update " . CANALPLAN_ROUTE_DAY . " set day_coords =%s where blog_id=%d and route_id=%d and day_id=%d ", $all_coords, $row['blog_id'], $row['route_id'], $row['day_id']);
			$r2 = $wpdb->get_results($sql3, ARRAY_A);
		}

	}
}

function canalplan_update_db_check()
{
	global $wpdb;
	$schedule = wp_get_schedule('canalplan_update_by_cron');

	if ($schedule != 'daily')
		wp_schedule_event(time(), 'daily', 'canalplan_update_by_cron');
	if (current_user_can('administrator') || current_user_can('super_administrator')) {
		$r2 = $wpdb->get_results("SELECT pref_value as dbv FROM  " . CANALPLAN_OPTIONS . " where blog_id=-1 and pref_code='database_version'", ARRAY_A);

		$dbv = $r2[0]['dbv'];
		if (!isset ($dbv)) {
			$dbv = 0;
		}
		if ($dbv <> CANALPLAN_DB_VERSION) {
			canal_update($dbv);
			$sql = "update " . CANALPLAN_OPTIONS . " set pref_value=" . CANALPLAN_DB_VERSION . " where blog_id=-1 and pref_code='database_version'";
			$r = $wpdb->query("SELECT pref_value FROM  " . CANALPLAN_OPTIONS . " where blog_id=-1 and pref_code='database_version'");
			if ($wpdb->num_rows == 0) {
				$sql = "insert into  " . CANALPLAN_OPTIONS . " (pref_value,blog_id,pref_code) values ('" . CANALPLAN_DB_VERSION . "',-1,'database_version')";
			}
			$result = $wpdb->query($sql);
		}

	}
}

function canalplan_update_cp_on_publish($new_status, $old_status, $post)
{
	global $blog_id, $wpdb;
	$post_id = $post->ID;
	if (isset ($_POST['UpdateCanalplan']) && $_POST['UpdateCanalplan'] == 'UpdateCanalplan' && $new_status == 'publish') {
		$sql = $wpdb->prepare("SELECT pref_value FROM " . CANALPLAN_OPTIONS . " where  blog_id=%s and pref_code='canalkey'", $blog_id);
		$r2 = $wpdb->get_results($sql, ARRAY_A);
		// Only do the update if we've got an canalkey
		if ($wpdb->num_rows > 0) {
			$api = explode('|', $r2[0]['pref_value']);
			update_canalplan($post_id, 'Y', 5, $api);
		}
	}
}


function canalplan_update_by_cron_function()
{
	global $blog_id, $wpdb;
	$sql = $wpdb->prepare("SELECT pref_value FROM " . CANALPLAN_OPTIONS . " where  blog_id=%s and pref_code='canalkey'", $blog_id);
	$r2 = $wpdb->get_results($sql, ARRAY_A);
	// Only do the update if we've got an canalkey
	if ($wpdb->num_rows > 0) {
		$api = explode('|', $r2[0]['pref_value']);
		$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_status='publish' and (post_type='post' or post_type='page') order by ID desc limit %d", 300);
		$r = $wpdb->get_results($query, ARRAY_A);
		foreach ($r as $rw) {
			update_canalplan($rw['ID'], 'Y', 50, $api);
		}
	}
}

function canalplan_wp_title($title_parts)
{
	global $blog_id, $wpdb;
	if (!isset ($_GET['routeid'])) {
		$_GET['routeid'] = 0;
	}
	$routeid = $_GET['routeid'];
	$routeid = preg_replace('{/$}', '', $routeid);
	if ($routeid > 0) {
		$sql = $wpdb->prepare("select route_id, title,blog_id,start_date from " . CANALPLAN_ROUTES . " where blog_id=%d and route_id=%d", $blog_id, $routeid);
		$r2 = $wpdb->get_results($sql, ARRAY_A);
		$title_parts['title'] = $r2[0]['title'];
	}
	return $title_parts;
}


function canalplan_http_request_args($r)
{
	$r['timeout'] = 150;
	return $r;
}


function canalplan_http_api_curl($handle)
{
	curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 150);
	curl_setopt($handle, CURLOPT_TIMEOUT, 150);
}

function canalplan_custom_http_request_timeout($timeout_value)
{
	return 150; // 30 seconds. Too much for production, only for testing.
}
function canalplan_enqueue_script()
{
	$plugurl = plugin_dir_url(__FILE__);

	wp_add_inline_script('gaz_osm_map', "var cp_info = {}; cp_info.language = 'en'; ", 'before');
	wp_add_inline_script('gaz_osm_map', "const steveqa = (selector, base = document) => {let elements = base.querySelectorAll('#'+selector);return (elements.length == 1) ? elements[0] : elements;};", 'before');


}

add_action('wp_enqueue_scripts', 'canalplan_enqueue_script');

add_action('http_api_curl', 'canalplan_http_api_curl', 100, 1);
add_filter('http_request_args', 'canalplan_http_request_args', 100, 1);
add_filter('http_request_timeout', 'canalplan_custom_http_request_timeout', 100, 1);
add_action('transition_post_status', 'canalplan_update_cp_on_publish', 10, 3);
add_action('canalplan_update_by_cron', 'canalplan_update_by_cron_function');
add_action('activated_plugin', 'save_error');
add_action('plugins_loaded', 'canalplan_update_db_check');
add_action('admin_menu', 'canalplan_add_custom_box');
add_action('init', 'canal_init');
register_activation_hook(__FILE__, 'canal_activate');
register_deactivation_hook(__FILE__, 'canal_deactivate');
register_uninstall_hook(__FILE__, 'canal_uninstall');
add_action('admin_menu', 'wp_canalplan_admin_pages');


include (plugin_dir_path(__FILE__) . "canalplan/canalplan_widget.php");

$sql = $wpdb->prepare("SELECT pref_value FROM  " . CANALPLAN_OPTIONS . " where blog_id=%d and pref_code='maptype'", $blog_id);
$r = $wpdb->get_results($sql, ARRAY_A);
if ($wpdb->num_rows == 0) {
	$maptype = "google";
} else {
	$maptype = $r[0]['pref_value'];
}
//var_dump($maptype);
if ($maptype == 'google') {
	include (plugin_dir_path(__FILE__)  . "canalplan/canalplan_google.php");
}
if ($maptype == 'cpmap') {
	include (plugin_dir_path(__FILE__) . "canalplan/canalplan_cpmaps.php");
}
?>
