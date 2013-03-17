<?php

require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_canvas.php');
require_once ('jpgraph/jpgraph_bar.php');
require_once ('jpgraph/jpgraph_scatter.php');

function numCommitsAndTagCloud($numCommits, $words){
	
	// Setup a basic canvas we can work
	$g = new CanvasGraph(400,100 + count($words) * 15,'auto');
	$g->SetMargin(5,11,6,11);
	$g->SetShadow();
	$g->SetMarginColor("teal");
	
	// We need to stroke the plotarea and margin before we add the
	// text since we otherwise would overwrite the text.
	$g->InitFrame();
	
	// Draw a text box in the middle
	$txt="Commits $numCommits\n";
	foreach ($words as $word) {
		$txt .= "$word\n";
	}
	
	$t = new Text($txt,200,10);
	$t->SetFont(FF_ARIAL,FS_BOLD,10);
	 
	// How should the text box interpret the coordinates?
	$t->Align('center','top');
	
	// How should the paragraph be aligned?
	$t->ParagraphAlign('center');
	
	// Add a box around the text, white fill, black border and gray shadow
	$t->SetBox("white","black","gray");
	
	// Stroke the text
	$t->Stroke($g->img);
	
	// Stroke the graph
	return $g;
	
}

function dayHourWiseGraph($data) {


	//each row of data is of the form
	/*
	 * data['d'], data['h'], data['cnt']
	* */
		
	$datax = array();
	$datay = array();
	
	
	// We need to create X,Y data vectors suitable for the
	// library from the above raw data.
	$n = count($data);
	$format = array();
	for( $i=0; $i < $n; ++$i ) {
	
		$datay[$i] = $data[$i]['d'];
		$datax[$i] = $data[$i]['h'];
	
		// (X,Y,Size,Color)
		// Create a faster lookup array so we don't have to search
		// for the correct values in the callback function
		$format[strval($datax[$i])][strval($datay[$i])] = array($data[$i]['cnt'], 'yellow');
	
	}
	
	//die(var_dump($format));
	
	// Callback for markers
	// Must return array(width,border_color,fill_color,filename,imgscale)
	// If any of the returned values are '' then the
	// default value for that parameter will be used (possible empty)
	function FCallback($aYVal,$aXVal) {
		global $format;
		return array($format[strval($aXVal)][strval($aYVal)][0],'',
		$format[strval($aXVal)][strval($aYVal)][1],'','');
	}
	
	// Setup a basic graph
	$graph = new Graph(1000,450,'auto');
	$graph->SetScale("intint");
	$graph->SetMargin(40,40,40,40);
	$graph->SetMarginColor('wheat');
	
	$graph->title->Set("Commit Hours");
	$graph->title->SetFont(FF_ARIAL,FS_BOLD,12);
	$graph->title->SetMargin(10);
	
	// Use a lot of grace to get large scales since the ballon have
	// size and we don't want them to collide with the X-axis
	$graph->yaxis->scale->SetGrace(50,10);
	$graph->xaxis->scale->SetGrace(50,10);
	
	// Make sure X-axis as at the bottom of the graph and not at the default Y=0
	$graph->xaxis->SetPos('min');
	
	// Create the scatter plot
	$sp1 = new ScatterPlot($datay,$datax);
	$sp1->mark->SetType(MARK_X);
	$sp1->mark->Show();
	
	// Uncomment the following two lines to display the values
	//$sp1->value->Show();
	//$sp1->value->SetFont(FF_FONT1,FS_BOLD);
	
	// Specify the callback
	$sp1->mark->SetCallbackYX("FCallback");
	
	// Add the scatter plot to the graph
	$graph->Add($sp1);
	
	return $graph;
}

function get_months() {
	$time1  = strtotime("2008-08-01");
	$time2  = strtotime("2013-03-01");
	
	$months = array();
	for($yr = 2008; $yr < 2014; $yr++) {
		for($m = 1; $m < 13; $m++) {
			array_push($months, date("Y M", mktime(0,0,0,$m,1,$yr)));
			
			if($yr == 2013 && $m == 03)
				return $months;
		}
	}
}

function monthwiseGraph($data) {

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
		$input_data[$row['yr'].' '.$row['mnt']] = $row['cnt'];
		
		if($i == 0)
			$start = $row['yr'].' '.$row['mnt'];
		if($i == (count($data) - 1))
			$end = $row['yr'].' '.$row['mnt'];
		
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
	
	return $graph;
}

?>