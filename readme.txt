=== Canalplan ===

Contributors: SteveAtty
Tags: crosspost, Canalplan AC
Requires at least: 5.0
Tested up to: 6.4.2
Stable tag: 5.31

== Description ==

This plugin allows you link your self hosted Wordpress blog to the Canalplan AC website. You can import routes from the route planner and link your blog posts to the canalplan  gazetteer.


== IMPORTANT ==

This plugin creates 9 tables in your database which occupy about 50MB of space.

Due to changes in Google's Maps API you need to have an API key to use the maps functionality of the plugin. Instructions on how to obtain an API key from Google can be found here:
https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key

GoogleMaps are no longer supported as I do no have a key

This plugin DOES NOT support BLOCKS



== Installation ==

1. Install through the standard Wordpress Plugin process.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Navigate to `Options` &rarr; `Canalplan AC` for configuration and follow the on-screen prompts.


== Features ==

- Imports data from the Canalplan AC website so you have an up to date list of all canalplan locations / features etc.
- Easy linking to Canalplan AC Gazetteer entries.
- Easy inclusion of Googlemaps related to Canalplan AC locations.
- Easy inclusion of maps of complete waterways or sections of waterways based on Canalplan AC Data.
- Import Planned routes from Canalplan AC and create a "Cruising Log" of blog entries for the trip.
- Supports a "Trips" page which summarises all the sets of "Cruising Log" entries. This page also displays a map and a list of individual posts for a specific "Cruising Log"
- Bulk export of links to Canalplan AC Gazetteer entries for when you publish cruising log. 
- Export of links to Canalplan AC Gazetteer entries when you publish / update a blog post.
- Crontab job to bulk export links once a week. Needed as referrer urls are ignored on HTTPS sites.
- Canalplan AC website automatically links back to relevant blog entries from Gazetteer pages.
- Works in "Classic" (Single blog) and Networked Blogs mode. Can be Network activated.
- Common set of Tables for Networked Blogs mode - so 1 set of tables per Networked Blogs install, not per blog.
- Global configuration for Networked blog installs can be done through a special multisite.php file.


== Screenshots ==

1. Main Canalplan Menu Page.
2. Route Management Page.
3. Canalplan Tag entry box on editor screen.
4. Trips summary Page.
5. Single Trip Overview Page showing start / turnround / end locations.
6. Single Trip Summary Page showing overnight stops.
7. Blog Post showing summary details (with links back to canalplan) plus embedded map for the day


== Changelog ==

= Version 5.31 (28/04/2024) =
- More changes to support Maplibre 4.x
- Code tidy up 

= Version 5.30 (24/03/2024) =
- Removed some obsolete code
- Changes to support Maplibre 4.x 

= Version 5.24 (24/03/2024) =
- plugin directory structure changes
- Adjustments to how file paths are determined

= Version 5.23 (24/03/2024) =
- Changes to Map rendering javascript to solve missing variable error
- Removal of obsolete JS code and graphics

= Version 5.22 (24/03/2024) =
- Changes to CP Data update processing to improve empty file handling

= Version 5.21 (24/03/2024) =
- Changes to Place Map handling where nothing used as code

= Version 5.20 (23/03/2024) =
- Major chages to CP Data update processing

= Version 5.12 (23/03/2024) =
- Fixes broken update of canalplan_codes table which broke maps

= Version 5.11 (23/03/2024) =
- Removes redundant files left over by accident

= Version 5.10 (23/03/2024) =
- Significant changes to Canalplan AC Map rendering
- Location lookup loop back changed to async fetch
- Start Date calendar re-engineered
- Database Schema changes 
- Additional configuration parameters in multisite config file to allow complete standalone version



= Version 5.02 (17/03/2024) =
- Minor fixes to the Canalplan AC Map handling to fix load before style complete errors.

= Version 5.01 (27/12/2023) =
- Minor fixes to the Canalplan AC Map handling

= Version 5.00 (08/04/2023) =
- Adds Support for Canalplan AC Maps.

= Version 4.32 (05/09/2021) =
- Fixes a problem with maintain routes.

= Version 4.31 (10/07/2021) =
- Remove logic for handling different map servers.

= Version 4.30 (10/07/2021) =
- Remove logic for handling different map servers.

= Version 4.29 (110/07/2021) =
- Changes to data load to work round some timeout issues.

= Version 4.28 (14/02/2021) =
- Changes to MySQL Geo commands.

= Version 4.27 (02/11/2020) =
- Changes to MySQL Geo commands.


= Version 4.26 (02/09/2019) =
- Adjustment to Location Widget

= Version 4.25 (30/07/2019) =
- Minor tweaks to location page.

