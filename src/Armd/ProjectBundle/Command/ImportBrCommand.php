<?php
namespace Armd\ProjectBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Armd\ReportBundle\Entity\Report;
use DateTime;

class ImportBrCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('armd:import:br')
            ->setDescription('Import time from br')
        ;
        
        
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        
        define('EOL', "\n");
        
        $day = date('Y-m-d');
        $datetime = new DateTime('now');
        $url = 'http://br.armd.ru/time_entries.atom?from='.$day.'&key=2JyYDhhrcIcWP8TuvcSRKzt2moYeRRExBOSA0jW8&per_page=100&period_type=2&to='.$day;
        
        $this->fh = fopen(dirname(__FILE__).'/../../../../app/logs/import.log', "a+");

        $xml = simplexml_load_file($url);
        
        $reports = array();
        foreach($xml->entry as $raw) {
            $reports[] = array(
                'project' => preg_replace("/http\:\/\/br\.armd\.ru\/projects\/([^\/]+)(.*)/", '$1', $raw->id),
                'name'    => explode(' ', $raw->author->name),
                'email'   => $raw->author->email,
                'time'    => str_replace(array(' ','-'), '', preg_replace("/([^\ - ]+)([ \- ])([^\час]+)(.*)/i", '$3', $raw->title)),
            );
        }
        
        $this->em = $this->getContainer()->get('doctrine')->getEntityManager();
        
        $imported = 0;
        
        if (!empty($reports)) {
            $task = $this->em->getRepository('ArmdProjectBundle:Task')->findOneByTitle('Свободный ресурс ');
            
            $generated_reports = array();
            
            foreach($reports as $r) {
                $employee = $this->getEmployee($r);
                $project = $this->getProject($r);
                
                if ($employee && $project) {
                    if (isset($generated_reports[$employee->getId()][$project->getId()])) {
                        $generated_reports[$employee->getId()][$project->getId()]['time'] += $r['time'];
                    } else {
                        $generated_reports[$employee->getId()][$project->getId()] =
                            array(
                                'time' => $r['time'],
                                'employee' => $employee,
                                'project' => $project
                            );
                    }
                }
            }
            
            foreach($generated_reports as $generated_report) {
                foreach($generated_report as $project_report) {
                    $report = new Report;
                    $report->setDay($datetime);
					
					$time = $this->getTime($project_report['employee']);
					
                    $report->setMinutes(min($project_report['time'], $time));
                    $report->setEmployee($project_report['employee']);
                    $report->setProject($project_report['project']);
                    $report->setTask($task);
                    $report->setGenerated(1);
                    $report->setDescription("Время автоматически импортировано из Системы управления процессом разработки. Пожалуйста, напишите отчет.");
                    $this->em->persist($report);
                    $this->em->flush();
                    $imported++;
                }
            }
                    
        }
        $this->debug('Imported '.$imported.' reports'.EOL);
    }
    
    public function getEmployee($report)
    {
        $repository = $this->em->getRepository('ArmdProjectBundle:Employee');
        
        $employee = $repository->findOneByEmail($report['email']);
        
        if (is_object($employee)) {
            $id = $employee->getId();
        } else {
            $query = $repository->createQueryBuilder('e');
            $query = $query->select('e.id as eid');
            $query = $query->andWhere('e.surname = :surname')->setParameter('surname', $report['name'][0]);
            $query = $query->andWhere('e.name = :name')->setParameter('name', $report['name'][1]);
            $employee = $query->getQuery()->getResult();
            if (isset($employee[0]['eid'])) {
                $id = (integer)$employee[0]['eid'];
            } else {
                return false;
            }
        }
        
        return $repository->findOneById($id);
    }
    
	public function getTime($employee_id)
	{
		$repository = $this->em->getRepository('ArmdProjectBundle:Employee');
        
        $employee = $repository->findOneById($employee_id);
		
		if (is_object($employee)) {
			return $employee->getTime();
		} else {
			return 8;
		}
	}
	
    public function getProject($report)
    {
        $repository = $this->em->getRepository('ArmdProjectBundle:Project');
        
        $project = $repository->findOneByRedmine($report['project']);
        
        if (is_object($project)) {
            return $project;
        } else {
            return false;
        }
    }
    
    public function debug($str, $append_time = true)
    {
        $this->output->write($str);
        fwrite($this->fh, ($append_time?'['.date('d.m.Y H:i:s').'] ':'').strip_tags($str));
    }


}
