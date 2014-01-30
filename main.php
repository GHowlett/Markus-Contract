<?php

// TODO: replace with actual credentials
$host = "example.com";
$user = "username";
$pass = "password";
$db = "db-name";

// TODO: handle network / execution failures

$con = mysqli_connect(host,user,pass,db);

$cleanup_query = "
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
	  ( select * FROM comprehension WHERE comprehension.id = comprehension_distractors.comprehension_id);";

// TODO: apply to a copy of the db, not the original
mysqli_query($con, $cleanup_query);

?>