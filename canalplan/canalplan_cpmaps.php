<?php

/*
	Copyright 2024, Steve Atty

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

function return_style($lat, $long, $maptype)
{
	global $wpdb, $post, $blog_id, $canalplan_map_code, $docpmmap, $canalplan_run_canal_route_maps;
	$region_array = array('ukuk' => 'uk', 'eueu' => 'europe', 'ieie' => 'ireland', 'ozoz' => 'australia', 'o4sr' => 'north-america');
	$canalplan_options = get_option('canalplan_options');
	//	var_dump($canalplan_options);
	$sql = $wpdb->prepare("SELECT place_name,canalplan_id,lat,`long`,region,ST_Length(LineString(lat_lng_point, ST_GeomFromText('Point(" . $lat . " " . $long . ")'))) AS distance FROM " . CANALPLAN_CODES . " where attributes != %s and region is not null ORDER BY distance ASC LIMIT 1", 'm');
	$res = $wpdb->get_results($sql, ARRAY_A);
	$row = $res[0];
	//var_dump($row['region']);
	if ($maptype == 'place') {
		$ukstyle = $canalplan_options["canalplan_cppm_type"];
		$nonukstyle = $canalplan_options["canalplan_cppmnotuk_type"];
	}
	if ($maptype == 'route') {
		$ukstyle = $canalplan_options["canalplan_cprm_type"];
		$nonukstyle = $canalplan_options["canalplan_cprmnotuk_type"];
	}
	if ($maptype == 'link') {
		$ukstyle = $canalplan_options["canalplan_cprm_type"];
		$nonukstyle = $canalplan_options["canalplan_cprmnotuk_type"];
	}
	$map_region = $region_array[$row['region']];
	//var_dump($map_region);
	$type = $nonukstyle;
	if ($map_region == 'uk')
		$type = $ukstyle;
	//var_dump($type); 
	$style = strtolower($type) . "_$map_region-style_background.json";
	if ($maptype == 'link')
		$style = strtolower($type) . "_$map_region-style.json";
	//var_dump($style);
	return $style;
}

function canal_route_maps($content, $mapblog_id = NULL, $post_id = NULL, $search = 'N')
{
	global $wpdb, $post, $blog_id, $canalplan_map_code, $docpmmap, $canalplan_run_canal_route_maps;
	// First we check the content for tags:
	if (preg_match_all('/' . preg_quote('[[CPRM') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[0];
	}
	// If the array is empty then we've no maps so don't do anything!
	if (!isset ($places_array)) {
		return $content;
	}
	if (count($places_array) == 0) {
		return $content;
	}
	if (get_query_var('feed') || $search == 'Y' || is_feed() || is_tag()) {
		$names = array();
		$links = array();
		foreach ($places_array as $place_code) {
			$words = explode(":", $place_code);
			$names[] = $place_code;
			$links[] = "[ Route Map embedded here ]";
		}
		return str_replace($names, $links, $content);
	}
	if (!isset ($canalplan_run_canal_route_maps[$post->ID])) {
		$canalplan_run_canal_route_maps[$post->ID] = 1;
	} else {
		$canalplan_run_canal_route_maps[$post->ID] = $canalplan_run_canal_route_maps[$post->ID] + 1;
	}
	if (isset ($mapblog_id)) {
	} else {
		$mapblog_id = $blog_id;
	}
	if (isset ($post_id)) {
	} else {
		$post_id = $post->ID;
		if (isset ($post->blog_id)) {
			$mapblog_id = $post->blog_id;
		}
	}

	$canalplan_map_code2 = '';
	//$mapstuff="<br />";
	$mapstuff = "";
	$minlat = 180;
	$maxlat = -180;
	$minlon = 90;
	$maxlon = -90;
	if ($canalplan_run_canal_route_maps[$post->ID] == 1) {
		$docpmmap = $docpmmap + 1;
	}
	$docpmmap = 'CPRM' . $mapblog_id . '_' . $post->ID;
	$canalplan_options = get_option('canalplan_options');
	$post_id = $post->ID;
	$sql = $wpdb->prepare("select distance,`locks`,start_id,end_id, day_coords from " . CANALPLAN_ROUTE_DAY . " where blog_id=%d and  post_id=%d", $mapblog_id, $post_id);
	$res = $wpdb->get_results($sql, ARRAY_A);
	$row = $res[0];
	$daycoords = explode("|", $row['day_coords']);
	$sql = $wpdb->prepare("select totalroute, total_coords from " . CANALPLAN_ROUTES . " cpr, " . CANALPLAN_ROUTE_DAY . " crd where cpr.route_id= crd.route_id and cpr.blog_id=crd.blog_id and crd.blog_id=%d and  crd.post_id=%d", $mapblog_id, $post_id);
	$res3 = $wpdb->get_results($sql, ARRAY_A);
	$place_count = 0;
	$row3 = $res3[0];
	$places = explode(",", $row3['totalroute']);
	if (isset ($row3['day_coords']))
		$totalcoords = explode("|", $row3['day_coords']);
	$dayroute = array_slice($places, $row['start_id'], ($row['end_id'] - $row['start_id']) + 1);
	$mid_point = round(count($dayroute) / 2, 0, PHP_ROUND_HALF_UP);
	$pointstring = "";
	$zoomstring = "";
	$lat = 0;
	$long = 0;
	$lpoint = "";
	$lpointb1 = "";
	$x = 3;
	$y = -1;
	$lastid = end($dayroute);
	$firstid = reset($dayroute);
	$turnaround = "";

	$options['zoom'] = $canalplan_options["canalplan_cprm_zoom"];
	$options['type'] = $canalplan_options["canalplan_cprm_type"];
	if (!isset ($options['type'])) {
		$options['type'] = 'bright';
	}
	if (!isset ($options['zoom'])) {
		$options['zoom'] = 9;
	}
	$options['lat'] = 53.4;
	$options['long'] = -2.8;
	$options['height'] = $canalplan_options["canalplan_cprm_height"];
	$options['width'] = $canalplan_options["canalplan_cprm_width"];
	$options['rgb'] = str_pad($canalplan_options["canalplan_cprm_r_hex"], 2, '0', STR_PAD_LEFT) . str_pad($canalplan_options["canalplan_cprm_g_hex"], 2, '0', STR_PAD_LEFT) . str_pad($canalplan_options["canalplan_cprm_b_hex"], 2, '0', STR_PAD_LEFT);
	$options['brush'] = $canalplan_options["canalplan_cprm_weight"];
	$brush = $options['brush'];
	$rgb = $options['rgb'];
	$words = substr($matches[1][0], 1);
	$opts = explode(",", $words);
	$markertext = '';
	$markertext2 = '';
	$markerdesc = '';
	$markerdesc2 = '';
	$json = '';
	foreach ($opts as $opt) {
		$opcode = explode("=", $opt);
		if (count($opcode) > 1) {
			$options[$opcode[0]] = strtoupper($opcode[1]);
		}
	}
	if (!canalplan_mobile()) {
		$mapstuff = '<div id="map_rmcanvas_' . $docpmmap . '" style="width: ' . $options['width'] . 'px; height: ' . $options['height'] . 'px"></div><br/>';
	} else
		$mapstuff = '<div id="map_rmcanvas_' . $docpmmap . '"  style="width:100%; height: ' . $options['height'] . 'px"></div><br/>';
	$previous_lat = 0;
	$previous_long = 0;
	$coordcount = 0;
	$row = array('lat' => "a", 'long' => "b", 'place_name' => "c", );
	foreach ($dayroute as $place) {
		$placecoords = explode(',', $daycoords[$coordcount]);
		//var_dump($daycoords[$coordcount]);
		$row['lat'] = $placecoords[0];
		$row['long'] = $placecoords[1];
		if (count($row) > 2 && strlen($row['lat']) > 0 && strlen($row['long']) > 0) {
			if ($row['lat'] < $minlat)
				$minlat = $row['lat'];
			if ($row['long'] < $minlon)
				$minlon = $row['long'];
			if ($row['lat'] > $maxlat)
				$maxlat = $row['lat'];
			if ($row['long'] > $maxlon)
				$maxlon = $row['long'];
			if ($place_count == $mid_point) {
				$centre_lat = $row['lat'];
				$centre_long = $row['long'];
			}
			$do_plot = 1;
			if ($previous_lat <> 0 && $previous_long <> 0) {
				$lat_dif = $row['lat'] - $previous_lat;
				$long_dif = $row['long'] - $previous_long;

				if (abs($lat_dif) > 0.1) {
					$row['lat'] = $previous_lat;
					$do_plot = 0;
				}
				if (abs($long_dif) > 0.1) {
					$row['long'] = $previous_long;
					$do_plot = 0;
				}
			}
			if ($do_plot == 1) {
				$centre_lat = $row['lat'];
				$centre_long = $row['long'];
				$previous_lat = $row['lat'];
				$previous_long = $row['long'];
				$place_count = $place_count + 1;
				if ($place == $firstid) {
					$sql = $wpdb->prepare("select `place_name` from " . CANALPLAN_CODES . " where canalplan_id=%s", $place);
					$res = $wpdb->get_results($sql, ARRAY_A);
					$row2 = $res[0];
					$row['place_name'] = $row2['place_name'];
					$firstname = addslashes($row['place_name']);
					$first_lat = $row['lat'];
					$first_long = $row['long'];
					$previous_lat = $row['lat'];
					$previous_long = $row['long'];
				}
				if ($place == $lastid) {
					$sql = $wpdb->prepare("select `place_name` from " . CANALPLAN_CODES . " where canalplan_id=%s", $place);
					$res = $wpdb->get_results($sql, ARRAY_A);
					$row2 = $res[0];
					$row['place_name'] = $row2['place_name'];
					$lastname = addslashes($row['place_name']);
					$last_lat = $row['lat'];
					$last_long = $row['long'];
				}
				$points = $place . "," . $row['lat'] . "," . $row['long'];
				$pointx = $row['lat'];
				$pointy = $row['long'];
				;
				$nlat = floor($pointx * 1e5);
				$nlong = floor($pointy * 1e5);
				$pointstring .= ascii_encode($nlat - $lat) . ascii_encode($nlong - $long);
				$zoomstring .= 'B';
				$lat = $nlat;
				$long = $nlong;
				$json .= "[$pointy, $pointx],";
				$cpoint = $row['lat'] . "," . $row['long'] . ',' . $place;
				if ($cpoint == $lpointb1) {
					$sql = $wpdb->prepare("select `place_name` from " . CANALPLAN_CODES . " where canalplan_id=%s", $oldplace);
					$res = $wpdb->get_results($sql, ARRAY_A);
					$row2 = $res[0];
					$row['place_name'] = $row2['place_name'];
					$lpoints = explode(",", $lpoint);
					$markertext .= '{"icon":"turnround_' . $x . '","lat":' . $lpoints[0] . ',"lng":' . $lpoints[1] . ',"idx":"' . $x . '"},';
					$markerdesc .= "<p id='day_header_$x' hidden>Turn Round Here : " . $row['place_name'] . CHR(13);
					$x = $x + 1;

				}
				$lpointb1 = $lpoint;
				$y = $y + 1;
				$lpoint = $cpoint;
				$oldplace = $place;
			}
		}
		$coordcount = $coordcount + 1;
	}
	$mapstyle = return_style($first_lat, $first_long, 'route');
	//	var_dump($mapstyle);
	if ($firstid == $lastid) {
		$markertext .= '{"icon":"small_yellow","lat":' . $first_lat . ',"lng":' . $first_long . ',"idx":"100"},';
		$markerdesc .= "<p id='day_header_100' hidden>Start / Finish : " . $firstname . CHR(13);
	} else {
		$markertext .= '{"icon":"small_green","lat":' . $first_lat . ',"lng":' . $first_long . ',"idx":"101"},';
		$markerdesc .= "<p id='day_header_101' hidden>Start : " . $firstname . CHR(13);
		$markertext .= '{"icon":"small_red","lat":' . $last_lat . ',"lng":' . $last_long . ',"idx":"102"},';
		$markerdesc .= "<p id='day_header_102' hidden>Finish : " . $lastname . CHR(13);

	}
	$zoom = $options['zoom'];
	$json = trim($json, ',');
	$mapstuff .= $markerdesc;
	$canalplan_map_code2 .= "var string_" . $docpmmap . " = '[$markertext]';";
	$canalplan_map_code2 .= " var map_$docpmmap = new maplibregl.Map({  container: 'map_rmcanvas_" . $docpmmap . "', style: '" . MAPSERVER_BASE . "/" . $mapstyle . "',
           center: [$centre_long, $centre_lat], // starting position [lng, lat]
		zoom: $zoom, // starting zoom
        attributionControl: true,
             hash: false,
        cooperativeGestures : true
      });
		map_" . $docpmmap . ".addControl(new maplibregl.NavigationControl());
		  map_" . $docpmmap . ".on('load', function() {

		map_" . $docpmmap . ".fitBounds([[$minlon-0.009, $minlat-0.009],[$maxlon+0.009, $maxlat+0.009]],{duration: 0});
		map_" . $docpmmap . ".addSource('route', {
			'type': 'geojson',
			'data': {
			'type': 'Feature',
			'properties': {},
			'geometry': {
			'type': 'LineString',
			'coordinates': [$json
			]
			}
			}
			});
			map_" . $docpmmap . ".addLayer({
			'id': 'route',
			'type': 'line',
			'source': 'route',
			'layout': {
			'line-join': 'round',
			'line-cap': 'round'
			},
			'paint': {
			'line-color': '#$rgb',
			'line-width': $brush
			}
			});
		
		";
	$canalplan_map_code2 .= "var stops_" . $docpmmap . " = eval(string_" . $docpmmap . ");
      var imgurl='" . plugin_dir_url(__FILE__) . "markers/';
       Add_Markers_Start(imgurl,map_" . $docpmmap . ",stops_" . $docpmmap . ");})";
	$names = array();
	$links = array();
	foreach ($places_array as $place_code) {
		$words = explode(":", $place_code);
		$names[] = $place_code;
		$links[] = $mapstuff;
	}
	if ($canalplan_run_canal_route_maps[$post->ID] == 1) {
		$canalplan_map_code .= $canalplan_map_code2;
	}
	return str_replace($names, $links, $content);
}



function canal_link_maps($content)
{
	global $wpdb, $post, $docpmmap, $canalplan_map_code, $canalplan_run_canal_link_maps;
	// First we check the content for tags:
	if (preg_match_all('/' . preg_quote('[[CPGMW:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[1];
	}
	// If the array is empty then we've no maps so don't do anything!
	if (!isset ($places_array)) {
		return $content;
	}
	if (count($places_array) == 0) {
		return $content;
	}
	if (!isset ($canalplan_run_canal_link_maps[$post->ID])) {
		$canalplan_run_canal_link_maps[$post->ID] = 1;
	} else {
		$canalplan_run_canal_link_maps[$post->ID] = $canalplan_run_canal_link_maps[$post->ID] + 1;
	}
	$canalplan_options = get_option('canalplan_options');

	if (!isset ($canalplan_options['canalplan_cppm_type'])) {
		$names = array();
		$links = array();
		foreach ($places_array as $place_code) {
			$words = explode("|", $place_code);
			$names[] = "[[CPGMW:" . $place_code . "]]";
			$links[] = "<b>Canalplan Maps not configured</b>";
		}
		return str_replace($names, $links, $content);
	}
	if (get_query_var('feed') || is_feed() || is_tag()) {
		$names = array();
		$links = array();
		foreach ($places_array as $place_code) {
			$words = explode("|", $place_code);
			$names[] = "[[CPGMW:" . $place_code . "]]";
			$links[] = "<b>[Embedded Canalplan Map for " . trim($words[0]) . "]</b>";
		}
		return str_replace($names, $links, $content);
	}

	$canalplan_map_code2 = '';
	$mapc = 0;
	foreach ($places_array as $place_code) {
		$mapc = $mapc + 1;
		$options['zoom'] = $canalplan_options["canalplan_cprm_zoom"];
		$options['type'] = $canalplan_options["canalplan_cprm_type"];


		if (!isset ($options['type'])) {
			$options['type'] = 'bright';
		}
		if (!isset ($options['zoom'])) {
			$options['zoom'] = 9;
		}
		$options['lat'] = 53.4;
		$options['long'] = -2.8;
		$options['height'] = $canalplan_options["canalplan_cprm_height"];
		$options['width'] = $canalplan_options["canalplan_cprm_width"];
		$options['rgb'] = $canalplan_options["canalplan_cprm_r_hex"] . $canalplan_options["canalplan_cprm_g_hex"] . $canalplan_options["canalplan_cprm_b_hex"];
		$options['brush'] = $canalplan_options["canalplan_cprm_weight"];
		//var_dump($canalplan_options);
		//	var_dump($options);
		$mapstuff = "<br />";
		$words = explode("|", $place_code);
		if (isset ($words[2])) {
			$opts = explode(",", $words[2]);
			foreach ($opts as $opt) {
				$opcode = explode("=", $opt);
				if (count($opcode) > 1) {
					$options[$opcode[0]] = strtoupper($opcode[1]);
				}
			}
		}
		$post_id = $post->ID;
		unset($missingpoly);
		unset($plines);
		unset($weights);
		unset($polylines);
		$missingpoly[] = $words[1];
		$sql2 = $wpdb->prepare(' select distinct lat,`long` from ' . CANALPLAN_CODES . ' where canalplan_id in (select distinct place1 from ' . CANALPLAN_LINK . ' where waterway in (select id from ' . CANALPLAN_CANALS . ' where parent=%s or id=%s)) limit 1', $words[1], $words[1]);
		$res = $wpdb->get_results($sql2, ARRAY_N);
		$rw = $res[0];
		$centre_lat = (float) $rw[0];
		$centre_long = (float) $rw[1];

		while (count($missingpoly) > 0) {
			reset($missingpoly);
			$sql = $wpdb->prepare("select 1 from " . CANALPLAN_POLYLINES . " where id=%s", current($missingpoly));
			$res = $wpdb->get_results($sql, ARRAY_A);
			if ($wpdb->num_rows == 1) {
				$polylines[] = current($missingpoly);
			}
			$sql = $wpdb->prepare("select id from " . CANALPLAN_CANALS . " where parent=%s", current($missingpoly));
			unset($missingpoly2);
			$res = $wpdb->get_results($sql, ARRAY_N);
			foreach ($res as $rw) {
				$missingpoly[] = $rw[0];
			}
			$missingpoly = array_slice($missingpoly, 1);
		}
		$markertext = "";
		$i = 1;
		$plinelist = "'" . implode("','", $polylines) . "'";
		$rgb = $options['rgb'];
		$brush = $options['brush'];
		$canalids = implode("','", $polylines);

		$sql = "select max(lat) maxlat,min(lat) minlat ,max(`long`) maxlon,min(`long`) minlon from " . CANALPLAN_CODES . " where place_name not like '!%' and canalplan_id in (select distinct place1 from " . CANALPLAN_LINK . " where waterway in ('" . $canalids . "'))";
		//print "@@@@".$sql;
		$res = $wpdb->get_results($sql, ARRAY_A);
		$maxlat = $res[0]['maxlat'];
		$minlat = $res[0]['minlat'];
		$maxlon = $res[0]['maxlon'];
		$minlon = $res[0]['minlon'];
		//var_dump($res[0]);
		$mapstyle = return_style($maxlat, $maxlon, 'link');
		//print $mapstyle;
		$docpmmap = 'CPGMW' . $words[1] . '_' . $post_id . '_' . $mapc;

		if (!canalplan_mobile()) {
			$mapstuff = '<div id="map_canvas_' . $docpmmap . '" style="width: ' . $options['width'] . 'px; height: ' . $options['height'] . 'px"></div> <br/>';
		} else
			$mapstuff = '<div id="map_canvas_' . $docpmmap . '"  style="width:100%; height: ' . $options['height'] . 'px"></div><br/>';

		$zoom = $options['zoom'];
		$canalplan_map_code .= "var cpids_$mapc = [$plinelist,]; var map_$docpmmap = new maplibregl.Map({  container: 'map_canvas_" . $docpmmap . "', style: '" . MAPSERVER_BASE . "/" . $mapstyle . "',
           center: [($maxlon + $minlon)/2, ($maxlat + $minlat)/2], // starting position [lng, lat]
		zoom: $zoom, // starting zoom
        attributionControl: true,
             hash: false,
        cooperativeGestures : true
      });
		map_" . $docpmmap . ".addControl(new maplibregl.NavigationControl());
		  map_" . $docpmmap . ".on('load', function() {
		map_" . $docpmmap . ".setLayoutProperty('cp-place-mooring', 'visibility', 'none'); // visible or none
		map_" . $docpmmap . ".setLayoutProperty('cp-place-fixedbridges', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-place-smallplaces', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-place-bigplaces', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-place-movebridges', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-place-winding', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-place-locks', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-place-junctions', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-place-facilities', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-place-mooring', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-narrow', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-narrow-excluded', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-broad-excluded', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-commercial', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-commercial-excluded', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-small', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-small-excluded', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-large', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-large-excluded', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-tidal', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-tidal-excluded', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-seaway', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-seaway-excluded', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-lake', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-lake-excluded', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-broad', 'visibility', 'none');
		map_" . $docpmmap . ".setLayoutProperty('cp-waterway-seaway', 'visibility', 'none');
		map_" . $docpmmap . ".fitBounds([[$minlon-0.1, $minlat-0.1],[$maxlon+0.1, $maxlat+0.1]],{duration: 0});
		map_" . $docpmmap . ".addLayer({
            'id': 'highlight-waterway',
            'type': 'line',
            'source': 'canalplantiles',
            'source-layer': 'canalplan_waterways',
            'filter': ['match', ['get','cp_id'], cpids_$mapc, true, false],
            'layout': {
                'line-join': 'round',
                'line-cap': 'round'
            },
            'paint': {
                'line-color': '#$rgb',
                'line-width': $brush
            }
        },'cp-waterway-lake-excluded');  // above last canal, below labels
	}
	);
	";

		if ($canalplan_run_canal_link_maps[$post->ID] == 1) {
			$canalplan_map_code .= $canalplan_map_code2;
		}

		$names[] = "[[CPGMW:" . $place_code . "]]";
		$links[] = $mapstuff;
	}
	if ($canalplan_run_canal_link_maps[$post->ID] == 1) {
		$canalplan_map_code .= $canalplan_map_code2;
	}
	return str_ireplace($matches[0], $links, $content);
}
function canal_place_maps($content, $mapblog_id = NULL, $post_id = NULL)
{
	global $docpmmap, $wpdb, $post, $canalplan_map_code, $blog_id, $canalplan_run_canal_place_maps;
	$gazstring = CANALPLAN_URL . 'gazetteer.cgi?id=';
	$canalplan_options = get_option('canalplan_options');

	// We don't support maps for features so lets just clean it from the content and return;
	if (preg_match_all('/' . preg_quote('[[CPGMF:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches2)) {
		$null_link[] = '';
		return str_ireplace($matches2[0], $null_link, $content);
	}

	if (preg_match_all('/' . preg_quote('[[CPGM:') . '(.*?)' . preg_quote(']]') . '/', $content, $matches)) {
		$places_array = $matches[1];
	}
	// If the array is empty then we've no links so don't do anything!
	if (!isset ($places_array)) {
		return $content;
	}

	if (count($places_array) == 0) {
		return $content;
	}
	if (!isset ($canalplan_run_canal_place_maps[$post->ID])) {
		$canalplan_run_canal_place_maps[$post->ID] = 1;
	} else {
		$canalplan_run_canal_place_maps[$post->ID] = $canalplan_run_canal_place_maps[$post->ID] + 1;
	}
	if (isset ($mapblog_id)) {
	} else {
		$mapblog_id = $blog_id;
	}
	if (isset ($post_id)) {
	} else {
		$post_id = $post->ID;
		if (isset ($post->blog_id)) {
			$mapblog_id = $post->blog_id;
		}
	}
	$names = array();
	$links = array();
	if (!isset ($canalplan_options['canalplan_cppm_type'])) {
		$names = array();
		$links = array();
		foreach ($places_array as $place_code) {
			$words = explode("|", $place_code);
			$names[] = "[[CPGM:" . $place_code . "]]";
			$links[] = "<b>Canalplan Maps not configured</b>";
		}
		return str_replace($names, $links, $content);
	}
	if (get_query_var('feed') || is_feed() || is_tag()) {
		foreach ($places_array as $place_code) {
			$words = explode("|", $place_code);
			$names[] = "[[CPGM:" . $place_code . "]]";
			$links[] = "<b>[Embedded Canalplan Map for " . trim($words[0]) . "]</b>";
		}
		return str_ireplace($names, $links, $content);
	}

	$canalplan_map_code2 = '';
	$mapc = 0;
 
	foreach ($places_array as $place_code) {
		$words = explode("|", $place_code);
		$options['height'] = $canalplan_options["canalplan_cppm_height"];
		$options['width'] = $canalplan_options["canalplan_cppm_width"];
		$options['zoom'] = $canalplan_options["canalplan_cppm_zoom"];
		$options['type'] = $canalplan_options["canalplan_cppm_type"];
		$options['place_name'] = $words[0];
		if(trim(strtolower($words[1]))=='nothing') { 
			$row=explode(',',$words[2]);
			if (isset($row[0]))  {
				$bits=explode('=',$row[0]);
				$options[$bits[0]] = $bits[1];
			}
			if (isset($row[1]))  {
				$bits=explode('=',$row[1]);
				$options[$bits[0]] = $bits[1];
			}
			if (isset($row[2]))  {
				$bits=explode('=',$row[2]);
				$options[$bits[0]] = $bits[1];
			}
			if (isset($row[3]))  {
				$bits=explode('=',$row[3]);
				$options[$bits[0]] = $bits[1];
			}
			if (isset($row[4]))  {
				$bits=explode('=',$row[4]);
				$options[$bits[0]] = $bits[1];
			}
		}
		else {
			$sql = $wpdb->prepare("select lat,`long`,place_name from " . CANALPLAN_CODES . " where canalplan_id=%s", $words[1]);
			$res = $wpdb->get_results($sql, ARRAY_A);
			if (count($res) > 0) {
				$row = $res[0];
				$options['lat'] = $row['lat'];
				$options['long'] = $row['long'];
				$options['place_name'] = $row['place_name'];
			}
		}
		$mapc = $mapc + 1;
		$mapstyle = return_style($options['lat'], $options['long'], 'place');
		//	var_dump($canalplan_options);
		
		if (!isset ($options['type'])) {
			$options['type'] = 'bright';
		}
		if (!isset ($options['zoom'])) {
			$options['zoom'] = 9;
		}
		if (count($words) >= 3) {
			$opts = explode(",", $words[2]);
			foreach ($opts as $opt) {
				$opcode = explode("=", $opt);
				if (count($opcode) > 1) {
					$options[$opcode[0]] = strtoupper($opcode[1]);
				}
			}
		}
		$mapstuff = "<br />";
		$docpmmap = 'CPGM' . $words[1] . '_' . $post->ID . '_' . $mapc;
		$pname = $options['place_name'];
		echo "<br/><p id='day_header_$docpmmap' hidden><br>$pname";
		if (!canalplan_mobile()) {
			$mapstuff = '<div id="map_canvas_' . $docpmmap . '" style="width: ' . $options['width'] . 'px; height: ' . $options['height'] . 'px"></div> <br/>';
		} else
			$mapstuff = '<div id="map_canvas_' . $docpmmap . '"  style="width:100%; height: ' . $options['height'] . 'px"></div><br/>';

		$names[] = "[[CPGM:" . $place_code . "]]";
		$links[] = $mapstuff;
		$zoom = $options['zoom'];
		$canalplan_map_code2 .= "var map_$docpmmap = new maplibregl.Map({  container: 'map_canvas_" . $docpmmap . "', style: '" . MAPSERVER_BASE . "/" . $mapstyle . "',
           center: [" . $options['long'] . ", " . $options['lat'] . "], // starting position [lng, lat]
		zoom: $zoom, // starting zoom
        attributionControl: true,
             hash: false,
        cooperativeGestures : true
      });
		map_" . $docpmmap . ".addControl(new maplibregl.NavigationControl());

		 map_" . $docpmmap . ".on('load', function() {
      var string_" . $docpmmap . " = '[{\"icon\":\"blank\",\"lat\":" . $options['lat'] . ",\"lng\":" . $options['long'] . ",\"idx\":\"$docpmmap\"}]';
      var stops_" . $docpmmap . " = eval(string_" . $docpmmap . ");
      var imgurl='" . plugin_dir_url(__FILE__) . "markers/';
       Add_Markers_Start(imgurl,map_" . $docpmmap . ",stops_" . $docpmmap . ");});";
	}
	if ($canalplan_run_canal_place_maps[$post->ID] == 1) {
		$canalplan_map_code .= $canalplan_map_code2;
	}
	return str_ireplace($matches[0], $links, $content);
}

function canal_bloggedroute($embed = 0, $overnight = "N", $routetag = NULL)
{
	global $wpdb, $blog_id, $canalplan_map_code, $docpmmap;

	if (!isset ($_GET['routeid'])) {
		$_GET['routeid'] = 0;
	}
	$routeid = $_GET['routeid'];
	$routeid = preg_replace('{/$}', '', $routeid);
	$routeid = $routeid . "'";
	$matches = array();
	preg_match('/([0-9]+)([^0-9]+)/', $routeid, $matches);
	$routeid = $matches[1];
	$blroute = '';
	$previous_lat = 0;
	$previous_long = 0;
	$tr = 1;
	$json = '';
	$minlat = 180;
	$maxlat = -180;
	$minlon = 90;
	$maxlon = -90;
	$markertext = '';
	$markertext2 = '';
	$markerdesc = '';
	$markerdesc2 = '';
	$canalplan_map_code2 = '';
	$canalplan_options = get_option('canalplan_options');
	$options['zoom'] = $canalplan_options["canalplan_cprm_zoom"];
	$options['type'] = $canalplan_options["canalplan_cprm_type"];
	if (!isset ($options['type'])) {
		$options['type'] = 'bright';
	}
	if (!isset ($options['zoom'])) {
		$options['zoom'] = 9;
	}
	$options['lat'] = 53.4;
	$options['long'] = -2.8;
	$options['height'] = $canalplan_options["canalplan_cprm_height"];
	$options['width'] = $canalplan_options["canalplan_cprm_width"];
	$options['rgb'] = str_pad($canalplan_options["canalplan_cprm_r_hex"], 2, '0', STR_PAD_LEFT) . str_pad($canalplan_options["canalplan_cprm_g_hex"], 2, '0', STR_PAD_LEFT) . str_pad($canalplan_options["canalplan_cprm_b_hex"], 2, '0', STR_PAD_LEFT);
	$options['brush'] = $canalplan_options["canalplan_cprm_weight"];
	$brush = $options['brush'];
	$rgb = $options['rgb'];
	if (!isset ($routeid)) {
		$routeid = 0;
	}
	if ($routeid <= 0) {
		$routeid = 0;
	}
	if ($embed > 0) {
		$routeid = $embed;
	}

	$docpmmap = 1;
	if (get_query_var('feed') || is_feed() || is_tag()) {
		$links[] = "<b>[ Canalplan Route Map embedded here ]</b>";
	} else {
		$canalplan_options = get_option('canalplan_options');
		if (!isset ($canalplan_options['canalplan_cppm_type'])) {
			$links[] = "<b>Canalplan Maps not enabled</b>";
		} else {
			if ($embed >= 1) {
				$routeid = $embed;
				$docpmmap = $embed;
			}
			if ($routeid == 0) {
				//print "Route ID ".$routeid;
				//	print "Blog ID ".$blog_id;
					//print "Blog ID ".$blog_id;
				if ($blog_id == 1) {

					$sql = $sql = $wpdb->prepare("select route_id, title,blog_id from " . CANALPLAN_ROUTES . " where status=3 and routetag=%s order by start_date desc", $routetag);
				//	print $sql;
					if (is_null($routetag))
						$sql = "select route_id,title,blog_id from " . CANALPLAN_ROUTES . " where status=3 order by start_date desc";
				} else {
					$sql = $wpdb->prepare("select route_id, title,description,blog_id from " . CANALPLAN_ROUTES . " where status=3 and blog_id=%d and routetag=%s order by start_date desc", $blog_id, $routetag);
					if (is_null($routetag))
						$sql = $wpdb->prepare("select route_id, title,description,blog_id from " . CANALPLAN_ROUTES . " where status=3 and blog_id=%d order by start_date desc", $blog_id);
				}
				if (!defined('CANALPLAN_ROUTE_SLUG')) {
					$r2 = $wpdb->get_results("SELECT pref_value FROM " . CANALPLAN_OPTIONS . " where blog_id=$blog_id and pref_code='routeslug'", ARRAY_A);
					if ($wpdb->num_rows == 0) {
						$routeslug = "UNDEFINED!";
					} else {
						$routeslug = $r2[0]['pref_value'];
					}
				} else {
					$routeslug = CANALPLAN_ROUTE_SLUG;
				}
				$res = $wpdb->get_results($sql, ARRAY_A);
				$blroute .= "<ol>";
				foreach ($res as $row) {
					if (is_multisite()) {
						if ($blog_id == 1) {
							$blroute .= '<li><a href=' . get_blog_option($row['blog_id'], "siteurl") . '/' . $routeslug . '/?routeid=' . $row['route_id'] . ' target=\"_new\">' . $row['title'] . '</a> ( from ' . get_blog_option($row['blog_id'], 'blogname') . ' )  </li>';
						} else {
							$blroute .= '<li><a href=' . get_blog_option($row['blog_id'], "siteurl") . '/' . $routeslug . '/?routeid=' . $row['route_id'] . ' target=\"_new\">' . $row['title'] . '</a> (' . $row['description'] . ')</li>';
						}
					}
					if (!is_multisite()) {
						if ($blog_id == 1) {
							$blroute .= '<li><a href=' . get_option("siteurl") . '/' . $routeslug . '/?routeid=' . $row['route_id'] . ' target=\"_new\">' . $row['title'] . '</a> ( from ' . get_option('blogname') . ' )  </li>';
						} else {
							$blroute .= '<li><a href=' . get_option("siteurl") . '/' . $routeslug . '/?routeid=' . $row['route_id'] . ' target=\"_new\">' . $row['title'] . '</a> (' . $row['description'] . ')</li>';
						}
					}
				}
				$blroute .= "</ol><br><br>";
			} else {
				$sql = $wpdb->prepare("select description, totalroute, total_coords from " . CANALPLAN_ROUTES . " where route_id=%d and blog_id=%d", $routeid, $blog_id);
				$res = $wpdb->get_results($sql, ARRAY_A);
				$row = $res[0];
				$mid_point = round(count(explode('|', $row['totalroute'])) / 2, 0, PHP_ROUND_HALF_UP);
				$place_count = 0;
				$row = $res[0];
				$coordcount = 0;
				if ($embed == 0) {
					$blroute .= "<h2>" . $row['description'] . "</h2><br/>";
				}
				if (!canalplan_mobile()) {
					$blroute .= '<div id="mapbl_canvas_' . $overnight . '_' . $docpmmap . '"  style="width: ' . $canalplan_options["canalplan_cprm_width"] . 'px; height: ' . $canalplan_options["canalplan_cprm_height"] . 'px"></div><br/>';
				} else
					$blroute .= '<div id="mapbl_canvas_' . $overnight . '_' . $docpmmap . '"  style="width:100%; height: ' . $canalplan_options["canalplan_cprm_height"] . 'px"></div><br/>';

				$pointstring = "";
				$zoomstring = "";
				$lat = 0;
				$long = 0;
				$lpoint = "";
				$lpointb1 = "";
				$x = 3;
				$y = -1;
				$places = explode(",", $row['totalroute']);
				$placescoords = explode("|", $row['total_coords']);
				$lastid = end($places);
				$firstid = reset($places);
				$turnaround = "";
				$firstname = "";
				$first_lat = "";
				$first_long = "";
				$row = array('lat' => "a", 'long' => "b", 'place_name' => "c", );
				foreach ($places as $place) {
					$placecoords = explode(',', $placescoords[$coordcount]);
					$coordcount = $coordcount + 1;
					$row['lat'] = $placecoords[0];
					$row['long'] = $placecoords[1];
					if ($row['lat'] < $minlat)
						$minlat = $row['lat'];
					if ($row['long'] < $minlon)
						$minlon = $row['long'];
					if ($row['lat'] > $maxlat)
						$maxlat = $row['lat'];
					if ($row['long'] > $maxlon)
						$maxlon = $row['long'];
					$do_plot = 1;
					if ($previous_lat <> 0 && $previous_long <> 0) {
						$lat_dif = $row['lat'] - $previous_lat;
						$long_dif = $row['long'] - $previous_long;

						if (abs($lat_dif) > 0.1) {
							$row['lat'] = $previous_lat;
							$do_plot = 0;
						}
						if (abs($long_dif) > 0.1) {
							$row['long'] = $previous_long;
							$do_plot = 0;
						}
					}
					if ((count($row) >= 2) && (strlen($row['lat']) >= 4) && (strlen($row['long']) >= 3) && ($do_plot == 1)) {
						$previous_lat = $row['lat'];
						$previous_long = $row['long'];
						if ($place == $firstid) {
							$sql = $wpdb->prepare("select `place_name` from " . CANALPLAN_CODES . " where canalplan_id=%s", $place);
							$res = $wpdb->get_results($sql, ARRAY_A);
							$row2 = $res[0];
							$row['place_name'] = $row2['place_name'];
							$firstname = addslashes($row['place_name']);
							$first_lat = $row['lat'];
							$first_long = $row['long'];
						}
						if ($place == $lastid) {
							$sql = $wpdb->prepare("select `place_name` from " . CANALPLAN_CODES . " where canalplan_id=%s", $place);
							$res = $wpdb->get_results($sql, ARRAY_A);
							$row2 = $res[0];
							$row['place_name'] = $row2['place_name'];
							$lastname = addslashes($row['place_name']);
							$last_lat = $row['lat'];
							$last_long = $row['long'];
						}
						if ($place_count == $mid_point) {
							$centre_lat = $row['lat'];
							$centre_long = $row['long'];
						}
						$place_count = $place_count + 1;
						$points = $place . "," . $row['lat'] . "," . $row['long'];
						$pointx = $row['lat'];
						$pointy = $row['long'];
						$nlat = floor($pointx * 1e5);
						$nlong = floor($pointy * 1e5);
						$json .= "[$pointy, $pointx],";
						$pointstring .= ascii_encode($nlat - $lat) . ascii_encode($nlong - $long);
						$zoomstring .= 'B';
						$lat = $nlat;
						$long = $nlong;
						$cpoint = $row['lat'] . "," . $row['long'] . ',' . $place;
						if ($cpoint == $lpointb1) {
							$sql = $wpdb->prepare("select `place_name` from " . CANALPLAN_CODES . " where canalplan_id=%s", $oldplace);
							$res = $wpdb->get_results($sql, ARRAY_A);
							$row2 = $res[0];
							$row['place_name'] = $row2['place_name'];
							$lpoints = explode(",", $lpoint);
							$markertext .= '{"icon":"turnround_' . $tr . '","lat":' . $lpoints[0] . ',"lng":' . $lpoints[1] . ',"idx":"9' . $tr . '"},';
							$markerdesc2 .= "<p id='day_header_9$tr' hidden>Turn Round Here : " . $row['place_name'] . CHR(13);
							$tr = $tr + 1;
						}
						$lpointb1 = $lpoint;
						$y = $y + 1;
						$lpoint = $cpoint;
						$oldplace = $place;
					}
				}

				if ($firstid == $lastid) {
					$markertext .= '{"icon":"small_yellow","lat":' . $first_lat . ',"lng":' . $first_long . ',"idx":"300"},';
					$markerdesc .= "<p id='day_header_300' hidden>Start / Finish : " . $firstname . CHR(13);
				} else {
					$markertext .= '{"icon":"small_green","lat":' . $first_lat . ',"lng":' . $first_long . ',"idx":"301"},';
					$markerdesc .= "<p id='day_header_301' hidden>Start : " . $firstname . CHR(13);
					$markertext .= '{"icon":"small_red","lat":' . $last_lat . ',"lng":' . $last_long . ',"idx":"302"},';
					$markerdesc .= "<p id='day_header_302' hidden>Finish : " . $lastname . CHR(13);

				}

				if ($overnight == 'Y') {
					$sql = $wpdb->prepare("select day_id,end_id from " . CANALPLAN_ROUTE_DAY . " where blog_id=%d and route_id=%d and day_id > 0", $blog_id, $routeid);
					$res = $wpdb->get_results($sql, ARRAY_A);
					$markertext2 = '';
					$x = 0;
					foreach ($res as $dayresult) {

						$endp = $places[$dayresult['end_id']];
						$sql = $wpdb->prepare("select distinct canalplan_id, place_name from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d union select canalplan_id, place_name from " . CANALPLAN_CODES . " where canalplan_id=%s and canalplan_id not in (select canalplan_id from " . CANALPLAN_FAVOURITES . " where canalplan_id=%s and blog_id=%d)", $endp, $blog_id, $endp, $endp, $blog_id);
						$res3 = $wpdb->get_results($sql, ARRAY_A);
						$endplaces[] = $res3[0];
					}
					if (is_array($endplaces)) {
						$endplace = array_pop($endplaces);
						foreach ($endplaces as $dayid => $onplace) {
							$sql = $wpdb->prepare("select `lat`,`long`,`place_name` from " . CANALPLAN_CODES . " where canalplan_id=%s", $onplace["canalplan_id"]);
							$res = $wpdb->get_results($sql, ARRAY_A);
							$row = '';
							if (count($res) > 0) {
								$row = $res[0];
								$x = $x + 1;
								$markertext .= '{"icon":"cp_' . $x . '","lat":' . $row['lat'] . ',"lng":' . $row['long'] . ',"idx":"' . $x . '"},';
								$markerdesc .= "<p id='day_header_$x' hidden>Overnight at : " . $row['place_name'] . CHR(13);
							}
						}
					}
				}
				$options['size'] = 200;
				$options['zoom'] = $canalplan_options["canalplan_cprm_zoom"];
				$options['type'] = $canalplan_options["canalplan_cprm_type"];
				if (!isset ($options['type'])) {
					$options['type'] = 'bright';
				}
				if (!isset ($options['zoom'])) {
					$options['zoom'] = 9;
				}
				$json = trim($json, ',');
				$options['lat'] = 53.4;
				$options['long'] = -2.8;
				$options['rgb'] = str_pad($canalplan_options["canalplan_cprm_r_hex"], 2, '0', STR_PAD_LEFT) . str_pad($canalplan_options["canalplan_cprm_g_hex"], 2, '0', STR_PAD_LEFT) . str_pad($canalplan_options["canalplan_cprm_b_hex"], 2, '0', STR_PAD_LEFT);

				// IN HERE
				$mapstyle = return_style($centre_lat, $centre_long, 'route');
				$zoom = $options['zoom'];
				$json = trim($json, ',');
				$blroute .= $markerdesc . $markerdesc2;
				//var_dump($markertext);
				$markertext = rtrim($markertext, ",");
				//var_dump($markertext);
				$canalplan_map_code2 = "var string_" . $docpmmap . " = '[$markertext]';";
				$canalplan_map_code2 .= " var map_$docpmmap = new maplibregl.Map({  container: 'mapbl_canvas_" . $overnight . "_" . $docpmmap . "', style: '" . MAPSERVER_BASE . "/" . $mapstyle . "',
           center: [$centre_long, $centre_lat], // starting position [lng, lat]
		zoom: $zoom, // starting zoom
        attributionControl: true,
             hash: false,
        cooperativeGestures : true
      });
		map_" . $docpmmap . ".addControl(new maplibregl.NavigationControl());
		  map_" . $docpmmap . ".on('load', function() {

		map_" . $docpmmap . ".fitBounds([[$minlon-0.06, $minlat-0.06],[$maxlon+0.06, $maxlat+0.06]],{duration: 0});
		map_" . $docpmmap . ".addSource('route', {
			'type': 'geojson',
			'data': {
			'type': 'Feature',
			'properties': {},
			'geometry': {
			'type': 'LineString',
			'coordinates': [$json
			]
			}
			}
			});
			map_" . $docpmmap . ".addLayer({
			'id': 'route',
			'type': 'line',
			'source': 'route',
			'layout': {
			'line-join': 'round',
			'line-cap': 'round'
			},
			'paint': {
			'line-color': '#$rgb',
			'line-width': $brush
			}
			});
		";
				$canalplan_map_code2 .= "var stops_" . $docpmmap . " = eval(string_" . $docpmmap . ");
      var imgurl='" . plugin_dir_url(__FILE__) . "markers/';
       Add_Markers_Start(imgurl,map_" . $docpmmap . ",stops_" . $docpmmap . ");
       })";

			}

		}
	}
	if ($canalplan_map_code == "<script>")
		$canalplan_map_code .= $canalplan_map_code2;
	if ($embed == 0 && $routeid > 0) {
		$blroute .= "<p><h2>Blog Entries for this trip</h2>";
		//print get_current_blog_id();
		//print $blog_id;
		$sql = "select id, post_title, crd.day_id from " . $wpdb->posts . " bp, " . CANALPLAN_ROUTE_DAY . " crd where bp.id = crd.post_id and crd.blog_id=" . get_current_blog_id() . " and  crd.route_id=" . $routeid . " and post_status='publish' order by crd.day_id asc";
		//print $sql;
		$res = $wpdb->get_results($sql, ARRAY_A);
		$blroute .= "<ol>";
		foreach ($res as $row) {
			$link = get_permalink($row['id']);
			$extra = '';
			if ($row['day_id'] == 0)
				$extra = '( Trip Summary )';
			$blroute .= "<li><a href=\"$link\" target=\"_new\">$row[post_title] $extra</a> </li>";
		}
		$blroute .= "</ol>";
	}
	return $blroute;
}

?>
