<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ReportBundle\Entity\Report;
use Armd\ReportBundle\Form\ReportType;
use Armd\ReportBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;

class ReportActionsController extends ReportController

{
    public function ajaxGetDaysAction()
    {
        $out = '[';
        $nc_days = $this->buildNotClosedDays(strtotime($_GET['begin']));
        foreach($nc_days as $key=>$day)
        {
            $out .= "'".$day."',";
        }
        
        $out .=  ']';
        
        echo str_replace(',]',']',$out);
        exit;
    }
    
    public function searchAction()
    {
        $this->user = $this->container->get('security.context')->getToken()->getUser();

        $request = Request::createFromGlobals();
        if (
                !($request->query->get('project'))      &&
                !($request->query->get('manager'))      &&
                !($request->query->get('subcontract'))  &&
                !($request->query->get('client'))       &&
                !($request->query->get('projectgroup')) &&
                !($request->query->get('text'))         &&
                !($request->query->get('begin'))        &&
                !($request->query->get('end'))          &&
                !($request->query->get('employee')) 
            ) {
                return $this->redirect($this->generateUrl('report'));
        }
        
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $projects_for_filter = array(); // массив проектов для фильтра
        
        /* Выбираем проекты для фильтра по менеджерам */
        $managers = $request->query->get('manager');
        
        if (count($managers) > 0) {
            $repository = $em->getRepository('ArmdProjectBundle:Project');
            $query = $repository->createQueryBuilder('r');
            
            // собираем where
            foreach ($managers as $key=>$manager) {
                $query = $query->orWhere('r.manager = :manager'.$key)->setParameter('manager'.$key, $manager);
            }
            
            $projects = $query->getQuery()->getResult();
        
            foreach ($projects as $project) {
                $projects_for_filter[] = $project->getId();
            }
        }
        /* Конец выборки проектов для фильтра по менеджерам */
        
        /* Выбираем проекты для фильтра по подразделениям */
        $subcontracts = $request->query->get('subcontract');
        
        if (count($subcontracts) > 0) {
            $repository = $em->getRepository('ArmdProjectBundle:Project');
            $query = $repository->createQueryBuilder('r');
            
            $query = $query->innerJoin('r.manager', 'm');
            
            // собираем where
            foreach ($subcontracts as $key=>$subcontract) {
                $query = $query->orWhere('m.department = :subcontract'.$key)->setParameter('subcontract'.$key, $subcontract);
            }
            
            $projects = $query->getQuery()->getResult();
        
            foreach ($projects as $project) {
                $projects_for_filter[] = $project->getId();
            }
        }
        /* Конец выборки проектов для фильтра по подразделениям */
        
        /* Выбираем проекты для фильтра по клиентам */
        $clients = $request->query->get('client');
        
        if (count($clients) > 0) {
            $repository = $em->getRepository('ArmdProjectBundle:Project');
            $query = $repository->createQueryBuilder('r');
            
            // собираем where
            foreach ($clients as $key=>$client) {
                $query = $query->orWhere('r.client = :client'.$key)->setParameter('client'.$key, $client);
            }
            
            $projects = $query->getQuery()->getResult();
        
            foreach ($projects as $project) {
                $projects_for_filter[] = $project->getId();
            }
        
        }
        /* Конец выборки проектов для фильтра по клиентам */
        
        /* Выбираем проекты для фильтра по типам проектов */
        $projectgroups = $request->query->get('projectgroup');
        
        if (count($projectgroups) > 0) {
            $repository = $em->getRepository('ArmdProjectBundle:Project');
            $query = $repository->createQueryBuilder('r');
            
            // собираем where
            foreach ($projectgroups as $key=>$projectgroup) {
                $query = $query->orWhere('r.project_group = :projectgroup'.$key)->setParameter('projectgroup'.$key, $projectgroup);
            }
            
            $projects = $query->getQuery()->getResult();
        
            foreach ($projects as $project) {
                $projects_for_filter[] = $project->getId();
            }
        
        }
        /* Конец выборки проектов для фильтра по типам проектов */
        
        
        $projects = $request->query->get('project');
        
        if ($projects !== null) {
            //$projects = $request->query->get('project');
            if (!is_array($projects)) {
                $projects = array($projects);
            }
        } else {
            $projects = array();
        }

		
        
        if (count($projects) > 0) {
            if (is_array($projects_for_filter)) {
                $projects_for_filter = array_merge($projects_for_filter, $projects);    
            } else {
                $projects_for_filter = $projects;
            }
            
        }
       
        
        //Выбираем отчеты по созданным фильтрам

        $filtered = false; // есть ли условия, по которым фильтровать
        
        $bind = array();
        
        $query_string = 'SELECT r FROM ArmdReportBundle:Report r WHERE ';         
        
        $search_text = $request->query->get('text');

		
        
        if ($search_text != '') {
            $filtered = true;
            $query_string .= ' ( r.description like :search_text ) ';
            $bind['search_text'] = '%'.$search_text.'%';
        }

		if (isset($_GET['task_id']) && ($_GET['task_id'] != '')) {
			
            if ($filtered) {
                $query_string .= ' AND ';
            }
			$query_string .= " r.task = :task_id";
			$bind['task_id'] = $_GET['task_id'];
            $filtered = true;
		}

        if (isset($_GET['begin']) && ($_GET['begin'] != '')) {
			
            if ($filtered) {
                $query_string .= ' AND ';
            }
            $filtered = true;
            $query_string .= ' ( r.day >= :begin ) ';
            $bind['begin'] = date('Y-m-d', strtotime($_GET['begin']));
        }

        if (isset($_GET['end']) && ($_GET['end'] != '')) {
			
            if ($filtered) {
                $query_string .= ' AND ';
            }
            $filtered = true;
            $query_string .= ' ( r.day <= :end ) ';
            $bind['end'] = date('Y-m-d', strtotime($_GET['end']));
        }

        if (isset($_GET['employee']) && count($_GET['employee'])) {
			
            if ($filtered) {
                $query_string .= ' AND ( ';
            } else {
                $query_string .= ' ( ';
            }
            $filtered = true;
            foreach ($_GET['employee'] as $key=>$employee) {
                if ($key > 0) {
                    $query_string .= ' OR ';
                }
                $query_string .= ' ( r.employee = :employee'.$key.' ) ';
                $bind['employee'.$key] = $employee;
            }
           
            $query_string .= ' ) ';
        }


        if (count($projects_for_filter) > 0) {
            
            // собираем where
			if ($filtered) {
                $query_string .= ' AND ';
            }
			$filtered = true;
            $query_string .= ' ( ';
            foreach ($projects_for_filter as $key=>$project) {
                
                $query_string .= ' r.project = '.$project.' ';
                if ($key != (count($projects_for_filter)-1)) {
                    $query_string .= ' or ';
                }
            }
            $query_string .= ' ) ';
        }
        
        $entities = array();
        if ($filtered) {
            if (!$this->user->roleReadAll()) {
                // если пользователь - руководитель подразделения, то ищем по отчетам его сотрудников
                $department_id = $this->isDepartmentBoss();
                if ($department_id) {
                    $employees = $this->getDepartmentEmployees($department_id);
                    if (count($employees)) {
                        $query_string .= ' AND ( ';
                        foreach($employees as $key=>$employee) {
                            $query_string .= ' r.employee = '.$employee->getId();
                            if ($key != (count($employees)-1)) {
                               $query_string .= ' or ';
                            }
                        }
                        $query_string .= ' ) ';
                    }
                    
                } else {
                    $query_string .= ' AND ( r.employee = '.$this->user->getId().' )';
                }
                
                
                
                
            }
            $query_string .= ' ORDER BY r.day DESC ';
            //echo $query_string; exit;
            $query = $em->createQuery($query_string);
            $query = $query->setParameters($bind);
            $entities = $query->getResult(); 

            // если пользователь - менеджер, то ищем по всем сотрудникам этого проекта
            $managed_projects = $this->getManagedProjects();
            if (count($managed_projects)) {
                // выбираем только те проекты, по которым пользователь ищет из тех, в которых он менеджер
                $managed_projects_for_filter = array();
                foreach($managed_projects as $mp) {
                    if (in_array($mp->getId(), $projects)) {
                        $managed_projects_for_filter[] = $mp;
                    }
                }
                
                if (count($managed_projects_for_filter)) {
                    $query_string = 'SELECT r FROM ArmdReportBundle:Report r WHERE ';
                    foreach($managed_projects_for_filter as $key=>$project) {
                        $query_string .= ' r.project = '.$project->getId();
                        if ($key != (count($managed_projects_for_filter)-1)) {
                               $query_string .= ' or ';
                        }
                    }
                    $query = $em->createQuery($query_string);
                    $entities_for_managed_projects = $query->getResult();
                    $entities = array_merge($entities, $entities_for_managed_projects);
                }
            }
            
            // сортируем 
            usort($entities, function($a, $b)
                {
                    if ($a->getDay() == $b->getDay()) return 0;
                    return ($a->getDay() > $b->getDay()) ? -1 : 1;
                }
            ); 
            
            $entities = array_unique($entities, SORT_REGULAR);
            $timestamp_sum = 0;
            foreach ($entities as $id=>$entity) {
                //$timestamp = mktime( 0, $entity->getMinutes() );
                //$timestamp_sum += $timestamp;
                //$entity->setMinutes($timestamp);
                
                $timestamp_sum += $entity->getMinutes();
                $entity->time = explode('.', $entity->getMinutes());
                if (isset($entity->time[1])) {
                    $entity->time[1] = $entity->time[1]*0.06*100;
                    if ($entity->time[1] < 10) {
                        $entity->time[1] = '0'.$entity->time[1];
                    }
                } else {
                    $entity->time[1] = '00';
                }
                
                $entity->time = implode(':', $entity->time);
                
                $entity->setDescription(nl2br($entity->getDescription()));
            }

        }
        
        
        $repository = $em->getRepository('ArmdReportBundle:Report');
        
        $form = $this->createFormBuilder($repository)
            ->getForm();

        list($managers_list, $subcontracts_list, $clients_list, $projectgroups_list, $projects_list) = $this->getFilters();


        return $this->render('ArmdReportBundle:Report:filter.html.twig', array(
            'entities'      => $entities,
            //'days'        => $days,
            'search_form'   => $form->createView(),
            //'projects'    => $projects,
            'subcontracts'  => $subcontracts_list,
            'clients'       => $clients_list,
            'projectgroups' => $projectgroups_list,
            'projects'      => $projects_list,
            'managers'      => $managers_list,
            'user'          => $this->user,
            'post'          => array(
                'client'       => isset($_GET['client']) ?$_GET['client']: array(),
                'subcontract'  => isset($_GET['subcontract']) ?$_GET['subcontract']: array(),
                'projectgroup' => isset($_GET['projectgroup']) ?$_GET['projectgroup']: array(),
                'manager'      => isset($_GET['manager']) ?$_GET['manager']: array(),
                'project'      => isset($_GET['project']) ?$_GET['project']: (isset($_GET['project'])?$_GET['project']:array())),
                'text'         => isset($_GET['text']) ?$_GET['text']: '',
                'begin'        => isset($_GET['begin']) ?$_GET['begin']: '',
                'end'          => isset($_GET['end']) ?$_GET['end']: '',
				 'task_id'          => isset($_GET['task_id']) ?$_GET['task_id']: '',
				'employee'          => isset($_GET['employee']) ?$_GET['employee'][0]: '',
            )
        );
    }