= Version 4.24 (30/07/2019) =
- Changes to handle structural changes in the data coming from Canalplan
- Adjustments to Route Importing and Route Management to fix a long standing bug to do with overnight stops
- Adds support for new Android GPS app.

= Version 4.23 (12/05/2019) =
- Adjustment to tbe absolute difference check for lat / lon to avoid rogue lines on maps
- Fixed some logic pulling places back from the database when adding tags into a post.

= Version 4.22 (02/03/2019) =
- Minor tweak to the Diagnostics page
- Minor tweak to the get new data functionality

= Version 4.21 (18/02/2019) =
- Fixed bug where Canalplan names with & in them lost their IDs when being pulled from the database.

= Version 4.20 (17/02/2019) =
- Tidied up code removing a lot of commented out obsolete code
- Fixed a lot of undefined index and other errors when error reporting set to everything
- Added uninstall functionality to remove tables when plugin deleted
- Added Delete All functionality to Favourites and Manage Routes

= Version 4.10 (16/02/2019) =
- Moved the Data pull from a single sqlite file to multiple JSON files.  This should get round problems where CURL execution time is limited and the connection is slow.

= Version 4.03 (16/02/2019) =
- More tweaks to the curl timeout parameters.

= Version 4.02 (16/02/2019) =
- More tweaks to the curl timeout parameters.

= Version 4.01 (16/02/2019) =
- Added some set_time_limit calls to try to work round timeouts when loading the main data.
- Data age check commented out to allow repeated attempts to reload.

= Version 4.00 (11/02/2019) =
- Following all the curl tweaks and code changes it seemed only right to move to version 4.00.

= Version 3.67 (02/02/2019) =
- Adjustments to CURL parameters.


= Version 3.66 (02/02/2019) =
- HTTP Header size and contents added to diagnostics page.

= Version 3.65 28/01/2019 =
- Change code where we use the uploads directory
- Moved hardcoded upload relative paths to ones based off ABSPATH
- Add upload directory details and exist check to diagnostics

= Version 3.64 28/01/2019 =
- More debug round wp_remote_get and my new fuction.

= Version 3.63 28/01/2019 =
- New standardised procedure which basically wraps wp_remote_get to simplify coding.	
- Complete removal of remote calls not using wp_remote_get
- More diagnostics added to the diagnostics screen
- Fix to longstanding error when rendering multiple maps on a single page

= Version 3.62 27/01/2019 =
- Commented out the data age override which I use for testing and had left enabled.
- Removes lots of old tag versions from SVN.
- Adds Assets folder with new screen shots and application Icon.
- Fixed Location Fetch from Canalplan to use wp_remote_get
- Fixed Curl override which wasn't working.
- Fixed release date - what can I say... worrying too much about things at work!

= Version 3.61 29/01/2019 =
- Moved some more calls to wp_remote_get
- Added CURL override parameters to see if it fixes a curl timeout issue
- Revised documentation 

= Version 3.60 28/01/2019 =
- Confirmed working against Wordpress 5.x
- Added support for Gutenberg (Plugin or native). Only works in Classic blocks or Code Edit mode. Inserts at END of block not at current cursor position
- Works if Gutenberg disabled using the Classic Editor plugin
- Works on Wordpress < 5 when Gutenberg is not installed
- Changed Polyline data collection as Canalplan no longer uses Polylines but plugin does 
- Moved all remote calls to use wp_remote_get and added some error handling round the call
- Fixed missing overnight markers in summary maps.


= Version 3.50 30/06/2018 =
- Minor bug fixes and code tweaks.

= Version 3.49 22/09/2017 =
- Fixed problem with re-importing routes where it kept overwriting the updated route
- More fixes to remove undefined indexes and variables.
 
= Version 3.48 22/09/2017 =
- Updated Location Widget to use new method and changed depricated PHP code
- Multiple changes to remove errors on undefined array indexes.
 
= Version 3.47 21/09/2017 =
- Removed some old code as part of a tidy up process
- Fixed an obscure bug in the google map rendering code.

= Version 3.46 21/09/2017 =
- Confirmed OK with 4.8.2
- Fixed a problem with the DB upgrade process.
- Moved to HTTPS for Canalplan URLs
- Moved gazetteer to place in canalplan URLs

= Version 3.45 21/05/2017 =
- fixed a typo in an admin url

= Version 3.44 11/03/2017 =
- fixed a typo in one of the create table scripts.

= Version 3.43 11/03/2017 =
- Moved Google Maps calls to HTTPS to stop mixed content warnings on HTTPS sites
- Added Google Maps API key support (Maps will run without it but it complains). See https://developers.google.com/maps/documentation/javascript/get-api-key for more details. 
- Added an extra OG tag handler so the trip pages give a better summary.

