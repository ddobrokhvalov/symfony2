<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ReportBundle\Entity\Report;
use Armd\ReportBundle\Form\ReportType;
use Armd\ReportBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;

/**
 * Report controller.
 *
 */
class ReportController extends Controller
{
    protected $user;
    
    protected $holidays;
    protected $rates;
    protected $salary;
    
    protected $cache;
    protected $rates_url = 'http://10.32.17.6:81/employee.xml';
    
    protected $memcache;
    
    protected function mcStart()
    {
        if (!is_object($this->memcache)) {
            $this->memcache = memcache_connect('127.0.0.1');
        }
    }
    
    protected function mcGet($key, $namespace = false)
    {
        if (in_array($_SERVER['HTTP_HOST'], array('k.report.armd.ru', 'm.report.armd.ru', 'a.report.armd.ru'))) {
            return false;
        }
        $this->mcStart();
        return $this->memcache->get(($namespace?:$this->mcGetNamesapace()).'ns_'.$key);
    }
    
    protected function mcSet($key, $var, $expire, $namespace = false)
    {
        if (in_array($_SERVER['HTTP_HOST'], array('k.report.armd.ru', 'm.report.armd.ru', 'a.report.armd.ru'))) {
            return true;
        }
        if (!$expire) $expire = 2592000;
        
        $this->mcStart();
        
        return $this->memcache->set(($namespace?:$this->mcGetNamesapace()).'ns_'.$key, $var, 0, $expire);
    }
    
    protected function mcInvalidate()
    {
        if (!is_object($this->user)) {
            $this->user = $this->container->get('security.context')->getToken()->getUser();
        }
        
        $user_id = $this->user->getId();
        
        $this->mcStart();

        $this->memcache->increment($user_id.'_namespace');
    }
    
    protected function mcGetNamesapace()
    {
        if (!is_object($this->user)) {
            $this->user = $this->container->get('security.context')->getToken()->getUser();
        }
        
        $user_id = $this->user->getId();
        
        $this->mcStart();
        
        $ns_key = $this->memcache->get($user_id.'_namespace');
        
        if ($ns_key === false) {
            $ns_key = rand(1, 10000);
            $this->memcache->set($user_id.'_namespace', $ns_key);
        }
        
        return $ns_key;
    }
    
    protected function week_from_monday($date) {
      // Assuming $date is in format DD-MM-YYYY
      list($day, $month, $year) = explode("-", $date);

      // Get the weekday of the given date
      $wkday = date('l',mktime('0','0','0', $month, $day, $year));

      switch($wkday) {
        case 'Monday': $numDaysToMon = 0; break;
        case 'Tuesday': $numDaysToMon = 1; break;
        case 'Wednesday': $numDaysToMon = 2; break;
        case 'Thursday': $numDaysToMon = 3; break;
        case 'Friday': $numDaysToMon = 4; break;
        case 'Saturday': $numDaysToMon = 5; break;
        case 'Sunday': $numDaysToMon = 6; break;   
      }

      // Timestamp of the monday for that week
      $monday = mktime('0','0','0', $month, $day-$numDaysToMon, $year);

      $seconds_in_a_day = 86400;

      // Get date for 7 days from Monday (inclusive)
      for($i=0; $i<7; $i++)
      {
        $dates[$i]['ts'] = $monday+($seconds_in_a_day*$i);
        $dates[$i]['key'] = $i;
      }

      return $dates;
    }

    /*
     * Получение данных для фильтров
     * @return array
     */
    protected function getFilters()
    {
        $cache = $this->mcGet('report_get_filters_distinct_managers', 'main');
        
        if ($cache) {
            $distinct_managers = $cache;
        }
        
        $em = $this->getDoctrine()->getEntityManager();

        // выбираем из БД подразделения
        $subcontracts_repository = $em->getRepository('ArmdProjectBundle:Department');
        $subcontracts = $subcontracts_repository->findAll();
            
        // выбираем из БД клиентов
        $clients_repository = $em->getRepository('ArmdProjectBundle:Client');
        $clients = $clients_repository->findAll();
        
        // выбираем типы проектов из БД
        $projectgroups_repository = $em->getRepository('ArmdProjectBundle:ProjectGroup');
        $projectgroups = $projectgroups_repository->findAll();
        
        // выбираем проекты из БД
        $projects_repository = $em->getRepository('ArmdProjectBundle:Project');
        $projects = $projects_repository->findAll();
        
        if (!isset($distinct_managers)) {
            $managers_repository = $em->getRepository('ArmdProjectBundle:Project');
            $managers = $managers_repository->createQueryBuilder('p');
            $managers = $managers->innerJoin('p.manager', 'm');
            $managers = $managers->groupBy('p.manager');
            $managers = $managers->getQuery()->getResult();
            
            // как выбрать distinct не понятно, эмулируем
            $distinct_managers = array();
            $distinct_managers_titles = array();
            foreach($managers as $m) {
                
                $manager = $m->getManager();
                if (!in_array($manager, $distinct_managers_titles)) {
                    if (is_object($manager)) {
                        $distinct_managers[] = array('id' => $manager->getId(), 'title'=>$manager->__toString());
                        $distinct_managers_titles[] = $manager;
                    }
                }
            }
        }
        $result = array($distinct_managers, $subcontracts, $clients, $projectgroups, $projects);
        
        $this->mcSet('report_get_filters_distinct_managers', $distinct_managers, 3600*24, 'main');
        
        return $result;
    }
    
