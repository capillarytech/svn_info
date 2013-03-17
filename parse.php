<?php

ini_set('memory_limit', '500M');

$file = "svn_log_verbose_to_add.xml";

$file_contents = file_get_contents($file);

$logs = new SimpleXMLElement($file_contents);

$batch = array();

$total = count($logs->logentry);
$count = 0;

$insert = "INSERT INTO `svn_analysis`.`log_entries` VALUES ";

$insert_changes = "INSERT INTO `svn_analysis`.`changes` VALUES ";
$changes_batch = array();

$remove = array("\n", "\r\n", "\r", "\t");
foreach ($logs->logentry as $logentry) {
	
	$author = $logentry->author;
	$commit_time = date("Y-m-d H:i:s", strtotime($logentry->date));
	$msg =
		mysql_escape_string(str_replace($remove, ' ', $logentry->msg));
	
	//print "Author : $author\n CommitTime:$commit_time\nMsg:$msg";
	$rev = "";
	foreach($logentry->attributes() as $k => $v) {
		$rev = $v;
		break;
	}
	
	$count++;
	
	array_push($batch, "(NULL,'$author','$commit_time','$msg', '$rev')");

	//populate the changes
	foreach($logentry->paths as $path) {
		foreach($path as $p) {
			$p = mysql_escape_string($p);
			array_push($changes_batch, "(NULL,'$author','$commit_time','M', '$p', '$rev')");
		}
	}

	if(!($count == $total || count($batch) == 500))
		continue;
	
	$insert_query = $insert.implode(",", $batch);
	$batch = array();
	putToDB($insert_query);
 
 
	//changes batch insertion
	$changes_total = count($changes_batch);
	$changes_batch_insert = array();
	$changes_batch_insert_count = 0;
	while($changes_total > 0) {
		$c = array_shift($changes_batch);
		$changes_batch_insert_count++;
		$changes_total--;
		array_push($changes_batch_insert, $c);
		if($changes_batch_insert_count == 10 || $changes_total == 0){
			
			$insert_changes_query = $insert_changes.implode(',', $changes_batch_insert);
			$changes_batch_insert = array();
			putToDB($insert_changes_query);
			$changes_batch_insert_count = 0;
		}
	}
}

//Add 330 mins to time for timezone adjustment
putToDB("UPDATE `svn_analysis`.`log_entries` SET `when` = DATE_ADD(`when`, INTERVAL 330 MINUTE)");
putToDB("UPDATE `svn_analysis`.`changes` SET `when` = DATE_ADD(`when`, INTERVAL 330 MINUTE)");

function putToDB($sql) {
	
	echo "executing $sql";
	
	$con = mysql_connect(null, "root", "root");
		// Execute query
	$success = mysql_query($sql,$con);
	echo "Query success : $success";
		
	mysql_close($con);
	
}


?>
