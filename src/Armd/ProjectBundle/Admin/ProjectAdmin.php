<?php
namespace Armd\ProjectBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class ProjectAdmin extends Admin
{
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('name' => 'Название') )
            ->add('project_type', null, array('name' => 'Тип') )
            ->add('open', null, array('name' => 'Статус') )
#            ->add('project_group', null, array('name' => 'Портфель') )
#            ->add('client', null, array('name' => 'Клиент') )
            //->add('department', null, array('name' => 'Подразделение') )
            ->add('manager', null, array('name' => 'Менеджер') )
            ->add('begin', null, array('name' => 'Начало') )
            ->add('end', null, array('name' => 'Окончание') )
        ;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        
        
        $formMapper
            ->with('Основное')
                ->add('open', null, array('required' => false, 'label' => 'Открыт'))
                ->add('title', null, array('required' => true, 'label' => 'Название'))
                ->add('redmine', null, array('required' => false, 'label' => 'Идентификатор redmine'))
                ->add('project_type', null, array('required' => true, 'label' => 'Тип', 'empty_value' => ''))
                ->add('contract_cost', null, array('required' => false, 'label' => 'Стоимость контракта, руб.'))
                ->add('begin', null, array('required' => true, 'label' => 'Дата начала') )
                ->add('end', null, array('required' => true, 'label' => 'Дата окончания'))
                
                ->add('real_end', null, array('required' => false, 'label' => 'Реальная дата окончания'))
                ->add('fzp', null, array('required' => false, 'label' => 'Стоимость работ по ФЗП'))
            ->end()
            ->with('Портфель')
                ->add('project_group', 'sonata_type_model', array('required' => true, 'label' => 'Портфель'), array( 'edit' => 'list'))
            ->end()
            ->with('Клиент')
                ->add('client', 'sonata_type_model', array('required' => true, 'label' => 'Клиент'), array( 'edit' => 'list'))
            ->end()
            ->with('Аккаунт')
                ->add('sales_manager', 'sonata_type_model', array('required' => true, 'label' => 'Аккаунт'), array( 'edit' => 'list'))
            ->end()
            //->with('Подразделение')
            //    ->add('department', 'sonata_type_model', array('required' => true, 'label' => 'Подразделение'), array( 'edit' => 'list'))
            //->end()
            ->with('Менеджер')
                ->add('manager', 'sonata_type_model', array('required' => true, 'label' => 'Менеджер'), array( 'edit' => 'list'))
            ->end()
			->with('Хозяин')
                ->add('owner', 'sonata_type_model', array('required' => true, 'label' => 'Хозяин'), array( 'edit' => 'list'))
            ->end()
            ->with('Юридическое лицо')
                ->add('legal', 'sonata_type_model', array('required' => true, 'label' => 'Юридическое лицо'), array( 'edit' => 'list'))
            ->end()
            ->with('Сотрудники')
                ->add('project_employee', 'sonata_type_collection', array('label' => 'Поставьте галочку для удаления сотрудника из проекта'), array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable'  => 'employee',
                ))
            ->end()
            ->with('Субподрядчики')
                ->add('project_subcontract', 'sonata_type_collection', array('label' => 'Поставьте галочку для удаления субподрядчика из проекта'), array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable'  => 'subcontract',
                ))
            ->end()
            ->with('Технологии')
                ->add('tag', 'sonata_type_model', array('expanded' => true))
            ->end()
            ->with('Дополнительно', array('collapsed' => true))
                ->add('ratio_subcontract', null, array('required' => true, 'label' => 'Коэффициент накладных расходов на субподрядчиков'))
                ->add('ratio_inside', null, array('required' => true, 'label' => 'Коэффициент накладных расходов на внутренние ресурсы'))
                ->add('ratio_bonus', null, array('required' => true, 'label' => 'Ставка премии'))
                ->add('ratio_outsourcing', null, array('required' => true, 'label' => 'Ставка премии от аутсорсинга'))
                ->add('other_cost', null, array('required' => false, 'label' => 'Дополнительные расходы'))
            ->end()
            
            ;
            
            
    }
    
    public function getNewInstance()
    {
        $object = $this->modelManager->getModelInstance($this->getClass());
        
        if ($object->getBegin() === NULL) {
            $object->setBegin(new \DateTime(date('01.01.Y')));
        }
        
        if ($object->getEnd() === NULL) {
            $object->setEnd(new \DateTime(date('01.01.Y')));
        }
        
        if ($object->getRealEnd() === NULL) {
            $object->setRealEnd(new \DateTime(date('01.01.Y')));
        }
        return $object;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('open', null, array('field_options' => array('label' => 'Открыт')))
            ->add('title', null, array('field_options' => array('label' => 'Название')))
            ->add('project_group', null, array('field_options' => array('label' => 'Портфель')))
            //->add('department', null, array('field_options' => array('label' => 'Подразделение')))
            ->add('manager', null, array('field_options' => array('label' => 'Менеджер')))
        ;
    }
    
    public function preUpdate($object)
    {
        
        $em = $this->getModelManager()->getEntityManager();
        $entities = $em->getRepository('ArmdProjectBundle:ProjectEmployee')->findByProject($object->getId());
        foreach($entities as $entity) {
            $em->remove($entity);
        }
        $em->flush();
        
        $entities = $em->getRepository('ArmdProjectBundle:ProjectSubcontract')->findByProject($object->getId());
        foreach($entities as $entity) {
            $em->remove($entity);
        }
        $em->flush();
            
    }
    
    


  protected $maxPerPage = 30;

}