= Version 3.42 05/02/2017 =
- Database Schema to version 2.
- Added support for mutiple bloggedroute pages per blog - uses a new route tag field on the Manage Route page
- Fixed some multi-site only option calls that had got missed somewhere along the line
- Fixed a long standing problem with routeslugs in single blog mode.
- Route page when viewing a single route will replace the page title with the route title when theme supports it.
- Added extra parameter to the interface to Canalplan so that HTTPS sites can use the route planner import functions
- Added an "Update Canalplan on Publish" checkbox to the Canalplan box on the Edit Post Screen : This allows you to push an update of the canalplan location in a post when you publish it
- Added a daily cron task to do a bulk update to Canalplan. This is because HTTPS sites don't pass referrer urls so the entries get aged out of canalplan.


= Version 3.41 08/09/2016 =
- Added an is_tag() check so that we don't try to render maps on tag display pages - it breaks the location widget.
- Tweaks to the data updated time code


= Version 3.40 17/04/2016 =
- Looks like I messed up something on 3.30 so I've upped the version to 3.4 even though there aren't any really differences.

= Version 3.30 17/04/2016 =
- Fixes silly mistake in versioning !!!


= Version 3.3.1 17/04/2016 =
- Removed some debug statements left in the DB update code.


= Version 3.3 16/04/2016 =
- Added DB maintenance code
- Added co-ordinates strings for each day in a route. Speeds up route rendering and gets round problems with Pling Points being re-used
- Changed Data Pull process to pull major to major place links as well as major to minor points. Gets round problems re-calculating distances.
- Added dates to information on Data updated times.
- Conformed 4.5 compatible.
- Added code to remove "rogue" co-ordinates from line plotting.


= Version 3.22 06/10/2015 =
- Removed some short PHP tags which had crept in somewhere along the line


= Version 3.21 08/09/2015 =
- Added Reference Table row counts to the diagnostics page.
- Added "Last Updated" detail to the diagnostics page
- Fixed an issue with the overnight marker code.


= Version 3.20 05/09/2015 =
- Fixed typo in mobile check
- Added ability to push updates to Canalplan when a post is published or updated.


