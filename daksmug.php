<?php
/*
Plugin Name: DAK-Smugmug-wordpress-plugin
Plugin URI: https://github.com/kvarteret/dak-smugmug-wordpress-plugin-v2
Description: Wordpress plugin files to get photos from smugmug and display on a Wordpress site..
Version: 0.1
Author: Det Akademiske Kvarter Webgroup Bergen
Author URI: http://www.kvarteret.no
License: GPL2
*/
?>

<?php
/*  Copyright 2013  Det Akademiske Kvarter Webgroup Bergen  (email : web@kvarteret.no)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_shortcode("Hello","Hello");
//add_action("init","activation");
register_activation_hook(__FILE__, 'activation');
register_deactivation_hook(__FILE__, 'my_deactivation');



add_action('fetchAlbums', 'runCron');


/* args should contain album_id and album_key */
function Hello($args)
{

	//global $apiKey, $mailAdress, $password;
	require_once "configcron.php";
require_once "phpSmug/phpSmug.php";
	$AppNameVersion = "kvarteret/1.0";
	$domain = "http://www.kvarteret.no";

	$album_id = $args['album_id'];
	$album_key = $args['album_key'];

	$smugObject = new phpSmug( "APIKey=" . $apiKey, "AppName=" . $AppNameVersion . "(" . $domain . ")" );

     $smugObject->login( "EmailAddress=" . $mailAdress, "Password=" .$password );
    $images = $smugObject->images_get("AlbumID={$album_id}", "AlbumKey={$album_key}", "Extras=MediumURL");

    foreach($images["Images"] as $image)
    {
       echo "<img src='" . $image['MediumURL'] . "' /><br/>";
    }

	echo "hello world woooooogfvkfhdggdbghd, albumid: " . $args['album_id'];
	print_r($images);
}

function runCron()
{
	echo "inside runCron function";
	
	
include("cronjob.php");

}

add_filter( 'cron_schedules', 'cron_add_seconds' );
 
 function cron_add_seconds( $schedules ) {
 	// Adds once weekly to the existing schedules.
 	$schedules['ten_seconds'] = array(
 		'interval' => 10,
 		'display' => __( 'ten seconds' )
 	);
 	return $schedules;
 }

function activation()
{
	echo "inside activation";
	wp_schedule_event( time(), 'ten_seconds', 'fetchAlbums');
}

function my_deactivation() {
	wp_clear_scheduled_hook('fetchAlbums');
}
?>