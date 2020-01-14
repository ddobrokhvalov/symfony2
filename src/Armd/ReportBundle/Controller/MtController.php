<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ReportBundle\Entity\Report;
use Armd\ReportBundle\Form\ReportType;
use Armd\ReportBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;

class MtController extends ReportController

{
    /*
     * Экшн отчета по себестоимости
     */
    public function indexAction()
    {
       
        $this->user = $this->container->get('security.context')->getToken()->getUser();
        if (!in_array('ROLE_SUPER_ADMIN', $this->user->getRoles())) {
            header('Location: /');
            exit;
        }
       
        $this->em = $this->getDoctrine()->getEntityManager();
        $project_types = $this->em->getRepository('ArmdProjectBundle:ProjectType')->findAll();
        $projects_repository = $this->em->getRepository('ArmdProjectBundle:Project');
        
        $project_ids = (isset($_GET['project'])?$_GET['project']:array());
        
        $projects = array();
        foreach($project_ids as $project_id) {
            $projects[] = $projects_repository->findOneById($project_id);
        }
        
        $this->begin = (isset($_GET['begin'])?$_GET['begin']:'01.01.'.date('Y'));
        $this->end = (isset($_GET['end'])?$_GET['end']:'31.12.'.date('Y'));
        
        $this->buildRates();
        
        $entities = array();
        foreach($project_types as $project_type) {
            foreach($projects as $project) {
                if ($project->getProjectType() == $project_type) {
                    $entities[$project_type->getId()]['title'] = $project_type->getTitle();
                    
                    $employees = $this->buildEmployees($project);
                    
                    $project_sum = $this->getProjectSum($employees);
                    
                    $entities[$project_type->getId()]['projects'][$project->getId()] = array(
                        'title'       => $project->getTitle(),
                        'employees'   => $employees,
                        'project_sum' => $project_sum
                    );
                    
                }
                
            }
            if (isset($entities[$project_type->getId()]) && count($entities[$project_type->getId()]['projects'])) {
                $entities[$project_type->getId()]['sum'] = array(
                    'time'            => 0,
                    'cost'            => 0,
                    'cost_multiplied' => 0                
                );
                foreach ($entities[$project_type->getId()]['projects'] as $project) {
                    $entities[$project_type->getId()]['sum']['time'] += $project['project_sum']['time'];
                    $entities[$project_type->getId()]['sum']['cost'] += $project['project_sum']['cost'];
                    $entities[$project_type->getId()]['sum']['cost_multiplied'] += $project['project_sum']['cost_multiplied'];
                }
            }
        }
        
        if (count($entities)) {
            $sum = array(
                'time'            => 0,
                'cost'            => 0,
                'cost_multiplied' => 0                
            );
            foreach($entities as $project_type) {
                $sum['time'] += $project_type['sum']['time'];
                $sum['cost'] += $project_type['sum']['cost'];
                $sum['cost_multiplied'] += $project_type['sum']['cost_multiplied'];
            }
        }
        
        
        $vars = array(
            'entities'        => isset($entities)?$entities:array(), // список записей
            'user'            => $this->user,
            'begin'           => (isset($_GET['begin'])?$_GET['begin']:'01.01.'.date('Y')),
            'end'             => (isset($_GET['end'])?$_GET['end']:'31.12.'.date('Y')),
            'breadcrumbs'     => array(
                                    array(
                                        'link'  => $this->generateUrl('report_mt'),
                                        'title' => 'Трудозатраты'
                                    )
                                ),
            'projects'        => $this->getProjects(),
            'sum'             => $sum,
        );
        
        if (isset($_GET['export'])) {
            if ($_GET['export'] == 'csv') {
                $template = 'ArmdReportBundle:Report:mt.csv.twig';
                $this->sendFileToBrowser(
                    iconv('utf-8', (strstr($_SERVER['HTTP_USER_AGENT'], 'Mac')?'MacCyrillic':'windows-1251'),$this->renderView($template, $vars)),
                    'Трудозатраты.csv',
                    'csv',
                    'text/csv'
                );
            }
        } else {
            $template = 'ArmdReportBundle:Report:mt.html.twig';
            return $this->render($template, $vars);
        }
    }
    
    protected function getProjectSum($employees)
    {
        $result = array(
            'time'            => 0,
            'cost'            => 0,
            'cost_multiplied' => 0                
        );
        foreach($employees as $employee) {
            $result['time'] += $employee['time'];
            $result['cost'] += $employee['cost'];
            $result['cost_multiplied'] += $employee['cost_multiplied'];
        }
        return $result;
    }
    
