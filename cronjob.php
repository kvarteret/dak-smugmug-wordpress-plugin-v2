<?php
// /*
// This script is a cron job. It checks the Smugmug site and calls an XML-RPC function if there are any new
// albums on the specified user's account.
// 
// NEEDS A DB SET UP WITH THE TABLE "ALBUMS" WITH AN INT COLUMN CALLED "ID" AND ALL VARIABLES SET BENEATH
// */

require_once "configcron.php";
require_once "phpSmug/phpSmug.php";


//$apiKey = "apikey";
$AppNameVersion = "kvarteret/1.0";
$domain = "http://www.kvarteret.no";

//$mailAdress = "mail@domain.com";
//$password = "password";

//$dbName = "smugTemp";

//$dbUser = "root";
//$dbPass = "pass";
//error_log("before getting values");
$smugObject = new phpSmug( "APIKey=" . $apiKey, "AppName=" . $AppNameVersion . "(" . $domain . ")" );

$smugObject->login( "EmailAddress=" . $mailAdress, "Password=" .$password );

//error message in cache function from phpSmug. No Cache is used.
//$smugObject->enableCache();

$albums = $smugObject->albums_get();

$db_albums = array();

//get all album id's from database

/*try {
	$dbh = new PDO('mysql:host=localhost;dbname=' . $dbName, $dbUser, $dbPass);
	foreach($dbh->query('SELECT id from albums') as $row) {
		array_push($db_albums, $row);
	}	
	
} catch (PDOException $e) {
	print "Error!: " . $e->getMessage() . "<br/>";
	die();
}*/
$table_name = $wpdb->prefix.'Dak_SmugMug_Albums';

foreach($wpdb->get_results( "SELECT id, album_id FROM ". $table_name ) as $row) {
	array_push($db_albums, $row);
}

error_log(count($albums) . "\n");

//compare to albums in $albums array
foreach ($albums as $album) {
	$isInDb = false;

	foreach($db_albums as $db_album) {
		if($db_album['id'] == $album['id']) {
			$isInDb = true;
		}
	}

	if(!$isInDb) {
		addNewAlbum($album);
	}
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
/*		$statement = "INSERT INTO `albums` (`id`) VALUES ('" . $album['id'] . "')";
		error_log($statement . "\n");
		try {
			$sth = $dbh->prepare($statement);
			$sth->execute();	
			$albumIsPosted=true;	
		}
		catch (PDOException $e) { 
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}		
*/
		$sql + $wpdb->insert($table_name, 'album_id' => $album['album_id']);
		
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


?>