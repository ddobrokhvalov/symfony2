<?php
namespace Armd\ProjectBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NotifyManagersCommand extends ContainerAwareCommand
{
    public $holidays;

    protected function configure()
    {
        $this
            ->setName('armd:notify:managers')
            ->setDescription("Summary report for project managers")
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force notification without confirmation')
        ;
        
        
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->date = time();
        define('EOL', "\n");

        $this->send = $input->getOption('force');

        $this->output = $output;
        
        $this->fh = fopen(dirname(__FILE__).'/../../../../app/logs/notification.log', "a+");
        
        $this->ini = parse_ini_file(dirname(__FILE__).'/../../../../app/config/parameters.ini');
        
        if (in_array(date('N'), array(6,7)) || $this->isHoliday(date('d.m.Y'))) {
            $this->debug('<error>Today is weekend. Terminating.</error>'.EOL);
            exit;
        }
        
        if (!$this->send) {
            $dialog = $this->getHelperSet()->get('dialog');
            if (!$dialog->askConfirmation($output, '<question>Send notification to managers?</question>', false)) {
                $this->debug('<error>Terminating.</error>'.EOL);
                exit;
            } else {
                $this->send = true;
            }
        }
        
        $this->em = $this->getContainer()->get('doctrine')->getEntityManager();
        $repository = $this->em->getRepository('ArmdProjectBundle:Project');
        $projects = $repository->findAll();
        
        foreach($projects as $project) {
            if (is_object($project->getManager())) {
                $report = $this->getReport($project);
                $report_unasigned = $this->getReportForUnasigned($project);
                if ($report || $report_unasigned) {
                    $manager = $project->getManager();
                    $this->mail(
                        $manager->__toString(),
                        $manager->getEmail(),
                        $project->getTitle(),
                        $report,
                        $report_unasigned
                    );
                }
            }
        }
    }

    public function mail($manager_title, $manager_email, $project_title, $report, $report_unasigned)
    {
        if ($this->send && $manager_email && is_array($report)) {
            $body = '';
            if ($report) {
                foreach($report as $r) {
                    $body.= $r['name'].' - '.$r['time']." ч.<br>\n";
                }
            }
            if ($report_unasigned) {
                $body .= "<br>\nСотрудники, не указанные в карточке проекта:<br>\n";
                foreach($report_unasigned as $r) {
                    $body.= $r['name'].' - '.$r['time']." ч.<br>\n";
                }
            }
            
            $message = \Swift_Message::newInstance()
                ->setTo($manager_email)
                ->setSubject('Отчет по проекту "'.$project_title.'" за '.date('d.m.Y', $this->date))
                ->setFrom('rpt@armd.ru')
                ->setBody('Отчет по проекту "'.$project_title.'" за '.date('d.m.Y', $this->date)."<br>\n<br>\n".$body, 'text/html')
            ;
            $this->getContainer()->get('mailer')->send($message);
           
            $this->debug('Sent summary project report to '.$manager_title.' ('. $manager_email .')'.EOL);
        }
    }

    public function debug($str, $append_time = true)
    {
        $this->output->write($str);
        fwrite($this->fh, ($append_time?'['.date('d.m.Y H:i:s').'] ':'').strip_tags($str));
    }
    
    /**
     * Является ли день нерабочим
     * @param string $day
     * @return boolean
     */
    public function isHoliday($day)
    {
        if (!is_array($this->holidays)) {
            $this->holidays = $this->getHolidays();
        }
        
        return in_array($day,$this->holidays);
    }
    /**
     * Список нерабочих дней
     * @return array
     */
    public function getHolidays()
    {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Holiday');
        $holidays = $repository->findAll();
        
        $result = array();
        foreach($holidays as $holiday){
            $result[] = $holiday->getDay()->format('d.m.Y');    
        }
        return $result;
    }
    
    public function getReport(\Armd\ProjectBundle\Entity\Project $project)
    {
        $employees = $project->getProjectEmployee();
        $reports = array();
        if ($employees) {
            foreach($employees as $employee) {
                $report = $this->em->getRepository('ArmdReportBundle:Report')->findOneBy(array(
                   'project' => $project->getId(),
                   'employee' => $employee->getEmployee()->getId(),
                   'day' => date('Y-m-d', $this->date)
                ));
                if (is_object($report)) { // пользователь составил отчет по проекту
                    $reports[] = array(
                        'name' => $employee->getEmployee()->__toString(),
                        'time' => $report->getMinutes()
                    );
                } else { // отчета нет
                    $reports[] = array(
                        'name' => $employee->getEmployee()->__toString(),
                        'time' => 0
                    );
                }
            }
        }
        return (count($reports)?$reports:false);
    }
    
    public function getReportForUnasigned(\Armd\ProjectBundle\Entity\Project $project)
    {
        $reports = array();
        $project_employees = array();
        
        $employees = $project->getProjectEmployee();
        if ($employees) {
            foreach($employees as $employee) {
                $project_employees[] = $employee->getEmployee()->getId();
            }
        }
        
        $all_reports = $this->em->getRepository('ArmdReportBundle:Report')->findBy(array(
            'project' => $project->getId(),
            'day' => date('Y-m-d', $this->date)
        ));
        
        if ($all_reports) {
            foreach($all_reports as $report) {
                
                if (!in_array($report->getEmployee()->getId(),$project_employees)) {
                    $reports[] = array(
                        'name' => $report->getEmployee()->__toString(),
                        'time' => $report->getMinutes()
                    );
                }
            }
        }
        return (count($reports)?$reports:false);
    }

}