    protected function buildEmployees($project)
    {
        if (!isset($this->salary)) {
            $this->salary = $this
                            ->em
                            ->getRepository('ArmdProjectBundle:Rate')
                            ->createQueryBuilder('r')
                            ->select('r')
                            ->getQuery()
                            ->getResult();
        }
        
        $result = array();

        $reports = $this
                    ->em
                    ->getRepository('ArmdReportBundle:Report')
                    ->createQueryBuilder('r')
                    ->select('e.surname, e.name, e.patronymic, e.subcontractor, e.id, sum(r.minutes) as sm, r.day')
                    ->innerJoin('r.employee', 'e')
                    ->where('r.project = :project_id')
                    ->setParameter('project_id', $project->getId())
                    ->andWhere('r.day >= :begin')
                    ->setParameter('begin', date('Y-m-d', strtotime($this->begin)))
                    ->andWhere('r.day <= :end')
                    ->setParameter('end', date('Y-m-d', strtotime($this->end)))
                    ->groupBy('r.day, e.id')
                    ->getQuery()
                    ->getResult();
        
        $i = array();
        foreach($reports as $report) {
            
            if (!isset($i[$report['id']])) {
                $i[$report['id']] = 0;
            }
            $id = $report['id'].'_'.$i[$report['id']];
            
            $rate = $this->getRateByDate($report['id'], strtotime($report['day']));
            
            if (isset($result[$id]) && ($result[$id]['rate'] != $rate)) {
                $id = $report['id'].'_'.++$i[$report['id']];
                $result[$id] = array(
                    'name'            => $report['surname'].' '.$report['name'].' '.$report['patronymic']. ($report['subcontractor']?' (субподрядчик) ':'') .' (ставка изменена '.date('d.m.Y', strtotime($report['day'])).')',
                    'time'            => 0,
                    'cost'            => 0,
                    'cost_multiplied' => 0
                );
            } elseif (!isset($result[$id])) {
                $id = $report['id'].'_'.++$i[$report['id']];
                $result[$id] = array(
                    'name'            => $report['surname'].' '.$report['name'].' '.$report['patronymic'] . ($report['subcontractor']?' (субподрядчик) ':''),
                    'time'            => 0,
                    'cost'            => 0,
                    'cost_multiplied' => 0
                );
            }
            
            
            
            $result[$id]['rate'] = $rate;
            $result[$id]['time'] += $report['sm'];
            $result[$id]['cost'] += $this->salary[$rate]*$report['sm'];
            $result[$id]['cost_multiplied'] += $this->salary[$rate]*$report['sm']*3.6;
            
            
            
            //$result[$id] = array(
            //    'time'            => $time,
            //    'name'            => $employee->getEmployee()->__toString(),
            //    'cost'            => $this->salary[$rate]*$time,
            //    'cost_multiplied' => $this->salary[$rate]*$time*3.6
            //    );
        }
        //var_dump($result);exit;
        return $result;
    }
    
    /**
     * Получение фактической стоимости по отчетам проекта
     * @param integer $project_id
     * @return float Сумма
     */
    protected function getReportSumForProject($project_id)
    {
        $result = 0;

        $reports_repository =   $this
                                ->getDoctrine()
                                ->getEntityManager()
                                ->getRepository('ArmdReportBundle:Report');
        
        // получаем количество отчетов по проекту
        $query = $reports_repository->createQueryBuilder('r');
        $query = $query->select('count(r)');
        $query = $query->innerJoin('r.project', 'p');
        $query = $query->innerJoin('r.employee', 'e');
        $query = $query->where('p.id = :project_id')->setParameter('project_id', $project_id);
        $count = $query->getQuery()->getResult();
        $count = $count[0][1];
        
        for ($i=0;$i<=$count;$i=$i+10000){
            // выбираем по 10000 отчетов за раз (больше уже просто все тупит)
            $query = $reports_repository->createQueryBuilder('r');
            $query = $query->select('e.id as eid, r.minutes, r.day');
            $query = $query->innerJoin('r.project', 'p');
            $query = $query->innerJoin('r.employee', 'e');
            $query = $query->where('p.id = :project_id')->setParameter('project_id', $project_id);
            $query = $query->setFirstResult($i);
            $query = $query->setMaxResults('10000');
            $reports = $query->getQuery()->getResult();
            
            // из-за версионности ставок, которая еще и не хранится у нас приходится считать стоимость каждого отчета отдельно
            // TODO надо что-то с этим делать
            foreach ($reports as $report) {
                $result += $this->getReportSum($report['eid'], strtotime($report['day']), $report['minutes']);
            }
        }
        return $result;
    }
    
    public function getProjects()
    {
        $project_ids = (isset($_GET['project'])?$_GET['project']:array());
        
        $projects = 
            $this
            ->getDoctrine()
            ->getEntityManager()
            ->getRepository('ArmdProjectBundle:Project')
            ->findAll();
        
        $result = array();
        foreach ($projects as $key=>$project) {
            $result[$project->getId()] = array(
                'id' =>   $project->getId(),
                'title' => $project->getTitle(),
                'selected' => in_array($project->getId(), $project_ids)
            );
        }
        return $result;
    }
    
}