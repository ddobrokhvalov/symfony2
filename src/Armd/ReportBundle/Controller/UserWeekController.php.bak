<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ReportBundle\Entity\Report;
use Armd\ReportBundle\Form\ReportType;
use Armd\ReportBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;

class UserWeekController extends ReportController

{
    /*
     * Экшн отчета по неделям
     */
    public function indexAction()
    {
        $this->user = $this->container->get('security.context')->getToken()->getUser();
        
		if (!$this->isDepartmentBoss() && !$this->isManager()) {
			echo 'Доступ запрещен';
			exit;
		}
		
        if (isset($_GET['department']) && ($_GET['department'] != '')) {
            if (!$this->isInDepartment($_GET['department'])) {
                return $this->redirect($this->generateUrl('report'));
            }
        } elseif(isset($_GET['department']) && ($_GET['department'] == '')){
        	unset($_GET['department']);
    	} else {
            $_GET['department'] = $this->user->getDepartment()->getId();
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Employee');
        
        // если не заданы даты, собираем текущие
        if (isset($_GET['begin']) && isset($_GET['end'])) {
            $from = strtotime($_GET['begin']);
            $to = strtotime($_GET['end']);
        } else {
            $from = strtotime('first day of this month');
            $to = strtotime('last day of this month');
        }
        
        // собираем список дней
        $operating_from = $from;
        $days = array();
        $week_mon_and_sun = array();
        while ($operating_from <= $to) {
            $days[] = array (   'ts' => $operating_from,
                                'human' => date('d.m.Y',$operating_from));
            $weeks[date('W',$operating_from)] = date('W',$operating_from);
            $week_mon_and_sun[date('W',$operating_from)]['mon'] = date('d.m.Y', strtotime(date('o-\\WW', $operating_from)));
            $week_mon_and_sun[date('W',$operating_from)]['sun'] = date('d.m.Y', strtotime('next sunday', strtotime($week_mon_and_sun[date('W',$operating_from)]['mon'])));
            $operating_from = strtotime('+1 day', $operating_from);

        }
        
        
        $query = $repository->createQueryBuilder('e');
        
        // если выбран фильт по подразделениям, то дополняем запрос затрудников
        if (isset($_GET['department'])) {
            // выбранное подразделение
            $query = $query->where('e.department = :department99999')->setParameter('department99999', $_GET['department']);
            
            if (isset($_GET['hide_discharged'])) {
                $query = $query->andWhere('e.discharged = :discharged')->setParameter('discharged', 0);
            }
            
            // и дочерние            
            $departments_repository = $em->getRepository('ArmdProjectBundle:Department');
            $department_parents = $departments_repository->findByParent($_GET['department']);
            foreach ($department_parents as $key=>$department_parent) {
                $query = $query->orWhere('e.department = :department'.$key)->setParameter('department'.$key, $department_parent->getId());
            }
        }

		if (isset($_GET['employee']) && ($_GET['employee'] != '')) {
			 $query = $query->andWhere('e.id = :employee_id')->setParameter('employee_id', $_GET['employee']); 
		}

		if (isset($_GET['employee_search']) && ($_GET['employee_search'] != '')) {
			$empl_search = $_GET['employee_search'];
			$empl_search = explode(" ", $empl_search);
			$search_string = "";
			foreach ($empl_search as $key => $word){
				//echo $key . " => " . $word;
				$search_string .= ($key == 0 ? " ( " : " and ") . " ( e.surname like '%".$word."%' or e.name like '%".$word."%' or e.patronymic like '%".$word."%' ) ";
			}
			
			$search_string .= " ) ";

			//echo $search_string;
			$query = $query->andWhere($search_string);
		}

        $query = $query->orderBy('e.surname', 'asc');

        $employees = $query->getQuery()->getResult();
        $entities = array();
        $full_sum = 0;
        
       
        $week_working_hours = array();
        
        foreach ($employees as $employee) {
			 // считаем кол-во рабочих часов в неделю
            foreach($days as $day) {
				$week = date('W',$day['ts']);
				if (!isset($week_working_hours[$employee->getId()][$week])) {
					$week_working_hours[$employee->getId()][$week] = 0;
				}

				if (!$this->isHoliday($day['human']) && !in_array(date('N',$day['ts']), array(6,7))) {
					$week_working_hours[$employee->getId()][$week] += $employee->getTime();
				}
			}
			
            $repository = $em->getRepository('ArmdReportBundle:Report');
            $query = $repository->createQueryBuilder('r');
            // вытаскиваем отчеты за выбранный период
            $query = $query->select('r.id, r.day, sum(r.minutes) as sm');
            $query = $query->where('r.employee = :employee')->setParameter('employee', $employee->getId());
            $query = $query->andWhere('r.day >= :from')->setParameter('from', date('Y-m-d', strtotime('now', $from)));
            $query = $query->andWhere('r.day <= :to')->setParameter('to', date('Y-m-d', strtotime('now', $to)));
            $query = $query->groupBy('r.day');

            if (isset($_GET['project']) && ($_GET['project'] != '')) {
				$query = $query->andWhere('r.project = :project_id')->setParameter('project_id', $_GET['project']); 
			}

			if (isset($_GET['manager']) && ($_GET['manager'] != '')) {
				$query = $query->join('r.project', 'p');
				$query = $query->andWhere('p.manager = :manager_id')->setParameter('manager_id', $_GET['manager']); 
			}

			if (isset($_GET['sales']) && ($_GET['sales'] != '')) {
				$query = $query->join('r.project', 'p');
				$query = $query->andWhere('p.sales_manager = :sales_id')->setParameter('sales_id', $_GET['sales']); 
			}

			if (isset($_GET['task']) && ($_GET['task'] != '')) {
				$query = $query->andWhere('r.task = :task_id')->setParameter('task_id', $_GET['task']); 
			}

            $reports = $query->getQuery()->getResult();

            $entities[$employee->getId()]['employee'] = $employee->__toString();
            $entities[$employee->getId()]['employee_id'] = $employee->getId();
            $entities[$employee->getId()]['discharged'] = (integer)$employee->getDischarged();
            $entities[$employee->getId()]['sum'] = 0;
            $entities[$employee->getId()]['id'] = $employee->getId();
			
            // считаем суммы
            foreach($reports as $report) {
                if (!isset($entities[$employee->getId()][date('W',strtotime($report['day']))])) {
                    $entities[$employee->getId()][date('W',strtotime($report['day']))] = $report['sm'];    
                } else {
                    $entities[$employee->getId()][date('W',strtotime($report['day']))] += $report['sm'];
                }
                $entities[$employee->getId()]['sum'] += $report['sm'];
            }
            
            foreach ($days as $day) {
                if (!isset($entities[$employee->getId()][date('W', $day['ts'])])) {
                    $entities[$employee->getId()][date('W', $day['ts'])] = '0';
                }
                
            }
            
			foreach($entities[$employee->getId()] as $key=>$value) {
				if (intval($key) != 0) {
					if ($value < $week_working_hours[$employee->getId()][$key]) {
						$entities[$employee->getId()]['not_full'][$key] = true;
					} else {
						$entities[$employee->getId()]['not_full'][$key] = false;
					}
				}
				
			}
			
            $full_sum += $entities[$employee->getId()]['sum'];
            
        }
        
        // считаем суммы по всем сотрудникам за каждую неделю
        $week_sums = array();
        foreach ($weeks as $week) {
            $week_sums[$week] = 0;
            foreach ($entities as $entity) {
                $week_sums[$week] += $entity[$week];
            }
        }

        $vars = array(
			'employees'          => $this->getEmployees((isset($_GET['employee'])?$_GET['employee']:'0')),
			'employee_search'   => (isset($_GET['employee_search'])?$_GET['employee_search']:''),
			'projects'           => $this->getProjects((isset($_GET['project'])?$_GET['project']:'0')),
			'tasks'              => $this->getTasks((isset($_GET['task'])?$_GET['task']:'0')),
			'managers'           => $this->buildManagers((isset($_GET['manager'])?$_GET['manager']:'0')),
			'saleses'            => $this->buildSalesManagers((isset($_GET['sales'])?$_GET['sales']:'0')),
			'week_mon_and_sun'   => $week_mon_and_sun,
			'hide_empty'         => isset($_GET['hide_empty']),
			'entities'           => $entities, // список записей
			'weeks'              => $weeks, // список недель
			'week_working_hours' => $week_working_hours, // кол-во рабочих часов в неделю
			'from'               => date('d.m.Y', $from), // начало периода
			'to'                 => date('d.m.Y', $to), // конец периода
			'prev_month_from'    => date('d.m.Y', strtotime('first day of last month', $from)), // начало периода для предыдущего месяца
			'prev_month_to'      => date('d.m.Y', strtotime('last day of last month', $from)), // конец периода для предыдущего месяца
			'next_month_from'    => date('d.m.Y', strtotime('first day of next month', $from)), // начало периода для следующего месяца
			'next_month_to'      => date('d.m.Y', strtotime('last day of next month', $from)), // конец периода для следующего месяца
			'week_sums'          => $week_sums, // суммы по неделям
			'full_sum'           => $full_sum, // общая сумма
			'departments'        => $this->buildDepartments((isset($_GET['department'])?$_GET['department']:null)), // список подразделений
			'department'         => (isset($_GET['department'])?$_GET['department']:false), // выбранное подразделение
			'user'               => $this->user,
			'breadcrumbs'        => array(
	                                    array(
	                                        'link'  => $this->generateUrl('report_user_week'),
	                                        'title' => 'Заполняемость ежедневных отчетов по сотрудникам'
	                                    )
                                	)
        );
        
        if (isset($_GET['export'])) {
            if ($_GET['export'] == 'csv') {
                $template = 'ArmdReportBundle:Report:user_week.csv.twig';
                $this->sendFileToBrowser(
                    iconv('utf-8', (strstr($_SERVER['HTTP_USER_AGENT'], 'Mac')?'MacCyrillic':'windows-1251'),$this->renderView($template, $vars)),
                    'Заполняемость_ежедневных_отчетов_по_сотрудникам_(по_неделям)_'.date('d.m.Y', $from).'_-_'.date('d.m.Y', $to).'.csv',
                    'csv',
                    'text/csv'
                );
            }
        } else {
            $template = 'ArmdReportBundle:Report:user_week.html.twig';
            return $this->render($template, $vars);
        }
    }
}