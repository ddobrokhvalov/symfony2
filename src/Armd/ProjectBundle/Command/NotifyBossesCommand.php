<?php
namespace Armd\ProjectBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NotifyBossesCommand extends ContainerAwareCommand
{
    public $holidays;

    protected function configure()
    {
        $this
            ->setName('armd:notify:bosses')
            ->setDescription("Summary department report for bosses")
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
        
        if (in_array(date('w'), array(6,7)) || $this->isHoliday(date('d.m.Y'))) {
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
        $repository = $this->em->getRepository('ArmdProjectBundle:Department');
        $departments = $repository->findAll();
        
        foreach($departments as $department) {
            if (is_object($department->getBoss())) {
                $report = $this->getReport($department);
                if ($report) {
                    $boss = $department->getBoss();
                    $this->mail(
                        $boss->__toString(),
                        $boss->getEmail(),
                        $department->getTitle(),
                        $report
                    );
                }
            }
        }
    }

    public function mail($boss_title, $boss_email, $department_title, $report)
    {
        if ($this->send && $boss_email && is_array($report)) {
            $body = '';
            foreach($report as $r) {
                $body.= $r['name'].' - '.$r['time']." ч.<br>\n";
            }
            $message = \Swift_Message::newInstance()
                ->setTo($boss_email)
                ->setSubject('Отчет по подразделению "'.$department_title.'" за '.date('d.m.Y', $this->date))
                ->setFrom('rpt@armd.ru')
                ->setBody('Отчет по подразделению "'.$department_title.'" за '.date('d.m.Y', $this->date)."<br>\n<br>\n".$body, 'text/html')
            ;
            $this->getContainer()->get('mailer')->send($message);
           
            $this->debug('Sent summary department report to '.$boss_title.' ('. $boss_email .')'.EOL);
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
    
    public function getReport(\Armd\ProjectBundle\Entity\Department $department)
    {
        $repository = $this->em->getRepository('ArmdProjectBundle:Employee');
        $employees = $repository->findBy(array(
                                            'department' => $department->getId(),
                                            'discharged' => '0'
                                            ));
        
        $reports = array();
        if ($employees) {
            foreach($employees as $employee) {
                
                $repository = $this->em->getRepository('ArmdReportBundle:Report');
                $query = $repository->createQueryBuilder('r');
                $query = $query->select('sum(r.minutes) as sm');
                $query = $query->where('r.employee = :employee')->setParameter('employee', $employee->getId());
                $query = $query->andWhere('r.day = :day')->setParameter('day', date('Y-m-d', $this->date));
                $report = $query->getQuery()->getResult();

                if ($report[0]['sm'] != null) { // пользователь составил отчет по проекту
                    $reports[] = array(
                        'name' => $employee->__toString(),
                        'time' => $report[0]['sm']
                    );
                } else { // отчета нет
                    $reports[] = array(
                        'name' => $employee->__toString(),
                        'time' => 0
                    );
                }
            }
        }
        return (count($reports)?$reports:false);
    }

}
