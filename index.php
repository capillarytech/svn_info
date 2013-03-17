<?php

$con = mysql_connect(null, "root", "root");

function getFromDB($sql) {

	global $con;

	//echo "executing $sql\n";

	//Execute query
	$res = mysql_query($sql,$con);

	return $res;
}

function select($sql) {
	$result = getFromDB($sql);
	$response = array();
	while($row = mysql_fetch_array($result)) {
		array_push($response, $row);
	}
	return $response;
}

function getDevs() {

	$query = "
		SELECT dev, COUNT(*) as cnt
		FROM `svn_analysis`.`log_entries`
		GROUP BY dev
		ORDER BY dev ASC
	";

	return select($query);
}

echo "<a href=\"user_page_new.php?dev=all&path=/\">all</a><br>";

foreach( getDevs() as $devinfo) {
	$dev = $devinfo['dev'];
	$cnt = $devinfo['cnt'];
	echo "<a href=\"user_page_new.php?dev=$dev&path=/\">$dev</a><br>";
}

mysql_close($con);
?>