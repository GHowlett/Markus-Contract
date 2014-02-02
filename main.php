<?php

// TODO: fill these variables with correct credentials

// Database Details
$host = "localhost";	// database host
$user = "root";			// database username
$pass = "";				// database password
$db = "contentdata";	// name of mySQL and SQLite database

// CloudFront Details
$key = 89283; 			 			   	// ID of the key pair used to sign CloudFront URLs for private distributions.
$secret = 'path/to/key'; 			   	// filepath to the private key used to sign CloudFront URLs for private distributions.
$dist = 12245; 			 			   	// Distribution ID
$paths = array('/ios/dev/$db.sqlite'); 	// List of objects to update / refresh

////////////////////////////////////////////////////////////

$cdb = "$db"."_copy";
$con = mysqli_connect($host,$user,$pass);

// TODO: support spaces in db names
function cloneDB($con, $db, $cdb) {
	$tables = mysqli_query($con, "SHOW TABLES"); 
	mysqli_query($con, "CREATE DATABASE $cdb");

	// faster than using mysqldump
	while ($table = mysqli_fetch_row($tables)) {
		mysqli_query($con, "CREATE TABLE $cdb.$table[0] LIKE $db.$table[0]");
		mysqli_query($con, "INSERT INTO $cdb.$table[0] SELECT * FROM $db.$table[0]");
	}
}

function cleanupDB($con) { mysqli_query($con, "
	DELETE FROM subtopics WHERE stage = 0;
	DELETE FROM cells WHERE NOT EXISTS
	  ( select * FROM subtopics WHERE cells.`subtopic_id` = subtopics.`subtopic_id`);
	DELETE FROM cells_to_phrases WHERE NOT EXISTS
	  ( select * FROM cells WHERE cells.`cell_id` = cells_to_phrases.`cell_id`);
	DELETE FROM phrases WHERE NOT EXISTS
	  ( select * FROM cells_to_phrases WHERE phrases.id = cells_to_phrases.`phrase_id`);
	DELETE FROM phrase_distractors WHERE NOT EXISTS
	  ( select * FROM phrases WHERE phrases.id = phrase_distractors.phrase_id);
	DELETE FROM blank_answers WHERE NOT EXISTS
	  ( select * FROM phrases WHERE phrases.id = blank_answers.`phrase_id`);
	DELETE FROM blank_distractors WHERE NOT EXISTS
	  ( select * FROM blank_answers WHERE blank_distractors.`blank_id` = blank_answers.`blank_id`);
	DELETE FROM cell_comprehension WHERE NOT EXISTS
	  ( select * FROM cells WHERE cells.cell_id = cell_comprehension.cell_id);
	DELETE FROM comprehension WHERE NOT EXISTS
	  ( select * FROM cell_comprehension WHERE cell_comprehension.comprehension_id = comprehension.id);
	DELETE FROM comprehension_distractors WHERE NOT EXISTS
	  ( select * FROM comprehension WHERE comprehension.id = comprehension_distractors.comprehension_id);"
);}

function refreshCloudFront() {
	$client = \Aws\CloudFront\CloudFrontClient::factory(array(
		'key_pair_id' => $key,
		'private_key' => $secret
	)); 

	$client->createInvalidation(array(
	    'DistributionId' => $dist,
	    'Paths' => array(
	        'Quantity' => count($paths),
	        'Items' => $paths,
	    ),
	    'CallerReference' => time()
	));
}

///////////////////////////////////////////////////////////////

mysqli_select_db($con, $db);
cloneDB($con, $db, $cdb);

mysqli_select_db($con, $cdb);
cleanupDB($con);

exec("mysql2sqlite.sh --user=$user --password=$pass --host=$host $cdb | sqlite3 $db.sqlite");

refreshCloudFront($key, $secret, $dist, $paths);

mysqli_query($con, "DROP DATABASE $cdb");

?>