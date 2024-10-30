<?php
require_once ('../../../../wp-config.php');
global $wpdb, $user_ID;
if (!isset ($_GET['match'])) {
	$_GET['match'] = '';
}
if (!isset ($_GET['place'])) {
	$_GET['place'] = '';
}
if (!isset ($_GET['blogid'])) {
	$_GET['blogid'] = 1;
}
//var_dump(strlen($_GET['match']));
if (strlen($_GET['match']) > 0) {
	$match = stripslashes(urldecode($_GET['match']));
	//var_dump($match);
	$cp_blog_id = intval($_GET['blogid']);
	if ($cp_blog_id < 1) {
		$cp_log_id = 1;
	}
	$sql = $wpdb->prepare("select canalplan_id, place_name from " . CANALPLAN_CODES . " where canalplan_id not like '!%' and substring(place_name,1," . strlen($match) . ")=%s", $match);
	$canalplaces = $wpdb->get_results($sql, ARRAY_A);
	foreach ($canalplaces as $canalplace) {
		print trim($canalplace['place_name']) . '#';
	}
	$sql = $wpdb->prepare("select canalplan_id, place_name from " . CANALPLAN_ALIASES . " where substring(place_name,1," . strlen($match) . ")=%s", $match);
	$canalplaces = $wpdb->get_results($sql, ARRAY_A);
	foreach ($canalplaces as $canalplace) {
		print trim($canalplace['place_name']) . '#';
	}
	$sql = $wpdb->prepare("select id, fullname from " . CANALPLAN_CANALS . " where parent!='' and id not like '!%' and substring(fullname,1," . strlen($match) . ")=%s", $match);
	$canalplaces = $wpdb->get_results($sql, ARRAY_A);
	foreach ($canalplaces as $canalplace) {
		print trim($canalplace['fullname']) . '#';
	}
}
$place = urldecode($_GET['place']);
//var_dump($place);
if (strlen($place) > 0) {
	$place = stripslashes(trim(urldecode($place)));
	$cp_blog_id = intval($_GET['blogid']);
	if ($cp_blog_id == "undefined") {
		unset($cp_blog_id);
	}
	// If we have a blog_id passed in then we need to get back the favourites and then the main rows but remove any common names
	if (isset ($cp_blog_id)) {
		// Get Check Favourites
		$sql = $wpdb->prepare('select canalplan_id from ' . CANALPLAN_FAVOURITES . ' where place_name=%s and blog_id=%d', $place, $cp_blog_id);
		$canalplaces = $wpdb->get_results($sql, ARRAY_A);
		foreach ($canalplaces as $canalplace) {
			print "X" . trim($canalplace['canalplan_id']);
		}
		// Now check the rest
		unset($canalplaces);
		$sql = $wpdb->prepare('select cc.canalplan_id from ' . CANALPLAN_CODES . ' cc where cc.canalplan_id not like "!%" and  cc.place_name=%s and  cc.canalplan_id not in (select cf.canalplan_id from ' . CANALPLAN_FAVOURITES . ' cf where cf.blog_id=%d and cf.place_name=%s)', $place, $cp_blog_id, $place);
		$canalplaces = $wpdb->get_results($sql, ARRAY_A);
		foreach ($canalplaces as $canalplace) {
			print "X" . trim($canalplace['canalplan_id']);
		}
		unset($canalplaces);
		$sql = $wpdb->prepare('select cc.canalplan_id from ' . CANALPLAN_ALIASES . ' cc where  cc.place_name=%s and  cc.canalplan_id not in (select cf.canalplan_id from ' . CANALPLAN_FAVOURITES . ' cf where cf.blog_id=%d and cf.place_name=%s) and substring(cc.canalplan_id,1,1)!="!"', $place, $cp_blog_id, $place);
		$canalplaces = $wpdb->get_results($sql, ARRAY_A);
		foreach ($canalplaces as $canalplace) {
			print "X" . trim($canalplace['canalplan_id']);
		}
		unset($canalplaces);
		$sql = $wpdb->prepare('select cc.canalplan_id from ' . CANALPLAN_ALIASES . ' cc where cc.place_name=%s and  cc.canalplan_id not in (select cf.canalplan_id from ' . CANALPLAN_FAVOURITES . ' cf where cf.blog_id=%d and cf.place_name=%s) and substring(cc.canalplan_id,1,1)="!" ', $place, $cp_blog_id, $place);
		$canalplaces = $wpdb->get_results($sql, ARRAY_A);
		foreach ($canalplaces as $canalplace) {
			print "F" . substr(trim($canalplace['canalplan_id']), 1, 10);
		}
		unset($canalplaces);
		$sql = $wpdb->prepare("select id from " . CANALPLAN_CANALS . " where fullname=%s", $place);
		$canalplaces = $wpdb->get_results($sql, ARRAY_A);
		foreach ($canalplaces as $canalplace) {
			print "W" . trim($canalplace['id']);
		}
	} else {
		$sql = $wpdb->prepare("select canalplan_id from " . CANALPLAN_CODES . " where place_name=%s", stripslashes($place));
		$canalplaces = $wpdb->get_results($sql, ARRAY_A);
		foreach ($canalplaces as $canalplace) {
			print "" . trim($canalplace['canalplan_id']);
		}
		$sql = $wpdb->prepare("select canalplan_id from " . CANALPLAN_ALIASES . " where place_name=%s", stripslashes($place));
		;
		$canalplaces = $wpdb->get_results($sql, ARRAY_A);
		foreach ($canalplaces as $canalplace) {
			print "" . trim($canalplace['canalplan_id']);
		}
	}
}
?>