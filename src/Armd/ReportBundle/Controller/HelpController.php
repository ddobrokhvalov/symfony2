<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ReportBundle\Entity\Report;
use Armd\ReportBundle\Form\ReportType;
use Armd\ReportBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;

class HelpController extends Controller
{
    
    public function indexAction()
	{
        $help = $this
                ->getDoctrine()
                ->getEntityManager()
                ->getRepository('ArmdProjectBundle:Help')
                ->findAll();
        
        foreach ($help as $atricle) {
            $atricle->setText(nl2br($atricle->getText()));
        }
        
		return $this->render('ArmdReportBundle:Report:help.html.twig',
                array('articles' => $help));
	}
}