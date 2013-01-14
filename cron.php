<?php
// /*
// This script is a cron job. It checks the Smugmug site and calls an XML-RPC function if there are any new
// albums on the specified user's account.
// */

require_once "phpSmug/phpSmug.php";

$apiKey = "INSERT API KEY";
$AppNameVersion = "AppName/VersionNo";
$domain = "http://www.domain.com";

$mailAdress = "mail@useraccounatsmug.com";
$password = "password";


$smugObject = new phpSmug( "APIKey=" . $apiKey, "AppName=" . $AppNameVersion . "(" . $domain . ")" );

$smugObject->login( "EmailAddress=" . $mailAdress, "Password=" .$password );
//$smugObject->enableCache();

$albums = $smugObject->albums_get();

$db_albums = array();

//DEBUG var_dump($albums);


//get all album id's from database

try {
    $dbh = new PDO('mysql:host=localhost;dbname=smugTemp', "root", "jur8ucib");
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
	
	echo "called addNewAlbum";
	var_dump($album['id']);
	echo $album['id'];
	//make the draft
	
	//if it succeded add the id to the database
	
	$statement = "INSERT INTO `smugTemp`.`albums` (`id`) VALUES ('" . $album['id'] . "')";
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