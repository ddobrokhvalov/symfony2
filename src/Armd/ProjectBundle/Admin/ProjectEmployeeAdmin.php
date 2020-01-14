<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Knp\Bundle\MenuBundle\MenuItem;

class ProjectEmployeeAdmin extends Admin
{
    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('title', null, array('name' => 'Наименование работ'))
            ->add('employee', null, array('name' => 'Сотрудник'))
            ->add('project', null, array('name' => 'Проект'))
            ->add('hours', null, array('name' => 'Количество часов'))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('name' => 'Наименование работ'))
            ->add('employee', null, array('name' => 'Сотрудник'))
            ->add('project', null, array('name' => 'Проект'))
            ->add('hours', null, array('name' => 'Количество часов'))
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Сотрудник')
                 ->add('employee', 'sonata_type_model', array('required' => true, 'label' => 'Сотрудник'), array( 'edit' => 'list'))
            ->end()
            ->with('Проект')
        	->add('project', 'sonata_type_model', array('required' => true, 'label' => 'Проект'), array( 'edit' => 'list'))
            ->end()
            ->add('title', null, array('required' => true, 'label' => 'Наименование работ'))
            ->add('hours', null, array('required' => true, 'label' => 'Количество часов'))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('project', null, array('field_options' => array('label' => 'Проект')))
        ;
    }
	
    protected $maxPerPage = 10;
	
	
}