    /**
     * Lists all Report entities.
     *
     */
    public function indexAction()
    {
		//phpinfo();
        $this->user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($this->user) || !$this->user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

		if (isset ($_GET['invalidate'])) {
			$this->mcInvalidate();
		}
		
        if (isset($_GET['day'])) {
            $date = $_GET['day'];
        } else {
            $date = date('d.m.Y');
        }
        
        $days = $this->week_from_monday(date("d-m-Y", strtotime($date)));
        
        for($i=0;$i<5;$i++){
            $this_week[] = date('d.m.Y', $days[$i]['ts']);
        }
        
        //$cache = $this->mcGet($this->user->getId().'_report_actions_index_'.$this_week[0]);
        //print_r($cache);
       /* if ($cache) {
            return $cache;
            exit;
        }*/
        
        $em = $this->getDoctrine()->getEntityManager();
                
        
        
        $repository = $em->getRepository('ArmdReportBundle:Report');
		
		//print_r($days);

        foreach ($days as $key=>$day) {
            
			$query = $repository->createQueryBuilder('r')->where('r.employee=:employee and r.day >= :today and r.day < :tomorrow')->setParameter('employee', $this->user->getId())->setParameter('today', date('Y-m-d', $day['ts']))->setParameter('tomorrow', date('Y-m-d', $day['ts']+86400))->orderBy('r.day', 'desc')->getQuery();
			
            $entities[$day['ts']] = $query->getResult();
            
            // выходные показываем, только если есть отчет
            if ((in_array($key, array(5,6))) || $this->isHoliday(date('d.m.Y', $day['ts']))) {
                $days[$key]['weekend'] = true;
                if (count($entities[$day['ts']])) {
                    
                    $days[$key]['show'] = true;
                } else {
                    $days[$key]['show'] = false;
                }
            } else {
                $days[$key]['weekend'] = false;
                $days[$key]['show'] = true;
            }
            
            $timestamp_sum = 0;
            foreach ($entities[$day['ts']] as $id=>$entity) {
               // $timestamp = mktime( 0, $entity->getMinutes() );
                //$timestamp_sum += $timestamp;
                $timestamp_sum += $entity->getMinutes();
                $entity->time = explode('.', $entity->getMinutes());
                if (isset($entity->time[1])) {
                    $entity->time[1] = $entity->time[1]*0.06*100;
                    if ($entity->time[1] < 10) {
                        $entity->time[1] = '0'.$entity->time[1];
                    }
                } else {
                    $entity->time[1] = '00';
                }
                
                $entity->time = implode(':', $entity->time);
               // $entity->setMinutes($timestamp);
               
               $entity->setDescription(nl2br($entity->getDescription()));
               
            }
            $days[$key]['sum'] = $timestamp_sum;

        }

        $form = $this->createFormBuilder($repository)
            ->getForm();

        list($managers_list, $subcontracts_list, $clients_list, $projectgroups_list, $projects_list) = $this->getFilters();
        
        // отображение ссылок на отчеты
        $show_report_links = false;
        
        $is_manager = $this
            ->getDoctrine()
            ->getEntityManager()
            ->getRepository('ArmdProjectBundle:Project')
            ->findByManager($this->user->getId());
            
        $is_boss = $this
            ->getDoctrine()
            ->getEntityManager()
            ->getRepository('ArmdProjectBundle:Department')
            ->findByBoss($this->user->getId());
        
        
        if ($this->user->roleReadAll() || count($is_manager) || count($is_boss)) {
            $show_report_links = true;
        }
        
		/*print_r("<pre>");
		print_r($days);
		print_r("</pre>");
		
		exit;*/

        $render =  $this->render('ArmdReportBundle:Report:index.html.twig', array(
            'entities' => $entities,
            'days' => $days,
            'search_form' => $form->createView(),
            //'projects' => $projects,
            'subcontracts' => $subcontracts_list,
            'clients' => $clients_list,
            'projectgroups' => $projectgroups_list,
            'projects' => $projects_list,
            'managers' => $managers_list,
            'user' => $this->user,
            'not_closed_days' => $this->buildNotClosedDays(strtotime('01.'.substr($date,3,2).'.'.substr($date,6,4))),
            'this_week' => $this_week,
            'datetime' => $date,
            'show_report_links' => $show_report_links,
			'time' => $this->user->getEmployee()->getTime()
        ));
        
		

        $this->mcSet($this->user->getId().'_report_actions_index_'.$this_week[0], $render, 3600*24);
        
        return $render;
    }

