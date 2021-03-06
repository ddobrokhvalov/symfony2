<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class EmployeeAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('surname', null, array('name' => 'Фамилия'))
            ->addIdentifier('name', null, array('name' => 'Имя'))
            ->addIdentifier('patronymic', null, array('name' => 'Отчество'))
            ->add('post', null, array('name' => 'Должность'))
            ->add('department', null, array('name' => 'Департамент'))
#            ->add('discharged', null, array('name' => 'Уволен'))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('surname', null, array('field_options' => array('label' => 'Фамилия')))
            ->add('name', null, array('field_options' => array('label' => 'Имя')))
            ->add('patronymic', null, array('field_options' => array('label' => 'Отчество')))
            ->add('post', null, array('field_options' => array('label' => 'Должность')))
            ->add('department', null, array('field_options' => array('label' => 'Департамент')))
            ->add('discharged', null, array('field_options' => array('label' => 'Уволен')))
			//->add('outside', null, array('field_options' => array('label' => 'Внешний сотрудник')))
        ;
    }

  

    protected $maxPerPage = 50;

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Основное')
                ->add('surname', null, array('required' => true, 'label' => 'Фамилия'))
                ->add('name', null, array('required' => true, 'label' => 'Имя'))
                ->add('patronymic', null, array('required' => false, 'label' => 'Отчество'))
                ->add('post', null, array('required' => true, 'label' => 'Должность'))
				->add('time', null, array('required' => false, 'label' => 'Списываемое время за день'))
                ->add('discharged', null, array('required' => false, 'label' => 'Уволен'))
                ->add('subcontractor', null, array('required' => false, 'label' => 'Субподрядчик'))
				->add('outside', 'checkbox', array('required' => false, 'label' => 'Внешний сотрудник'))
				->add('rate', 'text', array('required' => false, 'label' => 'Разряд'))
            ->end()
            ->with('Департамент')
                ->add('department', 'sonata_type_model', array('required' => true, 'label' => 'Департамент'), array( 'edit' => 'list'))
            ->end()
        ;
    }
	
	
	


  protected $filter = array(
    //'surname',
  );
}
