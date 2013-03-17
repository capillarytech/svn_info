<?php

$dev = $_GET['dev'];
$path = $_GET['path'];

require_once 'graph_queries.php';


function get_months() {
	$time1  = strtotime("2008-08-01");
	$time2  = strtotime("2013-03-01");

	$months = array();
	for($yr = 2008; $yr < 2014; $yr++) {
		for($m = 1; $m < 13; $m++) {
			array_push($months, date("M'y", mktime(0,0,0,$m,1,$yr)));

			if($yr == 2013 && $m == 03)
			return $months;
		}
	}
}

$data = getPathMonthDistribution($dev, $path);

$datax = array();
$datay = array();

$all_months = get_months();
$input_data = array();


//each row of data is of the form
/*
 * data['yr'], data['mnt'], data['dev'], data['cnt']
* */
$start = "";
$end = "";
$devs = array();
$tot_files = 0;

for($i = 0; $i < count($data); $i++) {
	$row = $data[$i];
	$yr = $row['mnt'].'\''.$row['yr'];
	$dev = $row['dev'];
	$input_data[$dev][$yr] = $row['cnt'];
	if(!$devs[$dev])
		$devs[$dev] = 0;
	$devs[$dev] = $devs[$dev] + $row['cnt'];
	if($i == 0)
		$start = $yr;
	if($i == (count($data) - 1))
		$end = $yr;
	$tot_files += $row['cnt'];
}

arsort($devs);


// Create some random text-encoded data for a line chart.
$url = 'http://chart.googleapis.com/chart';
$chd = "t:"; //counts
$chl = ""; //perc
$chld = ""; //dev
$max_slice = 0;
foreach($devs as $d => $cnt) {
	$perc = round(100*($cnt/$tot_files));
	
	if($perc < 1)
		continue;
	
	$chd .= "$cnt,";
	$chld .= "$d($perc%)|";
	$chl .= $perc."%|";
	if($cnt > $max_slice)
		$max_slice = $cnt;
}
//REMOVE LAST comma
$chd = substr($chd, 0, -1);
$chl= substr($chl, 0, -1);
$chld = substr($chld, 0, -1);

//    'chco' => '41A317,808080',
//    'chdl' => $chl,
// Add data, chart type, chart size, and scale to params.
$chart = array(
    'cht' => 'p',
	'chtt' => 'Num of Files Modified',
    'chs' => '600x400',
    'chds' => "0,".$max_slice,
    'chma' => '5,5,10,10',
    'chl' => $chld,
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