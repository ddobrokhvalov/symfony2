<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ReportBundle\Entity\Report;
use Armd\ReportBundle\Form\ReportType;
use Armd\ReportBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;

class MoneyFlowController extends ReportController

{
    public function indexAction()
    {
        if (isset($_GET['id'])) {
            $project_id = $_GET['id'];
        } else {
            $project_id = 1;
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $mf_repository = $em->getRepository('ArmdProjectBundle:MoneyFlow');
        $mf = $mf_repository->findByProject($project_id);
        
        $sum = 0;
        
        if (!empty($mf)) {
            foreach ($mf as $row) {
                $money_flow[] = array(
                                        'date'       => $row->getDate()->format('d.m.Y'),
                                        'value'      => $row->getValue(),
                                        'contractor' => $row->getContractor(),
                                        'client'     => $row->getClient(),
                                        'ticker'     => $row->getTicker(),
                                        'legal'      => $row->getLegal(),
                                        'analytics'  => $row->getAnalytics(),
                                        
                                      );
            }
            
            foreach ($money_flow as $row) {
                $sum += $row['value'];
            }
        }
        
        return $this->render('ArmdReportBundle:Report:money_flow.html.twig', array(
            'flow'     => isset($money_flow)?$money_flow:array(),
            'sum'  => $sum,
        )); 
    }
}