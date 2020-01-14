<?php
/**
 * Парсер xml
 * @link http://br.armd.ru/issues/41109
 * @author Dmitry Dobrokhvalov <ddobrokhvalov@gmail.com>
 */

 if (!isset($argv[1]) || !isset($argv[2])) {
	echo "Usage: php -c /etc/php5/cli/php.ini new_xml_parser.php <XML files storage> <Start date>\n\n";
	exit;
}

//$time_start = getmicrotime();

$files = array();
foreach (glob("$argv[1]/employers_*.xml") as $filename) {
	if ($filename >= "$argv[1]/employers_$argv[2]_0_0.xml"){	
		$files[] = $filename;
	}
	
}

//$time_end = getmicrotime();
//$time = $time_end - $time_start;
//print_r("Read file names ".$time." seconds.\n");





if (isset($files) && sizeof($files)>0) {

	
	//$host="10.32.17.6";
	$host="localhost";
	$user="employe";
	$pwd="Oongee6m";
	$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');
	mysql_select_db("employe",$db);
	
	$max_date_hist = mysql_select("select max(date) as max_date from employees_history");
	$max_date_hist = ($max_date_hist[0]['max_date']?$max_date_hist[0]['max_date']:"19900101000000");	
	
	//$time_start = getmicrotime();

	foreach ($files as $file){
		
		$date = explode('_', $file);
		$date = "20".$date[1].str_pad($date[2], 2, "0", STR_PAD_LEFT).str_pad(str_replace(".xml", "", $date[3]), 2, "0", STR_PAD_LEFT)."00";
		
		if ($date > $max_date_hist){

			$db_empl = mysql_select("select * from employees");
			$current_date_empl = mysql_select("select max(date) as max_date from employees_history");
			$current_date_empl = ($current_date_empl[0]['max_date']?$current_date_empl[0]['max_date']:"19900101000000");
			
			
			if (count($db_empl) > 0){
				foreach ($db_empl as $row){
					$empl_array[$row['employee_id']] = $row;
					$empl_array[$row['employee_id']]['new'] = false;
				}
			}else{
				$empl_array = array();
			}
		
			$employees = simplexml_load_file($file);
			$fh = fopen(dirname(__FILE__).'/logs/export_'.$date.'.log', "a+");
			
			write_in_log($fh, "Found ".count($employees)." employees in file ".$file."\n");
			print_r("Found ".count($employees)." employees in file ".$file."\n");
			
			foreach($employees as $employee) {
				$e_attr = $employee->attributes();
				
				$outside = (integer)$employee->Outside->__toString();
				
				if ($outside == 1 && isset($empl_array[(integer)$e_attr['ID']->__toString()]) ){
					$category = $empl_array[(integer)$e_attr['ID']->__toString()]['category'];
				}else{
					$category = (integer)$employee->Category->__toString();
				}

				$empl_array[(integer)$e_attr['ID']->__toString()] = array(
					"employee_id"		=> (integer)$e_attr['ID']->__toString(),
					"surname"			=> $employee->SirName->__toString(),
					"firstname"			=> $employee->FirstName->__toString(),
					"patronymic"		=> $employee->PatronymicName->__toString(),
					"manager_id"		=> (integer)$employee->IDManager->__toString(),
					"department"		=> $employee->Department->__toString(),
					"department_id"		=> (integer)$employee->IDDepartment->__toString(),
					"post"				=> $employee->Post->__toString(),
					"post_id"			=> (integer)$employee->IDPost->__toString(),
					"email"				=> $employee->Email->__toString(),
					"discharged"		=> (integer)$employee->Discharged->__toString(),
					"category"			=> $category,
					"outside"			=> (integer)$employee->Outside->__toString(),
					"date"				=> $date,					
					"new"				=> true
					);
			}
			
			//$time_1 = getmicrotime();

			foreach ($empl_array as $id=>$empl){
				$empl_array[$id]["date"] = $date;				
			}		
			

			$ins_empl = 0; $ins_hist = 0; $update_empl = 0; $copied_hist = 0; $old_empl = 0;			

			foreach ($empl_array as $empl){
				$exists = mysql_select("select employee_id from employees where employee_id = ".$empl['employee_id']);
				if (count($exists) == 0){
					
					$res = mysql_query ("insert 
							into employees
								(employee_id, surname, firstname, patronymic, manager_id , department , department_id , post , post_id , email , discharged , category , 
								outside , date , time_stamp )
							values
								(".$empl['employee_id'].", '".$empl['surname']."', '".$empl['firstname']."',	'".$empl['patronymic']."', ".$empl['manager_id'].", '".$empl['department']."', ".$empl['department_id'].", '".$empl['post']."',	".$empl['post_id'].", '".$empl['email']."', ".$empl['discharged'].", ".$empl['category'].", ".$empl['outside'].", '".$empl['date']."', now() )"
					);
					
					if ($empl['new']){
						write_in_log($fh, "Employee ".$empl['employee_id']." ".$empl['surname']." ".$empl['firstname']." ".$empl['patronymic']." inserted in table employees\n");
						$ins_empl++;
					}else{
						$old_empl++;
					}
				}else{
					$res = mysql_query ("update 
							employees
							set							 
								surname = '".$empl['surname']."', 
								firstname = '".$empl['firstname']."', 
								patronymic = '".$empl['patronymic']."', 
								manager_id = ".$empl['manager_id'].", 
								department = '".$empl['department']."', 
								department_id = ".$empl['department_id'].", 
								post = '".$empl['post']."', 
								post_id = ".$empl['post_id'].", 
								email = '".$empl['email']."', 
								discharged = ".$empl['discharged'].", 
								category = ".$empl['category'].", 
								outside = ".$empl['outside'].", 
								date = '".$empl['date']."', 
								time_stamp =  now()
							where
								employee_id = ".$empl['employee_id']
					);
					
					if ($empl['new']){
						write_in_log($fh, "Employee ".$empl['employee_id']." ".$empl['surname']." ".$empl['firstname']." ".$empl['patronymic']." updated in table employees\n");
						$update_empl++;
					}else{
						$old_empl++;
					}				
				}

				$res = mysql_query ("insert 
						into employees_history
							(employee_id, surname, firstname, patronymic, manager_id , department , department_id , post , post_id , email , discharged , category , 
							outside , date , time_stamp )
						values
							(".$empl['employee_id'].", '".$empl['surname']."', '".$empl['firstname']."',	'".$empl['patronymic']."', ".$empl['manager_id'].", '".$empl['department']."', ".$empl['department_id'].", '".$empl['post']."',	".$empl['post_id'].", '".$empl['email']."', ".$empl['discharged'].", ".$empl['category'].", ".$empl['outside'].", '".$empl['date']."', now() )"
					);

					
					
				if ($empl['new']){
					write_in_log($fh, "Employee ".$empl['employee_id']." ".$empl['surname']." ".$empl['firstname']." ".$empl['patronymic']." inserted in table employees_history\n");
					$ins_hist++;
				}else{
					
					$copied_hist++;
				}
				
			}
			$total_records = $old_empl+$ins_empl+$update_empl;
			write_in_log($fh,"In table employees inserted: ".$ins_empl." records, updated: ".$update_empl." records.\n");
			write_in_log($fh,"Old records in table employees: ".$old_empl.". Total: ".$total_records." records.\n");
			print_r("In table employees inserted: ".$ins_empl." records, updated: ".$update_empl." records.\n");
			print_r("Old records in table employees: ".$old_empl.". Total: ".$total_records." records.\n");
			
			write_in_log($fh,"In table employees_history inserted: ".$ins_hist." records, copied from table employees: ".$copied_hist." records.\n");
			print_r("In table employees_history inserted: ".$ins_hist." records, copied from table employees: ".$copied_hist." records.\n");

			//$time_2 = getmicrotime();
			//$time_on_file = $time_2 - $time_1;
			//$time_summ = $time_2 - $time_start;
			//print_r($time_on_file. " seconds on file. ".$time_summ." seconds summary.\n");

		}
	}
	
} else {
	echo "No XML files found\n\n";
}



function mysql_select($query)
{
	$res = array();
	$result = mysql_query($query);
	
	while ($row = mysql_fetch_assoc($result)) {
		$res[] = $row;
	}
	return $res;
}

function write_in_log($fh, $str, $append_time = true)
{
    //print_r($str);
    fwrite($fh, ($append_time?'['.date('d.m.Y H:i:s').'] ':'').$str);
}

function getmicrotime() 
{ 
    list($usec, $sec) = explode(" ", microtime()); 
    return ((float)$usec + (float)$sec); 
} 