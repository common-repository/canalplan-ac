<?php
/*
Extension Name: Canalplan Google options
Extension URI: http://blogs.canalplan.org.uk/canalplanac/canalplan-plug-in/
Version: 3.0
Description: Favourites admin for the Canalplan AC Plugin
Author: Steve Atty
*/

require_once('admin.php');
$parent_file = 'canalplan-manager.php';
$title = __('Favourites','canalplan');
$this_file = 'canalplan-favour.php';
global $blog_id;
echo'<p><hr><h2>';
_e('Google Map Settings','canalplan');
echo'</h2><form action="options.php" method="post" action="">';
wp_nonce_field('canalplan_gm_options');
settings_fields('canalplan_options');
$canalplan_options1["canalplan_pm_type"]='H';
$canalplan_options1["canalplan_pm_zoom"]=14;
$canalplan_options1["canalplan_pm_height"]=200;
$canalplan_options1["canalplan_pm_width"]=200;
$canalplan_options1["canalplan_rm_type"]='H';
$canalplan_options1["canalplan_rm_zoom"]=9;
$canalplan_options1["canalplan_rm_height"]=600;
$canalplan_options1["canalplan_rm_width"]=500;
$canalplan_options1["canalplan_rm_r_hex"]="00";
$canalplan_options1["canalplan_rm_g_hex"]="00";
$canalplan_options1["canalplan_rm_b_hex"]="ff";
$canalplan_options1["canalplan_rm_weight"]=4;
$canalplan_options1["canalplan_rm_key"]="Your Maps API Key goes here";
$canalplan_options = get_option('canalplan_options');
if (!empty($canalplan_options)) $canalplan_options = array_merge($canalplan_options1, $canalplan_options);
$checked_flag=array('on'=>'checked','off'=>'');
//if (!isset($canalplan_options['canalplan_pm_type'])) {

//}

echo "<h3>Place Map Options</h3>";
$arr = array("S"=> "Satellite","R"=> "Road Map","T"=> "Terrain","H"=> "Hybrid");

echo '<label for="cp_place_map_type">'.__('Place Map Type', 'canalplan').' :</label> <select id="canalplan_pm_type" name="canalplan_options[canalplan_pm_type]"  >';
foreach ($arr as $i => $value) {
        if ($i==$canalplan_options['canalplan_pm_type']){ print '<option selected="yes" value="'.$i.'" >'.$arr[$i].'</option>';}
       else {print '<option value="'.$i.'" >'.$arr[$i].'</option>';}}
echo "</select><br />";

echo '<label for="cp_place_map_zoom">'.__('Place Map Zoom Level', 'canalplan').' :</label> <select id="canalplan_pm_zoom" name="canalplan_options[canalplan_pm_zoom]"  >';
for ($i = 0; $i <= 21; $i++) {
        if ($i==$canalplan_options['canalplan_pm_zoom']){ print '<option selected="yes" value="'.$i.'" >'.$i.'</option>';}
       else {print '<option value="'.$i.'" >'.$i.'</option>';}}
echo "</select><br />";

echo '<label for="cp_place_map_height">'.__('Place Map Height', 'canalplan').' : </label>';
echo '<INPUT NAME="canalplan_options[canalplan_pm_height]" size=3 maxlength=3 value="'.stripslashes($canalplan_options["canalplan_pm_height"]).'"> pixels <br />';
if($canalplan_options['canalplan_pm_height']<1) {$canalplan_options['canalplan_pm_height']=200;}
echo '<label for="cp_place_map_width">'.__('Place Map Width', 'canalplan').' : </label>';

echo '<INPUT NAME="canalplan_options[canalplan_pm_width]" size=3 maxlength=3 value="'.stripslashes($canalplan_options["canalplan_pm_width"]).'"> pixels <br />';
if($canalplan_options['canalplan_pm_width']<1) {$canalplan_options['canalplan_pm_width']=200;}

echo "<br /><br /><h3>Route Map Options</h3>";

echo '<label for="cp_route_map_type">'.__('Route Map Type', 'canalplan').' :</label> <select id="canalplan_rm_type" name="canalplan_options[canalplan_rm_type]"  >';
foreach ($arr as $i => $value) {
        if ($i==$canalplan_options['canalplan_rm_type']){ print '<option selected="yes" value="'.$i.'" >'.$arr[$i].'</option>';}
       else {print '<option value="'.$i.'" >'.$arr[$i].'</option>';}}
echo "</select><br />";

echo '<label for="cp_route_map_zoom">'.__('Route Map Zoom Level', 'canalplan').' :</label> <select id="canalplan_rm_zoom" name="canalplan_options[canalplan_rm_zoom]"  >';
for ($i = 0; $i <= 21; $i++) {
        if ($i==$canalplan_options['canalplan_rm_zoom']){ print '<option selected="yes" value="'.$i.'" >'.$i.'</option>';}
       else {print '<option value="'.$i.'" >'.$i.'</option>';}}
