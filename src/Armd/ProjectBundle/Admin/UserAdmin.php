<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class UserAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('username', null, array('name' => 'Имя пользователя') )
            ->add('employee', null, array('name' => 'Сотрудник') )
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('username', null, array('field_options' => array('label' => 'Имя пользователя')))
            ->add('employee', null, array('field_options' => array('label' => 'Сотрудник')))
            
        ;
    }

  

    protected $maxPerPage = 50;

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Основное')
                ->add('username', null, array('required' => true, 'label' => 'Имя пользователя'))
                ->add('employee', null, array('required' => true, 'label' => 'Сотрудник'))
        ;
    }

  

  protected $filter = array(
    //'surname',
  );
}
