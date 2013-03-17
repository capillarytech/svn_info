<?php

$con = mysql_connect(null, "root", "root");

function do_post_request($data, $optional_headers = null)
{
	$url = "http://api.search.yahoo.com/ContentAnalysisService/V1/termExtraction";
	$params = array('http' => array(
              'method' => 'POST',
              'content' => $data
	));
	if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	}
	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		throw new Exception("Problem with $url, $php_errormsg");
	}
	$response = @stream_get_contents($fp);
	if ($response === false) {
		throw new Exception("Problem reading data from $url, $php_errormsg");
	}
	return $response;
}

function do_curl_request($data) {
	// The request URL prefix

	$request =  'http://search.yahooapis.com/ContentAnalysisService/V1/termExtraction';
	// The request parameters
	$appid = $data['appid'];
	$context = $data['context'];
	//$query = 'madonna';
	// urlencode and concatenate the POST arguments
	$postargs = 'appid='.$appid.'&context='.urlencode($context).'&show_metadata=true';

	$session = curl_init($request);

	// Tell curl to use HTTP POST
	curl_setopt ($session, CURLOPT_POST, true);
	// Tell curl that this is the body of the POST
	curl_setopt ($session, CURLOPT_POSTFIELDS, $postargs);
	// Tell curl not to return headers, but do return the response
	curl_setopt($session, CURLOPT_HEADER, false);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($session);
	curl_close($session);

	return $response;

	/*$request =  'http://query.yahooapis.com/v1/public/yql';
	 // The request parameters
	$appid = $data['appid'];
	$context = $data['context'];
	//$query = 'madonna';
	// urlencode and concatenate the POST arguments
	//$postargs = 'appid='.$appid.'&context='.urlencode($context).'&show_metadata=true';

	$yql_query = "select * from contentanalysis.analyze where context='$context'";

	$request = $request . "?q=" . urlencode($yql_query);

	$session = curl_init($request);

	// Tell curl to use HTTP POST
	//curl_setopt ($session, CURLOPT_POST, true);
	// Tell curl that this is the body of the POST
	//curl_setopt ($session, CURLOPT_POSTFIELDS, $postargs);
	// Tell curl not to return headers, but do return the response
	//curl_setopt($session, CURLOPT_HEADER, false);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($session);
	curl_close($session);

	return $response;*/

}

function extractTagsFromWebResponse($webResponse) {
	$resultSet = new SimpleXMLElement($webResponse);

	$tags = array();
	foreach($resultSet->Result as $v) {
		array_push($tags, (string)$v);
	}

	return $tags;
}


function getFromDB($sql) {

	global $con;

	echo "executing $sql\n";

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

function getTopComitters() {
	$query = "SELECT dev, COUNT(*) as `commits`
			FROM  `svn_analysis`.`log_entries` 
			GROUP BY dev
			ORDER BY commits DESC";
	return select($query);
}

function commitDayHourDistribution() {
	
	$query = "
		SELECT DAYNAME(`when`) as d, HOUR(`when`) as h , COUNT(*) as cnt
		FROM `svn_analysis`.`log_entries`
		GROUP BY DAYNAME(`when`), HOUR(`when`)
		ORDER BY COUNT(*) DESC
	";
	
	return select($query);
}

function commitMonthDistribution() {

	$query = "
		SELECT YEAR(`when`) as yr, MONTHNAME(`when`) as mnt, COUNT(*) as cnt
		FROM `svn_analysis`.`log_entries`
		GROUP BY YEAR(`when`), MONTH(`when`)
		ORDER BY YEAR(`when`), MONTH(`when`) ASC
	";

	return select($query);
}

function getMsgs() {

	$query = "
			SELECT msg
			FROM `svn_analysis`.`log_entries`
		";

	$msgs = "";
	$rows = select($query);
	foreach($rows as $row) {
		$msgs .= " ".$row['msg'];
	}
	return $msgs;
}

function getTags() {

	$msgs = getMsgs();

	try {
		$data = array();
		$data['text'] = $msgs;
		$data['context'] = $msgs;
		$data['appid'] = "XV95n.XV34EXlZeSIPWoW2n7IpI8sNPPAQm8HL4NqaWVzH5_jBj1_A00svZKuSs.ug--";
		//$output = do_post_request($data);
		$webResp = do_curl_request($data);

		$output = extractTagsFromWebResponse($webResp);

		return $output;
	} catch (Exception $e) {
		echo "".print_r($e, true);
	}

}

die(var_dump(getTags(), true));

mysql_close($con);
?>