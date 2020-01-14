<?php

namespace Armd\ProjectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class SubcontractType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('title')
        ;
    }

    public function getName()
    {
        return 'armd_projectbundle_subcontracttype';
    }
}
