<?php
	$host="10.32.17.6";
	//$host="localhost";
	$user="employe";
	$pwd="Oongee6m";
	$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');
	mysql_select_db("employe",$db);
	
	//$fh = fopen(dirname(__FILE__).'/logs/export.log', "a+");
	//var_dump(dirname(__FILE__).'/logs/export.log');
	//$result0 = mysql_query ("delete from employees");
	//$result00 = mysql_query ("delete from employees_history");

	$result = mysql_select("select date, category, discharged, outside from employees_history where employee_id = 8782");
	var_dump($result);
	mysql_close($db);


	//$result = mysql_select("select * from employees_history where employee_id = 8905");
	//var_dump($result);

	function mysql_select($query)
{
	$res = array();
	$result = mysql_query($query);
	
	while ($row = mysql_fetch_assoc($result)) {
		$res[] = $row;
	}
	return $res;
}