echo "</select><br />";
echo '<label for="cp_route_map_height">'.__('Route Map Height', 'canalplan').' : </label>';
if($canalplan_options['canalplan_rm_height']<1) {$canalplan_options['canalplan_rm_height']=200;}
echo '<INPUT NAME="canalplan_options[canalplan_rm_height]" size=3 maxlength=3 value="'.stripslashes($canalplan_options["canalplan_rm_height"]).'"> pixels <br />';

echo '<label for="cp_route_map_width">'.__('Route Map Width', 'canalplan').' : </label>';
if($canalplan_options['canalplan_rm_width']<1) {$canalplan_options['canalplan_rm_width']=200;}
echo '<INPUT NAME="canalplan_options[canalplan_rm_width]" size=3 maxlength=3 value="'.stripslashes($canalplan_options["canalplan_rm_width"]).'"> pixels <br />';

echo '<label for="cp_route_map_r_hex">'.__('Route Map Canal Colour (RGB)', 'canalplan').' :</label> <select id="canalplan_rm_r_hex" name="canalplan_options[canalplan_rm_r_hex]"  >';
for ($i = 0; $i <= 255; $i++) {
        if (str_pad(dechex($i),2,'0',STR_PAD_LEFT)==$canalplan_options['canalplan_rm_r_hex']){ print '<option selected="yes" value="'.str_pad(dechex($i),2,'0',STR_PAD_LEFT).'" >'.strtoupper(str_pad(dechex($i),2,'0',STR_PAD_LEFT)).'</option>';}
       else {print '<option value="'.dechex($i).'" >'.strtoupper(str_pad(dechex($i),2,'0',STR_PAD_LEFT)).'</option>';}}
echo "</select>";
echo '<select id="canalplan_rm_g_hex" name="canalplan_options[canalplan_rm_g_hex]"  >';
for ($i = 0; $i <= 255; $i++) {
        if (str_pad(dechex($i),2,'0',STR_PAD_LEFT)==$canalplan_options['canalplan_rm_g_hex']){ print '<option selected="yes" value="'.str_pad(dechex($i),2,'0',STR_PAD_LEFT).'" >'.strtoupper(str_pad(dechex($i),2,'0',STR_PAD_LEFT)).'</option>';}
       else {print '<option value="'.dechex($i).'" >'.strtoupper(str_pad(dechex($i),2,'0',STR_PAD_LEFT)).'</option>';}}
echo "</select>";
echo'<select id="canalplan_rm_b_hex" name="canalplan_options[canalplan_rm_b_hex]"  >';
for ($i = 0; $i <= 255; $i++) {
        if (str_pad(dechex($i),2,'0',STR_PAD_LEFT)==$canalplan_options['canalplan_rm_b_hex']){ print '<option selected="yes" value="'.str_pad(dechex($i),2,'0',STR_PAD_LEFT).'" >'.strtoupper(str_pad(dechex($i),2,'0',STR_PAD_LEFT)).'</option>';}
       else {print '<option value="'.dechex($i).'" >'.strtoupper(str_pad(dechex($i),2,'0',STR_PAD_LEFT)).'</option>';}}
echo "</select><br />";

echo '<label for="cp_route_map_weight">'.__('Route Map Canal Width', 'canalplan').' :</label> <select id="canalplan_rm_weight" name="canalplan_options[canalplan_rm_weight]"  >';
for ($i = 0; $i <= 21; $i++) {
        if ($i==$canalplan_options['canalplan_rm_weight']){ print '<option selected="yes" value="'.$i.'" >'.$i.'</option>';}
       else {print '<option value="'.$i.'" >'.$i.'</option>';}}
echo "</select> pixels <br />";
echo "<h3>Other Map Options</h3>";
if(!isset($canalplan_options["supress_google"])) {$canalplan_options["supress_google"]='off';}
echo '<label for="cp_gmap_disable">'.__('Disable Google Map API load', 'canalplan').' :</label>';
echo '<INPUT TYPE=CHECKBOX NAME="canalplan_options[supress_google]" '.$checked_flag[$canalplan_options["supress_google"]].' ><br />';
echo '<label for="cp_route_map_key">'.__('Google Maps API', 'canalplan').' : </label>';
echo '<INPUT NAME="canalplan_options[canalplan_rm_key]" size=50 maxlength=50 value="'.stripslashes($canalplan_options["canalplan_rm_key"]).'"><br />';
echo '<br /><input type="submit" name="SBLO" value="'.__("Save Google Map Options", 'canalplan').'" class="button-primary"  />&nbsp;&nbsp;&nbsp;<input type="submit" name="RSD" value="'.__("Reset to System Defaults", 'canalplan').'" class="button-primary" action="poo" /</p></form>';
?>
