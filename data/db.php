<?php
$con = "";
function dbopen() {
	global $con;
	$con = mysql_connect(null, "root", "root");
}

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

function dbclose() {
	global $con;
	mysql_close($con);
}

?>