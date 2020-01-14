<?php

/* 
 * Поиск сотрудников с нулевыми ставками
 * @author Konstantin Potemichev <k.potemichev@gmail.com>
 */


$ini = parse_ini_file(dirname(__FILE__).'/../../app/config/parameters.ini');

$dbh = new PDO( 
            'mysql:host='.$ini['database_host'].';dbname='.$ini['database_name'], 
            $ini['database_user'], 
            $ini['database_password'], 
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") 
        );

foreach($dbh->query('SELECT d.ext_id, e.surname, e.name, e.patronymic FROM Department d JOIN Employee e ON d.employee_id = e.id') as $row) {
	$boss[sprintf('%05d',$row['ext_id'])] = $row['surname'].' '.$row['name'].' '.$row['patronymic'];
}


$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, 'http://10.32.17.6:82/employee.xml'); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
curl_setopt($ch, CURLOPT_USERPWD, 'prod:MadPython3471');
$body = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch); 

$xml = simplexml_load_string($body);

foreach ($xml as $node) {
	if (($node->Discharged == 0) && ($node->Category == 0)) {
		
		echo	$node->SirName.' '.
				$node->FirstName.' '.
				$node->PatronymicName.' - '.
				$node->Department.' - '.
				(isset($boss[$node->IDDepartment->__toString()])?$boss[$node->IDDepartment->__toString()]:'руководитель не указан').
				"\n";
				
	}
}