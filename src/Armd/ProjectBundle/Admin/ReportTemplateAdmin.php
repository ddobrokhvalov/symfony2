<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class ReportTemplateAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('name' => 'Название'))
            ->add('body', null, array('name' => 'Шаблон'))
        ;
    }

    protected $maxPerPage = 50;

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Основное')
                ->add('title', null, array('required' => true, 'label' => 'Название'))
                ->add('body', null, array('required' => true, 'label' => 'Шаблон'))
        ;
    }

}



