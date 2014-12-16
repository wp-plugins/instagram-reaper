=== Instagram Reaper ===
Contributors: _qrrr
Donate link:
Tags: theme development, instagram, wp_cron
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin for developers. Gets instagram images by username or hashtag

== Description ==

Set up wp_cron jobs to occur every minute, half-hour, hour, daily, or weekly.  Saves instagram image data to database for use in custom theme development.

Saves image src's for full-size, low-resolution and thumbnail sizes.  Also saves the like count for an image at the time of the api query, and will update this count should the image be part of a later query.

`inst_reaper_get_harvest()`
* returns array of image data for every image saved in the cron
  * id [the unique ID of the image in the Wordpress database]
  * inst_id [the unique ID of the image as supplied by Instagram]
  * url [the url link to the image on Instagram]
  * src [the full size image url]
  * src_low_res [the smaller size image url]
  * src_thumb [the thumbnail size image url]
  * likes_count [the number of favories or likes of the image]
  * comments count [the number of comments of the image]
  * date_created [the date the image was created on Instagram]

  example:

`
$images = inst_reaper_get_harvest();
foreach ($images as $image) { ?>
  <a href="<?php echo $image['url']; ?>">
    <img src="<?php echo $image['src']; ?>" />
  </a>
<?php }
`


`inst_reaper_get_harvest_in_range($start, $stop)`
* returns array of image data for images in range supplied as arguments
* data return is the same as get_instagram_reap()

example: 

`
$images = inst_reaper_get_harvest_in_range(0, 25);
foreach ($images as $image) { ?>
  <a href="<?php echo $image['url']; ?>">
    <img src="<?php echo $image['src']; ?>" />
  </a>
<?php }
`


`inst_reaper_get_user_id_by_name($username)`
* returns Instagram user id (for use in custom Instagram query)

`inst_reaper_save_photos()`
* this is the function called in the cron-job.  You can call it at your leasure if you want to update get new images outside of the schedule

`inst_reaper_get_photos($args)`
* query the instagram API directly. This will not save in the database
* currently can query by Hashtag or Username


`$args:`
* query (required) - 'hashtag' or 'username'
* user_id - the Instagram User id for the query.  If you do not know the User ID, use
* username - this will get the user ID by the username before querying
* hashtag - the hashtag for the query
* count - the number of images to return

example:

`
$args = array(
  'query' => 'username',
  'username' => 'dvl',
  'count' => '30'
);

$images = inst_reaper_get_photos($args);
foreach ($images as $image) { ?>
  <a href="<?php echo $image['url']; ?>">
    <img src="<?php echo $image['src']; ?>" />
  </a>
<?php }
`

== Installation ==

1. Upload this plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Provide Instagram Client ID and query options, and start the cron

== Changelog ==

== Code Snippets ==

`inst_reaper_get_harvest()`
* returns array of image data for every image saved in the cron
  * id [the unique ID of the image in the Wordpress database]
  * inst_id [the unique ID of the image as supplied by Instagram]
  * url [the url link to the image on Instagram]
  * src [the full size image url]
  * src_low_res [the smaller size image url]
  * src_thumb [the thumbnail size image url]
  * likes_count [the number of favories or likes of the image]
  * comments count [the number of comments of the image]
  * date_created [the date the image was created on Instagram]

  example:

`
$images = inst_reaper_get_harvest();
foreach ($images as $image) { ?>
  <a href="<?php echo $image['url']; ?>">
    <img src="<?php echo $image['src']; ?>" />
  </a>
<?php }
`


`inst_reaper_get_harvest_in_range($start, $stop)`
* returns array of image data for images in range supplied as arguments
* data return is the same as get_instagram_reap()

example: 

`
$images = inst_reaper_get_harvest_in_range(0, 25);
foreach ($images as $image) { ?>
  <a href="<?php echo $image['url']; ?>">
    <img src="<?php echo $image['src']; ?>" />
  </a>
<?php }
`


`inst_reaper_get_user_id_by_name($username)`
* returns Instagram user id (for use in custom Instagram query)

`inst_reaper_save_photos()`
* this is the function called in the cron-job.  You can call it at your leasure if you want to update get new images outside of the schedule

`inst_reaper_get_photos($args)`
* query the instagram API directly. This will not save in the database
* currently can query by Hashtag or Username

`$args:`
* query (required) - 'hashtag' or 'username'
* user_id - the Instagram User id for the query.  If you do not know the User ID, use
* username - this will get the user ID by the username before querying
* hashtag - the hashtag for the query
* count - the number of images to return

example:

`
$args = array(
  'query' => 'username',
  'username' => 'dvl',
  'count' => '30'
);

$images = inst_reaper_get_photos($args);
foreach ($images as $image) { ?>
  <a href="<?php echo $image['url']; ?>">
    <img src="<?php echo $image['src']; ?>" />
  </a>
<?php }
`