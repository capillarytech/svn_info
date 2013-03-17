<?php

require_once ('./../data/db.php');

function getPathFilter($dev, $path_prefix){
	
	$dev_filter = "";
	if($dev != "all") {
		$dev_filter = "AND sub.dev = '$dev'";
	}
	
	$path_filter = "";
	
	if($path_prefix != '/') {
		$path_filter = " AND `rev` IN (
			
			SELECT DISTINCT sub.rev
			FROM `svn_analysis`.`changes` sub
			WHERE sub.`path` LIKE '$path_prefix%'
				$dev_filter
		)";
	}
	return $path_filter;
}

function commitMonthDistribution($dev, $path_prefix) {

	dbopen();
	$dev_filter = "";
	if($dev != "all") {
		$dev_filter = " AND dev = '$dev'";
	}
	
	/*$path_filter = getPathFilter($dev, $path_prefix);
	
	$query = "
		SELECT DATE_FORMAT(`when`, '%y') as yr, DATE_FORMAT(`when`, '%b') as mnt, COUNT(*) as cnt
		FROM `svn_analysis`.`log_entries`
		WHERE 1 = 1
			$dev_filter
			$path_filter
		GROUP BY YEAR(`when`), MONTH(`when`)
		ORDER BY YEAR(`when`), MONTH(`when`) ASC
	";*/
	$path_prefix = mysql_escape_string($path_prefix);
	
	$query = "
		SELECT DATE_FORMAT(`when`, '%y') as yr, DATE_FORMAT(`when`, '%b') as mnt,
				COUNT(DISTINCT rev) as cnt
		FROM `svn_analysis`.`changes`
		WHERE `path` LIKE '$path_prefix%'
			$dev_filter
		GROUP BY YEAR(`when`), MONTH(`when`)
		ORDER BY YEAR(`when`), MONTH(`when`) ASC
	";

	$ret = select($query);
	dbclose();
	return $ret;
}


function commitHourDistribution($dev, $path_prefix) {

	dbopen();
	$dev_filter = "";
	if($dev != "all") {
		$dev_filter = "AND dev = '$dev'";
	}

	/*$path_filter = getPathFilter($dev, $path_prefix);
	
	$query = "
			SELECT DAYOFWEEK(`when`) as d, HOUR(`when`) as h , COUNT(*) as cnt
			FROM `svn_analysis`.`log_entries`
			WHERE 1 = 1
				$dev_filter
				$path_filter
			GROUP BY DAYNAME(`when`), HOUR(`when`)
		";*/
	
	$path_prefix = mysql_escape_string($path_prefix);
	
	$query = "
			SELECT  DAYOFWEEK(`when`) as d, HOUR(`when`) as h , COUNT(DISTINCT rev) as cnt
			FROM `svn_analysis`.`changes`
			WHERE `path` LIKE '$path_prefix%'
				$dev_filter
			GROUP BY DAYNAME(`when`), HOUR(`when`)
	";
	
	$ret = select($query);
	dbclose();
	return $ret;
	
	
}

function getMsgs($dev, $path_prefix) {

	dbopen();
	$dev_filter = "";
	if($dev != "all") {
		$dev_filter = " AND c.dev = '$dev'";
	}
	
	/*$path_filter = getPathFilter($dev, $path_prefix);
	
	$query = "
			SELECT msg
			FROM `svn_analysis`.`log_entries`
			WHERE 1 = 1 
				$dev_filter
				$path_filter
		";
	$msgs = "";*/
	
	$path_prefix = mysql_escape_string($path_prefix);
	
	$query = "
		SELECT msg
		FROM `svn_analysis`.`changes` c
		JOIN `svn_analysis`.`log_entries` l ON l.rev = c.rev
		WHERE `path` LIKE '$path_prefix%'
			$dev_filter
		GROUP BY c.rev
	";
	
	
	$rows = select($query);
	foreach($rows as $row) {
		$msgs .= " ".$row['msg'];
	}

	dbclose();
	return $msgs;
}

function getCommitCount($dev, $path_prefix) {

	/*dbopen();
	$dev_filter = "";
	if($dev != "all") {
		$dev_filter = " AND dev = '$dev'";
	}
	
	$path_filter = getPathFilter($dev, $path_prefix);
	
	$query = "SELECT COUNT(*) as `cnt` FROM  `svn_analysis`.`log_entries` 
				WHERE 1 = 1 
					$dev_filter
					$path_filter";
	$res = select($query);
	$first = $res[0];
	$ret = "";
	foreach ($first as $key => $val) {
		$ret = $val;
		break;
	}
	dbclose();*/
	
	dbopen();
	$dev_filter = "";
	if($dev != "all") {
		$dev_filter = "AND dev = '$dev'";
	}
	$path_prefix = mysql_escape_string($path_prefix);
	
	$query = "
				SELECT COUNT(DISTINCT rev) as cnt
				FROM `svn_analysis`.`changes`
				WHERE `path` LIKE '$path_prefix%'
					$dev_filter
			";
	
	$res = select($query);
	$first = $res[0];
	$ret = "";
	foreach ($first as $key => $val) {
		$ret = $val;
		break;
	}
	
	dbclose();
	return $ret;
		
}

function getPathMonthDistribution($dev, $path_prefix) {
	
	dbopen();
	$dev_filter = "";
	if($dev != "all") {
		$dev_filter = "AND dev = '$dev'";
	}
	$path_prefix = mysql_escape_string($path_prefix);
	
	$query = "
			SELECT DATE_FORMAT(`when`, '%y') as yr, DATE_FORMAT(`when`, '%b') as mnt,
					`dev`, COUNT(*) as cnt
			FROM `svn_analysis`.`changes`
			WHERE `path` LIKE '$path_prefix%'
				$dev_filter
			GROUP BY YEAR(`when`), MONTH(`when`), `dev`
			ORDER BY YEAR(`when`), MONTH(`when`), `dev` ASC
		";
	
	$ret = select($query);
	dbclose();
	return $ret;
	
}

function getCommonEdits($dev, $path_prefix) {

	dbopen();
	$dev_filter = "";
	if($dev != "all") {
		$dev_filter = " AND t.devs LIKE '%$dev%'";
	}
	$path_prefix = mysql_escape_string($path_prefix);

	$query = "
			SELECT t.devs, COUNT( * ) as cnt
			FROM (
				SELECT GROUP_CONCAT( 
					DISTINCT dev
					ORDER BY dev ASC 
					SEPARATOR ',' ) AS devs
				FROM `svn_analysis`.`changes` 
				WHERE `path` LIKE '$path_prefix%'
				GROUP BY `path` 
				HAVING COUNT( DISTINCT dev ) > 1
			) t
			WHERE 1 = 1
				$dev_filter
			GROUP BY t.devs
		";

	$ret = select($query);
	dbclose();
	return $ret;

}

?>