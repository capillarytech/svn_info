<?php

require_once ('tags.php');

require_once ('jpgraph/jpgraph_mgraph.php');

require_once ('graph_helper.php');

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

function getCommitCount($dev) {
	
	$query = "SELECT COUNT(*) as `cnt` FROM  `svn_analysis`.`log_entries` WHERE dev = '$dev'";
	$res = select($query);
	$first = $res[0];
	foreach ($first as $key => $val) {
		return $val;
	}
}

function commitDayHourDistribution($dev) {

	$query = "
		SELECT DAYOFWEEK(`when`) as d, HOUR(`when`) as h , COUNT(*) as cnt
		FROM `svn_analysis`.`log_entries`
		WHERE dev = '$dev'
		GROUP BY DAYNAME(`when`), HOUR(`when`)
	";

	return select($query);
}

function commitMonthDistribution($dev) {

	$query = "
		SELECT YEAR(`when`) as yr, DATE_FORMAT(`when`, '%b') as mnt, COUNT(*) as cnt
		FROM `svn_analysis`.`log_entries`
		WHERE dev = '$dev'
		GROUP BY YEAR(`when`), MONTH(`when`)
		ORDER BY YEAR(`when`), MONTH(`when`) ASC
	";

	return select($query);
}

function getMsgs($dev) {

	$query = "
			SELECT msg
			FROM `svn_analysis`.`log_entries`
			WHERE dev = '$dev'
		";
	
	$msgs = "";
	$rows = select($query);
	foreach($rows as $row) {
		$msgs .= " ".$row['msg'];
	}
	return $msgs;
}

function getTags($dev) {
	
	$msgs = getMsgs($dev);
	
	return frequencyBasedTags($msgs);
	
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
		//echo "".print_r($e, true);
	}
	
}

function tagCloud($dev) {
	$tags = getTags($dev);
	
	$maxsize = 15;
	$minsize = 10;
	
	$tagstmp = array_count_values($tags);
	$maxqty = @max(array_values($tagstmp));
	$minqty = @min(array_values($tagstmp));
	
	$spread = $maxqty - $minqty;
	if ($spread == 0) $spread = 1;
	$step = ($maxsize - $minsize) / ($spread);
	
	$output = '';
	$html = '<ul class="tagcloud">';
	foreach ($tags as $t) {
		$size = rand($minsize, $maxsize);
		//$output .= '<a style=\"font-size:'.$size.'px;\">'.$t.'</a>';
		$ratio = rand(11, 20) * 10;
		$html.= '<li class="cloud-'.$ratio.'"><a href="/tag/'.$t.'">'.$t.'</a></li>';
	}
	//close the UL
	$html.= '</ul>';
	
	$output = $html;
	echo $output;
}

function commitsAndTagCloud($dev) {
	$tags = getTags($dev);
	$commits = getCommitCount($dev);

	return numCommitsAndTagCloud($commits, $tags);
}

function dayHourWise($dev) {

	/*$datay = implode(',', array(11,30,20,13,10,'x',16,12,'x',15,4,9));
	
	
	//echo "<img src=\"graph_helper.php?type=dayhour&data=$datay\">";
	return dayHourWiseGraph($datay);*/
	return dayHourWiseGraph(commitDayHourDistribution($dev));
	
}

function monthwise($dev) {
	// Some data
	/*$ydata = array(5,10,15,20,15,10,8,7,4,10,5);
	
	$datay = implode(',', array(11,30,20,13,10,'x',16,12,'x',15,4,9));
	
	
	//echo "<img src=\"graph_helper.php?type=month&data=$datay\">";
	//return monthwiseGraph($datay);*/
	
	return monthwiseGraph(commitMonthDistribution($dev));
	
}

$graphs = array();

//TODO GET
$dev = $_GET['dev'];

//Commit Count

//Tag Cloud
//tagCloud($dev);
array_push($graphs, commitsAndTagCloud($dev));

//DayHourDistribution
array_push($graphs, dayHourWise($dev));

//MonthDistribution
array_push($graphs, monthwise($dev));


if(count($graphs) > 0) {
	$mgraph = new MGraph();
	
	$y = 0;
	
	foreach ($graphs as $graph) {
		$mgraph->Add($graph,0,$y);
		$y += $graph->img->original_height;
		$y += 10; 
	}
	
	$mgraph->Stroke();
}

mysql_close($con);
?>