<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class SubcontractAdmin extends Admin
{
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('name' => 'Название') )
            ->add('parent', null, array('name' => 'Родитель') )
        ;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Основное')
                ->add('title', null, array('required' => true, 'label' => 'Название'))
                ->add('parent', null, array('required' => false, 'label' => 'Родитель'))
                
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, array('field_options' => array('label' => 'Название')))
        ;
    }

    protected $maxPerPage = 50;

}
