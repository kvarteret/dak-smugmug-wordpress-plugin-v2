<?PHP
/* 
Plugin Name: Album
Plugin URI: http://wordpress.com/
Description: A simple hello world wordpress plugin
Version: 1.0
Author: Uma
Author URI: http://wordpress/
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