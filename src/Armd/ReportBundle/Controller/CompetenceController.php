<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ReportBundle\Entity\Report;
use Armd\ReportBundle\Form\ReportType;
use Armd\ReportBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;

class CompetenceController extends ReportController

{
    /*
     * Экшн отчета "Реестр компетенций"
     */
    public function indexAction()
    {
        
        $this->user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        
        // выбираем все теги
        $tags = $em
            ->getRepository('ArmdProjectBundle:Tag')
            ->findAll();
        
        $export_tags = array();
        
        // собираем массив тегов для шаблона
        foreach($tags as $tag) {
            $export_tags[] = array(
                'id' => $tag->getId(),
                'title' => $tag->getTitle(),
                'checked' => in_array($tag->getId(), (isset($_GET['tags'])?$_GET['tags']:array()))
                );
        }
        
        if (isset($_GET['tags'])) {
            // ищем проекты с выбранными тегами
            $projects = $em
                ->getRepository('ArmdProjectBundle:Project')
                ->createQueryBuilder('p')
                ->select('p')
                ->innerJoin('p.tag', 't')
                ->leftJoin('p.project_employee', 'e');
                
            foreach($export_tags as $tag) {
                if ($tag['checked']) {
                    $projects = $projects
                        ->orWhere('t.id = :tag'.$tag['id'])
                        ->setParameter('tag'.$tag['id'], $tag['id']);
                }
            }
    
            $projects = $projects
                ->orderBy('p.begin', 'DESC')
                ->getQuery()
                ->getResult();
            
            // ищем сотрудников найденных проектов
            $employees = $em
                ->getRepository('ArmdReportBundle:Report')
                ->createQueryBuilder('r')
                ->select('e.id, e.surname, e.name, e.patronymic, pst.title as pst_title, d.title as d_title, sum(r.minutes) as sm')
                ->innerJoin('r.employee', 'e')
                ->innerJoin('e.post', 'pst')
                ->innerJoin('e.department', 'd')
                ->innerJoin('r.project', 'p');
                
            foreach($projects as $project) {
                $employees = $employees
                    ->orWhere('p.id = :project_id'.$project->getId())->setParameter('project_id'.$project->getId(), $project->getId());
            }
            
            $employees = $employees
                ->andWhere('e.discharged = 0')
                ->orderBy('sm', 'desc')
                ->groupBy('r.employee')
                ->getQuery()
                ->getResult();
    
            // собираем массив проектов для каждого сотрудника
            // TODO придумать, можно ли это засунуть в выборку выше
            $employee_projects = array();
            foreach($employees as $employee) {
                $employee_projects[$employee['id']] = $em
                    ->getRepository('ArmdReportBundle:Report')
                    ->createQueryBuilder('r')
                    ->select('p.title')
                    ->innerJoin('r.project', 'p');
             
                foreach($projects as $project) {
                    $employee_projects[$employee['id']] = $employee_projects[$employee['id']]
                       ->orWhere('p.id = :project_id'.$project->getId())->setParameter('project_id'.$project->getId(), $project->getId());
                }
                
                $employee_projects[$employee['id']] = $employee_projects[$employee['id']]
                    ->andWhere('r.employee = :employee')->setParameter('employee', $employee['id'])
                    ->groupBy('p.title')
                    ->getQuery()
                    ->getResult();
            }
        } else {
            $projects = array();
            $employees = array();
            $employee_projects = array();
        }
        
        $vars = array(
            'tags'              => $export_tags,        
            'user'              => $this->user,
            'projects'          => $projects,
            'employees'         => $employees,
            'employee_projects' => $employee_projects,
            'breadcrumbs'       => array(
                                        array(
                                            'link'  => $this->generateUrl('report_competence'),
                                            'title' => 'Реестр компетенций'
                                        )
                                    )
        );
        
        if (isset($_GET['export']) && isset($_GET['type'])) {
            if (($_GET['export'] == 'csv') && (in_array((integer)$_GET['type'], array(1,2)))) {
                $template = 'ArmdReportBundle:Report:competence.'.$_GET['type'].'.csv.twig';
                
                switch($_GET['type']) {
                    case 1: $title = 'проекты';break;
                    case 2: $title = 'сотрудники';break;
                }
                
                $this->sendFileToBrowser(
                    iconv('utf-8', (strstr($_SERVER['HTTP_USER_AGENT'], 'Mac')?'MacCyrillic':'windows-1251'),$this->renderView($template, $vars)),
                    'Реестр_компетенций_('.$title.').csv',
                    'csv',
                    'text/csv'
                );
            }
        } else {
            $template = 'ArmdReportBundle:Report:competence.html.twig';
            return $this->render($template, $vars);
        }
    }
    
    
}