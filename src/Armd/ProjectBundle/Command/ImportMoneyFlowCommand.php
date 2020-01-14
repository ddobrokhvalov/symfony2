<?php
namespace Armd\ProjectBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Armd\ProjectBundle\Entity\MoneyFlow;

class ImportMoneyFlowCommand extends ContainerAwareCommand
{
    
    
    protected function configure()
    {
        $this
            ->setName('armd:import:money_flow')
            ->setDescription('Import money flow')
            ->addOption('xml', null, InputOption::VALUE_REQUIRED, 'How many times should the message be printed?', 1)
        ;
        
        
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        
        define('EOL', "\n");
        
        
        $this->fh = fopen(dirname(__FILE__).'/../../../../app/logs/import_money_flow.log', "a+");
        $this->debug('<info>Money flow import started.</info>'.EOL);
        
        
        $ini = parse_ini_file(dirname(__FILE__).'/../../../../app/config/parameters.ini');
        $this->debug('<info>Parsed db config.</info>'.EOL);
        
        $xml = $input->getOption('xml');
        
        if (is_string($xml)) {
            $body = file_get_contents($xml);
        } else {
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $this->getContainer()->getParameter('mf.url')); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
            curl_setopt($ch, CURLOPT_USERPWD, $this->getContainer()->getParameter('mf.login').':'.$this->getContainer()->getParameter('mf.password'));
            $body = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch); 
            $this->debug('<info>Recieved '.$info['size_download'].' bytes from '.$this->getContainer()->getParameter('mf.url').'.</info>'.EOL);
        }

        $xml = simplexml_load_string($body);
        $this->debug('<info>Found '.count($xml).' transactions.</info>'.EOL);
        
        $this->em = $this->getContainer()->get('doctrine')->getEntityManager();        
        
        $existing_transactions = $this->em->getRepository('ArmdProjectBundle:MoneyFlow')->findAll();   
        
        $imported = 0;
        $updated = 0;
        
        foreach($xml as $transaction) {
            $id = (integer)$transaction->id;
            
            $this->debug('Transaction '.$id);
                        
            $update = false;
            
            foreach ($existing_transactions as $existing_transaction) {
                if ($id == $existing_transaction->getId()) {
                    $update = $existing_transaction;
                }
            }
            
            if ($update === false) {
                $tr = new MoneyFlow;
                $tr->setId($transaction->id);
                $tr->setData(new \DateTime(date('Y-m-d',strtotime($transaction->Data))));
                $tr->setKodklient($transaction->KodKlient);
                $tr->setNameklient($transaction->NameKlient);
                $tr->setInnklient($transaction->InnKlient);
                $tr->setFisik($transaction->Fisik);
                $tr->setKodproject($transaction->KodProject);
                $tr->setNameproject($transaction->NameProject);
                $tr->setAnalitika1($transaction->Analitika1);
                $tr->setAnalitika2($transaction->Analitika2);
                $tr->setAnalitika3($transaction->Analitika3);
                $tr->setKomment($transaction->Komment);
                $tr->setTiker($transaction->Tiker);
                $tr->setSumma($transaction->Summa);
                $this->em->persist($tr);
                $this->em->flush();
                $imported++;
                $this->debug(' imported'.EOL, false);
            } else {
                $updated++;
                $this->debug(' updated'.EOL, false);
            }
        }
        
        $this->debug('Imported '.$imported.', updated '.$updated.EOL);
    }
    
    public function debug($str, $append_time = true)
    {
        $this->output->write($str);
        fwrite($this->fh, ($append_time?'['.date('d.m.Y H:i:s').'] ':'').strip_tags($str));
    }

}
