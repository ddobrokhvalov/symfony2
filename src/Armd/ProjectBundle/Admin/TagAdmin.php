<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class TagAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('name' => 'Наименование'))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, array('field_options' => array('label' => 'Наименование')))
        ;
    }

  

    protected $maxPerPage = 50;

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
          ->add('title', null, array('required' => true, 'label' => 'Наименование'))
        ;
    }

  protected $filter = array(
    //'surname',
  );

  
}
