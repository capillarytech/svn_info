<?php

$dev = $_GET['dev'];
$path = $_GET['path'];

require_once 'graph_queries.php';

//eg : devs=abhilash,arjit,prasun	cnt=15
// convert to abhilash-arjit 15, abhilash-prasun 15, arjit-prasun 15
// if dev = abhilash, ignore arjit-prasun edge
$common_edits = getCommonEdits($dev, $path);

//ordered alphabetically
$edges = array();
foreach($common_edits as $c_edit) {
	$cnt = $c_edit['cnt'];
	$devs = explode(',', $c_edit['devs']);
	$count = count($devs);
	
	//Add up the counts
	for($i=0; $i<$count; $i++) {
		for($j=$i+1; $j < $count; $j++) {
			$d1 = $devs[$i];
			$d2 = $devs[$j];
			
			if($dev != 'all' && ($d1 != $dev && $d2 != $dev))
				continue; //Irrelevant edge
			
			if(!(isset($edges[$d1][$d2])))
				$edges[$d1][$d2] = 0;
			
			$edges[$d1][$d2] = $edges[$d1][$d2] + $cnt;
		}
	}
}

//make a map, key is the edge format and the value can be the weight
$edges_map = array();
foreach($edges as $d1 => $d2_array) {
	foreach($d2_array as $d2 => $cnt) {
		$edges_map[$d1.'--'.$d2] = $cnt;
	}
}
arsort($edges_map);

$chl = "";
$max_edges = 150;
foreach($edges_map as $d1d2 => $cnt) {
	
	if($cnt < 4)
		continue;
	
	//a--b[weight=5]
	$chl .= $d1d2."[weight=$cnt],";
	
	$max_edges--;
	if($max_edges == 0)
		break;
}
$chl = substr($chl, 0, -1);
$chl = str_replace(".", "_", $chl);

// create a graphs with weights
$chart = array(
    'cht' => 'gv',
    'chl' => "graph{".$chl."}");

header('content-type: image/png');

// Send the request, and print out the returned bytes.
$context = stream_context_create(
array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query($chart, '', '&'))));

$url = 'http://chart.googleapis.com/chart';
fpassthru(fopen($url, 'r', false, $context));

?>