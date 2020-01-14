<?php
/**
 * Парсер xml
 * @link http://br.armd.ru/issues/40734
 * @author Konstantin Potemichev <k.potemichev@gmail.com>
 */

if (!isset($argv[1]) || !isset($argv[2])) {
	echo "Usage: php -c /etc/php5/cli/php.ini xmp_parser.php <XML files storage> <result XML file>\n\n";
	exit;
}

if (isset($argv[3])) {
	include($argv[3]);
}

foreach (glob("$argv[1]/employers_*.xml") as $filename) {
	$files[] = $filename;
}

if (isset($files) && sizeof($files)>0) {
        $xml = simplexml_load_file($files[0]);

	foreach ($files as $file) {
		$date = explode('_', $file);
		$date = $date[1];
		$date = substr($date, 4, 2).'.'.substr($date, 2, 2).'.20'.substr($date, 0, 2);
		
		$employees = simplexml_load_file($file);
	
		foreach($employees as $employee) {
			$e_attr = $employee->attributes();
			if (isset($rates[(integer)$e_attr['ID']->__toString()])) {
				if ($rates[(integer)$e_attr['ID']->__toString()][count($rates[(integer)$e_attr['ID']->__toString()])-1]['rate'] == $employee->Category->__toString()) {
					continue;
				}
			}
			$rates[(integer)$e_attr['ID']->__toString()][] = array( 'rate' => $employee->Category->__toString(), 'date' => $date);
		}
	}
} else {
	echo "No XML files found\n\n";
}

if (isset($rates)) {
	foreach($rates as $k=>$rate) {
		foreach($rate as $key=>$el) {
			if ($key == 0) {
				$rates[$k][$key]['begin'] = '01.01.1970';
				$rates[$k][$key]['end'] = (isset($rate[$key+1])?date('d.m.Y', strtotime($rate[$key+1]['date'])-(24*3600)):'31.12.2050');
			} elseif ($key == (count($rate)-1)) {
				$rates[$k][$key]['end'] = '31.12.2050';
				$rates[$k][$key]['begin'] = $el['date'];
			} else {
				$rates[$k][$key]['end'] = date('d.m.Y', strtotime($rate[$key+1]['date'])-(24*3600));
				$rates[$k][$key]['begin'] = $el['date'];
			}
		}
	}
}

if (isset($xml)) {
	foreach($xml as $employee) {
                $e_attr = $employee->attributes();
		unset($employee->Category);
                if ( isset($ext_employee) && in_array( (integer)$e_attr['ID']->__toString(), $ext_employee) ) {
			$new_cat = $employee->addChild('Category', 3);
                } else {
			$e_attr = $employee->attributes();
			foreach($rates[(integer)$e_attr['ID']->__toString()] as $key=>$rate) {
		
				$new_cat = $employee->addChild('Category', $rate['rate']);
		
				$new_cat->addAttribute('begin', $rate['begin']);
				$new_cat->addAttribute('end', $rate['end']);
		
			}
                }
	}

	file_put_contents("$argv[2]/employee.xml", $xml->asXML());
}
