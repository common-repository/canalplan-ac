<?php
/*
Extension Name: Canalplan Menu
Extension URI: http://blogs.canalplan.org.uk/canalplanac/canalplan-plug-in/
Version: 4.0
Description: Menu Page for the Canalplan AC Plugin
Author: Steve Atty
*/

$title = __('CanalPlan AC Integration');
nocache_headers();
?>
<div class="wrap">
<h2><?php _e('CanalPlan AC Integration Overview') ?> </h2>
This plugin allows you to integrate your blog with <a href="<?php echo CANALPLAN_BASE; ?>">Canalplan AC</a>
<p><a href="?page=canalplan-ac/admin-pages/cp-admin-general.php"> General Options </a> <br />
This sets up various general options for the Canalplan plugin such as the Distance Format and your Canalplan AC link code</p>
<p><a href="?page=canalplan-ac/admin-pages/cp-admin-home.php"> Home Mooring </a><br />
This allows you to set your home mooring location and give it a customised name </p>
<p><a href="?page=canalplan-ac/admin-pages/cp-admin-fav.php"> Favourite Locations </a><br />
This allows you to set up some favourite locations and give them customised names.</p>
<?php if ($maptype=='google' ) { ?><p><a href="?page=canalplan-ac/admin-pages/cp-admin-google.php"> Google Map Options </a><br />
This allows you to set up the various options to do with how Google Maps are displayed.</p>
<?php };  if ($maptype=='cpmap' ) { ?> <p><a href="?page=canalplan-ac/admin-pages/cp-admin-cpmaps.php"> Canalplan Map Options </a><br />
This allows you to set up the various options to do with how Canalplan Maps are displayed.</p>
<?php }; ?>
<p><a href="?page=canalplan-ac/admin-pages/cp-import_route.php"> Import a route from CanalPlan AC </a><br />
This is the starting point for importing a route from Canalplan AC and creating a set of blog posts for the imported route</p>
<p><a href="?page=canalplan-ac/admin-pages/cp-manage_route.php"> Manage Imported Routes </a><br />
Once you've imported a route you might need to make adjustments to the daily totals - this page allows you to do that</p>
<p><a href="?page=canalplan-ac/admin-pages/cp-admin-location.php"> Set Location </a><br />
This page allows you to set your location using either Latitude and Longitude co-ordinates, from a Canalplan Location, or, if you have an Android Device, using Backitude
<p><a href="?page=canalplan-ac/admin-pages/cp-admin-diagnostics.php"> Diagnostics / Version Information </a><br />
Provides information and diagnostics - You'll need to refer to this page if you've got problems.
</p>
<p><a href="?page=canalplan-ac/admin-pages/cp-admin-update.php"> Bulk Link Notifier </a><br />
During normal use the Canalplan AC website will learn about links from you blog back to it's gazetteer. However if you've just added a set of posts you might want to let Canalplan AC know about all the links.
</p>
<p><a href="../wp-content/plugins/canalplan-ac/canalplan/canalplan_ac_user_guide.pdf" target="wordpress">Canalplan AC plugin User Guide</a> - a PDF containing a basic guide to using the Canalplan AC plugin
</p>
</div>
<?php
?>
