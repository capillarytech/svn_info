<?php

$dev = $_GET['dev'];
$path = $_GET['path'];

require_once 'graph_queries.php';

$data = commitHourDistribution($dev, $path);
//each row of data is of the form
/*
 * data['d'], data['h'], data['cnt']
* */
$hour = "0";
$day = "0";
$punch = "0";

$max_punch = 0;
foreach ($data as $row) {
	if($row['cnt'] > $max_punch)
		$max_punch = $row['cnt'];
}

foreach ($data as $row) {
	$hour .= ','.($row['h']);
	$day .= ','.(7 - $row['d']);
	$punch .= ','.(intval((30 * ($row['cnt']/$max_punch))));
}

// Create some random text-encoded data for a line chart.
$url = 'http://chart.googleapis.com/chart';
$chd = "t:$hour|$day|$punch";

// Add data, chart type, chart size, and scale to params.
$chart = array(
    'cht' => 's',
	'chtt' => 'Punch Card',
    'chs' => '800x300',
    'chds' => '-1,24,-1,7,0,20',
    'chf' => 'bg,s,EFEFEF',
	'chm' => 'o,333333,1,-1,30',
    'chxt' => 'x,y',
    'chxl' => "0:||12am|1|2|3|4|5|6|7|8|9|10|11|12pm|1|2|3|4|5|6|7|8|9|10|11||1:||Sat|Fri|Thu|Wed|Tue|Mon|Sun|",
    'chd' => $chd);
//die(var_dump($chart));
header('content-type: image/png');

// Send the request, and print out the returned bytes.
$context = stream_context_create(
	array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query($chart, '', '&'))));


fpassthru(fopen($url, 'r', false, $context));

?>