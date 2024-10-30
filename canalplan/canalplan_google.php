<?php

/*
	Copyright 2019, Steve Atty

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

function canal_route_maps($content,$mapblog_id=NULL,$post_id=NULL,$search='N') {
    global $wpdb,$post,$blog_id,$google_map_code,$dogooglemap,$canalplan_run_canal_route_maps;
	// First we check the content for tags:
	if (preg_match_all('/' . preg_quote('[[CPRM') . '(.*?)' . preg_quote(']]') .'/',$content,$matches)) { $places_array=$matches[0]; }
	// If the array is empty then we've no maps so don't do anything!
	if (!isset($places_array)) {return $content;}
	if (count($places_array)==0) {return $content;}
		if ( get_query_var('feed') || $search=='Y' || is_feed() ||  is_tag() )  {
		$names = array();
		$links = array();
		foreach ($places_array as $place_code) {
			$words=explode(":",$place_code);
			$names[] = $place_code;
			$links[] ="[ Google Route Map embedded here ]" ;
		}
	return str_replace($names,$links , $content);
	}
	if(!isset($canalplan_run_canal_route_maps[$post->ID])) {$canalplan_run_canal_route_maps[$post->ID]=1;} else {
		$canalplan_run_canal_route_maps[$post->ID]=$canalplan_run_canal_route_maps[$post->ID]+1;
	}
    if (isset($mapblog_id)) {} else { $mapblog_id=$blog_id;}
    if (isset($post_id)) {} else {$post_id=$post->ID;
    if (isset($post->blog_id)) {$mapblog_id=$post->blog_id;}}

	$google_map_code2='';
	//$mapstuff="<br />";
	$mapstuff="";
	if($canalplan_run_canal_route_maps[$post->ID]==1) {$dogooglemap=$dogooglemap+1;}
	$dogooglemap='CPRM'.$mapblog_id.'_'.$post->ID;
	$canalplan_options = get_option('canalplan_options');
	$post_id=$post->ID;
 	$sql=$wpdb->prepare("select distance,`locks`,start_id,end_id, day_coords from ".CANALPLAN_ROUTE_DAY." where blog_id=%d and  post_id=%d",$mapblog_id,$post_id);
	$res = $wpdb->get_results($sql,ARRAY_A);
	$row = $res[0];
	$daycoords=explode("|",$row['day_coords']);
	$sql=$wpdb->prepare("select totalroute, total_coords from ".CANALPLAN_ROUTES." cpr, ".CANALPLAN_ROUTE_DAY." crd where cpr.route_id= crd.route_id and cpr.blog_id=crd.blog_id and crd.blog_id=%d and  crd.post_id=%d",$mapblog_id,$post_id);
	$res3 = $wpdb->get_results($sql,ARRAY_A);
	$place_count=0;
	$row3 = $res3[0];
	$places=explode(",",$row3['totalroute']);
	if(isset($row3['day_coords'])) $totalcoords=explode("|",$row3['day_coords']);
	$dayroute=array_slice($places,$row['start_id'], ($row['end_id'] - $row['start_id'])+1);
	$mid_point=round(count($dayroute)/2,0,PHP_ROUND_HALF_UP);
	$pointstring = "";
	$zoomstring = "";
	$lat = 0;
	$long = 0;
	$lpoint="";
	$lpointb1="";
	$x=3;
	$y=-1;
	$lastid=end($dayroute);
	$firstid=reset($dayroute);
	$turnaround="";
   	$maptype['S']="SATELLITE";
   	$maptype['R']="ROADMAP";
   	$maptype['T']="TERRAIN";
   	$maptype['H']="HYBRID";
	$options['zoom']=$canalplan_options["canalplan_rm_zoom"];
	$options['type']=$canalplan_options["canalplan_rm_type"];
	if (!isset($options['type'])) {$options['type']='H';}
	if (!isset($options['zoom'])) {$options['zoom']=9;}
	$options['lat']=53.4;
	$options['long']=-2.8;
	$options['height']=$canalplan_options["canalplan_rm_height"];
	$options['width']=$canalplan_options["canalplan_rm_width"];
	$options['rgb']=$canalplan_options["canalplan_rm_r_hex"].$canalplan_options["canalplan_rm_g_hex"].$canalplan_options["canalplan_rm_b_hex"];
	$options['brush']=$canalplan_options["canalplan_rm_weight"];
	$words=substr($matches[1][0],1);
	$opts=explode(",",$words);
	foreach ($opts as $opt) {
		 $opcode=explode("=",$opt);
		 if (count($opcode)>1) {$options[$opcode[0]]=strtoupper($opcode[1]);}
	}
	if (canalplan_mobile()) {  
	 $mapstuff.= '<div id="map_canvas_'.$dogooglemap.'" style="width:100%; height: '.$options['height'].'px"></div>';}
	  else
	$mapstuff.= '<div id="map_canvas_'.$dogooglemap.'" style="width: '.$options['width'].'px; height: '.$options['height'].'px"></div>';
	$previous_lat=0;
	$previous_long=0;
	$coordcount=0;
	$row = array('lat'  => "a",'long' => "b", 'place_name' => "c",);
	foreach ($dayroute as $place) {
		$placecoords=explode(',',$daycoords[$coordcount]);
		
		$row['lat']=$placecoords[0];
		$row['long']=$placecoords[1];
		if (count($row)> 2 && strlen($row['lat']) >0 && strlen($row['long']) >0 ) {
			if($place_count==$mid_point) {
				$centre_lat=$row['lat'];
				$centre_long=$row['long'];
			}
			$do_plot=1;
			if ($previous_lat <> 0 && $previous_long <> 0) {
			 $lat_dif= $row['lat']-$previous_lat;
			 $long_dif= $row['long']-$previous_long;
		
			if (abs($lat_dif) > 0.1 ) {
				$row['lat']=$previous_lat;
				$do_plot=0;
			}
			if (abs($long_dif) > 0.1 ) {
				$row['long']=$previous_long;
				$do_plot=0;
			}
			}
			if ($do_plot==1) {
			$previous_lat=$row['lat'];
			$previous_long=$row['long'];
			$place_count=$place_count+1;
			if ($place==$firstid){
				$sql=$wpdb->prepare("select `place_name` from ".CANALPLAN_CODES." where canalplan_id=%s",$place);
				$res =  $wpdb->get_results($sql,ARRAY_A);
				$row2 = $res[0];
				$row['place_name']=$row2['place_name'];
				$firstname=addslashes($row['place_name']);
				$first_lat=$row['lat'];
				$first_long=$row['long'];
				$previous_lat=$row['lat'];
				$previous_long=$row['long'];
			}
			if ($place==$lastid){
				$sql=$wpdb->prepare("select `place_name` from ".CANALPLAN_CODES." where canalplan_id=%s",$place);
				$res =  $wpdb->get_results($sql,ARRAY_A);
				$row2 = $res[0];
				$row['place_name']=$row2['place_name'];
				$lastname=addslashes($row['place_name']);
				$last_lat=$row['lat'];
				$last_long=$row['long'];
			}
				$points=$place.",".$row['lat'].",".$row['long'];
				$pointx = $row['lat'];
				$pointy = $row['long'];;
				$nlat = floor($pointx * 1e5);
				$nlong = floor($pointy * 1e5);
				$pointstring .= ascii_encode($nlat - $lat) . ascii_encode($nlong -  $long);
				$zoomstring .= 'B';
				$lat = $nlat;
				$long = $nlong;
				$cpoint=$row['lat'].",".$row['long'].','.$place;
				if ($cpoint==$lpointb1) {
					$sql=$wpdb->prepare("select `place_name` from ".CANALPLAN_CODES." where canalplan_id=%s",$oldplace);
					$res =  $wpdb->get_results($sql,ARRAY_A);
					$row2 = $res[0];
					$row['place_name']=$row2['place_name'];
					$lpoints=explode(",",$lpoint);
					$turnaround.='var marker_turn'.$dogooglemap.'_'.$x.' = new google.maps.Marker({ position: new google.maps.LatLng('.$lpoints[0].','.$lpoints[1].'), map: map_'.$dogooglemap.',   title: "Turn Round here  : '. addslashes($row['place_name']).'" });';
					$turnaround.='iconFile = "https://maps.google.com/mapfiles/ms/icons/blue-dot.png"; marker_turn'.$dogooglemap.'_'.$x.'.setIcon(iconFile) ; ';
					$x=$x+1;

				}
				$lpointb1=$lpoint;
				$y=$y+1;
				$lpoint=$cpoint;
				$oldplace=$place;
			}
		}
		$coordcount=$coordcount+1;
	}

	if ($firstid==$lastid) {
		$markertext='var marker_start'.$dogooglemap.' = new google.maps.Marker({ position: new google.maps.LatLng('.$first_lat.','.$first_long.'), map: map_'.$dogooglemap.',   title: "Start / Finish : '.$firstname.'"});';
		$markertext.='iconFile = "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png"; marker_start'.$dogooglemap.'.setIcon(iconFile) ; ';
	}
	else
	{
		$markertext='var marker_start'.$dogooglemap.' = new google.maps.Marker({ position: new google.maps.LatLng('.$first_lat.','.$first_long.'), map: map_'.$dogooglemap.',   title: "Start : '.$firstname.'" });';
		$markertext.='var marker_stop'.$dogooglemap.' = new google.maps.Marker({ position: new google.maps.LatLng('.$last_lat.','.$last_long.'), map: map_'.$dogooglemap.',  title: "Stop : '.$lastname.'" });';
		$markertext.='iconFile = "https://maps.google.com/mapfiles/ms/icons/green-dot.png"; marker_start'.$dogooglemap.'.setIcon(iconFile) ; ';
		$markertext.='iconFile = "https://maps.google.com/mapfiles/ms/icons/red-dot.png"; marker_stop'.$dogooglemap.'.setIcon(iconFile) ; ';
	}
	$google_map_code2.= 'var map_'.$dogooglemap.'_opts = { zoom: '.$options['zoom'].',center: new google.maps.LatLng('.$centre_lat.','.$centre_long.'),';
	$google_map_code2.='  scrollwheel: false, navigationControl: true, mapTypeControl: true, scaleControl: false, draggable: false,';
	$google_map_code2.= ' mapTypeId: google.maps.MapTypeId.'.$maptype[$options['type']].' };';
	$google_map_code2.= 'var map_'.$dogooglemap.' = new google.maps.Map(document.getElementById("map_canvas_'.$dogooglemap.'"),map_'.$dogooglemap.'_opts);';
	$google_map_code2.='  var polyOptions'.$dogooglemap.' = {strokeColor: "#'.$options['rgb'].'", strokeOpacity: 1.0,strokeWeight: '.$options['brush'].' }; ';
	$i=1;
	$google_map_code2.=' var line'.$dogooglemap.'_'.$i.' = new google.maps.Polyline(polyOptions'.$dogooglemap.');';
 	$google_map_code2.=' line'.$dogooglemap.'_'.$i.'.setPath(google.maps.geometry.encoding.decodePath("'.$pointstring.'"));';
 	$google_map_code2.=' line'.$dogooglemap.'_'.$i.'.setMap(map_'.$dogooglemap.');';
	$google_map_code2.='var bounds'.$dogooglemap.' = new google.maps.LatLngBounds();';
	$google_map_code2.='line'.$dogooglemap.'_'.$i.'.getPath().forEach(function(latLng) {bounds'.$dogooglemap.'.extend(latLng);});';
	$google_map_code2.='map_'.$dogooglemap.'.fitBounds(bounds'.$dogooglemap.');';
	$google_map_code2.='var resizer'.$dogooglemap.' = new CPResizeControl(map_'.$dogooglemap.'); ';
	$google_map_code2.=$turnaround.$markertext;
	$names = array();
	$links = array();
	foreach ($places_array as $place_code) {
		$words=explode(":",$place_code);
		$names[] = $place_code;
		$links[] =$mapstuff;
	}
	if($canalplan_run_canal_route_maps[$post->ID]==1) {$google_map_code.=$google_map_code2;}
	return str_replace($names,$links , $content);
}



function canal_link_maps($content) {
   	 global $wpdb,$post,$dogooglemap,$google_map_code,$canalplan_run_canal_link_maps;
	// First we check the content for tags:
        if (preg_match_all('/' . preg_quote('[[CPGMW:') . '(.*?)' . preg_quote(']]') .'/',$content,$matches)) { $places_array=$matches[1]; }
	// If the array is empty then we've no maps so don't do anything!
	if (!isset($places_array)) {return $content;}
	if (count($places_array)==0) {return $content;}
	if(!isset($canalplan_run_canal_link_maps[$post->ID])) {$canalplan_run_canal_link_maps[$post->ID]=1;} else {
	$canalplan_run_canal_link_maps[$post->ID]=$canalplan_run_canal_link_maps[$post->ID]+1;}
	$canalplan_options = get_option('canalplan_options');
	if (!isset($canalplan_options['canalplan_pm_type'])) {
				$names = array();
		$links = array();
		foreach ($places_array as $place_code) {
			$words=explode("|",$place_code);
		    $names[] = "[[CPGMW:" .$place_code . "]]";
		    $links[] = "<b>Google Maps not configured</b>";
	    	}
		return str_replace($names,$links , $content);
	}
	if ( get_query_var('feed') ||  is_feed() ||  is_tag() )  {
		$names = array();
		$links = array();
		foreach ($places_array as $place_code) {
			$words=explode("|",$place_code);
		    $names[] = "[[CPGMW:" .$place_code . "]]";
		    $links[] = "<b>[Embedded Google Map for ".trim($words[0])."]</b>";
	    	}
		return str_replace($names,$links , $content);
	}
	$maptype['S']="SATELLITE";
   	$maptype['R']="ROADMAP";
   	$maptype['T']="TERRAIN";
   	$maptype['H']="HYBRID";
   	$google_map_code2='';
   	$mapc=0;
	foreach ($places_array as $place_code) {
	$mapc=$mapc+1;
	$options['zoom']=$canalplan_options["canalplan_rm_zoom"];
	$options['type']=$canalplan_options["canalplan_rm_type"];
	if (!isset($options['type'])) {$options['type']='H';}
	if (!isset($options['zoom'])) {$options['zoom']=9;}
	$options['lat']=53.4;
	$options['long']=-2.8;
	$options['height']=$canalplan_options["canalplan_rm_height"];
	$options['width']=$canalplan_options["canalplan_rm_width"];
	$options['rgb']=$canalplan_options["canalplan_rm_r_hex"].$canalplan_options["canalplan_rm_g_hex"].$canalplan_options["canalplan_rm_b_hex"];
	$options['brush']=$canalplan_options["canalplan_rm_weight"];
		$mapstuff="<br />";
		$words=explode("|",$place_code);
		if (isset($words[2])) {
			$opts=explode(",",$words[2]);
		foreach ($opts as $opt) {
			 $opcode=explode("=",$opt);
			if (count($opcode)>1) { $options[$opcode[0]]=strtoupper($opcode[1]);}
		}
	}
		$dogooglemap='CPGMW'.$words[1].'_'.$post->ID.'_'.$mapc;
			if (!canalplan_mobile()) { 
	$mapstuff.= '<div id="map_canvas_'.$dogooglemap.'"  style="width: '.$options['width'].'px; height: '.$options['height'].'px"></div>'; }
		else
		$mapstuff.= '<div id="map_canvas_'.$dogooglemap.'"  style="width:100%; height: '.$options['height'].'px"></div>';
		$post_id=$post->ID;
		unset($missingpoly);
		unset($plines);
		unset($weights);
		unset($polylines);
		$missingpoly[]=$words[1];
		$sql2=$wpdb->prepare(' select distinct lat,`long` from '.CANALPLAN_CODES.' where canalplan_id in (select distinct place1 from '.CANALPLAN_LINK.' where waterway in (select id from '.CANALPLAN_CANALS.' where parent=%s or id=%s)) limit 1',$words[1],$words[1]);
		 $res = $wpdb->get_results($sql2,ARRAY_N);
		$rw = $res[0];
		$centre_lat=(float)$rw[0];
		$centre_long=(float)$rw[1];

		while ( count($missingpoly)>0 ) {
			reset($missingpoly);
			$sql=$wpdb->prepare("select 1 from ".CANALPLAN_POLYLINES." where id=%s",current($missingpoly));
			$res = $wpdb->get_results($sql,ARRAY_A);
			if ($wpdb->num_rows==1){$polylines[]=current($missingpoly);}
			$sql=$wpdb->prepare("select id from ".CANALPLAN_CANALS." where parent=%s",current($missingpoly));
			unset($missingpoly2);
			$res = $wpdb->get_results($sql,ARRAY_N);
			foreach($res as $rw) {
				$missingpoly[]=$rw[0];
			}
		$missingpoly=array_slice($missingpoly,1);
		}
		$markertext="";
		$i=1;
		$google_map_code2.= 'var map_'.$dogooglemap.'_opts = { zoom: '.$options['zoom'].',center: new google.maps.LatLng('.$centre_lat.','.$centre_long.'),';
		$google_map_code2.='  scrollwheel: false, navigationControl: true, mapTypeControl: true, scaleControl: false, draggable: false,';
		$google_map_code2.= ' mapTypeId: google.maps.MapTypeId.'.$maptype[$options['type']].' };';
		$google_map_code2.= 'var map_'.$dogooglemap.' = new google.maps.Map(document.getElementById("map_canvas_'.$dogooglemap.'"),map_'.$dogooglemap.'_opts);';
		$google_map_code2.='  var polyOptions'.$dogooglemap.' = {strokeColor: "#'.$options['rgb'].'", strokeOpacity: 1.0, strokeWeight: '.$options['brush'].' }; ';
		$i=1;
		$google_map_code2.='var bounds'.$dogooglemap.' = new google.maps.LatLngBounds();';
		foreach ($polylines as $polyline) {
			$sql=$wpdb->prepare("select pline from ".CANALPLAN_POLYLINES." where id=%s",$polyline);
			$res=$wpdb->get_results($sql,ARRAY_N);
			$rw = $res[0];
		    $google_map_code2.=' var line'.$dogooglemap.'_'.$i.' = new google.maps.Polyline(polyOptions'.$dogooglemap.');';
		 	$google_map_code2.=' line'.$dogooglemap.'_'.$i.'.setPath(google.maps.geometry.encoding.decodePath("'.$rw[0].'"));';
		 	$google_map_code2.=' line'.$dogooglemap.'_'.$i.'.setMap(map_'.$dogooglemap.');';
	   		$google_map_code2.='line'.$dogooglemap.'_'.$i.'.getPath().forEach(function(latLng) {bounds'.$dogooglemap.'.extend(latLng);});';
			$google_map_code2.='map_'.$dogooglemap.'.fitBounds(bounds'.$dogooglemap.');';
			$i=$i+1;
		}
      		$names[] = "[[CPGMW:" .$place_code . "]]";
      		$links[] = $mapstuff;
      	}
    if($canalplan_run_canal_link_maps[$post->ID]==1) {$google_map_code.=$google_map_code2;}
	return str_ireplace($matches[0], $links, $content);
}


function canal_place_maps($content,$mapblog_id=NULL,$post_id=NULL) {
	global $dogooglemap,$wpdb,$post,$google_map_code,$canalplan_run_canal_place_maps;
	$gazstring=CANALPLAN_URL.'gazetteer.cgi?id=';
	$canalplan_options = get_option('canalplan_options');

	// We don't support maps for features so lets just clean it from the content and return;
	if (preg_match_all('/' . preg_quote('[[CPGMF:') . '(.*?)' . preg_quote(']]') .'/',$content,$matches2)) { $null_link[]=''; return str_ireplace($matches2[0], $null_link, $content);}

	if (preg_match_all('/' . preg_quote('[[CPGM:') . '(.*?)' . preg_quote(']]') .'/',$content,$matches)) { $places_array=$matches[1]; }
	// If the array is empty then we've no links so don't do anything!
	if (!isset($places_array)) {return $content;}

   	if (count($places_array)==0) {return $content;}
   	if(!isset($canalplan_run_canal_place_maps[$post->ID])) {$canalplan_run_canal_place_maps[$post->ID]=1;} else {
   	$canalplan_run_canal_place_maps[$post->ID]=$canalplan_run_canal_place_maps[$post->ID]+1;}
	if (isset($mapblog_id)) {} else { $mapblog_id=$wpdb->blogid;}
	if (isset($post_id)) {} else {$post_id=$post->ID;
        if (isset($post->blog_id)) {$mapblog_id=$post->blog_id;}}
    	$names = array();
    	$links = array();
	if (!isset($canalplan_options['canalplan_pm_type'])) {
				$names = array();
		$links = array();
		foreach ($places_array as $place_code) {
			$words=explode("|",$place_code);
		    $names[] = "[[CPGM:" .$place_code . "]]";
		    $links[] = "<b>Google Maps not configured</b>";
	    	}
		return str_replace($names,$links , $content);
	}
    	if ( get_query_var('feed') || is_feed() ||  is_tag() ) {
    		foreach ($places_array as $place_code) {
    		$words=explode("|",$place_code);
	    	$names[] = "[[CPGM:" .$place_code . "]]";
	    	$links[] = "<b>[Embedded Google Map for ".trim($words[0])."]</b>";
	    }
    	return str_ireplace($names, $links, $content);
    	}
   		$maptype['S']="SATELLITE";
	   	$maptype['R']="ROADMAP";
	   	$maptype['T']="TERRAIN";
	   	$maptype['H']="HYBRID";
	   	$google_map_code2='';
	   	$mapc=0;
	foreach ($places_array as $place_code) {
		$words=explode("|",$place_code);
		$mapc=$mapc+1;
		$sql=$wpdb->prepare("select lat,`long` from ".CANALPLAN_CODES." where canalplan_id=%s",$words[1]);
	//	var_dump($sql);
		$res = $wpdb->get_results($sql,ARRAY_A);
//		var_dump($res);
	    if (count($res)>0) {
			$row = $res[0];
			$options['lat']=$row['lat'];
			$options['long']=$row['long'];
		}
	    $options['height']=$canalplan_options["canalplan_pm_height"];
		$options['width']=$canalplan_options["canalplan_pm_width"];
		$options['zoom']=$canalplan_options["canalplan_pm_zoom"];
		$options['type']=$canalplan_options["canalplan_pm_type"];
		if (!isset($options['type'])) {$options['type']='H';}
		if (!isset($options['zoom'])) {$options['zoom']=9;}
		if (count($words)>=3) {
			$opts=explode(",",$words[2]);
			foreach ($opts as $opt) {
				 $opcode=explode("=",$opt);
				if (count($opcode)>1) { $options[$opcode[0]]=strtoupper($opcode[1]);}
			}
		}
		$mapstuff="<br />";
		$dogooglemap='CPGM'.$words[1].'_'.$post->ID.'_'.$mapc;
	if (!canalplan_mobile()) { 
		$mapstuff= '<div id="map_canvas_'.$dogooglemap.'" style="width: '.$options['width'].'px; height: '.$options['height'].'px"></div> '; }
		else
		$mapstuff= '<div id="map_canvas_'.$dogooglemap.'"  style="width:100%; height: '.$options['height'].'px"></div>';
		
		$names[] = "[[CPGM:" .$place_code . "]]";
		$links[] = $mapstuff;
		$google_map_code2.= 'var map_'.$dogooglemap.'_opts = { zoom: '.$options['zoom'].',center: new google.maps.LatLng('.$options['lat'].','.$options['long'].'),';
		$google_map_code2.='  scrollwheel: false, navigationControl: true, mapTypeControl: true, scaleControl: false, draggable: false,';
		$google_map_code2.= ' mapTypeId: google.maps.MapTypeId.'.$maptype[$options['type']].' };';
		$google_map_code2.= 'var map_'.$dogooglemap.' = new google.maps.Map(document.getElementById("map_canvas_'.$dogooglemap.'"),map_'.$dogooglemap.'_opts);';
		$google_map_code2.= 'var marker'.$dogooglemap.' = new google.maps.Marker({ position: new google.maps.LatLng('.$options['lat'].','.$options['long'].'), map: map_'.$dogooglemap.', title: "'.$words[0].'"  });  ';
     }
    if($canalplan_run_canal_place_maps[$post->ID]==1) {$google_map_code.=$google_map_code2;}
	return str_ireplace($matches[0], $links, $content);
}

function canal_bloggedroute($embed=0,$overnight="N",$routetag=NULL){
	if (!isset($_GET['routeid'])){$_GET['routeid']=0;}
	$routeid = $_GET['routeid'];
	$routeid = preg_replace('{/$}', '', $routeid);
	$routeid=$routeid."'";
	$matches = array();
	preg_match('/([0-9]+)([^0-9]+)/',$routeid,$matches);
	$routeid = $matches[1];
	$blroute='';
	$previous_lat=0;
$previous_long=0;
	
	if (!isset($routeid)){$routeid=0;}
	if ($routeid<=0){$routeid=0;}
	if ($embed>0) {$routeid=$embed;}
	global $wpdb,$blog_id,$google_map_code,$dogooglemap;
	$dogooglemap=1;
	if ( get_query_var('feed')  || is_feed() ||  is_tag() )  {
			$links[] ="<b>[ Google Route Map embedded here ]</b>" ;
		} else {
	$canalplan_options = get_option('canalplan_options');
		if (!isset($canalplan_options['canalplan_pm_type'])) {
			$links[] ="<b>Google Maps not enabled</b>" ;
		} else {
	if ($embed>=1) {$routeid=$embed;$dogooglemap=$embed;}
	if ($routeid==0){
		if ($wpdb->blogid==1) {

			$sql=$sql=$wpdb->prepare("select route_id, title,blog_id from ".CANALPLAN_ROUTES." where status=3 and routetag=%s order by start_date desc",$routetag);
			if (is_null($routetag)) $sql="select route_id,title,blog_id from ".CANALPLAN_ROUTES." where status=3 order by start_date desc";
		}
		else
		{
			$sql=$wpdb->prepare("select route_id, title,description,blog_id from ".CANALPLAN_ROUTES." where status=3 and blog_id=%d and routetag=%s order by start_date desc",$blog_id,$routetag);
			if (is_null($routetag)) $sql=$wpdb->prepare("select route_id, title,description,blog_id from ".CANALPLAN_ROUTES." where status=3 and blog_id=%d order by start_date desc",$blog_id);
		}
		if (!defined('CANALPLAN_ROUTE_SLUG')){
			$r2 = $wpdb->get_results("SELECT pref_value FROM ".CANALPLAN_OPTIONS." where blog_id=$blog_id and pref_code='routeslug'",ARRAY_A);
			if ($wpdb->num_rows==0) {
		    		 $routeslug="UNDEFINED!";
			}
			else
			{
					$routeslug=$r2[0]['pref_value'];
			}
		}
		else {
			$routeslug=CANALPLAN_ROUTE_SLUG;
		}
		$res = $wpdb->get_results($sql,ARRAY_A);
		$blroute .="<ol>";
		foreach ($res as $row) {
			if(is_multisite()){
			if ($wpdb->blogid==1) {
				$blroute .='<li><a href='.get_blog_option($row['blog_id'],"siteurl").'/'.$routeslug.'/?routeid='.$row['route_id'].' target=\"_new\">'.$row['title'].'</a> ( from '. get_blog_option($row['blog_id'],'blogname').' )  </li>';
			}
			else
			{
				$blroute .='<li><a href='.get_blog_option($row['blog_id'],"siteurl").'/'.$routeslug.'/?routeid='.$row['route_id'].' target=\"_new\">'.$row['title'].'</a> ('.$row['description'].')</li>';
			}
			}
			if(!is_multisite()){
						if ($wpdb->blogid==1) {$blroute .='<li><a href='.get_option("siteurl").'/'.$routeslug.'/?routeid='.$row['route_id'].' target=\"_new\">'.$row['title'].'</a> ( from '. get_option('blogname').' )  </li>';
			}
			else
		{
				$blroute .='<li><a href='.get_option("siteurl").'/'.$routeslug.'/?routeid='.$row['route_id'].' target=\"_new\">'.$row['title'].'</a> ('.$row['description'].')</li>';
			}
		}
		}
		$blroute .="</ol><br><br>";
	}
	else
	{
		$sql=$wpdb->prepare("select description, totalroute, total_coords from ".CANALPLAN_ROUTES." where route_id=%d and blog_id=%d",$routeid,$blog_id);
		$res = $wpdb->get_results($sql,ARRAY_A);
		$row=$res[0];
		$mid_point=round(count(explode('|',$row['totalroute']))/2,0,PHP_ROUND_HALF_UP);
		$place_count=0;
		$row = $res[0];
		$coordcount=0;
		if($embed==0) { $blroute .="<h2>".$row['description']."</h2><br/>"; }
	if (!canalplan_mobile()) { 
		$blroute.='<div id="map_canvas_'.$overnight.'_'.$dogooglemap.'"  style="width: '.$canalplan_options["canalplan_rm_width"].'px; height: '.$canalplan_options["canalplan_rm_height"].'px"></div>'; }
    else
		$blroute.='<div id="map_canvas_'.$overnight.'_'.$dogooglemap.'"  style="width:100%; height: '.$canalplan_options["canalplan_rm_height"].'px"></div>';
	
		$pointstring = "";
		$zoomstring = "";
		$lat = 0;
		$long = 0;
		$lpoint="";
		$lpointb1="";
		$x=3;
		$y=-1;
		$places=explode(",",$row['totalroute']);
		$placescoords=explode("|",$row['total_coords']);
		$lastid=end($places);
		$firstid=reset($places);
		$turnaround="";
		$firstname="";
		$first_lat="";
		$first_long="";
		$row = array('lat'  => "a",'long' => "b", 'place_name' => "c",);
	if (!canalplan_mobile()) { 
  		$mapstuff='<div id="map_canvas_'.$overnight.'_'.$dogooglemap.'"  style="width: '.$canalplan_options["canalplan_rm_width"].'px; height: '.$canalplan_options["canalplan_rm_height"].'px"></div>'; }
  		else
		$mapstuff='<div id="map_canvas_'.$overnight.'_'.$dogooglemap.'"  style="width:100%; height: '.$canalplan_options["canalplan_rm_height"].'px"></div>';
		foreach ($places as $place) {
			$placecoords=explode(',',$placescoords[$coordcount]);
		$coordcount=$coordcount+1;
		$row['lat']=$placecoords[0];
		$row['long']=$placecoords[1];
		$do_plot=1;
		if ($previous_lat <> 0 && $previous_long <> 0) {
			 $lat_dif= $row['lat']-$previous_lat;
			 $long_dif= $row['long']-$previous_long;
		
			if (abs($lat_dif) > 0.1 ) {
				$row['lat']=$previous_lat;
				$do_plot=0;
			}
			if (abs($long_dif) > 0.1 ) {
				$row['long']=$previous_long;
				$do_plot=0;
			}
		}
			if ((count($row)>= 2 ) && (strlen($row['lat']) >=4) && (strlen($row['long']) >=3 ) && ($do_plot==1)) {
							$previous_lat=$row['lat'];
			$previous_long=$row['long'];
			if ($place==$firstid){
				$sql=$wpdb->prepare("select `place_name` from ".CANALPLAN_CODES." where canalplan_id=%s",$place);
				$res =  $wpdb->get_results($sql,ARRAY_A);
				$row2 = $res[0];
				$row['place_name']=$row2['place_name'];
				$firstname=addslashes($row['place_name']);
				$first_lat=$row['lat'];
				$first_long=$row['long'];
			}
			if ($place==$lastid){
				$sql=$wpdb->prepare("select `place_name` from ".CANALPLAN_CODES." where canalplan_id=%s",$place);
				$res =  $wpdb->get_results($sql,ARRAY_A);
				$row2 = $res[0];
				$row['place_name']=$row2['place_name'];
				$lastname=addslashes($row['place_name']);
				$last_lat=$row['lat'];
				$last_long=$row['long'];
			}
			if($place_count==$mid_point) {
				$centre_lat=$row['lat'];
				$centre_long=$row['long'];
			}
			$place_count=$place_count+1;
			$points=$place.",".$row['lat'].",".$row['long'];
		      	$pointx = $row['lat'];
			$pointy = $row['long'];
			$nlat = floor($pointx * 1e5);
			$nlong = floor($pointy * 1e5);
			$pointstring .= ascii_encode($nlat - $lat) . ascii_encode($nlong - $long);
			$zoomstring .= 'B';
			$lat = $nlat;
			$long = $nlong;
			$cpoint=$row['lat'].",".$row['long'].','.$place;
			if ($cpoint==$lpointb1) {
				$sql=$wpdb->prepare("select `place_name` from ".CANALPLAN_CODES." where canalplan_id=%s",$oldplace);
				$res =  $wpdb->get_results($sql,ARRAY_A);
				$row2 = $res[0];
				$row['place_name']=$row2['place_name'];
				$lpoints=explode(",",$lpoint);
				$turnaround.='var marker_turn_'.$overnight.'_'.$dogooglemap.'_'.$x.' = new google.maps.Marker({ position: new google.maps.LatLng('.$lpoints[0].','.$lpoints[1].'), map: map_'.$overnight.'_'.$dogooglemap.',   title: "Turn Round here  : '. addslashes($row['place_name']).'" });';
				$turnaround.='iconFile = "https://maps.google.com/mapfiles/ms/icons/blue-dot.png"; marker_turn_'.$overnight.'_'.$dogooglemap.'_'.$x.'.setIcon(iconFile) ; ';
			 	$x=$x+1;
			}
			$lpointb1=$lpoint;
			$y=$y+1;
			$lpoint=$cpoint;
			$oldplace=$place;
			}
		if ($firstid==$lastid) {
			$markertext='var marker_start_'.$overnight.'_'.$dogooglemap.' = new google.maps.Marker({ position: new google.maps.LatLng('.$first_lat.','.$first_long.'), map: map_'.$overnight.'_'.$dogooglemap.',   title: "Start / Finish : '.$firstname.'"});';
			$markertext.='iconFile = "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png"; marker_start_'.$overnight.'_'.$dogooglemap.'.setIcon(iconFile) ; ';
		}
		else
		{
			$markertext='var marker_start_'.$overnight.'_'.$dogooglemap.' = new google.maps.Marker({ position: new google.maps.LatLng('.$first_lat.','.$first_long.'), map: map_'.$overnight.'_'.$dogooglemap.',   title: "Start : '.$firstname.'" });';
			$markertext.='var marker_stop_'.$overnight.'_'.$dogooglemap.' = new google.maps.Marker({ position: new google.maps.LatLng('.$last_lat.','.$last_long.'), map: map_'.$overnight.'_'.$dogooglemap.',  title: "Stop : '.$lastname.'" });';
			$markertext.='iconFile = "https://maps.google.com/mapfiles/ms/icons/green-dot.png"; marker_start_'.$overnight.'_'.$dogooglemap.'.setIcon(iconFile) ; ';
			$markertext.='iconFile = "https://maps.google.com/mapfiles/ms/icons/red-dot.png"; marker_stop_'.$overnight.'_'.$dogooglemap.'.setIcon(iconFile) ; ';
		}}
		$options['size']=200;
		$options['zoom']=$canalplan_options["canalplan_rm_zoom"];
		$options['type']=$canalplan_options["canalplan_rm_type"];
		if (!isset($options['type'])) {$options['type']='H';}
		if (!isset($options['zoom'])) {$options['zoom']=9;}
		$options['lat']=53.4;
		$options['long']=-2.8;
		$options['rgb']=$canalplan_options["canalplan_rm_r_hex"].$canalplan_options["canalplan_rm_g_hex"].$canalplan_options["canalplan_rm_b_hex"];
	   	$maptype['S']="SATELLITE";
	   	$maptype['R']="ROADMAP";
	   	$maptype['T']="TERRAIN";
	   	$maptype['H']="HYBRID";

		$google_map_code.= 'var map_'.$overnight.'_'.$dogooglemap.'_opts = { zoom: '.$options['zoom'].',center: new google.maps.LatLng('.$centre_lat.','.$centre_long.'),';
	    $google_map_code.='  scrollwheel: false, navigationControl: true, mapTypeControl: true, scaleControl: false, draggable: false,';
	    $google_map_code.= ' mapTypeId: google.maps.MapTypeId.'.$maptype[$options['type']].' };';
	    $google_map_code.= 'var map_'.$overnight.'_'.$dogooglemap.' = new google.maps.Map(document.getElementById("map_canvas_'.$overnight.'_'.$dogooglemap.'"),map_'.$overnight.'_'.$dogooglemap.'_opts);';
	    $google_map_code.='  var polyOptions_'.$overnight.'_'.$dogooglemap.' = {strokeColor: "#'.$options['rgb'].'", strokeOpacity: 1.0,strokeWeight: '.$canalplan_options["canalplan_rm_weight"].' }; ';
		$i=1;
		$google_map_code.=' var line_'.$overnight.'_'.$dogooglemap.'_'.$i.' = new google.maps.Polyline(polyOptions_'.$overnight.'_'.$dogooglemap.');';
	 	$google_map_code.=' line_'.$overnight.'_'.$dogooglemap.'_'.$i.'.setPath(google.maps.geometry.encoding.decodePath("'.$pointstring.'"));';
	 	$google_map_code.=' line_'.$overnight.'_'.$dogooglemap.'_'.$i.'.setMap(map_'.$overnight.'_'.$dogooglemap.');';
		$google_map_code.='var bounds_'.$overnight.'_'.$dogooglemap.' = new google.maps.LatLngBounds();';
		$google_map_code.='line_'.$overnight.'_'.$dogooglemap.'_'.$i.'.getPath().forEach(function(latLng) {bounds_'.$overnight.'_'.$dogooglemap.'.extend(latLng);});';
		$google_map_code.='map_'.$overnight.'_'.$dogooglemap.'.fitBounds(bounds_'.$overnight.'_'.$dogooglemap.');';
		$google_map_code.=$turnaround.$markertext;
	}
	if ($overnight=='Y'){
		$sql=$wpdb->prepare("select day_id,end_id from ".CANALPLAN_ROUTE_DAY." where blog_id=%d and route_id=%d and day_id > 0",$blog_id,$routeid);
		$res = $wpdb->get_results($sql,ARRAY_A);
		$markertext2='';
		foreach($res as $dayresult){

		$endp=$places[$dayresult['end_id']];
		$sql=$wpdb->prepare("select distinct canalplan_id, place_name from ".CANALPLAN_FAVOURITES." where canalplan_id=%s and blog_id=%d union select canalplan_id, place_name from ".CANALPLAN_CODES." where canalplan_id=%s and canalplan_id not in (select canalplan_id from ".CANALPLAN_FAVOURITES." where canalplan_id=%s and blog_id=%d)",$endp,$blog_id,$endp,$endp,$blog_id);
			$res3 = $wpdb->get_results($sql,ARRAY_A);
			$endplaces[]  = $res3[0];
		}
		if (is_array($endplaces)) {
		$endplace=array_pop($endplaces);
		foreach ($endplaces as $dayid=>$onplace) {
			$sql=$wpdb->prepare("select `lat`,`long`,`place_name` from ".CANALPLAN_CODES." where canalplan_id=%s",$onplace["canalplan_id"]);
			$res =  $wpdb->get_results($sql,ARRAY_A);
			$row='';
			if (count($res)>0) {
				$row = $res[0];
				$markertext.='var marker_onight'.($dayid+1).'_'.$overnight.'_'.$dogooglemap.' = new google.maps.Marker({ position: new google.maps.LatLng('.$row['lat'].','.$row['long'].'), map: map_'.$overnight.'_'.$dogooglemap.',   title: "Overnight at : '.$row['place_name'].'"});';
				$markertext.='iconFile = "/wp-content/plugins/canalplan-ac/canalplan/markers/cp_'.($dayid+1).'.png"; marker_onight'.($dayid+1).'_'.$overnight.'_'.$dogooglemap.'.setIcon(iconFile) ; ';
			}
		}
	}
		$google_map_code.=$markertext;
	}
}
}
if ($canalplan_map_code == "<script>") $canalplan_map_code.=$canalplan_map_code2;
	if($embed==0 && $routeid>0) {
		$blroute .= "<p><h2>Blog Entries for this trip</h2>";
		//print get_current_blog_id();
		//print $wpdb->blogid;
		$sql="select id, post_title, crd.day_id from ".$wpdb->posts." bp, ".CANALPLAN_ROUTE_DAY." crd where bp.id = crd.post_id and crd.blog_id=".get_current_blog_id()." and  crd.route_id=".$routeid." and post_status='publish' order by crd.day_id asc";
		$res = $wpdb->get_results($sql,ARRAY_A);
		$blroute .="<ol>";
		foreach ($res as $row) {	
			$link = get_permalink(  $row['id'] ) ;
			$extra='';
			if ($row['day_id']==0) $extra='( Trip Summary )';
			$blroute .="<li><a href=\"$link\" target=\"_new\">$row[post_title] $extra</a> </li>";
		}
		$blroute .="</ol>";
	}
	return $blroute ;
}

?>
