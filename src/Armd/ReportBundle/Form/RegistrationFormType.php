<?php

namespace Armd\ReportBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Doctrine\ORM\EntityRepository;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('email')
            ->add('surname')
            ->add('name')
            ->add('patronymic')
            ->add('plainPassword', 'repeated', array('type' => 'password'));
        ;
    }

    public function getName()
    {
        return 'armd_user_registration';
    }
}
