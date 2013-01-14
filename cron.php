<?php
// /*
// This script is a cron job. It checks the Smugmug site and calls an XML-RPC function if there are any new
// albums on the specified user's account.
// 
// NEEDS A DB SET UP WITH THE TABLE "ALBUMS" WITH AN INT COLUMN CALLED "ID" AND ALL VARIABLES SET BENEATH
// */

require_once "phpSmug/phpSmug.php";

$apiKey = "apikey";
$AppNameVersion = "kvarteret/1.0";
$domain = "http://www.kvarteret.no";

$mailAdress = "mail@domain.com";
$password = "password";

$dbName = "smugTemp";

$dbUser = "root";
$dbPass = "pass";

$smugObject = new phpSmug( "APIKey=" . $apiKey, "AppName=" . $AppNameVersion . "(" . $domain . ")" );

$smugObject->login( "EmailAddress=" . $mailAdress, "Password=" .$password );

//error message in cache function from phpSmug. No Cache is used.
//$smugObject->enableCache();

$albums = $smugObject->albums_get();

$db_albums = array();


//get all album id's from database

try {
    $dbh = new PDO('mysql:host=localhost;dbname=' . $dbName, $dbUser, $dbPass);
		    foreach($dbh->query('SELECT id from albums') as $row) {
		    	array_push($db_albums, $row);
		}	
	
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}


//compare to albums in $albums array
	foreach ($albums as $album) {
    		 $isInDb = false;
			foreach($db_albums as $db_album) {
				if($db_album['id'] == $album['id']) {
					$isInDb = true;
				}
			}
			if(!$isInDb) {
				addNewAlbum($album, $dbh);
			}
    	}		

    $dbh = null;

//Function for adding new album to the db and creating a draft in wp.
function addNewAlbum($album, $dbh) {
	
	global $dbName;
	
	echo "called addNewAlbum";
	var_dump($album['id']);
	echo $album['id'];
	//make the draft
	
	//if it succeded add the id to the database
	
	$statement = "INSERT INTO `" . $dbName . "`.`albums` (`id`) VALUES ('" . $album['id'] . "')";
	echo $statement;
	try {
		$sth = $dbh->prepare($statement);
		$sth->execute();		
	}
	catch (PDOException $e) { 
		print "Error!: " . $e->getMessage() . "<br/>";
    	die();
	}
	
}	


?>