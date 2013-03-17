<?php

$dev = $_GET['dev'];
$path = $_GET['path'];

require_once 'graph_queries.php';

require_once ('./../jpgraph/jpgraph.php');
require_once ('./../jpgraph/jpgraph_bar.php');

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

$data = commitMonthDistribution($dev, $path);

$datax = array();
$datay = array();

$all_months = get_months();
$input_data = array();


//each row of data is of the form
/*
 * data['yr'], data['mnt'], data['cnt']
* */
$start = "";
$end = "";
for($i = 0; $i < count($data); $i++) {
	$row = $data[$i];
	$yr = $row['mnt'].'\''.$row['yr'];
	$input_data[$yr] = $row['cnt'];

	if($i == 0)
		$start = $yr;
	if($i == (count($data) - 1))
		$end = $yr;

	//array_push($datay, $row['cnt']);
	//array_push($datax, $row['yr'].' '.$row['mnt']);
}

$skip = true;
foreach($all_months as $m) {

	if($m != $start && $skip)
		continue;

	$skip = false;
	array_push($datay, $input_data[$m] ? $input_data[$m] : 0);
	array_push($datax, $m);

	if($m == $end)
	break;
}


/*
// Create some random text-encoded data for a line chart.
$url = 'http://chart.googleapis.com/chart';
$chd = 't:';
foreach($datay as $y) {
	$chd .= "$y,";
}
$chd = substr($chd, 0, -1);

//Create the x-axis
$x_axis = "";
foreach($datax as $m) {
	$x_axis .= "|$m";
}

// Add data, chart type, chart size, and scale to params.
$width = count($datax) * 24;
$width = min(array($width, 1000));
$chbh = "10,12";
if($width == 1000)
	$chbh = intval(($width/count($datax)) - 5).",5";
$chart = array(
    'cht' => 'bvs',
	'chtt' => 'Commits / Month',
    'chs' => ''.$width.'x300',
    'chds' => 'a',
    'chbh' => $chbh,
    'chf' => 'bg,s,EFEFEF',
    'chxt' => 'x,y',
    'chxl' => "0:$x_axis",
    'chd' => $chd);
//die(var_dump($chart));
header('content-type: image/png');

// Send the request, and print out the returned bytes.
$context = stream_context_create(
	array('http' => array(
	      'method' => 'POST',
	      'content' => http_build_query($chart, '', '&'))));


fpassthru(fopen($url, 'r', false, $context)); */

//JPGRAPH IMPL
// Setup the graph.
$graph = new Graph(max(300, (count($datax) * 25)),350);
$graph->img->SetMargin(60,20,35,100);
$graph->SetScale("textint");
$graph->SetMarginColor("lightblue:1.1");
$graph->SetShadow();

// Set up the title for the graph
$graph->title->Set("Month wise commits");
$graph->title->SetMargin(8);
$graph->title->SetFont(FF_VERDANA,FS_BOLD,12);
$graph->title->SetColor("darkred");

// Setup font for axis
$graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,10);
$graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,10);

// Show 0 label on Y-axis (default is not to show)
$graph->yscale->ticks->SupressZeroLabel(false);

// Setup X-axis labels
$graph->xaxis->SetTickLabels($datax);
$graph->xaxis->SetLabelAngle(50);

// Create the bar pot
$bplot = new BarPlot($datay);
$bplot->SetWidth(0.6);

// Setup color for gradient fill style
$bplot->SetFillGradient("navy:0.9","navy:1.85",GRAD_LEFT_REFLECTION);

// Set color for the frame of each bar
$bplot->SetColor("white");
$graph->Add($bplot);

$graph->Stroke();

?>