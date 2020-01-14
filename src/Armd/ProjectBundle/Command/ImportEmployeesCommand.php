<?php
namespace Armd\ProjectBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;

class ImportEmployeesCommand extends ContainerAwareCommand
{
    
    protected $rates;


    protected function configure()
    {
        $this
            ->setName('armd:import:employees')
            ->setDescription('Import employees')
        ;
        
        
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        function translit($str) 
        {
            $tr = array(
                "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
                "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
                "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
                "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
                "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
                "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
                "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
                "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
                "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
                "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
                "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
                "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
                "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
            );
            return strtr($str,$tr);
        }
        
        function mb_ucasefirst($string, $e ='utf-8') { 
            if (function_exists('mb_strtoupper') && function_exists('mb_substr') && !empty($string)) { 
                $string = mb_strtolower($string, $e); 
                $upper = mb_strtoupper($string, $e); 
                    preg_match('#(.)#us', $upper, $matches); 
                    $string = $matches[1] . mb_substr($string, 1, mb_strlen($string, $e), $e); 
            } 
            else { 
                $string = ucfirst($string); 
            } 
            return $string; 
        }
        
        $this->output = $output;
        
        define('EOL', "\n");
        
        
        $this->fh = fopen(dirname(__FILE__).'/../../../../app/logs/import.log', "a+");
        $this->debug('<info>Employees import started.</info>'.EOL);
        
        
        $ini = parse_ini_file(dirname(__FILE__).'/../../../../app/config/parameters.ini');
        $this->debug('<info>Parsed db config.</info>'.EOL);
        
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $this->getContainer()->getParameter('rates.url')); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($ch, CURLOPT_USERPWD, $this->getContainer()->getParameter('rates.login').':'.$this->getContainer()->getParameter('rates.password'));
        $body = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch); 
        $this->debug('<info>Recieved '.$info['size_download'].' bytes from '.$this->getContainer()->getParameter('rates.url').'.</info>'.EOL);
        
        $xml = simplexml_load_string($body);
        $this->debug('<info>Found '.count($xml).' employees.</info>'.EOL);
        
        $this->buildRates($xml);
		
        $dbh = new PDO( 
            'mysql:host='.$ini['database_host'].';dbname='.$ini['database_name'], 
            $ini['database_user'], 
            $ini['database_password'], 
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") 
        );
        
        $dbh->exec('SET FOREIGN_KEY_CHECKS = 0');
        
        $departments = array();
        
        foreach($xml as $employee) {
            
            $update = false;
        
            $this->debug($employee->Department->__toString(). ' ');
        
            foreach($dbh->query('SELECT * from Department WHERE ext_id = '.$employee->IDDepartment->__toString()) as $row) {
                $update = true;
            }
            
            if ($update) {
                $dbh->exec("UPDATE Department SET
                           title = '".$employee->Department->__toString()."',
                           employee_id = '".$employee->IDManager->__toString()."'
                           WHERE
                           ext_id = ".$employee->IDDepartment->__toString());
                $this->debug('updated'.EOL, false);
            } else {
                $dbh->exec("INSERT INTO Department
                           (`id`, `title`, `employee_id`, `ext_id`)
                           VALUES
                           ('".$employee->IDDepartment->__toString()."','".$employee->Department->__toString()."', '".$employee->IDManager->__toString()."', '".$employee->IDDepartment->__toString()."')
                           ");
				$this->debug('inserted'.EOL, false);
                //$this->debug('skipped'.EOL, false);
            }
            
        }
        
        $dbh->exec("UPDATE Department SET employee_id = null WHERE employee_id = 0");
        
		$posts_count = 0;
		foreach($xml as $employee) {
			if (!($employee->IDPost->__toString()) || !($employee->Post->__toString())) {
				continue;
			}
			
			$update = false;
            
			foreach($dbh->query('SELECT * from Post WHERE id = '.$employee->IDPost->__toString()) as $row) {
                $update = true;
            }

			if ($update) {
                $dbh->exec("UPDATE Post SET
                           title = '".$employee->Post->__toString()."'
                           WHERE
                           id = ".$employee->IDPost->__toString());
            } else {
                $dbh->exec("INSERT INTO Post
                           (`id`, `title`)
                           VALUES
                           (
						   '".$employee->IDPost->__toString()."',
                           '".$employee->Post->__toString()."'
                           )
                           ");
            }
			$posts_count++;
        }
		$this->debug('<info>Parsed '.$posts_count.' posts.</info>'.EOL);
		
        foreach($xml as $employee) {
            
            $attr = $employee->attributes();
            
            $update = false;
            $this->debug($employee->SirName.' '. $employee->FirstName . ' ' . $employee->PatronymicName. ' ');
            foreach($dbh->query('SELECT * from Employee WHERE id = '.$attr['ID']->__toString()) as $row) {
                $update = true;
            }
			
			$department_exists = false;
			foreach($dbh->query('SELECT * from Department WHERE ext_id = '.$employee->IDDepartment) as $row) {
                $department_exists = true;
            }
           
			if (in_array($attr['ID']->__toString(),	array('00979','01103','08884'))) {
				$department_exists = true;
			}
			
			if ($department_exists) {
				if ($update) {
					$dbh->exec("UPDATE Employee SET
							   surname = '".mb_ucasefirst($employee->SirName)."',
							   name = '".mb_ucasefirst($employee->FirstName)."',
							   patronymic = '".mb_ucasefirst($employee->PatronymicName)."',
							   department_id = (select id from Department where ext_id = '".$employee->IDDepartment."'),
							   "./*email = '".($employee->Email)."',*/"
							   discharged = '".($employee->Discharged)."',
							   post_id = '".($employee->IDPost->__toString()?:'null')."'

							   WHERE
							   id = ".$attr['ID']->__toString());
					$this->debug('updated'.EOL, false);
				} else {

					$dbh->exec("INSERT INTO Employee
							   (`id`, `surname`, `name`, `patronymic`,  `department_id`, `email`, `discharged`, `post_id`)
							   VALUES
							   ('".$attr['ID']->__toString()."',
							   '".mb_ucasefirst($employee->SirName)."',
							   '".mb_ucasefirst($employee->FirstName)."',
							   '".mb_ucasefirst($employee->PatronymicName)."',

							   (select id from Department where ext_id = '".$employee->IDDepartment."'),
							   '".$employee->Email."',
							   '".$employee->Discharged."',
							   '".($employee->IDPost->__toString()?:'null')."'
							   )
							   ");

					$this->debug('inserted'.EOL, false);
				}
			} else {
				$this->debug('skiped'.EOL, false);
			}
			 
			 
        }
        //$dbh->exec('DELETE FROM Employee WHERE id not in (979, 1103, 8884) AND department_id is null');
		
		$dbh->exec('drop table IF EXISTS `temptable`');
		$dbh->exec('CREATE TABLE IF NOT EXISTS `temptable` ( `id` int NULL )');
		$dbh->exec('insert into `temptable` select d.id from Department d left join Employee e  on d.employee_id = e.id where e.id is null');
		$dbh->exec('update Department set employee_id = null where id in (	select distinct `id` from `temptable` )');
		$dbh->exec(' drop table IF EXISTS `temptable`');
		
        $dbh->exec('SET FOREIGN_KEY_CHECKS = 1');
        
        $this->debug('<info>Employees import finished.</info>'.EOL);
        $this->debug('', false);
        
        
    }
    
    public function debug($str, $append_time = true)
    {
        $this->output->write($str);
        fwrite($this->fh, ($append_time?'['.date('d.m.Y H:i:s').'] ':'').strip_tags($str));
    }

    /**
     * Сборка массивов ставок
     */
    protected function buildRates($xml)
    {
        if (!isset($this->rates)) {
            foreach($xml as $employee) {
                $e_attr = $employee->attributes();
                foreach ($employee->Category as $category) {
                    $attr = $category->attributes();
                    $this->rates[(integer)$e_attr['ID']->__toString()][] = array(
                        'begin' => ($attr['begin']?strtotime($attr['begin']->__toString()):0),
                        'end' => ($attr['end']?strtotime($attr['end']->__toString()):30000000000),
                        'category'=>$category->__toString());
                }
            }
        }
    }
	
	
    
    /**
     * Получение ставки сотрудника
     * @param integer $employee_id
     * @return integer
     */
    protected function getRate($employee_id)
    {
        $date = time();
        if (isset($this->rates[$employee_id])) {
            foreach($this->rates[$employee_id] as $rate) {
                if (($date >= $rate['begin']) && ($date <= $rate['end'])) {
                    return $rate['category'];
                }
            }
        }
        
        return 1;
    }
}
