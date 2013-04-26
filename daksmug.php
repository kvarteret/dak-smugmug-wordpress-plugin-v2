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
    global $wpdb;
    require_once "phpSmug/phpSmug.php";
    add_shortcode("DakSmugInsert","DakSmugInsert");
//add_action("init","activation");
    register_activation_hook(__FILE__, 'activation');
    register_deactivation_hook(__FILE__, 'my_deactivation');
    add_action('fetchAlbums', 'runCron');
    add_filter( 'cron_schedules', 'cron_add_seconds' );
    /* args should contain album_id and album_key */
    function DakSmugInsert($args)
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

	//echo "hello world woooooogfvkfhdggdbghd, albumid: " . $args['album_id'];
     print_r($images);
 }

 function runCron()
 {
   $smugObject = new phpSmug( "APIKey=" . $apiKey, "AppName=" . $AppNameVersion . "(" . $domain . ")" );
$smugObject->login( "EmailAddress=" . $mailAdress, "Password=" .$password );

//error message in cache function from phpSmug. No Cache is used.
//$smugObject->enableCache();

$albums = $smugObject->albums_get();

$db_albums = array();

//get all album id's from database
$table_name = $wpdb->prefix.'Dak_SmugMug_Albums';
foreach($wpdb->get_results( "SELECT album_id FROM ". $table_name ) as $row) {
  array_push($db_albums, $row);
}
error_log(count($albums) . "\n");
//compare to albums in $albums array
foreach ($albums as $album) {
  $isInDb = false;
  foreach($db_albums as $db_album) {
    if($db_album['album_id'] == $album['id']) {
      $isInDb = true;
    }
  }
  if(!$isInDb) {
    addNewAlbum($album);
  }
} 
}

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
  $tablename = $wpdb->prefix.'Dak_SmugMug_Albums';
  $sql = "CREATE TABLE $tablename ( 
      `album_id` VARCHAR(255) NOT NULL,  
      PRIMARY  KEY (`album_id`)
      );";
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
	
  $results=$wpdb -> get_results("SELECT * FROM wp_Dak_SmugMug_Albums");
 // echo $results;
error_log(print_r($results, true));

wp_schedule_event( time(), 'ten_seconds', 'fetchAlbums');
}

function my_deactivation() {
  global $wpdb; //required global declaration of WP variable

  $table_name = $wpdb->prefix.'Dak_SmugMug_Albums';

  $sql = "DROP TABLE ". $table_name;

  $wpdb->query($sql);
	wp_clear_scheduled_hook('fetchAlbums');
  
}
//Function for adding new album to the db and creating a draft in wp.
function addNewAlbum($album) {
  global $dbName;
  $albumIsPosted = false;
  error_log("inside add new album");
  //TODO make the draft
  $draftMade = TRUE;
  //if it succeded add the id to the database
  
  if($draftMade) {
    $wpdb->insert($table_name, array('album_id' => $album['album_id']));
    $albumIsPosted = true;
  }

  if($albumIsPosted)
  {
    $new_post = array(
      'post_title' => 'New Album',
      'post_content' => "[DakSmugInsert album_id={$album['id']} album_key={$album['Key']}]",
      'post_status' => 'draft',
      'post_date' => date('Y-m-d H:i:s'),
//'post_author' => $user_ID,
      'post_type' => 'post',
      'post_category' => array(0)
      );
    //echo "creating posts";
    $post_id = wp_insert_post($new_post,true);
    if ( is_wp_error( $post_id ) ) {
      $error_string = $post_id->get_error_message();
      echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
    }
  } 
}