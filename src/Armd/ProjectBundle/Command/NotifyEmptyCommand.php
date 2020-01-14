<?php
namespace Armd\ProjectBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;


class NotifyEmptyCommand extends ContainerAwareCommand
{
    public $holidays;

    protected function configure()
    {
        $this
            ->setName('armd:notify:empty')
            ->setDescription("Notify employess who didn't fill report")
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force notification without confirmation')
        ;
        
        
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->date = date('d.m.Y');
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
            if (!$dialog->askConfirmation($output, '<question>Send notification to employees?</question>', false)) {
                $this->debug('<error>Terminating.</error>'.EOL);
                exit;
            } else {
                $this->send = true;
            }
        }
        
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
                
        $repository = $em->getRepository('ArmdReportBundle:Report');
        
        $query = $repository->createQueryBuilder('r');
        $query = $query->select('e.id as eid');
        $query = $query->innerJoin('r.employee', 'e');
        $query = $query->where('e.discharged = 0');
        $query = $query->andWhere('r.day = :day')->setParameter('day', date('Y-m-d'));
        $query = $query->groupBy('r.employee');
        $employees = $query->getQuery()->getResult();
        
        $filled = '';
        foreach($employees as $e) {
            $filled .= $e['eid'].',';
        }
        $filled = substr($filled, 0, strlen($filled)-1);
        
        $repository = $em->getRepository('ArmdProjectBundle:Employee');
        $query = $repository->createQueryBuilder('e');
        $query = $query->select('e.id as eid, e.surname, e.name, e.patronymic, e.email');
        $query = $query->where('e.discharged = 0');
        $query = $query->andWhere('e.email is not null');
        $query = $query->andWhere('e.email != \'\'');
        if ($filled) $query = $query->andWhere('e.id not in ('.$filled.')');
        $employees = $query->getQuery()->getResult();
        
        foreach($employees as $employee) {
            $this->mail($employee);
        }
        
    }

    public function mail($employee)
    {
        if ($this->send) {
            $message = \Swift_Message::newInstance()
                ->setTo($employee['email'])
                ->setSubject('Напишите отчет за '.$this->date)
                ->setFrom('rpt@armd.ru')
                ->setBody("Здравствуйте, ".$employee['surname'].' '.$employee['name'].".<br>\nБудьте так добры - <a href=\"http://rpt.armd.ru/\">напишите отчет</a> за ".$this->date, 'text/html')
            ;
            $this->getContainer()->get('mailer')->send($message);
            $this->debug('Sent notification to '.$employee['email'].EOL);
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

}