    /*
     * Является ли юзер руководителем департамента и его сотрудники - менеджеры проекта
     * @param integer $project_id
     * @retrun bolean
     */
    public function isDepartmentBossAndStaffAreManagers($project_id)
    {

        $this->user = $this->container->get('security.context')->getToken()->getUser();
        
        if ($this->user->roleReadAll()) {
            return true;
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Project');
        if (!isset($this->cache['isDepartmentBossAndStaffAreManagers'])) {
            $this->cache['isDepartmentBossAndStaffAreManagers'] = $repository->findAll();    
        }
        
        foreach($this->cache['isDepartmentBossAndStaffAreManagers'] as $cached_project) {
            if ($cached_project->getId() == $project_id) {
                $project = $cached_project;
            }
        }
        
		$departments = array();
        if (is_object($project->getManager()) &&
			is_object($project->getManager()->getDepartment()) &&
			is_object($project->getManager()->getDepartment()->getBoss()) && 
			($this->user->getEmployee()->getId() == $project->getManager()->getDepartment()->getBoss()->getId())) {
			$departments = $this->getDepartmentList($project->getManager()->getDepartment()->getId());
		}
		
		if (is_object($project->getOwner()) &&
			is_object($project->getOwner()->getDepartment()) &&
			is_object($project->getOwner()->getDepartment()->getBoss()) && 
			($this->user->getEmployee()->getId() == $project->getOwner()->getDepartment()->getBoss()->getId())) {
            $departments = $this->getDepartmentList($project->getOwner()->getDepartment()->getId());
		}
		
		if (count($departments)) {
            $isManager = false;
            
            foreach ($departments as $department) {
                foreach($department['employees'] as $employee) {
					
                    if (is_object($project->getManager()) && ($employee->getId() == $project->getManager()->getId())) {
						$isManager = true;
					} elseif (is_object($project->getSalesManager()) && ($employee->getId() == $project->getSalesManager()->getId())) {
						$isManager = true;
					} elseif (is_object($project->getOwner()) && ($employee->getId() == $project->getOwner()->getId())) {
						$isManager = true;
					}
                }
            }
            return $isManager;
        } else {
            return false;
        }
        
    }
    
    /*
     * Принадлежит ли юзер к отделу
     * @param integer $department_id
     * @return boolean
     */
    public function isInDepartment($department_id = null)
    {

        $this->user = $this->container->get('security.context')->getToken()->getUser();
        
        if ($this->user->roleReadAll()) {
            return true;
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Department');
        
        $departments = $repository->createQueryBuilder('d');
        $departments = $departments->where('d.id = :id')->setParameter('id', $this->user->getDepartment()->getId());
        $departments = $departments->orWhere('d.parent = :parent')->setParameter('parent', $this->user->getDepartment()->getId());
        $departments = $departments->getQuery()->getResult();
        
        foreach($departments as $key=>$department) {
            if ($department->getId() == $department_id) {
                return true;
            }
        }
        return false;
    }
    
    /*
     * Является ли юзер менеджером проекта
     * @param integer $project_id
     * @return boolean
     */
    public function isProjectManager($project_id = null)
    {

        $this->user = $this->container->get('security.context')->getToken()->getUser();
        $user_id = $this->user->getId();
        
        if ($this->user->roleReadAll()) {
            return true;
        }
            
        if (!isset($this->cache['isProjectManager'])) {
            $em = $this->getDoctrine()->getEntityManager();
            $repository = $em->getRepository('ArmdProjectBundle:Project');
            $projects = $repository->createQueryBuilder('p');
            $projects = $projects->select('p.id as pid, m.id as mid, s.id as sid');
            $projects = $projects->leftJoin('p.manager', 'm');
            $projects = $projects->leftJoin('p.sales_manager', 's');
            $projects = $projects->getQuery()->getResult();
            
            foreach ($projects as $project) {
                $db[$project['pid']] = array(
                    'mid' => $project['mid'],
                    'sid' => $project['sid']
                );
            }
            
            $this->cache['isProjectManager'] =  $db;
            $projects = $db;
        } else {
            $projects = $this->cache['isProjectManager'];
        }
        
        return (($projects[$project_id]['mid'] == $user_id) || ($projects[$project_id]['sid'] == $user_id));
    }
    
    /*
     * Получение дерева подразделений
     * @param integer $department_id
     * @return array
     */
    public function getDepartmentTree($department_id) {
        
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Department');
        $departments = $repository->findByParent($department_id);
        $head = $repository->findById($department_id);
        
        $result = array();
        
        $result = array('title'   => $head[0]->getTitle(),
                        'id'      => $head[0]->getId(),
                        'boss'    => is_object($head[0]->getBoss())?$head[0]->getBoss()->__toString():'',
                        'boss_id' => is_object($head[0]->getBoss())?$head[0]->getBoss()->getId():'',
                        'employees' => $this->getDepartmentEmployees($department_id),
                        );
        
        if (count($departments)) {
            foreach($departments as $department) {
                    $result['children'][] = $this->getDepartmentTree($department->getId());
            }
        }
        
        
        return $result;
    }
    
    /*
     * Получение списка сотрудников подразделения
     * @param $integer $department_id
     * @return array
     */
    public function getDepartmentEmployees($department_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Employee');
        return $repository->findByDepartment($department_id);
    }
    
    /*
     * Получение списка подразделений
     * @param integer $department_id
     * @return array
     */
    public function getDepartmentList($department_id) {
        
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Department');
        $departments = $repository->findByParent($department_id);
        $head = $repository->findById($department_id);
        
        $result = array();
        
        $result[] = array('title'   => $head[0]->getTitle(),
                        'id'      => $head[0]->getId(),
                        'boss'    => is_object($head[0]->getBoss())?$head[0]->getBoss()->__toString():'',
                        'boss_id' => is_object($head[0]->getBoss())?$head[0]->getBoss()->getId():'',
                        'employees' => $this->getDepartmentEmployees($department_id),
                        );
        
        if (count($departments)) {
            foreach($departments as $department) {
               $result = array_merge($result,$this->getDepartmentList($department->getId()));
            }
        }
        
        return $result;
    }
    
    
    /*
     * Сборка списка подразделений
     * @param integer $selected выбранное подразделение
     * @return unknown
     */
    public function buildDepartments($selected = null)
    {
        $this->user = $this->container->get('security.context')->getToken()->getUser();
        
        $repository = $this->getDoctrine()->getEntityManager()->getRepository('ArmdProjectBundle:Department');
        
        if ($this->user->roleReadAll()) {
            $departments = array();
            
            $temp_departments = $repository->findByParent('36');
            foreach($temp_departments as $temp_department) {
                $departments[] = $temp_department;
                $temp2_departments = $repository->findByParent($temp_department->getId());
                foreach($temp2_departments as $temp2_department) {
                    $temp2_department->setTitle('&nbsp;&nbsp;'.$temp2_department->getTitle());
                    $departments[] = $temp2_department;
                    $temp3_dpartments = $repository->findByParent($temp2_department->getId());
                    foreach($temp3_dpartments as $temp3_dpartment) {
                        $temp3_department->setTitle('&nbsp;&nbsp;&nbsp;&nbsp;'.$temp3_department->getTitle());
                        $departments[] = $temp3_department;
                    }
                }
            }
        } else {
            $departments =  $repository
                            ->createQueryBuilder('d')
                            ->where('d.id = :id')
                                ->setParameter('id', $this->user->getDepartment()->getId())
                            ->orWhere('d.parent = :parent')
                                ->setParameter('parent', $this->user->getDepartment()->getId())
                            ->getQuery()
                            ->getResult();
                            
            foreach($departments as $key=>$department) {
                if ($department->getParent()->getId() != '36') {
                    $departments[$key]->setTitle('&nbsp;&nbsp;'.$departments[$key]->getTitle());
                }
            }
        }
        
        // помечаем выбранное  
        foreach($departments as $key=>$department) {
            if ($department->getId() == $selected) {
                $department->selected = true;
            } else {
                $department->selected = false;
            }
        }
        
        return $departments;
    }
    
    /*
     * Сборка списка типов проекта
     * @param integer $selected выбранное подразделение
     * @return unknown
     */
    public function buildProjectTypes($selected = null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:ProjectType');
        $project_types = $repository->findAll();
       
        // помечаем выбранное  
        foreach($project_types as $key=>$project_type) {
            if ($project_type->getId() == $selected) {
                $project_type->selected = true;
            } else {
                $project_type->selected = false;
            }
        }
        
        return $project_types;
    }
    
    /*
     * Сборка списка менеджеров
     * @param integer $selected
     * @param EntityManager $em
	 * @param boolean $except_discharged
	 * @param boolean $only_in_department 
     * @return unknown
     */
    public function buildManagers($selected = null, $em = false, $except_discharged = false, $only_in_department = false)
    {
        if (!$em) $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Project');
        $projects = $repository->findAll();
       
        $managers = array();
        
        // помечаем выбранное  
        foreach($projects as $key=>$project) {
            $manager = $project->getManager();
            if (is_object($manager)) {
                $manager_id = $manager->getId();
                if (($except_discharged && ($manager->getDischarged() == 0)) || !$except_discharged) {
                    $managers[$manager_id]['title'] = $manager->__toString();
                    $managers[$manager_id]['id'] = $manager->getId();
					$managers[$manager_id]['project_owner_id'] = is_object($project->getOwner())?$project->getOwner()->getId():false;
                    if ($manager_id == $selected) {
                        $managers[$manager_id]['selected'] = true;
                    } elseif ( isset($managers[$manager_id]['selected']) && ($managers[$manager_id]['selected'] != true)) {
                        $managers[$manager_id]['selected'] = false;
                    } else {
                        $managers[$manager_id]['selected'] = false;
                    }
                }
            }
        }
		
		if ($only_in_department) {
			$department = $this->user->getEmployee()->getDepartment();
			$employees = $this->getDepartmentEmployees($department->getId());
			
			foreach ($managers as $key=>$manager) {
				$in_department = false;
				foreach ($employees as $employee) {
					if (is_object($department->getBoss()) && ($department->getBoss()->getId() == $this->user->getEmployee()->getId())) {
						if (($employee->getId() == $manager['id']) || ($employee->getId() == $manager['project_owner_id'])) {
							$in_department = true;
						}
					} else {
						if ($this->user->getEmployee()->getId() == $manager['id']) {
							$in_department = true;
						}
					}
					
					if ($manager['project_owner_id'] == $this->user->getEmployee()->getId()) {
						$in_department = true;
					}
				}
				if (!$in_department) {
					unset($managers[$key]);
				}
			} 
		}
        return $managers;
    }

     /*
     * Сборка списка менеджеров по продажам
     * @param integer $selected
     * @param EntityManager $em
     * @param boolean $except_discharged
     * @param boolean $only_in_department 
     * @return unknown
     */
    public function buildSalesManagers($selected = null, $em = false, $except_discharged = false, $only_in_department = false)
    {
        if (!$em) $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Project');
        $projects = $repository->findAll();
       
        $managers = array();
        
        // помечаем выбранное  
        foreach($projects as $key=>$project) {
            $manager = $project->getSalesManager();
            if (is_object($manager)) {
                $manager_id = $manager->getId();
                if (($except_discharged && ($manager->getDischarged() == 0)) || !$except_discharged) {
                    $managers[$manager_id]['title'] = $manager->__toString();
                    $managers[$manager_id]['id'] = $manager->getId();
                    $managers[$manager_id]['project_owner_id'] = is_object($project->getOwner())?$project->getOwner()->getId():false;
                    if ($manager_id == $selected) {
                        $managers[$manager_id]['selected'] = true;
                    } elseif ( isset($managers[$manager_id]['selected']) && ($managers[$manager_id]['selected'] != true)) {
                        $managers[$manager_id]['selected'] = false;
                    } else {
                        $managers[$manager_id]['selected'] = false;
                    }
                }
            }
        }
        
        if ($only_in_department) {
            $department = $this->user->getEmployee()->getDepartment();
            $employees = $this->getDepartmentEmployees($department->getId());
            
            foreach ($managers as $key=>$manager) {
                $in_department = false;
                foreach ($employees as $employee) {
                    if (is_object($department->getBoss()) && ($department->getBoss()->getId() == $this->user->getEmployee()->getId())) {
                        if (($employee->getId() == $manager['id']) || ($employee->getId() == $manager['project_owner_id'])) {
                            $in_department = true;
                        }
                    } else {
                        if ($this->user->getEmployee()->getId() == $manager['id']) {
                            $in_department = true;
                        }
                    }
                    
                    if ($manager['project_owner_id'] == $this->user->getEmployee()->getId()) {
                        $in_department = true;
                    }
                }
                if (!$in_department) {
                    unset($managers[$key]);
                }
            } 
        }
        return $managers;
    }
    
    /*
     * Сборка списка клиентов
     * @param integer $selected 
     * @return unknown
     */
    public function buildClients($selected = null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Project');
        $projects = $repository->findAll();
       
        $clients = array();
        
        // помечаем выбранное  
        foreach($projects as $key=>$project) {
            if (is_object($project->getClient())) {
                $clients[$project->getClient()->getId()]['title'] = $project->getClient()->__toString();
                $clients[$project->getClient()->getId()]['id'] = $project->getClient()->getId();
                if ($project->getClient()->getId() == $selected) {
                    $clients[$project->getClient()->getId()]['selected'] = true;
                } elseif ( isset($client[$project->getClient()->getId()]['selected']) && ($manager[$project->getClient()->getId()]['selected'] != true)) {
                    $clients[$project->getClient()->getId()]['selected'] = false;
                } else {
                    $clients[$project->getClient()->getId()]['selected'] = false;
                }
            }
        }

        return $clients;
    }
    
    
    
    
    /*
     * Дни с незаполнеными отчетами
     * @param integer $begin дата начала
     * @param integer $end дата окончания
     * @return array
     */
    public function buildNotClosedDays($begin = null)
    {
        if ($begin === null) $begin = strtotime('-1 month');
        $end   = strtotime('+1 months', $begin);
        $this->user = $this->get('security.context')->getToken()->getUser();
        
        $cache = $this->mcGet($this->user->getId().'_report_build_not_closed_days_'.$begin);
        if ($cache) {
            return $cache;
        }
        
        $repository = $this->getDoctrine()->getEntityManager()->getRepository('ArmdReportBundle:Report');
        
        $reports = $repository
            ->createQueryBuilder('r')
            ->select('r.day, sum(r.minutes) as sm')
            ->where('r.employee = :employee')
                ->setParameter('employee', $this->user->getId())
            ->andWhere('r.day >= :begin')
                ->setParameter('begin', date('Y-m-d', $begin))
            ->andWhere('r.day <= :end')
                ->setParameter('end', date('Y-m-d', $end))
            ->groupBy('r.day')
            ->getQuery()->getResult();
        
        $start_date = $begin;
        $days = array();
        while ($start_date < $end) {
            $days[] = date('d.m.Y', $start_date);
            $start_date = strtotime('+1 day', $start_date);
        }
       
        
        foreach($days as $key=>$day) {
            foreach($reports as $report) {
                if (
                    ( (strtotime($report['day']) == strtotime($day)) && ($report['sm'] >= 8) ) ||
                   ( date('w',strtotime($day)) == 6) || ( date('w',strtotime($day)) == 0)
                   || ($this->isHoliday($day))
                   ){
                    unset($days[$key]);
                }
            }
        }
       
       
        $this->mcSet($this->user->getId().'_report_build_not_closed_days_'.$begin, $days, 3600*24);
        return $days;
    }
    
    /**
     * Является ли день нерабочим
     * @param string $day
     * @return boolean
     */
    public function isHoliday($day)
    {
        if (!is_array($this->holidays)) {
            $this->holidays = $this->getHolidays();
        }
        
        return in_array($day,$this->holidays);
    }
    /**
     * Список нерабочих дней
     * @return array
     */
    public function getHolidays()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Holiday');
        $holidays = $repository->findAll();
        
        $result = array();
        foreach($holidays as $holiday){
            $result[] = $holiday->getDay()->format('d.m.Y');    
        }
        return $result;
    }
    
    
    
    
    
    /*
     * Проекты, доступные юзеру
     * @param boolean $only_ids только одномерный массив идентификаторов
     * @param boolean $all все проекты
     * @return array
     */
    public function getUserProjects($only_ids = false, $all = false)
    {
        $this->user =   $this
                        ->container
                        ->get('security.context')
                        ->getToken()
                        ->getUser();
                        
        $projects   =   $this
                        ->getDoctrine()
                        ->getEntityManager()
                        ->getRepository('ArmdProjectBundle:Project')
                        ->findAll();
        
        $result     = array();
        $result_ids = array();
        
        // проекты пользователя
        foreach($projects as $key=>$project) {
            foreach($project->getProjectEmployee() as $employee) {
                if ($employee->getEmployee()->getId() == $this->user->getId()) {
					$result_ids[] = $project->getId();
                    $result[] = array(
                    	'title' => $project->getTitle(),
                        'id'    => $project->getId()
                    );
                }
            }
        }
        
        if ($all) {
            $result[] = array(
                'title' => '---------------',
                'id'    => ''
            );
            
            // остальные проекты
            foreach($projects as $key=>$project) {
                if (!in_array($project->getId(), $result_ids)) {
                    $result_ids[] = $project->getId();
                    $result[] = array(
                        'title' => $project->getTitle(),
                        'id'    => $project->getId()
                    );
                }
            }
        }
        
        if ($only_ids) {
        	return $result_ids;
        } else {
        	return $result;
        }
    }
    
    /**
     * Сборка шаблонов отчетов
     * @return array
     */
    public function buildReportTemplates()
    {
        $report_templates = $this
                            ->getDoctrine()
                            ->getEntityManager()
                            ->getRepository('ArmdProjectBundle:ReportTemplate')
                            ->findAll();
                            
        foreach ($report_templates as $key=>$value) {
            $report_templates[$key]
                ->setBody(
                    str_replace("\r", '\n',
                        str_replace("\n", '', $report_templates[$key]->getBody())
                    )
                );
        }
        return $report_templates;
    }
    
    
    
    /**
     * Сборка массивов ставок
     */
    protected function buildRates()
    {
		/*
		// получаем ставки сотрудников с сервиса
        if (!isset($this->rates)) {
						
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $this->container->getParameter('rates.url')); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
			curl_setopt($ch, CURLOPT_USERPWD, $this->container->getParameter('rates.login').':'.$this->container->getParameter('rates.password'));
            $body = curl_exec($ch);
            curl_close($ch); 
			
            $xml = simplexml_load_string($body);
            
            // собираем массив версий ставок вида
            //[1234]=> // id пользователя
            //    array(1) { 
            //      [0]=>
            //      array(3) {
            //        ["begin"]=>
            //        int(1230757200)
            //        ["end"]=>
            //        int(1262206800)
            //        ["category"]=>
            //        string(1) "2"
            //      }
            //    }
			
            foreach($xml as $employee) {
                $e_attr = $employee->attributes();
				if ($employee->Discharged == 1) {
					$this->rates[(integer)$e_attr['ID']->__toString()][] = array(
						'begin' => 0,
						'end' => 30000000000,
						'category'=>$employee->CategoryDischarged->__toString());
				} else {
					foreach ($employee->Category as $category) {
						$attr = $category->attributes();
						$this->rates[(integer)$e_attr['ID']->__toString()][] = array(
							'begin' => ($attr['begin']?strtotime($attr['begin']->__toString()):0),
							'end' => ($attr['end']?strtotime($attr['end']->__toString()):30000000000),
							'category'=>$category->__toString());
					}
				}
            }
			
        }
		*/

        // получаем информацию о ставках из БД
        if (!isset($this->salary)) {
			
			$ini = parse_ini_file(dirname(__FILE__).'/../../../../app/config/parameters.ini');		
		
			$host="localhost";
			$user=$ini['database_user'];
			$pwd=$ini['database_password'];
			$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');
			
			mysql_select_db($ini['database_name'],$db);

			$salaries = mysql_select("select * from Rate");
			//print_r($salaries);
            //$em = $this->getDoctrine()->getEntityManager();
            //$salaries = $em->getRepository('ArmdProjectBundle:Rate')->findAll();
            foreach($salaries as $salary) {
                //$this->salary[$salary->getTitle()] = $salary->getSalary();
				$this->salary[$salary['title']] = $salary['salary'];
            }
				
        }
		
    }
    
    /**
     * Получение ставки сотрудника в конкретный день
     * @param integer $employee_id
     * @param integer $date
     * @return integer
     */
    protected function getRateByDate($employee_id, $date)
    {
        $this->buildRates();
		
		 // для субподрядчика ставка 0
       /* $em = $this->getDoctrine()->getEntityManager();
        $employee = $em->getRepository('ArmdProjectBundle:Employee')->findOneById($employee_id);
        
        if ($employee->getSubcontractor()) {
        	return 0;
        }
		*/
		$host="10.32.17.6";
		$user="employe";
		$pwd="Oongee6m";
		$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');
		
		mysql_select_db("employe",$db);
		
		if (date("Ymd",$date) == date("Ymd", time())){
		$rate = mysql_select("select category, discharged, outside  from employees where employee_id = ".$employee_id);
		}else{
		$rate = mysql_select("select category, discharged, outside  from employees_history where employee_id = ".$employee_id." and date like '".date("Ymd",$date)."%'");
		}
		
		
			if (count($rate) > 0){
				if ($rate[0]['discharged'] == 1){
					$rate = mysql_select("select category, discharged, outside, date from employees_history where employee_id = ".$employee_id." and category > 0 order by date desc");
					$rate = $rate[0]['category'];
				}else{
					$rate = $rate[0]['category'];
				}
			}else{
				$rate = mysql_select("select category, discharged, outside from employees where employee_id = ".$employee_id);
				if (count($rate) > 0){
					if ($rate[0]['discharged'] == 1){
						$rate = mysql_select("select category, discharged, outside, date from employees_history where employee_id = ".$employee_id." and category > 0 order by date desc");
						$rate = $rate[0]['category'];
					}else{
						$rate = $rate[0]['category'];
					}
				}else{
					$rate = 0;
				}
			}
		
		mysql_close($db);

		return $rate;
        
       
       /* 
        if (isset($this->rates[$employee_id])) {
            foreach($this->rates[$employee_id] as $rate) {
                if (($date >= $rate['begin']) && ($date <= $rate['end'])) {
                    return $rate['category'];
                }
            }
        }
        
        return 0;
		*/
    }
    
    /**
     * Получение стоимости отчетов сотрудника по дате
     * @param integer $employee_id
     * @param integer $date
     * @param float $time
     * @return float Сумма
     */
    protected function getReportSum($employee_id, $date, $time)
    {
        $this->buildRates();
        
    	// для субподрядчика ставка 0
        $em = $this->getDoctrine()->getEntityManager();
        $employee = $em->getRepository('ArmdProjectBundle:Employee')->findOneById($employee_id);
        
        if ($employee->getSubcontractor()) {
        	return 0;
        }
        
		$host="10.32.17.6";
		$user="employe";
		$pwd="Oongee6m";
		$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');
		
		mysql_select_db("employe",$db);

		$rate = mysql_select("select category, discharged, outside from employees_history where employee_id = ".$employee_id." and date like '".date("Ymd",$date)."%'");
		
			if (count($rate) > 0){
				$rate = $rate[0]['category'];
			}else{
				$rate = 0;
			}
		
		
		mysql_close($db);

		return $time * $this->salary[$rate];
        
       /* if (isset($this->rates[$employee_id])) {
            foreach($this->rates[$employee_id] as $rate) {
                if (($date >= $rate['begin']) && ($date <= $rate['end'])) {
                    return $time * $this->salary[$rate['category']];
                }
            }
        }

        return 0;*/
    }
    
     /*
     * Получение плановой себестоимости проекта
     * @param integer $project_id
     * @return float сумма
     */
    protected function getPlanCost($project_id)
    {
        $this->buildRates();
        
        $project =  $this
                    ->getDoctrine()
                    ->getEntityManager()
                    ->getRepository('ArmdProjectBundle:Project')
                    ->findOneById($project_id);
        
        $ratio_subcontract        = $project->getRatioSubcontract();
        $ratio_inside             = $project->getRatioInside();
        $ratio_bonus              = $project->getRatioBonus();
        $ratio_outsourcing        = $project->getRatioOutsourcing();
        $other_cost               = $project->getOtherCost();
        
        $total_sum                = 0;
        $subcontracts_total_sum   = 0;

        $employees                = array();
        
        foreach($project->getProjectEmployee() as $employee) {
            if (is_object($employee->getEmployee())) {
                $total_sum +=   $this->salary[$this->getRateByDate(
                                    $employee->getEmployee()->getId(),
                                    $project->getBegin()->getTimestamp()
                                )] * $employee->getHours();
            }
        }

        foreach($project->getProjectSubcontract() as $subcontract) {
            $subcontracts_total_sum += $subcontract->getSubcontract()->getSalary() * $subcontract->getHours();
        }

        return
            ($ratio_inside * $total_sum) +
            $other_cost +
            ($subcontracts_total_sum * $ratio_subcontract) +
            ($total_sum * $ratio_bonus) +
            ($subcontracts_total_sum * $ratio_subcontract * $ratio_outsourcing);
    }
    
    /**
     * Скачать файл на компьютер пользователю
     *
     * @param unknown_type $data
     * @param unknown_type $file_name
     * @param unknown_type $file_type
     * @param unknown_type $file_content_type
     */
    public function sendFileToBrowser($data, $file_name, $file_ext = '', $file_content_type = '')
    {                
        if (!$file_content_type) {
            switch ( strtolower($file_ext) ) {
        		case 'tif':
        		    $file_content_type = 'image/tiff';
        			break;
        		case 'doc':
        		case 'docx':
        		case 'rtf':
        			header('Content-Type: application/msword;');
        			$file_content_type = 'application/msword';
        			break;
        		case 'pdf':
        			$file_content_type = 'application/pdf';
        			break;
        		case 'xls':
        			$file_content_type = 'application/vnd.ms-excel';
        			break;
        		case 'jpg':
        		case 'jpeg':        			
        			$file_content_type = 'image/jpeg';
        			break;
        		case 'xml':        			
        			$file_content_type = 'application/xml';
        			break;
        		case 'bmp':        			
        			$file_content_type = 'image/bmp';
        			break;
        		case 'vsd':        			
        			$file_content_type = 'application/vnd.visio';
        			break;
        		case 'txt':
        		case 'ini':        			
        			$file_content_type = 'text/plain';
        			break;
        		case 'png':        			
        			$file_content_type = 'image/png';
        			break;
        		case 'svg':        			
        			$file_content_type = 'image/svg+xml';
        			break;
        		case 'gif':
        		    $file_content_type = 'image/gif';        			
        			break;
        		case 'odt':        			
        			$file_content_type = 'application/odt';
        			break;
        		case 'rar':        			
        			$file_content_type = 'application/x-rar-compressed';
        			break;
        		case 'zip':        			
        			$file_content_type = 'archive/zip';
        			break;
        		default:        			
        			$file_content_type = 'application/octet-stream';
        	}
        }
        ob_clean();
    	header('Content-Type: ' . $file_content_type . ';');
    	header('Content-Description: File Transfer');
		if ( preg_match( "/MSIE/", $_SERVER["HTTP_USER_AGENT"] ) ) {
			header('Content-Disposition: attachment; filename=' . str_replace('+', ' ', urlencode($file_name)) );
		} else {
			header('Content-Disposition: attachment; filename=' . $file_name );
		}
		header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
		header('Content-Length: ' . strlen($data) );
		
  		
        echo $data;
        exit();
    }
    
    /**
     * Является ли пользователь руководителем
     * @return integer id подразделения либо false
     */
    public function isDepartmentBoss()
    {
		$this->user = $this->container->get('security.context')->getToken()->getUser();
		
		if ($this->user->roleReadAll()) {
            return true;
        }
               
        $boss = $this
            ->getDoctrine()
            ->getEntityManager()
            ->getRepository('ArmdProjectBundle:Department')
            ->findByBoss($this->user->getId());
        
        if (count($boss)) {
            return $boss[0]->getId();
        } else {
            return false;
        }
    }
	
	/**
     * Является ли пользователь менеджером
     * @return integer id проекта либо false
     */
    public function isManager()
    {
        $this->user = $this->container->get('security.context')->getToken()->getUser();
        
		if ($this->user->roleReadAll()) {
            return true;
        }
		
        $manager = $this
            ->getDoctrine()
            ->getEntityManager()
            ->getRepository('ArmdProjectBundle:Project')
            ->findByManager($this->user->getId());
        
        if (count($manager)) {
            return $manager[0]->getId();
        } else {
            return false;
        }
    }
    
    /**
     * Получение проектов, в которых пользователь - менеджер
     * @return array
     */
    public function getManagedProjects()
    {
        $this->user = $this->container->get('security.context')->getToken()->getUser();
                
        $manager = $this
            ->getDoctrine()
            ->getEntityManager()
            ->getRepository('ArmdProjectBundle:Project')
            ->findByManager($this->user->getId());
        $sales = $this
            ->getDoctrine()
            ->getEntityManager()
            ->getRepository('ArmdProjectBundle:Project')
            ->findBy(array('sales_manager'=>$this->user->getId()));
        return array_merge($manager, $sales);
    }
	
	/**
	 * Является ли пользователь хозяином проекта
	 * @param integer $project_id 
	 * @return boolean
	 */
	public function isProjectOwner($project_id)
	{
		$this->user = $this->container->get('security.context')->getToken()->getUser();
		
		if ($this->user->roleReadAll()) {
            return true;
        }
               
        $owner = $this
            ->getDoctrine()
            ->getEntityManager()
            ->getRepository('ArmdProjectBundle:Project')
            ->findBy(array('owner' => $this->user->getId(), 'id' => $project_id));
        
        if (count($owner)) {
            return true;
        } else {
            return false;
        }
	}
	
	/**
	 * Является ли пользователь хозяином какого-либо проекта
	 * @return boolean
	 */
	public function isOwner()
	{
		$this->user = $this->container->get('security.context')->getToken()->getUser();
		
		if ($this->user->roleReadAll()) {
            return true;
        }
               
        $owner = $this
            ->getDoctrine()
            ->getEntityManager()
            ->getRepository('ArmdProjectBundle:Project')
            ->findBy(array('owner' => $this->user->getId()));
        
        if (count($owner)) {
            return true;
        } else {
            return false;
        }
	}

    /* 
     * Получение всех сотрудников
     * @param $selected integer выбранный сотрудник
     */
    public function getEmployees($selected = 0)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Employee');
        $employees  = $repository->findAll();

        foreach($employees as $key=>$employee) {
            if ($employee->getId() == $selected) {
                $employee->selected = true;
            } else {
                $employee->selected = false;
            }
        }

        return $employees;
    }

    /* 
     * Получение всех проектов
     * @param $selected integer выбранный проект
     */
    public function getProjects($selected = 0)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Project');
        $projects  = $repository->findAll();

        foreach($projects as $key=>$project) {
            if ($project->getId() == $selected) {
                $project->selected = true;
            } else {
                $project->selected = false;
            }
        }

        return $projects;
    }

    /* 
     * Получение всех типов работ
     * @param $selected integer выбранный тип работ
     */
    public function getTasks($selected = 0)
    {
        $em         = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Task');
        $tasks  = $repository->findAll();

        foreach($tasks as $key=>$task) {
            if ($task->getId() == $selected) {
                $task->selected = true;
            } else {
                $task->selected = false;
            }
        }

        return $tasks;
    }
	
}

function mysql_select($query)
{
	$res = array();
	$result = mysql_query($query);
	
	while ($row = mysql_fetch_assoc($result)) {
		$res[] = $row;
	}
	return $res;
}