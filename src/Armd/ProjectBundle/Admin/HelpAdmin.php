<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class HelpAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('name' => 'Заголовок') )
            ->add('anchor', null, array('name' => 'Якорь') )
            ->add('text', null, array('name' => 'Текст') )
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, array('field_options' => array('label' => 'Заголовок')))
            ->add('anchor', null, array('field_options' => array('label' => 'Якорь')))
            ->add('text', null, array('field_options' => array('label' => 'Текст')))
        ;
    }

  

    protected $maxPerPage = 50;

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Основное')
                ->add('title', null, array('required' => true, 'label' => 'Заголовок'))
                ->add('anchor', null, array('required' => true, 'label' => 'Якорь'))
                ->add('text', null, array('required' => true, 'label' => 'Текст'))
        ;
    }

  

  protected $filter = array(
    //'surname',
  );
}
