<?php
namespace Armd\ProjectBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Armd\ReportBundle\Entity\Report;
use DateTime;

class ReportGenerateCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('armd:report:generate')
            ->setDescription('Generate reports if not exists')
			->addArgument('file', InputArgument::OPTIONAL, 'Load employees from file instead of service')
        ;
        
        
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->em = $this->getContainer()->get('doctrine')->getEntityManager();
        define('EOL', "\n");
        
        $this->fh = fopen(dirname(__FILE__).'/../../../../app/logs/report_generate.log', "a+");
	
		$this->task = $this->em->getRepository('ArmdProjectBundle:Task')->findOneByTitle('Свободный ресурс ');

		$file = $input->getArgument('file');
		
		if ($file) {
			$xml = simplexml_load_file($file);
		} else {
			// получаем xml
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
		}
		
        $this->debug('<info>Found '.count($xml).' employees.</info>'.EOL);
        
        // получаем список нерабочих дней
        $holidays_repository = $this
            ->em
            ->getRepository('ArmdProjectBundle:Holiday')
            ->findAll();
        
        $holidays = array();
        foreach($holidays_repository as $holiday) {
            $holidays[] = $holiday->getDay()->format('Y-m-d');
        }
         
        // генерируем список рабочих дней за текущий год до сегодня
        $work_days = array();
        $day = strtotime(date('Y').'-01-01');
        while($day < (time() - 3600*24)) {
            if (!in_array(date('N',$day), array(6,7)) && !in_array(date('Y-m-d', $day), $holidays)) {
                $work_days[] = date('Y-m-d', $day);
            }
            $day = strtotime('+1 day', $day);
        }
        
        // получаем всех работающих сотрудников
        //$employees = $this
        //    ->em
        //    ->getRepository('ArmdProjectBundle:Employee')
        //    ->findByDischarged(0);
        
		foreach($xml as $xml_employee) {
			
			$e_attr = $xml_employee->attributes();
			
			
			$employee = $this
				->em
				->getRepository('ArmdProjectBundle:Employee')
				->findOneById($e_attr['ID']);
				
			if (!is_object($employee))	continue;
			
			// получаем проекты за этот год, в которых участвовал сотрудник
			$projects = $this
				->em
				->getRepository('ArmdProjectBundle:Project')
				->createQueryBuilder('p')
				->innerJoin('p.project_employee', 'pe')
				->where('p.begin <= :begin')
				->setParameter('begin', date('Y-m-d'))
				->andWhere('p.end >= :end')
				->setParameter('end', date('Y').'-01-01')
				->andWhere('pe.employee = :employee')
				->setParameter('employee', $employee)
				->andWhere('p.title != :title')
				->setParameter('title','Административный проект')
				->getQuery()
				->getResult();
				
			$this->debug('Parsing '.$employee->__toString().'. ');
			
			if (count($projects)) {
				$reports = $this
					->em
					->getRepository('ArmdReportBundle:Report')
					->createQueryBuilder('r')
					->select('r.id, r.day, sum(r.minutes) as sm')
					->where('r.day <= :begin')
					->setParameter('begin', date('Y-m-d'))
					->andWhere('r.day >= :end')
					->setParameter('end', date('Y').'-01-01')
					->andWhere('r.employee = :employee')
					->setParameter('employee', $employee)
					->groupBy('r.day')
					->getQuery()
					->getResult();
				
				$bad_reports = 0;
				$fixed_reports = 0;
				foreach($work_days as $work_day) {
					$found_report = false;
					foreach($reports as $key=>$report) {
						if ($report['day'] == $work_day) {
							$found_report = $report;
						}
					}
					
					// отчета за день нет
					if (!$found_report) { 
						$found_report = array(
							'day' => $work_day,
							'sm'  => 0
						);
					}
					
					
					if ($found_report['sm'] < $employee->getTime()) { // меньше восьми часов
						$bad_reports++;
						
						// ищем проекты, которые совпадают по времени с отчетом
						$projects_ids = array();
						foreach($projects as $key=>$project) {
							if (($project->getBegin()->getTimestamp() <= strtotime($found_report['day'])) &&
								($project->getEnd()->getTimestamp() >= strtotime($found_report['day']))) {
								//$this->debug($project->getTitle().' - '.$found_report['day'].' - '. $project->getBegin()->format('d.m.Y').' - '.$project->getEnd()->format('d.m.Y').EOL);
								$projects_ids[] = $key;
							}
						}
						
						// если нашли проекты, то выбираем рандомный =)
						// TODO надо что-то более осмысленное тут сделать
						$key = false;
						if (count($projects_ids)) {
							$key = $projects_ids[rand(0, count($projects_ids)-1)];
						}
						
						// собрали все данные, создаем отчет
						if ($key !== false) {
							$fixed_reports++;
							$this->createReport($employee, $projects[$key], $found_report['day'], $employee->getTime()-$found_report['sm']);
						}
					}
					
				}
				
				$this->debug('Bad reports: '.$bad_reports.'. ');
				$this->debug('Fixed reports: '.$fixed_reports.'.'.EOL);
			} else {
				$this->debug('No projects, skip.'.EOL);
			}
			
		}
        
    }
    
	public function createReport($employee, $project, $day, $time)
	{
		$report = new Report;
		$report->setDay(new DateTime(($day)));
		$report->setMinutes($time);
		$report->setEmployee($employee);
		$report->setProject($project);
		$report->setTask($this->task);
		$report->setGenerated(1);
		$report->setDescription("Пожалуйста, напишите отчет.");
		$this->em->persist($report);
		$this->em->flush();
	}
    
    
    
    
    public function debug($str, $append_time = true)
    {
        $this->output->write($str);
        fwrite($this->fh, ($append_time?'['.date('d.m.Y H:i:s').'] ':'').strip_tags($str));
    }


}
