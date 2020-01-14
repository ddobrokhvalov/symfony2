<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Knp\Bundle\MenuBundle\MenuItem;

class ProjectStageAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('name' => 'Название'))
            ->add('date', null, array('name' => 'Дата'))
            ->add('description', null, array('name' => 'Описание'))
            ->add('project', null, array('name' => 'Проект'))
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
            ->add('title', null, array('required' => true, 'label' => 'Название'))
            ->add('date', null, array('required' => true, 'label' => 'Дата'))
            ->add('description', null, array('required' => false, 'label' => 'Описание'))
            ->with('Проект')
                ->add('project', 'sonata_type_model', array('required' => true, 'label' => 'Проект'), array( 'edit' => 'list'))
            ->end()
        ;
    }

}
