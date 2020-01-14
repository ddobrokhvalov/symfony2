<?php

namespace Armd\ReportBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Doctrine\ORM\EntityRepository;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('day', 'date')
            ->add('minutes', 'number')
            ->add('task')
            ->add('project')
            ->add('description')
        ;
    }

    public function getName()
    {
        return 'armd_reportbundle_reporttype';
    }
}
