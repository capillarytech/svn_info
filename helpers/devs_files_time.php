<?php

$dev = $_GET['dev'];
$path = $_GET['path'];

require_once 'graph_queries.php';

require_once ('./../jpgraph/jpgraph.php');
require_once ('./../jpgraph/jpgraph_line.php');

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
}

arsort($devs);

$skip = true;
//Populate the x axis
foreach($all_months as $m) {
	
	if($m != $start && $skip)
		continue;
	
	$skip = false;
	array_push($datax, $m);

	if($m == $end)
		break;
}

//Populate the data series for each dev
$dev_plots = array();
foreach($devs as $d => $v) {
	$ydata = array();
	foreach($datax as $m) {
		array_push($ydata, $input_data[$d][$m]);
	}
	//Create the second data series
	$lineplot=new LinePlot($ydata);
	$lineplot->SetStyle( 'solid' );   // Two pixel wide
	$lineplot->SetWeight( 5 );   // Two pixel wide
	$lineplot->SetLineWeight(5);
	$lineplot->SetLegend($d);
	
	//Add the second plot to the graph
	array_push($dev_plots, $lineplot);
}


//JPGRAPH IMPL
// Setup the graph.
$graph = new Graph(max(800, (count($datax) * 25)),550);
$graph->img->SetMargin(40,10,30,100);
$graph->SetScale("textint");
$graph->SetMarginColor("lightblue:1.1");
$graph->SetShadow();

// Set up the title for the graph
$graph->title->Set("Month wise files modified");
$graph->title->SetMargin(8);
$graph->title->SetFont(FF_VERDANA,FS_BOLD,12);
$graph->title->SetColor("darkred");

// Setup font for axis
$graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,10);
$graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,10);
$graph->yaxis->title->Set('# of files');

// Show 0 label on Y-axis (default is not to show)
$graph->yscale->ticks->SupressZeroLabel(false);

// Setup X-axis labels
$graph->xaxis->SetTickLabels($datax);
$graph->xaxis->SetLabelAngle(50);

//Add the line plots
foreach($dev_plots as $dev_plot) {
	$graph->Add($dev_plot);
}

$graph->Stroke();

?>