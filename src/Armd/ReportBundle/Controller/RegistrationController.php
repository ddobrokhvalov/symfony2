<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;
use Armd\ProjectBundle\Entity\User;
use Armd\ReportBundle\Form\RegistrationFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Report controller.
 *
 */
class RegistrationController extends BaseRegistrationController
{

    public function confirmedAction()
    {
        /**
         * Return confirmation
         */
    }

}
