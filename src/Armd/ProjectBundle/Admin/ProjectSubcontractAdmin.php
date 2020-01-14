<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Knp\Bundle\MenuBundle\MenuItem;

class ProjectSubcontractAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('name' => 'Наименование работ'))
            ->add('subcontract', null, array('name' => 'Субподрядчик'))
            ->add('project', null, array('name' => 'Проект'))
            ->add('hours', null, array('name' => 'Количество часов'))
            ->add('salary', null, array('name' => 'Стоимость, руб.'))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('project', null, array('field_options' => array('label' => 'Проект')))
        ;
    }


  protected $maxPerPage = 10;

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', null, array('required' => true, 'label' => 'Наименование работ'))
            ->with('Субподрядчик')
                ->add('subcontract', 'sonata_type_model', array('required' => true, 'label' => 'Субподрядчик'), array( 'edit' => 'list'))
            ->end()
            ->with('Проект')
                ->add('project', 'sonata_type_model', array('required' => true, 'label' => 'Проект'), array( 'edit' => 'list'))
            ->end()
            ->add('hours', null, array('required' => true, 'label' => 'Количество часов'))
            ->add('salary', null, array('required' => true, 'label' => 'Стоимость, руб.'))
        ;
    }

}
