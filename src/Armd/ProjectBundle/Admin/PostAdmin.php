<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class PostAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('name' => 'Наименование'))
            ->addIdentifier('task', null, array('name' => 'Тип работ'))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, array('field_options' => array('label' => 'Наименование')))
            ->add('task', null, array('field_options' => array('label' => 'Тип работ')))
        ;
    }

  

    protected $maxPerPage = 50;

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
          ->add('title', null, array('required' => true, 'label' => 'Наименование'))
          ->add('task', null, array('required' => false, 'label' => 'Тип работ'))
        ;
    }

  protected $filter = array(
    //'surname',
  );

  
}
