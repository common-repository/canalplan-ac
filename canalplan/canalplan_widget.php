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
class CanalPLanWidget extends WP_Widget
{
	function __construct()
	{
		parent::__construct('Canalplan_widget', 'Canalplan Location', array('description' => 'The widget displays your current location (if set from the Locations Options page) and a link to the nearest location in Canalplan', 'class' => 'CanalPlanWidgetclassesc_attr'));
	}
	function widget($args, $instance)
	{
		extract($args, EXTR_SKIP);
		global $wpdb, $user_ID, $table_prefix, $blog_id, $google_map_code;
		$userid = $instance['snorl'];
		// Display the widget!
		echo $before_widget;
		echo "<!--Canalplan Location Start -->\n";
		echo $before_title;
		echo $instance['title'];
		echo $after_title;
		$sql = $wpdb->prepare("select * from " . CANALPLAN_OPTIONS . " where blog_id=%d and pref_code='Location'", $blog_id);
		//print $sql;
		$res = $wpdb->get_results($sql, ARRAY_A);
		$values = explode("|", "none|none");
		//	var_dump($values);
		if (count($res) > 0) {
			$values = explode('|', $res[0]['pref_value']);
		}
		$cp_lat = 0;
		$cp_long = 0;
		$cp_name = '';
		switch ($values[0]) {
			case 'none':
				unset($values);
				break;
			case 'Browser':
				$values2 = explode('|', $res[0]['pref_value']);
				$cp_lat = $values2[1];
				$cp_long = $values2[2];
				$cp_id = $values2[5];
				$cp_name = $values2[6];
				break;
			case 'From Mobile':
			case 'GPS Tracker for Android':
			case 'Backitude':
				$values = explode('|', $res[0]['pref_value']);
				$cp_lat = $values[1];
				$cp_long = $values[2];
				$cp_id = $values[5];
				$cp_name = $values[6];
				break;
			case 'Canalplan':
				if (strlen($values[1]) >= 5 && substr($values[1], 0, 1) == "X")
					$values[1] = substr($values[1], 1, 99);
				$sql = $wpdb->prepare("select lat,`long` from " . CANALPLAN_CODES . " where canalplan_id=%s", $values[1]);
				$res = $wpdb->get_results($sql, ARRAY_A);
				$row = $res[0];
				$cp_lat = $row['lat'];
				$cp_long = $row['long'];
				$cp_id = $values[1];
				$cp_name = $values[2];
				break;
			default:
				unset($values);
		}
		echo "<div align='center'>";
		$canalplan_options = get_option('canalplan_options');
		$options['type'] = $canalplan_options["canalplan_cppm_type"];
		if (!isset($options['type'])) {
			$options['type'] = 'bright';
		}
		if (is_null($cp_lat) || is_null($cp_long) || is_null($cp_name) || !isset($values) || strlen($cp_lat) < 3 || strlen($cp_long) < 3 || strlen($cp_name) < 3) {
			echo "<br /> No Location Set <br />";
		} else {

			if ($instance['mf'] == 'C') {
				$zoom = $instance['zl'] - 2;
				$locfrom = $values[0];
				echo "<br/><p id='day_header_0' hidden>" . stripslashes($cp_name);
				$mapstyle = return_style($cp_lat, $cp_long, 'place');
				//var_dump($mapstyle);
				echo '<div id="map_canvas_widget_' . $blog_id . '"  style="width: ' . $instance['width'] . 'px; height: ' . $instance['height'] . 'px"></div><br/>';
				echo "<script> var map_widget = new maplibregl.Map({  container: 'map_canvas_widget_" . $blog_id . "', style: '" . MAPSERVER_BASE . "/" . $mapstyle . "',
           center: [" . $cp_long . ", " . $cp_lat . "], // starting position [lng, lat]
    zoom: $zoom, // starting zoom
        attributionControl: true,
        hash: false,   
        cooperativeGestures : true
      });
      var imgurl='" . plugin_dir_url(__FILE__) . "markers/';
      map_widget.addControl(new maplibregl.NavigationControl());
      map_widget.on('load', function() {
      var string = '[{\"icon\":\"blank\",\"lat\":" . $cp_lat . ",\"lng\":" . $cp_long . ",\"idx\":\"0\"}]';
      var stops = eval(string);
       Add_Markers_Start(imgurl,map_widget,stops)});
  </script>";
				//wp_add_inline_script( 'output_osm_map', "Add_Markers_Start(map,stops)",  'after' );
			} else {
				$maptype['S'] = "SATELLITE";
				$maptype['R'] = "ROADMAP";
				$maptype['T'] = "TERRAIN";
				$maptype['H'] = "HYBRID";
				//var_dump($instance);
				echo '<div id="map_canvas_widget_' . $blog_id . '"  style="width: ' . $instance['width'] . 'px; height: ' . $instance['height'] . 'px"></div>';
				$google_map_code .= 'var map_widget_' . $blog_id . '_opts = { zoom: ' . $instance['zl'] . ',center: new google.maps.LatLng(' . $cp_lat . ',' . $cp_long . '),';
				$google_map_code .= ' scrollwheel: false, navigationControl: false, mapTypeControl: false, scaleControl: false, draggable: false, disableDefaultUI: true,';
				$google_map_code .= ' mapTypeId: google.maps.MapTypeId.' . $maptype[$instance['mf']] . ' };';
				$google_map_code .= 'var map_widget_' . $blog_id . ' = new google.maps.Map(document.getElementById("map_canvas_widget_' . $blog_id . '"),map_widget_' . $blog_id . '_opts);';
				$google_map_code .= 'var marker_widget_' . $blog_id . ' = new google.maps.Marker({ position: new google.maps.LatLng(' . $cp_lat . ',' . $cp_long . '), map: map_widget_' . $blog_id . ', title: "' . $instance['pin_title'] . '"  });  ';
			}
			print "Nearest Canalplan location is : <br /> <a href='" . CANALPLAN_GAZ_URL . $cp_id . "' target='_new' > " . stripslashes($cp_name) . "</a> <br />";
			//}
		}
		echo "</div></p>" . $after_widget;
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['snorl'] = $new_instance['snorl'];
		$instance['pin_title'] = strip_tags($new_instance['pin_title']);
		$instance['mf'] = strip_tags($new_instance['mf']);
		$instance['zl'] = strip_tags($new_instance['zl']);
		$instance['height'] = strip_tags($new_instance['height']);
		$instance['width'] = strip_tags($new_instance['width']);
		return $instance;
	}

	function form($instance)
	{
		global $user_ID;
		$default = array('title' => 'Where Am I', 'snorl' => $user_ID, 'width' => 250, 'height' => 300, 'maptype' => 'road', 'zoom' => 0, 'pin_title' => "I'm Here", 'mf' => "S", 'zl' => '17');
		$instance = wp_parse_args((array) $instance, $default);
		$title_id = $this->get_field_id('title');
		$title_name = $this->get_field_name('title');
		$pin_title_id = $this->get_field_id('pin_title');
		$pin_title_name = $this->get_field_name('pin_title');
		$snorl_id = $this->get_field_id('snorl');
		$snorl_name = $this->get_field_name('snorl');
		$width_id = $this->get_field_id('width');
		$width_name = $this->get_field_name('width');
		$height_id = $this->get_field_id('height');
		$height_name = $this->get_field_name('height');
		$mf_id = $this->get_field_id('mf');
		$mf_name = $this->get_field_name('mf');
		$zl_id = $this->get_field_id('zl');
		$zl_name = $this->get_field_name('zl');
		$zoom_id = $this->get_field_id('zoom');
		$zoom_name = $this->get_field_name('zoom');
		echo '<input type="hidden" class="widefat" id="' . $snorl_id . '" name="' . $snorl_name . '" value="' . esc_attr($instance['snorl']) . '" /></p>';
		echo '<p><label for="' . $title_id . '">Title of Widget: </label> <input type="text" class="widefat" id="' . $title_id . '" name="' . $title_name . '" value="' . esc_attr($instance['title']) . '" /></p>';
		echo '<p><label for="' . $pin_title_id . '">Pin Title: </label> <input type="text" class="widefat" id="' . $pin_title_id . '" name="' . $pin_title_name . '" value="' . esc_attr($instance['pin_title']) . '" /></p>';
		echo '<p><label for="' . $width_id . '">Widget Width : </label> <input type="text" size="7" id="' . $width_id . '" name="' . $width_name . '" value="' . esc_attr($instance['width']) . '" /></p>';

		echo '<p><label for="' . $height_id . '">Widget Height: </label> <input type="text" size="7" id="' . $height_id . '" name="' . $height_name . '" value="' . esc_attr($instance['height']) . '" /></p>';
		echo '<p><label for="' . $mf_id . '"> Map Format :  </label>';
		echo '<select id=id="' . $mf_id . '" name="' . $mf_name . '" >';

		$arr = array("C" => "Canalplan", "R" => "Road", "T" => "Terrain", "H" => "Hybrid", "S" => "Satellite");

		foreach ($arr as $i => $value) {
			if ($i == esc_attr($instance['mf'])) {
				print '<option selected="yes" value="' . $i . '" >' . $arr[$i] . '</option>';
			} else {
				print '<option value="' . $i . '" >' . $arr[$i] . '</option>';
			}
		}
		echo '</select></p>';

		echo '<p><label for="' . $zl_id . '"> Zoom Level :  </label>';
		echo '<select id=id="' . $zl_id . '" name="' . $zl_name . '" >';

		$arr = array(0 => "Automatic", 3 => "Continent", 5 => "Country", 7 => "Region", 11 => "City", 15 => "Neighbourhood", 17 => "Street", 21 => "House");
		foreach ($arr as $i => $value) {
			if ($i == esc_attr($instance['zl'])) {
				print '<option selected="yes" value="' . $i . '" >' . $arr[$i] . '</option>';
			} else {
				print '<option value="' . $i . '" >' . $arr[$i] . '</option>';
			}
		}
		echo '</select></p>';
	}

}
/* register widget when loading the WP core */
add_action('widgets_init', 'canalplan_widgets');
//$plugin_dir = basename(dirname(__FILE__));
//var_dump($plugin_dir);
function canalplan_widgets()
{
	register_widget('CanalplanWidget');
}
?>
