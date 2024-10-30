<?php
/*
Extension Name: Canalplan Home Mooring
Extension URI: http://blogs.canalplan.org.uk/canalplanac/canalplan-plug-in/
Version: 3.0
Description: Home Mooring admin page for the Canalplan AC Plugin
Author: Steve Atty
*/

require_once ('admin.php');
$title = __('Home Mooring');
$this_file =  'canalplan-home.php';
$parent_file = 'canalplan-manager.php';
$base_dir=dirname(__FILE__);
global $blog_id,$wpdb;
echo '<script type="text/javascript"> var linktype=1; cplogid='.$blog_id.'</script>';


if(isset($_POST['_submit_check']))
{
	parse_data($_POST['dataset'],$blog_id);
}
	echo '<script type="text/javascript"> var linktype=1; cplogid='.$blog_id.'</script>';
	echo '<script type="text/javascript"> var wpcontent="'.plugins_url().'"</script>';
?>
<script type="text/javascript" src="../wp-content/plugins/canalplan-ac/canalplan/javascript/plan.js"></script>
<script type="text/javascript" src="../wp-content/plugins/canalplan-ac/canalplan/javascript/canalplan_actb.js"></script>
<script type="text/javascript" src="../wp-content/plugins/canalplan-ac/canalplan/javascript/canalplanfunctions.js" DEFER></script>
<script language="JavaScript" type="text/javascript"><!--

function getCanalPlan2(tag)
{
 code_id=Canalplan_Download_Code(tag);
 document.getElementById("CanalPlanText").value=tag
}

        function showValue(cptext,cpid)
        {
document.getElementById("current").value=cptext;
document.getElementById("dataset").value=cpid+"|"+cptext;
        }

	//-->
</script>
<div class="wrap">

<h2><?php _e('Set Home Mooring') ?> </h2>
<br>
<h3>Current Mooring</h3>
<form>
<?php
$r = $wpdb->get_results("SELECT place_name FROM ".CANALPLAN_FAVOURITES." where blog_id=".$blog_id." and place_order=0",ARRAY_A);
if ($wpdb->num_rows==0) {
     echo '<input disabled name="current" id="current" value="No Home Mooring Set"> </input>';
}
else
{
//	var_dump($r);
foreach($r as $rw)
{
  echo '<input disabled name="current" id="current" value="'.$rw['place_name'].'">';
}
}
?>
<br>
<h3>Stage 1 : Find a Canalplan Location</h3>
<br>
<input type="hidden" name="tagtypeID" value="ZED" />
 <input type="text" name="CanalPlanID" ID="CanalPlanID" align="LEFT" size="40" maxlength="90" autocomplete="off"/>
<INPUT TYPE="button" name="CPsub" VALUE="Insert CanalPlan Location"  onclick="getCanalPlan2(CanalPlanID.value);"/>
<br>
<h3>Stage 2 : Edit the Description</h3>
 <input type="text" name"CanalPlanText" ID="CanalPlanText" align="LEFT" size="40" maxlength="90"/>
<INPUT TYPE="button" name="CPTsub" VALUE="Update Mooring "  onclick="showValue(CanalPlanText.value,code_id);"/>
</form>
<form action="" name="flid" id="fav_list" method="post">
<input type="hidden" name="_submit_check" value="1"/>
<input type="hidden" name="dataset" id="dataset" value="" />
 <div align=right> <input type="submit"  value="Save Changes" /></div>
</form>
<script language="JavaScript" type="text/javascript">
canalplan_actb(document.getElementById("CanalPlanID"),new Array());
</script>
</div>

<?php
function parse_data($data,$blid)
{
global $wpdb;
$data=stripslashes($data);
$sql="Delete from ".CANALPLAN_FAVOURITES." where blog_id=".$blid." and place_order=0";

$res = $wpdb->query($sql);
      $values = explode("|", $data);
     $sql=$wpdb->prepare("insert into ".CANALPLAN_FAVOURITES." set blog_id=%d ,canalplan_id=%s, place_name=%s,place_order=0",$blid,$values[0],$values[1]);
     $res = $wpdb->query($sql);
}
?>
