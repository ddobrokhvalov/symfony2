<?php
namespace Armd\ProjectBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;


class ImportRptCommand extends ContainerAwareCommand
{
    protected $emails = array(
        'ddobrokhvalov@gmail.com',
        //'vdrogalin@armd.ru',
        //'mrymar@armd.ru'
    );

    protected function configure()
    {
        $this
            ->setName('armd:import:rpt')
            ->setDescription('Import users and reports from RPT')
            ->addArgument('from', InputArgument::OPTIONAL, 'Import reports from date (YYYY-MM-DD)')
            ->addOption('reports-only', null, InputOption::VALUE_NONE, 'Reports only')
            ->addOption('simulate', null, InputOption::VALUE_NONE, 'Only simulate import (no DB changes)')
        ;
        
        
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->date = time();
        function convert($size)
        {
            $unit=array('b','kb','mb','gb','tb','pb');
            return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
        }
        define('EOL', "\n");
        $this->simulate = $input->getOption('simulate');
        $this->reports_only = (boolean)$input->getOption('reports-only');
        $this->from = $input->getArgument('from');
        
        if ($this->from === null) {
            $this->from = date('Y-m-d');
        }

        $this->output = $output;
        
        $this->fh = fopen(dirname(__FILE__).'/../../../../app/logs/import.log', "a+");
        
        $ini = parse_ini_file(dirname(__FILE__).'/../../../../app/config/parameters.ini');
        
        if ($this->simulate) {
            $this->debug('<comment>Import simulation.</comment>'.EOL);
        }
        
        if ($this->reports_only == true) {
            $this->debug('<info>Reports only.</info>'.EOL);    
        }

        if ($this->from !== null) {
            $this->debug('<info>From '.$this->from.'.</info>'.EOL);    
        }
        
        
        
        $this->debug('<info>Parsed db config.</info>'.EOL);
        
        $this->dbh = new PDO( 
            'mysql:host='.$ini['database_host'].';dbname='.$ini['database_name'], 
            $ini['database_user'], 
            $ini['database_password'], 
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") 
        );

        $this->dbh_rpt = new PDO( 
            'mysql:host=ring.armd.ru;dbname=rpt', 
            'rpt_ext', 
            'm4jt75uQEydyo', 
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") 
        );
        
        $this->exec('SET FOREIGN_KEY_CHECKS = 0');
        

        ////////////////////////////////////////////////////
		
  /////////////////////////////////////////////////////////////////


        ////////////////////////////////////////////////////////////////////////////////
        if (!$this->reports_only) {
            $this->exec('DELETE FROM Task');
            $task_types = $this->query('SELECT * from TASK_TYPE', $this->dbh_rpt);
    
            foreach($task_types as $task_type) {
                $this->exec('INSERT INTO Task (`id`, `title`) VALUES ("'.$task_type['TASK_TYPE_ID'].'", "'.$task_type['NAME'].'")');
            }
            $this->debug('<info>Imported '.count($task_types).' task types</info>'.EOL);
        }
    
        ////////////////////////////////////////////////////////////////////////////////
        if (!$this->reports_only) {
            $this->exec('DELETE FROM ProjectType');
            $project_types = $this->query('SELECT * from PROJECT_TYPE', $this->dbh_rpt);
            
            foreach($project_types as $project_type) {
                $this->exec('INSERT INTO ProjectType (`id`, `title`) VALUES ("'.$project_type['PROJECT_TYPE_ID'].'", "'.$project_type['NAME'].'")');
            }
            $this->debug('<info>Imported '.count($project_types).' project types</info>'.EOL);
        }
        ////////////////////////////////////////////////////////////////////////////////
        
        if (!$this->reports_only) {
            $this->exec('DELETE FROM User');
            $employees = $this->query('SELECT * from EMPLOYEE', $this->dbh_rpt);
            
            $this->debug('<info>Found '.count($employees).' employees.</info>'.EOL);
            
            $employees_assign = $this->query('SELECT id, email FROM Employee');
            
            foreach($employees as $e) {
                foreach($employees_assign as $ea) {
                    if ($e['EMAIL'] == $ea['email']) {
                        $empl[$e['EMPLOYEE_ID']] = $ea['id'];
                    }
                }
            }
            
            
            
            $imported_employees = 0;
            foreach($employees as $key=>$employee) {
                $employee['NAME'] = trim($employee['NAME']);
                $split = explode(' ', $employee['NAME']);
                
                if (count($split) >= 2) {    
                    $db_employee = $this->query('SELECT * FROM Employee
                                   WHERE
                                   (
                                   name = "'.$split[1].'"
                                   and
                                   surname = "'.$split[0].'"
                                   )'
                                   .($employee['EMAIL']?' or email = "'.$employee['EMAIL'].'"':'')
                                    
                                   );
                    if (count($db_employee) > 0) {
                        if (!isset($employee['EMAIL']) || !$employee['EMAIL']) {
                            $employee['EMAIL'] = $employee['LOGIN'].'@noemail.armd.ru';
                        }
                                if (!$this->simulate) {
                                    exec('/usr/bin/php '.dirname(__FILE__).'/../../../../app/console fos:user:create '.$employee['LOGIN'].' '.$employee['EMAIL'].' '.$employee['PASSWORD']);
                                }
                             
                                $this->exec('UPDATE Employee SET
                                        `user_id` = (SELECT u.id FROM User u WHERE u.email = "'.$employee['EMAIL'].'")
                                        WHERE
                                        email = "'.$employee['EMAIL'].'"
                                        '
                                        );
                                
                                $this->exec('UPDATE User SET
                                        `employee_id` = (SELECT e.id FROM Employee e WHERE e.email = "'.$employee['EMAIL'].'")
                                        WHERE
                                        email = "'.$employee['EMAIL'].'"
                                        '
                                        );
                                $imported_employees++;
                         
                    } else {
                       // echo ('SELECT * FROM Employee WHERE ( name = "'.$split[1].'" and surname = "'.$split[0].'" )' .($employee['EMAIL']?' or email = "'.$employee['EMAIL'].'"':'') ).EOL;
                        $this->debug( '<error>User '.$employee['EMPLOYEE_ID'].' ('.$employee['NAME'].') has no suitable employee. Ignored.</error>'.EOL);
                    }
                } else {
                    $db_employee = array();
                    $this->debug( '<error>User '.$employee['EMPLOYEE_ID'].' ('.$employee['NAME'].') has corrupted name. Ignored.</error>'.EOL);
                }
            }
            $this->debug('<info>Imported '.$imported_employees.', ignored '.(count($employees)-$imported_employees).'.</info>'.EOL);
        }/*else{
			//-------------------------------------------------------------------------------------------------------------------------------
			$employees = $this->query('SELECT * from EMPLOYEE', $this->dbh_rpt);
			var_dump('Found '.count($employees).' employees.');
			$employees_assign = $this->query('SELECT * FROM Employee');
			var_dump('Found '.count($employees_assign).' employees_assign.');
			$users = $this->query('SELECT * FROM User');
			var_dump('Found '.count($users).' users.');
			
			var_dump("***************************************************************************");
			foreach($employees as $e) {
				var_dump($e['EMPLOYEE_ID'].' ('.$e['NAME'].')');
                foreach($employees_assign as $ea) {
                    if ($e['EMAIL'] == $ea['email']) {
                        $empl[$e['EMPLOYEE_ID']] = $ea['id'];
                    }
                }
            }
			var_dump("***************************************************************************");
			foreach($employees_assign as $ea) {
				var_dump($ea['id']."(".$ea['surname']." ".$ea['name']." ".$ea['patronymic'].")");
			}
			var_dump("***************************************************************************");
			foreach($users as $u) {
				var_dump($u['id']."-".$u['employee_id']."(".$u['username']." ".$u['email']." ".$u['username_canonical'].")");
			}
			
			
			exit;
			//-------------------------------------------------------------------------------------------------------------------------------
		}*/
        ////////////////////////////////////////////////////////////////////////////////
        if (!$this->reports_only) {
            $this->exec('DELETE FROM Project');
            $projects = $this->query('SELECT * from PROJECT', $this->dbh_rpt);
    
            foreach($projects as $project) {
                $this->exec('INSERT INTO Project
                           (
                            `id`,
                            `title`,
                            `project_type_id`,
                            `begin`,
                            `end`,
                            `manager_id`,
                            '.($project['SALES_ID']?'`sales_manager_id`,':'').'
                            `open`,
                            `ratio_subcontract`,
                            `ratio_inside`,
                            `ratio_bonus`,
                            `ratio_outsourcing`
            
                           )
                           VALUES (
                           "'.$project['PROJECT_ID'].'",
                           "'.$project['NAME'].'",
                           "'.$project['PROJECT_TYPE_ID'].'",
                           "'.$project['START_DATE'].'",
                           "'.$project['END_DATE'].'",
                           (select id from Employee where id = '.(isset($empl[$project['MANAGER_ID']])?$empl[$project['MANAGER_ID']]:'0').'),
                           '.($project['SALES_ID']?'(select id from Employee where id = '.(isset($empl[$project['SALES_ID']])?$empl[$project['SALES_ID']]:'0').'),':'').
                           '"'.(int)(!$project['IS_FINISHED']).'",
                           "1.8",
                           "3.6",
                           "0.15",
                           "0.01"
                           )');
            }
                    $this->debug('<info>Imported '.count($projects).' projects</info>'.EOL);
        }else{
			//Добавление новых проектов из rpt к уже существующим в НУС

			$employees = $this->query('SELECT * from EMPLOYEE', $this->dbh_rpt);
            
            $employees_assign = $this->query('SELECT id, email FROM Employee');
            
            foreach($employees as $e) {
                foreach($employees_assign as $ea) {
                    if ($e['EMAIL'] == $ea['email']) {
                        $empl[$e['EMPLOYEE_ID']] = $ea['id'];
                    }
                }
            }

			$projects = $this->query('SELECT * from PROJECT', $this->dbh_rpt);
			$exists = true;
			$new_proj_count = 0;
			foreach ($projects as $project){
				$exists_project = $this->query('SELECT * from Project where id = '.$project['PROJECT_ID']);
				if (count($exists_project)>0) {
					$exists = true; 
					//$this->debug('<info>Exists '.$project['PROJECT_ID'].' ('.count($exists_project).')</info>'.EOL);
				}else{
					//$this->debug('<info>Not exists '.$project['PROJECT_ID'].' ('.count($exists_project).')</info>'.EOL);
					$exists = false; 
				}
				
				
				if (!$exists){
					$test = $this->exec('INSERT INTO Project
                           (
                            `id`,
                            `title`,
                            `project_type_id`,
                            `begin`,
                            `end`,
                            `manager_id`,
                            '.($project['SALES_ID']?'`sales_manager_id`,':'').'
                            `open`,
                            `ratio_subcontract`,
                            `ratio_inside`,
                            `ratio_bonus`,
                            `ratio_outsourcing`
            
                           )
                           VALUES (
                           "'.$project['PROJECT_ID'].'",
                           "'.str_replace('"', "'",$project['NAME']).'",
                           "'.$project['PROJECT_TYPE_ID'].'",
                           "'.$project['START_DATE'].'",
                           "'.$project['END_DATE'].'",
                           (select id from Employee where id = '.(isset($empl[$project['MANAGER_ID']])?$empl[$project['MANAGER_ID']]:'0').'),
                           '.($project['SALES_ID']?'(select id from Employee where id = '.(isset($empl[$project['SALES_ID']])?$empl[$project['SALES_ID']]:'0').'),':'').
                           '
						   "'.(int)(!$project['IS_FINISHED']).'",
                           "1.8",
                           "3.6",
                           "0.15",
                           "0.01"
                           )');
						if ($test){
							$new_proj_count++;
						}else{							
							$this->debug('<info>Skipped project ['.$project['PROJECT_ID'].']'.$project['NAME'].'</info>'.EOL);
						}
				}
			}
			$this->debug('<info>Imported '.$new_proj_count.' projects</info>'.EOL);
		}
        ////////////////////////////////////////////////////////////////////////////////
        
        if ($this->reports_only) {
            $employees = $this->query('SELECT * from EMPLOYEE', $this->dbh_rpt);
            
            $this->debug('<info>Found '.count($employees).' employees.</info>'.EOL);
            
            $employees_assign = $this->query('SELECT id, email FROM Employee');
            
            foreach($employees as $e) {
                foreach($employees_assign as $ea) {
                    if ($e['EMAIL'] == $ea['email']) {
                        $empl[$e['EMPLOYEE_ID']] = $ea['id'];
                    }
                }
            }
        }
        
        if ($this->from !== null) {
            $clause_delete = " WHERE day >= '".$this->from."'";
            //$clause_select = " WHERE FILL_TIME >= '".$this->from."'";
            $clause_select = " WHERE REPORT_DATE != 'error' AND REPORT_DATE >= '".str_replace('-','',$this->from)."000000'";
        } else {
            $clause_delete = '';
            $clause_select = '';
        }
        
        $this->exec('DELETE FROM Report'.$clause_delete);

        $reports = $this->query('SELECT * from REPORT'.$clause_select, $this->dbh_rpt);
        $this->debug('<info>Found '.count($reports).' reports. (memory: '.convert(memory_get_usage(true)).')</info>'.EOL);
        
        $imported_reports = 0;
        $skipped_reports = 0;
        
        $failed_users = array();
        
        foreach($reports as $key=>$report) {
            if ($key % 5000 == 0) $this->debug( $key.' parsed'.EOL);
            if (isset($empl[$report['EMPLOYEE_ID']])) {
            
                $this->prepare('INSERT INTO Report
                           (
                            `task_id`,
                            `project_id`,
                            `employee_id`,
                            `day`,
                            `minutes`,
                            `description`
            
                           )
                           VALUES (
                           "'.$report['TASK_TYPE_ID'].'",
                           "'.$report['PROJECT_ID'].'",
                           "'.$empl[$report['EMPLOYEE_ID']].'",
                           "'.$report['REPORT_DATE'].'",
                           "'.$report['TASK_TIME'].'",
                           ?
                           
                           
                           )', $report['BODY']);
                
                $imported_reports++;
            } else {
                $skipped_reports++;
                var_dump($report);
                if (!in_array($report['EMPLOYEE_ID'], $failed_users)) {
                    $failed_users[] = $report['EMPLOYEE_ID'];
                }
            }
        }

        $this->debug( '<info>Imported '.$imported_reports.' reports.</info>'.EOL);
        $this->debug( '<info>Skipped '.$skipped_reports.' reports.</info>'.EOL);
        $this->debug( '<info>Bad users:</info>'.EOL);
        
        foreach($failed_users as $key=>$user) {
            $this->debug($user);
            if ($key != (count($failed_users)-1)) {
                $this->debug(', ');
            } else {
                $this->debug(EOL);
            }
        }
        
        ////////////////////////////////////////////////////////////////////////////////
        
        foreach($this->emails as $email) {
            $message = \Swift_Message::newInstance()
                ->setTo($email)
                ->setSubject('Импорт из rpt.armd.ru с '.$this->from)
                ->setFrom('rpt@armd.ru')
                ->setBody('
                    Импорт из rpt.armd.ru с '.$this->from."<br>\n<br>\n
                    Всего: ".count($reports)."<br>\n<br>\n
                    Импортировано: ".$imported_reports."<br>\n<br>\n
                    Не импортировано: ".$skipped_reports."<br>\n<br>\n
                ", 'text/html')
            ;
            $this->getContainer()->get('mailer')->send($message);    
        }
        
    }

    public function debug($str, $append_time = true)
    {
        $this->output->write($str);
        fwrite($this->fh, ($append_time?'['.date('d.m.Y H:i:s').'] ':'').strip_tags($str));
    }
    
    public function query($sql, $dbh = false)
    {
        if (!$dbh) {
            $dbh = $this->dbh;
        }

        $result = array();
        try {
            foreach($dbh->query($sql) as $row) {
                $result[] = $row;    
            }
        } catch (Exception $e) {
            
        }
        return $result;
    }
    
    public function exec($sql, $dbh = false)
    {
        if (!$dbh) {
            $dbh = $this->dbh;
        }

        if (!$this->simulate) {
            return $dbh->exec($sql);
        } else {
            return 1;
        }
    }
    
    public function prepare($sql, $param, $dbh = false)
    {
        if (!$dbh) {
            $dbh = $this->dbh;
        }

        if (!$this->simulate) {
            return $dbh->prepare($sql)->execute(array($param));
        } else {
            return 1;
        }
    }

}
