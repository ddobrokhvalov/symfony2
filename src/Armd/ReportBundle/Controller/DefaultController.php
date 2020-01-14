<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('ArmdReportBundle:Default:index.html.twig'/*, array('name' => $name)*/);
    }
}
