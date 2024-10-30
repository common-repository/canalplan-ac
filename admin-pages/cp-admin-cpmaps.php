<?php
/*
Extension Name: Canalplan Canalplan Map options
Extension URI: http://blogs.canalplan.org.uk/canalplanac/canalplan-plug-in/
Version: 1.0
Description: Canalplan Map Options for the Canalplan AC Plugin
Author: Steve Atty
*/

require_once('admin.php');
$parent_file = 'canalplan-manager.php';
$title = __('Canalplan Map Options','canalplan');
$this_file = 'canalplan_cpmaps.php';
global $blog_id;
echo'<p><hr><h2>';
_e('Canalplan Map Settings','canalplan');
echo'</h2><form action="options.php" method="post" action="">';
wp_nonce_field('canalplan_cpm_options');
settings_fields('canalplan_options');
$canalplan_options1["canalplan_cppm_type"]='bright';
$canalplan_options1["canalplan_cppmnotuk_type"]='bright';
$canalplan_options1["canalplan_cppm_zoom"]=14;
$canalplan_options1["canalplan_cppm_height"]=200;
$canalplan_options1["canalplan_cppm_width"]=200;
$canalplan_options1["canalplan_cprm_type"]='bright';
$canalplan_options1["canalplan_cprmnotuk_type"]='bright';
$canalplan_options1["canalplan_cprm_zoom"]=9;
$canalplan_options1["canalplan_cprm_height"]=600;
$canalplan_options1["canalplan_cprm_width"]=500;
$canalplan_options1["canalplan_cprm_r_hex"]="00";
$canalplan_options1["canalplan_cprm_g_hex"]="00";
$canalplan_options1["canalplan_cprm_b_hex"]="ff";
$canalplan_options1["canalplan_cprm_weight"]=4;
$canalplan_options = get_option('canalplan_options');
//var_dump($canalplan_options);
if (!empty($canalplan_options)) $canalplan_options = array_merge($canalplan_options1, $canalplan_options);
$checked_flag=array('on'=>'checked','off'=>'');
//if (!isset($canalplan_options['canalplan_cppm_type'])) {

//}

echo "<h3>Place Map Options</h3>";
$arr = array("bright"=> "Bright","liberty"=> "Liberty","basic"=> "Basic","osroad"=> "OS Roads","osoutdoor"=> "OS Outdoors","oslight"=> "OS Light");

echo '<label for="cp_place_map_type">'.__('Place Map Type (UK Only)' , 'canalplan').' :</label> <select id="canalplan_cppm_type" name="canalplan_options[canalplan_cppm_type]"  >';
foreach ($arr as $i => $value) {
        if ($i==$canalplan_options['canalplan_cppm_type']){ print '<option selected="yes" value="'.$i.'" >'.$arr[$i].'</option>';}
       else {print '<option value="'.$i.'" >'.$arr[$i].'</option>';}}
echo "</select><br />";
$arr2 = array("bright"=> "Bright","liberty"=> "Liberty","basic"=> "Basic");
echo '<label for="cp_place_mapnouk_type">'.__('Place Map Type (Rest of the World)', 'canalplan').' :</label> <select id="canalplan_cppmnotuk_type" name="canalplan_options[canalplan_cppmnotuk_type]"  >';
foreach ($arr2 as $i => $value) {
        if ($i==$canalplan_options['canalplan_cppmnotuk_type']){ print '<option selected="yes" value="'.$i.'" >'.$arr2[$i].'</option>';}
       else {print '<option value="'.$i.'" >'.$arr2[$i].'</option>';}}
echo "</select><br />";


echo '<label for="cp_place_map_zoom">'.__('Place Map Zoom Level', 'canalplan').' :</label> <select id="canalplan_cppm_zoom" name="canalplan_options[canalplan_cppm_zoom]"  >';
for ($i = 0; $i <= 17; $i++) {
        if ($i==$canalplan_options['canalplan_cppm_zoom']){ print '<option selected="yes" value="'.$i.'" >'.$i.'</option>';}
       else {print '<option value="'.$i.'" >'.$i.'</option>';}}
echo "</select><br />";

echo '<label for="cp_place_map_height">'.__('Place Map Height', 'canalplan').' : </label>';
echo '<INPUT NAME="canalplan_options[canalplan_cppm_height]" size=3 maxlength=3 value="'.stripslashes($canalplan_options["canalplan_cppm_height"]).'"> pixels <br />';
if($canalplan_options['canalplan_cppm_height']<1) {$canalplan_options['canalplan_cppm_height']=200;}
echo '<label for="cp_place_map_width">'.__('Place Map Width', 'canalplan').' : </label>';

echo '<INPUT NAME="canalplan_options[canalplan_cppm_width]" size=3 maxlength=3 value="'.stripslashes($canalplan_options["canalplan_cppm_width"]).'"> pixels <br />';
if($canalplan_options['canalplan_cppm_width']<1) {$canalplan_options['canalplan_cppm_width']=200;}

echo "<br /><br /><h3>Route Map Options</h3>";

echo '<label for="cp_route_map_type">'.__('Route Map Type (UK Only)', 'canalplan').' :</label> <select id="canalplan_cprm_type" name="canalplan_options[canalplan_cprm_type]"  >';
foreach ($arr as $i => $value) {
        if ($i==$canalplan_options['canalplan_cprm_type']){ print '<option selected="yes" value="'.$i.'" >'.$arr[$i].'</option>';}
       else {print '<option value="'.$i.'" >'.$arr[$i].'</option>';}}
