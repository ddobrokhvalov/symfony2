<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class ClientAdmin extends Admin
{
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('name' => 'Название') )
            ->addIdentifier('full_title', null, array('name' => 'Полное название') )
        ;
    }

    protected $maxPerPage = 5;

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Основное')
                ->add('title', null, array('required' => true, 'label' => 'Название'))
                ->add('full_title', null, array('required' => true, 'label' => 'Полное название'))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, array('field_options' => array('label' => 'Название')))
            ->add('full_title', null, array('field_options' => array('label' => 'Полное название')))
        ;
    }

}
