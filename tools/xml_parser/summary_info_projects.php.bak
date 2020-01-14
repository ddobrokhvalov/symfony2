<?php
$ini = parse_ini_file(dirname(__FILE__).'/../../app/config/parameters.ini');

$host="localhost";
$user=$ini['database_user'];
$pwd=$ini['database_password'];
$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');		
mysql_select_db($ini['database_name'],$db);

$time_start = getmicrotime();

$projects = mysql_select("select * from Project");

mysql_close($db);
$x=0;
$y=0;
foreach ($projects as $key=> &$project){
	$host="localhost";
	$user=$ini['database_user'];
	$pwd=$ini['database_password'];
	$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');		
	mysql_select_db($ini['database_name'],$db);
	$reports = mysql_select("select id, employee_id as eid, day, minutes from Report where project_id = ".$project['id']);
		
	$sum_for_project = 0;
	
	$max_report_date = mysql_select("select max(day) as maxdate from Report where project_id = ".$project['id']);
	$min_report_date = mysql_select("select min(day) as mindate from Report where project_id = ".$project['id']);
	mysql_close($db);

	if (count($max_report_date) > 0 && $max_report_date[0]['maxdate'] != null){
		$max_report_date = Date("Ymd",strtotime($max_report_date[0]['maxdate']))."235959";
	}else{
		
		$max_report_date = false;
	}
	if (count($max_report_date) > 0 && $min_report_date[0]['mindate'] != null){
		$min_report_date = Date("Ymd",strtotime($min_report_date[0]['mindate']))."235959";
	}else{
		
		$min_report_date = false;
	}
	
	if ($max_report_date && $min_report_date){
		$x++;
		var_dump($project['id']);
		var_dump($max_report_date);var_dump($min_report_date);
	}else{
		$y++;
		//unset($projects[$key]);
	}

	$host="10.32.17.6";
			$user="employe";
			$pwd="Oongee6m";
			$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');
			mysql_select_db("employe",$db);

	/*if ($project['id'] == 1046){
	foreach ($reports as $report){
		$host="10.32.17.6";
			$user="employe";
			$pwd="Oongee6m";
			$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');
			mysql_select_db("employe",$db);
			$rate = mysql_select("select category, discharged, outside  from employees_history where employee_id = ".$report['eid']." and date like '".date("Ymd",strtotime($report['day']))."%'");
			var_dump($rate);
			mysql_close($db);
	}
	}*/
	
	
}
$time_end = getmicrotime();
var_dump($x);var_dump($y);
var_dump($time_end - $time_start);
var_dump(count($projects));


function mysql_select($query)
{
	$res = array();
	$result = mysql_query($query);
	
	while ($row = mysql_fetch_assoc($result)) {
		$res[] = $row;
	}
	return $res;
}

function getmicrotime() 
{ 
    list($usec, $sec) = explode(" ", microtime()); 
    return ((float)$usec + (float)$sec); 
} 