<?php

$dev = $_GET['dev'];
$path = $_GET['path'];

// $path="/";

echo "<html><body>";

//keywords
if(!($dev == "all" && $path== "/"))
	echo "<img alt='keywords' src='helpers/keywords.php?dev=$dev&path=$path'><br><br>";

//punch card
echo "<img alt='punch_card' src='helpers/punch_card.php?dev=$dev&path=$path'><br><br>";

//month wise break up
echo "<img alt='co_authors' src='helpers/co_authors.php?dev=$dev&path=$path'><br>";

//files modified
if($dev == 'all')
	echo "<img alt='files_pie' src='helpers/devs_files_pie.php?dev=$dev&path=$path'><br><br>";

//month wise break up
echo "<img alt='month' src='helpers/month_wise_commits.php?dev=$dev&path=$path'><br>";

//files modified
echo "<img alt='files_time' src='helpers/devs_files_time.php?dev=$dev&path=$path'><br><br>";

echo "</body></html>";
?>