echo "</select><br />";

echo '<label for="cp_route_mapnonuk_type">'.__('Route Map Type (Rest of the World)', 'canalplan').' :</label> <select id="canalplan_cprmnotuk_type" name="canalplan_options[canalplan_cprmnotuk_type]"  >';
foreach ($arr2 as $i => $value) {
        if ($i==$canalplan_options['canalplan_cprmnotuk_type']){ print '<option selected="yes" value="'.$i.'" >'.$arr2[$i].'</option>';}
       else {print '<option value="'.$i.'" >'.$arr2[$i].'</option>';}}
echo "</select><br />";

echo '<label for="cp_route_map_zoom">'.__('Route Map Zoom Level', 'canalplan').' :</label> <select id="canalplan_cprm_zoom" name="canalplan_options[canalplan_cprm_zoom]"  >';
for ($i = 0; $i <= 17; $i++) {
        if ($i==$canalplan_options['canalplan_cprm_zoom']){ print '<option selected="yes" value="'.$i.'" >'.$i.'</option>';}
       else {print '<option value="'.$i.'" >'.$i.'</option>';}}
echo "</select><br />";
echo '<label for="cp_route_map_height">'.__('Route Map Height', 'canalplan').' : </label>';
if($canalplan_options['canalplan_cprm_height']<1) {$canalplan_options['canalplan_cprm_height']=200;}
echo '<INPUT NAME="canalplan_options[canalplan_cprm_height]" size=3 maxlength=3 value="'.stripslashes($canalplan_options["canalplan_cprm_height"]).'"> pixels <br />';

echo '<label for="cp_route_map_width">'.__('Route Map Width', 'canalplan').' : </label>';
if($canalplan_options['canalplan_cprm_width']<1) {$canalplan_options['canalplan_cprm_width']=200;}
echo '<INPUT NAME="canalplan_options[canalplan_cprm_width]" size=3 maxlength=3 value="'.stripslashes($canalplan_options["canalplan_cprm_width"]).'"> pixels <br />';
echo '<label for="cp_route_map_r_hex">'.__('Route Map Canal Colour (RGB)', 'canalplan').' :</label> <select id="canalplan_cprm_r_hex" name="canalplan_options[canalplan_cprm_r_hex]"  >';
for ($i = 0; $i <= 255; $i++) {
        if (str_pad(dechex($i),2,'0',STR_PAD_LEFT)==$canalplan_options['canalplan_cprm_r_hex']){ print '<option selected="yes" value="'.str_pad(dechex($i),2,'0',STR_PAD_LEFT).'" >'.strtoupper(str_pad(dechex($i),2,'0',STR_PAD_LEFT)).'</option>';}
       else {print '<option value="'.str_pad(dechex($i),2,'0',STR_PAD_LEFT).'" >'.strtoupper(str_pad(dechex($i),2,'0',STR_PAD_LEFT)).'</option>';}}
echo "</select>";
echo '<select id="canalplan_cprm_g_hex" name="canalplan_options[canalplan_cprm_g_hex]"  >';
for ($i = 0; $i <= 255; $i++) {
        if (str_pad(dechex($i),2,'0',STR_PAD_LEFT)==$canalplan_options['canalplan_cprm_g_hex']){ print '<option selected="yes" value="'.str_pad(dechex($i),2,'0',STR_PAD_LEFT).'" >'.strtoupper(str_pad(dechex($i),2,'0',STR_PAD_LEFT)).'</option>';}
       else {print '<option value="'.str_pad(dechex($i),2,'0',STR_PAD_LEFT).'" >'.strtoupper(str_pad(dechex($i),2,'0',STR_PAD_LEFT)).'</option>';}}
echo "</select>";
echo'<select id="canalplan_cprm_b_hex" name="canalplan_options[canalplan_cprm_b_hex]"  >';
for ($i = 0; $i <= 255; $i++) {
        if (str_pad(dechex($i),2,'0',STR_PAD_LEFT)==$canalplan_options['canalplan_cprm_b_hex']){ print '<option selected="yes" value="'.str_pad(dechex($i),2,'0',STR_PAD_LEFT).'" >'.strtoupper(str_pad(dechex($i),2,'0',STR_PAD_LEFT)).'</option>';}
       else {print '<option value="'.str_pad(dechex($i),2,'0',STR_PAD_LEFT).'" >'.strtoupper(str_pad(dechex($i),2,'0',STR_PAD_LEFT)).'</option>';}}
echo "</select><br />";

echo '<label for="cp_route_map_weight">'.__('Route Map Canal Width', 'canalplan').' :</label> <select id="canalplan_cprm_weight" name="canalplan_options[canalplan_cprm_weight]"  >';
for ($i = 0; $i <= 21; $i++) {
        if ($i==$canalplan_options['canalplan_cprm_weight']){ print '<option selected="yes" value="'.$i.'" >'.$i.'</option>';}
       else {print '<option value="'.$i.'" >'.$i.'</option>';}}
echo "</select> pixels <br />";

echo '<br /><input type="submit" name="SBLO" value="'.__("Save Canalplan Map Options", 'canalplan').'" class="button-primary"  />&nbsp;&nbsp;&nbsp;<input type="submit" name="RSD" value="'.__("Reset to System Defaults", 'canalplan').'" class="button-primary" action="poo" /</p></form>';
?>
