<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class DepartmentAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('name' => 'Название'))
            ->add('parent', null, array('name' => 'Родитель'))
            ->add('boss', null, array('name' => 'Руководитель'))
        ;
    }

    protected $maxPerPage = 50;

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Основное')
                ->add('title', null, array('required' => true, 'label' => 'Название'))
            ->end()
            ->with('Родитель')
                ->add('parent', 'sonata_type_model', array('required' => true, 'label' => 'Родитель'), array( 'edit' => 'list')) 
            ->end()
            ->with('Руководитель')
                ->add('boss', 'sonata_type_model', array('required' => true, 'label' => 'Руководитель'), array( 'edit' => 'list'))
            ->end()
        ;
    }
    
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, array('field_options' => array('label' => 'Название')))
            ->add('parent', null, array('field_options' => array('label' => 'Родитель')))
            ->add('boss', null, array('field_options' => array('label' => 'Руководитель')))
        ;
    }
	
	public function preUpdate($object)
	{
		echo 'Редактирование организационной структуры заблокировано.';
		exit;
	}
	
	public function prePersist($object)
	{
		echo 'Редактирование организационной структуры заблокировано.';
		exit;
	}
	
	public function preRemove($object)
	{
		echo 'Редактирование организационной структуры заблокировано.';
		exit;
	}

}