    /**
     * Finds and displays a Report entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdReportBundle:Report')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Report entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArmdReportBundle:Report:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Report entity.
     *
     */
    public function newAction()
    {
        $request = $this->getRequest();
        $day     = $request->query->get('day');
        
        if (!$day) {
            $day = time();
        }
        
        $days = $this->week_from_monday(date("d-m-Y", time()));
        
        for($i=0;$i<7;$i++){
            $this_week[] = date('d.m.Y', $days[$i]['ts']);
        }
   
        $entity = new Report();
		//print_r($this->getUserProjects(false, true));
        $datetime = new \DateTime(); 
        $datetime->setTimestamp($day);
        $entity->setDay($datetime);
        $form   = $this->createForm(new ReportType(), $entity);
        
        return $this->render('ArmdReportBundle:Report:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'not_closed_days' => $this->buildNotClosedDays(strtotime('01.'.date('m.Y',$day))),
            'datetime' => $datetime->format('d.m.Y'),
            'this_week' => $this_week,
            'projects' => $this->getUserProjects(false, true),
            'report_templates' => $this->buildReportTemplates(),
            'report_tasks' => $this->getReportTasks(),
			'time' => $this->user->getEmployee()->getTime()
        ));
    }

    /**
     * Creates a new Report entity.
     *
     */
    public function createAction()
    {
        
        $entity  = new Report();
        $request = $this->getRequest();
        $form    = $this->createForm(new ReportType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            
            $this->user = $this->get('security.context')->getToken()->getUser();
            $employee = $em->getRepository('ArmdProjectBundle:Employee')->find($this->user->getId());
            $entity->setGenerated(0);
            $days = $this->week_from_monday(date("d-m-Y", time()));
            for($i=0;$i<7;$i++){
                $this_week[] = date('d.m.Y', $days[$i]['ts']);
            }
            
			$time = $this->user->getEmployee()->getTime();
            if ($entity->getMinutes() > $time) {
                echo 'Вы не можете указать более '.$time.' час';exit;
            }
            
            $repository = $em->getRepository('ArmdReportBundle:Report');
            $query = $repository->createQueryBuilder('r');
            $query = $query->select('r.day, sum(r.minutes) as sm');
            $query = $query->where('r.employee = :employee')->setParameter('employee', $this->user->getId());
            $query = $query->andWhere('r.day = :day')->setParameter('day', $entity->getDay()->format('Y-m-d'));
            $reports = $query->getQuery()->getResult();
            
            if (isset($reports[0]['sm']) && (($reports[0]['sm']+$entity->getMinutes()) > $time)) {
                echo 'Вы не можете указать более '.$time.' час';exit;
            }
            
            if (!in_array($entity->getDay()->format('d.m.Y'), $this_week)) {
                echo 'Вы можете заполнить отчет только за текущую неделю';exit;
            }
            
            //if (!in_array($entity->getProject()->getId(), $this->getUserProjects(true))) {
            //    echo 'Вы можете заполнить отчет только по проекту с Вашим участием';exit;
            //}
            
            $entity->setEmployee($employee);
            $em->persist($entity);
            $em->flush();
            $this->mcInvalidate();
            echo 'true';exit;
            return $this->redirect($this->generateUrl('report'/*, array('id' => $entity->getId())*/));
        }
        echo 'Проверьте правильность заполнения полей';exit;
        return $this->render('ArmdReportBundle:Report:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Report entity.
     *
     */
    public function editAction($id)
    {
        $this->user = $this->container->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdReportBundle:Report')->findOneBy(array(
                                                                    'id'       => $id,
                                                                    'employee' => $this->user->getId()));

        if (!$entity) {
            echo '<div style="width:100%; height:100%; background: #FFFFFF;" align="center"><br><br><br><br><br><br>Отчет не найден.</div>';exit;
        }
        
        $editForm   = $this->createForm(new ReportType(), $entity);
        $deleteForm = $this->createDeleteForm($id);
        
        $datetime = new \DateTime(); 
        $datetime->setTimestamp($entity->getDay()->getTimestamp());
        
        $days = $this->week_from_monday(date("d-m-Y", time()));
        
        for($i=0;$i<7;$i++){
            $this_week[] = date('d.m.Y', $days[$i]['ts']);
        }
        
        if ($entity->getGenerated() == 1) {
            $days = $this->week_from_monday(date("d-m-Y", strtotime('-7 days')));
            for($i=0;$i<7;$i++){
                $this_week[] = date('d.m.Y', $days[$i]['ts']);
            }
        }

        return $this->render('ArmdReportBundle:Report:edit.html.twig', array(
            'entity'           => $entity,
            'edit_form'        => $editForm->createView(),
            'delete_form'      => $deleteForm->createView(),
            'not_closed_days'  => $this->buildNotClosedDays($datetime->getTimestamp()),
            'datetime'         => $datetime->format('d.m.Y'),
            'this_week'        => $this_week,
            'projects'         => $this->getUserProjects(false, true),
            'report_templates' => $this->buildReportTemplates(),
            'report_tasks'     => $this->getReportTasks(),
            'selected_task'    => $entity->getTask()->getId(),
            'selected_project' => $entity->getProject()->getId(),
            'minutes'          => $entity->getMinutes(),
            'time'             => $this->user->getEmployee()->getTime()
        ));
    }

    /**
     * Edits an existing Report entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ArmdReportBundle:Report')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Report entity.');
        }

        $editForm   = $this->createForm(new ReportType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            
            $this->user = $this->get('security.context')->getToken()->getUser();
            $employee = $em->getRepository('ArmdProjectBundle:Employee')->find($this->user->getId());
            
            $days = $this->week_from_monday(date("d-m-Y", time()));
            for($i=0;$i<7;$i++){
                $this_week[] = date('d.m.Y', $days[$i]['ts']);
            }
            
            if ($entity->getGenerated() == 1) {
                $days = $this->week_from_monday(date("d-m-Y", strtotime('-7 days')));
                for($i=0;$i<7;$i++){
                    $this_week[] = date('d.m.Y', $days[$i]['ts']);
                }
            }
            
            $entity->setEmployee($employee);
            $entity->setGenerated(0);
            
			$time = $this->user->getEmployee()->getTime();
			
            if ($entity->getMinutes() > $time) {
                echo 'Вы не можете указать более '.$time.' час';exit;
            }
            
            $repository = $em->getRepository('ArmdReportBundle:Report');
            $query = $repository->createQueryBuilder('r');
            $query = $query->select('r.day, sum(r.minutes) as sm');
            $query = $query->where('r.employee = :employee')->setParameter('employee', $this->user->getId());
            $query = $query->andWhere('r.day = :day')->setParameter('day', $entity->getDay()->format('Y-m-d'));
            $query = $query->andWhere('r.id != :id')->setParameter('id', $entity->getId());
            
            $reports = $query->getQuery()->getResult();
            
            if (isset($reports[0]['sm']) && (($reports[0]['sm']+$entity->getMinutes()) > $time)) {
                echo 'Вы не можете указать более '.$time.' час';exit;
            }
            
            
            if (!in_array($entity->getDay()->format('d.m.Y'), $this_week)) {
                echo 'Вы можете писать отчеты только за текущую неделю';exit;
            }
            
            //if (!in_array($entity->getProject()->getId(), $this->getUserProjects(true))) {
            //    echo 'Вы не можете писать отчет по этому проекту';exit;
            //}
            
            $em->persist($entity);
            $em->flush();
            $this->mcInvalidate();
            echo 'true';exit;
            return $this->redirect($this->generateUrl('report' /*, array('id' => $id)*/));
        }
        echo 'Проверьте правильность заполнения полей';exit;
        return $this->render('ArmdReportBundle:Report:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Report entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);
		
		$this->mcInvalidate();
		
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('ArmdReportBundle:Report')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Report entity.');
            }

            $em->remove($entity);
            $em->flush();
        }
		
        return $this->redirect($this->generateUrl('report'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
	
	public function profileAction()
	{
		 return $this->redirect($this->generateUrl('report'));
	}
    
    /**
     * Получение доступных пользователю типов работ
     * @return unknown
     */
    protected function getReportTasks()
    {
        if (!is_object($this->user)) {
            $this->user = $this->container->get('security.context')->getToken()->getUser();
        }

        return $this->user->getEmployee()->getPost()->getTask();
    }
}