= Version 3.19 05/09/2015 =
- Confirmed 4.3 compatible
- Added the ability to map imported route posts to existing posts ( but requires manual editing of posts to add summary tags
- Added Roue ID to the Route management page to make it easier to work out which route is which.
- Added Trip and Post details to the Diagnostics - displays post titles and links for each day of each trip.


= Version 3.18 28/03/2015 =
- Confirmed 4.2.2 compatible
- Changes to the Location Configuration Page so that None Backitude location updates propogate to Canalplan if selected
- Changes to the Location Configuration Page and location handling page to force a wp-supercache refresh if the plugin in installed. 
- Adds support for mobile devices - on these the map width is always 100% of the view port. None mobile devices will use the user defined width.
- Change to the Bulk Notify so that it picks up the start and finish locations for the day.
- Moved some javascript to the footer to help improve page load times.
- Added Filters so that Jetpack creates OG:Description tags with place names in them instead of the [[CP tags
- Changes to the Map generation code to solve intermittent issues with map and "where are we" widget rendering.


= Version 3.17 22/11/2014 =
- Confirmed 4.0.1 compatible
- Fixed a bug where Posts in the WPMU site-search results picked up the containing page url rather than the actual post url
- Changes to short codes to include summary post in list of available posts


= Version 3.16 17/08/2014 =
- Changes to route import to include the summary post (if created) in the posts for that trip
- Changes to short codes to include summary post in list of available posts
- Unpublished posts were shown in the short codes if a route was published. Now only published Posts are shown as links
- Marker Places hidden from route management - unless they were set as an overnight when Importing the route.
- Fixed long standing bug relating to lock counts where flights are involved
- Fixed long standing bugs relating to stopping before or after lock flights
- Changes to route recalculation code to handle missing links (caused by importing a route using data points not in the local copy) which caused skewed values


= Version 3.15 21/07/2014 =
- Changes to Location Widget to stop missing values from blowing up all the googlemaps on the page
- Fixes to the location setting page to handle odd time differences appearing in Multisite installs
- Fixes to the location settings page to try to stop odd results when selecting one update option but using another.


= Version 3.14 20/07/2014 =
- Minor bug fixes to resolve some issues with the WPMUDEV global search plugin
- Minor bug fixes to the location screen to get rid of rogue slash characters appearing when manually setting location
- Remove diagnostic log writes from location code.


= Version 3.13 19/07/2014 =
- Location Updating now links through to Canalplan. So if you've liked and marked a boat for tracking on there then you can update your blog and canalplan at the same time
- Added New Short Code to link to Trip blog posts
- Other minor tweaks made.


= Version 3.12 17/05/2014 =
- Introduces Location updating using Backitude (an Android App) - allows you to automatically update your location using your mobile phone.
- Caches location so that the "where am I widget" doesn't have to keep doing the intensive distance look ups.


= Version 3.11 25/01/2014 =
- Confirms Wordpress 3.8.1 Compatibility


= Version 3.10 15/12/2013 =
- Confirms Wordpress 3.8 Compatibility


= Version 3.9 27/10/2013 =
- Tidied up the links to use the Canalplan shorter urls (/waterway /gazetteer )
- Added code to support Features


= Version 3.8 05/10/2013 =
- Added better support for RSS feeds - CP Links now work in RSS feeds
- Added better support for WPMU's Global Posts / Network RSS Feed
- Added New Short Codes to support Trip Summaries and Trip Maps


= Version 3.7 17/08/2013 =
- Added Location Options screen to the page menu
- Added the ability to re-import a route from canalplan.


= Version 3.6 17/08/2013 =
- New Location Options screen
- Location Widget recoded to use Location Options Screen
- API Key was not being saved properly.
- Minor tweaks to the Day Stats code
- Minor changes to the format of draft posts created during route importing.


= Version 3.5 04/05/2013 =
- Restored favourites per blog which had got lost somewhere (only affects multiblog installs)
- Added new linkify function to just return raw text.


= Version 3.4 28/04/2013 =
- Fixed a couple of bugs in the Manage Route page.


= Version 3.3 28/04/2013 =
- Found a bug in the midpoint logic for maps.


= Version 3.2 10/02/2013 =
- Tidied up a lot of the array index references
- Found some more mysql_ calls
- Fixed obsolescent menu constructs
- Confirmed Wordpress 3.5.1 compatible


= Version 3.1 22/01/2013 =
- Missing ARRAY_A broke a setting


= Version 3.0 20/01/2013 =
- Recoded all DB calls to use $wpdb calls
- Recoded all functions using mysql_ functions
- Recoded Google Map functions to make them work with JetPack
- Removed a lot of old commented out code.


= Version 2.8 13/10/2012 =
- Changed a couple of fixed URLS to use constants to make url changes easier.
- Added a couple of conditional checks to make things tidy.
- Moved Data Pull from Canalplan to use wp_get - so fopen is not needed any more which makes things better.


= Version 2.7 21/07/2012 =
- Server work to reduce DB download size.
- Recode data loader to use smaller fetch requests
- Fixed a rogue 500 error in the place matching routine.
- Changed all javascript urls to be relative rather than absolute to fix issue with running blog in a subdirectory
- Changed Where Am I widget to need full Google ID (i.e including the -) rather than just assuming it.
- Added before and after widget calls to the Widget so that it picks up theme formatting for widgets.
- Changed more hard coded Canalplan URLs to use the constants defined in the main file.


= Version 2.6 14/07/2012 =
- Found a few incorrect urls which would have caused some unwanted 404s


= Version 2.5 03/07/2012 =
- Rebuilding the blog revealed a glitch when a place vanished from the database which messed up the maps. Now fixed.
- Database moved on live server - so local data wasn't updating.


= Version 2.4 17/06/2012 =
- Fixed a problem with blogged routes. Was coded to expect a .htaccess re-write rule. No-one seems to have noticed though!
- Checked 3.4 compatability


= Version 2.3 12/06/2012 =
- Fixed the paths for lots of script and http references. Serves me right for developing in a folder with the wrong name!


= Version 2.2 03/05/2012 =
- Fixed the paths in the admin page so that the menus worked properly. Serves me right for developing in a folder with the wrong name!


= Version 2.1 29/04/2012 =
- Fixed an incorrect header in the widget plugin which confused the Wordpress SVN which reported the plugin as Version 1.0 rather than 2.0
- Latitude Widget no longer shows ! places as nearest locations.


= Version 2.0 28/04/2012 =
- Checked for Wordpress 3.3.2 compatability
- Removed Google Map API key code
- Recoded Google Maps to use Version 3 of the Maps API
- Added a Latitude Widget which links to Canalplan locations
- Added a Maps options page
- Recoded tag handling code to incorporate map customisation options.
- Re-wrote the user guide.
- Removed lots of commented out code left over from original development.


= Version 1.0 22/12/2011 =
- Checked for WP 3.3 compatability.
- Minor tweaks in some of the refresh logic


= Version 0.9.1 12/07/2011 =
- Added PDF user guide and added links to it
- Moved Calendar javascript into its own file
- Removed a lot of commented out code that wasn't needed any more
- Added Code revision tag for version checking of live installations
- Changed Bulk load limit back to 20 from 2 (left in by accident during testing)


= Version 0.9  04/07/2011 =
- Initial Beta release

