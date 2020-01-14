<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class LegalAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('name' => 'Название'))
        ;
    }

    protected $maxPerPage = 50;

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Основное')
                ->add('title', null, array('required' => true, 'label' => 'Название'))
        ;
    }

}



