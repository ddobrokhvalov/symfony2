<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ReportBundle\Entity\Report;
use Armd\ReportBundle\Form\ReportType;
use Armd\ReportBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;


class ReportProjectController extends ReportController

{
    /*
     * Экшн отчета 
     */
    public function indexAction($by)
    {
        
		$this->user = $this->container->get('security.context')->getToken()->getUser();
		
		if (!$this->isDepartmentBoss() && !$this->isManager() && !$this->isOwner()) {
			echo 'Доступ запрещен';
			exit;
		}
		
        if (isset($_GET['department'])) {
            if (!$this->isInDepartment($_GET['department'])) {
                return $this->redirect($this->generateUrl('report'));
            }
        } /*else {
            if (is_object($this->user->getDepartment())) {
                $_GET['department'] = $this->user->getDepartment()->getId();    
            } else {
                echo 'Для Вашего аккаунта не указано подразделение. Напишите об этой ошибке на <a href="mailto:support-report@armd.ru">support-report@armd.ru</a>.';
                exit;
            }
            
        }*/
		
		
		$this->em = $this->getDoctrine()->getEntityManager();
		$this->repository = $this->em->getRepository('ArmdProjectBundle:Project');
        
		// собираем фильтр сотрудников
		if (!in_array('ROLE_SUPER_ADMIN', $this->user->getRoles())) {
			$employees = $this->getDepartmentEmployees($this->user->getEmployee()->getDepartment()->getId());
		} else {
			$employees = $this->em->getRepository('ArmdProjectBundle:Employee')->findAll();
		}
		foreach ($employees as $key=>$employee) {
			if (isset($_GET['employee']) && ($_GET['employee']) == $employee->getId()) {
				$employees[$key]->selected = true;
			} else {
				$employees[$key]->selected = false;
			}
		}
		
		if (isset($_GET['from'])) {
			$from = strtotime($_GET['from']);
		}
		
		if (isset($_GET['to'])) {
			$to = strtotime($_GET['to']);
		}
		
		if ($by == 'day') {
			if (!isset($from)) {
				if (date('N') == 1) {
					$from = time();
				} else {
					$from = strtotime('last monday');        
				}
			}
			if (!isset($to)) {
				$to = strtotime('+6 days', $from);
			}
		} elseif ($by == 'week') {
			if (!isset($from)) {
				$from = strtotime('first day of this month');
			}
			if (!isset($to)) {
				$to = strtotime('last day of this month');
			}
		} else {
			if (!isset($from)) {
				$from = strtotime('01.01.'.date('Y'));
			}
			if (!isset($to)) {
				$to = strtotime('31.12.'.date('Y'));
			}
		}
		
        // собираем список дней в формате ДД.ММ.ГГГГ
		$current = $from;
		while ($current <= $to) {
			$dates[] = date('d.m.Y', $current);
			$current = strtotime('+1 day', $current);
		}
        
		$export_dates = array();
		foreach ($dates as $date) {
			if ($by == 'day') {
				$export_dates[] = $date;
			} elseif ($by == 'week') {
				if (!in_array(date('W', strtotime($date)), $export_dates)) {
					$export_dates[] = date('W', strtotime($date));
				}
			} else {
				if (!in_array(date('m', strtotime($date)), $export_dates)) {
					$export_dates[] = date('m', strtotime($date));
				}
			}
		}
		
        $query = $this->repository->createQueryBuilder('p')
					->leftJoin('p.manager', 'm');

		if (!in_array('ROLE_SUPER_ADMIN', $this->user->getRoles())) {
			
			// менеджер видит только свои отчеты
			$query =	$query
						->leftJoin('p.owner', 'o')
						->orWhere('m.id = :manager')->setParameter('manager', $this->user->getEmployee())
						->orWhere('o.id = :owner')->setParameter('owner', $this->user->getEmployee());
			
			// начальник подразделения видит проекты, где его подчиненные менеджеры
			if (($department_id = $this->isDepartmentBoss())) {
				foreach ($this->getDepartmentEmployees($department_id) as $key=>$this->employee) {
					$query =	$query
								->orWhere('m.id = :manager'.$key)->setParameter('manager'.$key, $this->employee)
								->orWhere('o.id = :owner'.$key)->setParameter('owner'.$key, $this->employee);
				}
			}
		}
        
        // если выбран фильтр по подразделениям, то дополняем запрос
        if (isset($_GET['department']) && ($_GET['department'] != '')) {
            // само подразделение
            $query = $query->andWhere('m.department = :department')->setParameter('department', $_GET['department']); 
			
            
            // и его дочерние
            $departments_repository = $this->em->getRepository('ArmdProjectBundle:Department');
            $department_parents = $departments_repository->findByParent($_GET['department']);
            foreach ($department_parents as $key=>$department_parent) {
                $query = $query->orWhere('m.department = :department'.$key)->setParameter('department'.$key, $department_parent->getId());
            }
        }
		
		if (isset($_GET['proj_search']) && ($_GET['proj_search'] != '')) {
			$proj_search = $_GET['proj_search'];
			$proj_search = explode(" ", $proj_search);
			$search_string = "";
			foreach ($proj_search as $key => $word){
				$search_string .= ($key == 0 ? " ( " : " and ") . "p.title like '%".$word."%'";
			}
				$search_string .= " ) ";
				$query = $query->andWhere($search_string);
		}

        $query = $query->orderBy('p.title', 'asc');

        $projects = $query->getQuery()->getResult();
		
        $entities = array();
        $full_sum = 0; 
        
        foreach ($projects as $project) {
			
			$project_id = $project->getId();
			
            $repository = $this->em->getRepository('ArmdReportBundle:Report');
            $query = $repository->createQueryBuilder('r');
            // вытаскиваем отчеты за выбранную неделю
            $query = $query->select('r.id, r.day, sum(r.minutes) as sm');
            $query = $query->where('r.project = :project')->setParameter('project', $project_id);
            $query = $query->andWhere('r.day >= :from')->setParameter('from', date('Y-m-d', $from));
            $query = $query->andWhere('r.day <= :to')->setParameter('to', date('Y-m-d', $to));
			
			if (isset($_GET['employee']) && is_numeric($_GET['employee'])) {
				$query = $query->andWhere('r.employee = :employee')->setParameter('employee', $_GET['employee']);
			}
			
            $query = $query->groupBy('r.day');
            $reports = $query->getQuery()->getResult();

            
            $entities[$project_id]['title'] = $project->getTitle();
			$entities[$project_id]['closed'] = !$project->getOpen();
            $entities[$project_id]['sum'] = 0;
            
            foreach($reports as $report) {
                // записываем и суммируем отчеты
				if ($by == 'day') {
					$entities[$project_id][date('d.m.Y',strtotime($report['day']))]['time'] = $report['sm'];
				} elseif ($by == 'week') {
					if (!isset($entities[$project_id][date('W',strtotime($report['day']))]['time'])) {
						$entities[$project_id][date('W',strtotime($report['day']))]['time'] = 0;
					}
					$entities[$project_id][date('W',strtotime($report['day']))]['time'] += $report['sm'];
				} else {
					if (!isset($entities[$project_id][date('m',strtotime($report['day']))]['time'])) {
						$entities[$project_id][date('m',strtotime($report['day']))]['time'] = 0;
					}
					$entities[$project_id][date('m',strtotime($report['day']))]['time'] += $report['sm'];
				}
                
                $entities[$project_id]['sum'] += $report['sm'];
            }
            
            // если отчетов нет, то заполняем пустыми значениями
            foreach ($export_dates as $day) {
				if (!isset($entities[$project_id][$day]['time'])) {
                    $entities[$project_id][$day]['time'] = '0';
				}
            }
			
            $full_sum += $entities[$project_id]['sum'];
            
        }
        
        // считаем суммы отчетов за день для всех сотрудников
        $day_sums = array();
        foreach ($export_dates as $day) {
            $day_sums[$day] = 0;
            foreach ($entities as $entity) {
                $day_sums[$day] += $entity[$day]['time'];
            }
        }
        
        $vars = array(
			'employees'    => $employees,
			'by'           => $by,
			'proj_search'   => (isset($_GET['proj_search'])?$_GET['proj_search']:''),
			'hide_empty'   => isset($_GET['hide_empty']),
			'hide_closed'  => isset($_GET['hide_closed']),
            'entities'     => $entities, // список отчетов
            'human_days'   => $export_dates, // список дней в формате ДД.ММ.ГГГГ
            'from'         => date('d.m.Y', $from), 
            'to'           => date('d.m.Y', $to), 
            'day_sums'     => $day_sums, // суммы по дням
            'full_sum'     => $full_sum, // полная сумма
            'departments'  => $this->buildDepartments((isset($_GET['department'])?$_GET['department']:null)), // список подразделений
            'department'   => (isset($_GET['department'])?$_GET['department']:false), // выбранное подразделение
			'employee'     => (isset($_GET['employee'])?$_GET['employee']:false), // выбранный сотрудник
            'week'         => (isset($_GET['week'])?$_GET['week']:false), // выбранная неделя
            'user'         => $this->user,
            'breadcrumbs'  => array(
                                array(
                                    'link'  => $this->generateUrl('report_project', array('by'=>$by)),
                                    'title' => 'Заполняемость ежедневных отчетов по проектам'
                                )
                            )
        );
		
		if ($by == 'day') {
			$vars['prev_from']  = date('d.m.Y', strtotime('last monday', $from));
            $vars['next_from']  = date('d.m.Y', strtotime('next monday', $from));
			$vars['prev_to']    = date('d.m.Y', strtotime('+6 days', strtotime($vars['prev_from'])));
            $vars['next_to']    = date('d.m.Y', strtotime('+6 days', strtotime($vars['next_from'])));
		} elseif ($by == 'week') {
			$vars['prev_from']  = date('d.m.Y', strtotime('first day of last month', $from));
            $vars['next_from']  = date('d.m.Y', strtotime('first day of next month', $from));
			$vars['prev_to']    = date('d.m.Y', strtotime('last day of last month', $to));
            $vars['next_to']    = date('d.m.Y', strtotime('last day of next month', $to));
		} else {
			$vars['prev_from']  = '01.01.'.(date('Y', $from)-1);
            $vars['next_from']  = '01.01.'.(date('Y', $from)+1);
			$vars['prev_to']    = '31.12.'.(date('Y', $from)-1);
            $vars['next_to']    = '31.12.'.(date('Y', $from)+1);
		}
		
		
        if (isset($_GET['export'])) {
            if ($_GET['export'] == 'csv') {
                $template = 'ArmdReportBundle:Report:report_project.csv.twig';
                $this->sendFileToBrowser(
                    iconv('utf-8', (strstr($_SERVER['HTTP_USER_AGENT'], 'Mac')?'MacCyrillic':'windows-1251'),$this->renderView($template, $vars)),
                    'Заполняемость_ежедневных_отчетов_по_проектам.csv',
                    'csv',
                    'text/csv'
                );
            }
        } else {
            $template = 'ArmdReportBundle:Report:report_project.html.twig';
            return $this->render($template, $vars);
        }
    }
	


}
