<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ReportBundle\Entity\Report;
use Armd\ReportBundle\Form\ReportType;
use Armd\ReportBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;

class UserMonthController extends ReportController

{
     /*
     * Экшн отчета по месяцам
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
        
        // если не выбран год, берем текущий
        if (isset($_GET['year'])) {
            $from = strtotime('01.01.'.$_GET['year']);
            $to = strtotime('31.12.'.$_GET['year']);
        } elseif (isset($_GET['begin']) && isset($_GET['end'])){
            $from = strtotime($_GET['begin']);
            $to = strtotime($_GET['end']);
        } else {
            $from = strtotime('01.01.'.date('Y'));
            $to = strtotime('31.12.'.date('Y'));
        }
        
        // собираем список дней
        $operating_from = $from;
        $days = array();
        while ($operating_from <= $to) {
            $days[] = array (   'ts' => $operating_from,
                                'human' => date('d.m.Y',$operating_from));
            $months[date('m',$operating_from)] = date('m',$operating_from);
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
          
          // и его дочерние
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
        $month_working_hours = array();
        foreach ($employees as $employee) {
			// считаем кол-во рабочих часов в неделю
			
			foreach($days as $day) {
				$month = date('m',$day['ts']);
				if (!isset($month_working_hours[$employee->getId()][$month])) {
					$month_working_hours[$employee->getId()][$month] = 0;
				}

				if (!$this->isHoliday($day['human']) && !in_array(date('N',$day['ts']), array(6,7))) {
					$month_working_hours[$employee->getId()][$month] += $employee->getTime();
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
            
            // записываем и считаем суммы 
            foreach($reports as $report) {
                if (!isset($entities[$employee->getId()][date('m',strtotime($report['day']))])) {
                    $entities[$employee->getId()][date('m',strtotime($report['day']))] = $report['sm'];    
                } else {
                    $entities[$employee->getId()][date('m',strtotime($report['day']))] += $report['sm'];
                }
                $entities[$employee->getId()]['sum'] += $report['sm'];
            }
            
            foreach ($days as $day) {
                if (!isset($entities[$employee->getId()][date('m', $day['ts'])])) {
                    $entities[$employee->getId()][date('m', $day['ts'])] = '0';
                }
            }
            
			foreach($entities[$employee->getId()] as $key=>$value) {
				if (in_array($key, array('01','02','03','04','05','06','07','08','09','10','11','12'))) {
					if ($value < $month_working_hours[$employee->getId()][$key]) {
						$entities[$employee->getId()]['not_full'][$key] = true;
					} else {
						$entities[$employee->getId()]['not_full'][$key] = false;
					}
				}
				
			}
			
            $full_sum += $entities[$employee->getId()]['sum'];
        }
        
        // суммы по месяцам        
        $month_sums = array();
        foreach ($months as $month) {
            $month_sums[$month] = 500;
            foreach ($entities as $entity) {
                $month_sums[$month] += $entity[$month];
            }
        }
        
        $vars = array(
            'employees'           => $this->getEmployees((isset($_GET['employee'])?$_GET['employee']:'0')),
			'employee_search'   => (isset($_GET['employee_search'])?$_GET['employee_search']:''),
            'projects'            => $this->getProjects((isset($_GET['project'])?$_GET['project']:'0')),
            'tasks'               => $this->getTasks((isset($_GET['task'])?$_GET['task']:'0')),
            'managers'            => $this->buildManagers((isset($_GET['manager'])?$_GET['manager']:'0')),
            'saleses'             => $this->buildSalesManagers((isset($_GET['sales'])?$_GET['sales']:'0')),
            'from'                => date('d.m.Y', $from),
            'to'                  => date('d.m.Y', $to),
            'hide_empty'          => isset($_GET['hide_empty']),
            'entities'            => $entities, // список записей
            'months'              => $months, // список месяцев
            'month_working_hours' => $month_working_hours, // кол-во рабочих часов в месяцах
            'year'                => date('Y', $from), // выбранный год
            'prev_year'           => date('Y', strtotime('first day of last year', $from)), // предыдущий год
            'next_year'           => date('Y', strtotime('last day of next year', $from)), // следующий год
            'month_sums'          => $month_sums, // суммы по месяцам
            'full_sum'            => $full_sum, // полная сумма
            'departments'         => $this->buildDepartments((isset($_GET['department'])?$_GET['department']:null)), // список подразделений
            'department'          => (isset($_GET['department'])?$_GET['department']:false), // выбранное подразделение
            'user'                => $this->user,
            'breadcrumbs'         => array(
                                array(
                                    'link'  => $this->generateUrl('report_user_month'),
                                    'title' => 'Заполняемость ежедневных отчетов по сотрудникам'
                                )
                            )
        );
        
        if (isset($_GET['export'])) {
            if ($_GET['export'] == 'csv') {
                $template = 'ArmdReportBundle:Report:user_month.csv.twig';
                $this->sendFileToBrowser(
                    iconv('utf-8', (strstr($_SERVER['HTTP_USER_AGENT'], 'Mac')?'MacCyrillic':'windows-1251'),$this->renderView($template, $vars)),
                    'Заполняемость_ежедневных_отчетов_по_сотрудникам_(по_месяцам)_'.date('Y', $from).'.csv',
                    'csv',
                    'text/csv'
                );
            }
        } else {
            $template = 'ArmdReportBundle:Report:user_month.html.twig';
            return $this->render($template, $vars);
        }
    }
}