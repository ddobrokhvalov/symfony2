<?php

namespace Armd\ProjectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('project_group_id')
            ->add('client_id')
            ->add('subcontract_id')
        ;
    }

    public function getName()
    {
        return 'armd_projectbundle_projecttype';
    }
}
