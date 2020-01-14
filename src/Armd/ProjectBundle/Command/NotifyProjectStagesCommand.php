<?php
namespace Armd\ProjectBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NotifyProjectStagesCommand extends ContainerAwareCommand
{
    public $holidays;

    protected function configure()
    {
        $this
            ->setName('armd:notify:project_stages')
            ->setDescription("Project stages notify for managers")
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
        
        //if (in_array(date('w'), array(6,7)) || $this->isHoliday(date('d.m.Y'))) {
        //    $this->debug('<error>Today is weekend. Terminating.</error>'.EOL);
        //    exit;
        //}
        
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
        $repository = $this->em->getRepository('ArmdProjectBundle:ProjectStage');
        $project_stages = $repository->findByDate(date('Y-m-d', strtotime('+7 days')));
        
        foreach($project_stages as $project_stage) {
            $project = $project_stage->getProject();
            $manager = (is_object($project->getManager())?$project->getManager():false);
            
            if ($manager) {
                $report = date('d.m.Y', strtotime('+7 days')).' наступает новый этап "'.$project_stage->getTitle().'" проекта "'.$project->getTitle()."\".<br>\n".
                "Описание: <br>\n".$project_stage->getDescription();
                
                $this->mail(
                    $manager->__toString(),
                    $manager->getEmail(),
                    $project->getTitle(),
                    $report
                );
            }
        }
    }

    public function mail($manager_title, $manager_email, $project_title, $report)
    {
        if ($this->send && $manager_email) {

            $message = \Swift_Message::newInstance()
                ->setTo('k.potemichev@gmail.com')
                //->setTo($manager_email)
                ->setSubject('Новый этап проекта "'.$project_title)
                ->setFrom('rpt@armd.ru')
                ->setBody($report, 'text/html')
            ;
            $this->getContainer()->get('mailer')->send($message);
            $this->debug('Sent project stage notification to '.$manager_title.' ('. $manager_email .')'.EOL);
        }
    }

    public function debug($str, $append_time = true)
    {
        $this->output->write($str);
        fwrite($this->fh, ($append_time?'['.date('d.m.Y H:i:s').'] ':'').strip_tags($str));
    }
    
}
