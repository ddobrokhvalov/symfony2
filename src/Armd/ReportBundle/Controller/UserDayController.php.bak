<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ReportBundle\Entity\Report;
use Armd\ReportBundle\Form\ReportType;
use Armd\ReportBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;


class UserDayController extends ReportController

{
    /*
     * Экшн отчета по дням
     */
    public function indexAction()
    {
       
		print_r("test_test_test: ");//exit;
        $this->user = $this->container->get('security.context')->getToken()->getUser();
		
		if (!$this->isDepartmentBoss() && !$this->isManager()) {
			echo 'Доступ запрещен';
			exit;
		}
		
        if (isset($_GET['department'])) {
            if (!$this->isInDepartment($_GET['department'])) {
                return $this->redirect($this->generateUrl('report'));
            }
        } else {
            if (is_object($this->user->getDepartment())) {
                $_GET['department'] = $this->user->getDepartment()->getId();    
            } else {
                echo 'Для Вашего аккаунта не указано подразделение. Напишите об этой ошибке на <a href="mailto:support-report@armd.ru">support-report@armd.ru</a>.';
                exit;
            }
            
        }
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ArmdProjectBundle:Employee');
       
       // если не передан параметр начала недели, то берем текущую 
        if (isset($_GET['week'])) {
            $week = strtotime($_GET['week']);
        } else {
            if (date('N') == 1) {
                $week = time();
            } else {
                $week = strtotime('last monday');        
            }
            
        }
        
        // собираем список дней в формате ДД.ММ.ГГГГ

        if (isset($_GET['begin']) && isset($_GET['end'])) {
            list($days, $human_days) = $this->buildDays(strtotime($_GET['begin']), strtotime($_GET['end']));
        } else {
        	$days = $this->week_from_monday(date("d-m-Y", $week));
       		foreach($days as $day) {
            	$human_days[] = date('d.m.Y', $day['ts']);
        	}	
        }
        
        
        $query = $repository->createQueryBuilder('e');
        
        // если выбран фильт по подразделениям, то дополняем запрос затрудников
        if (isset($_GET['department']) && ($_GET['department'] != '')) {
            // само подразделение
            $query = $query->where('e.department = :department99999')->setParameter('department99999', $_GET['department']); 
            
            if (isset($_GET['hide_discharged'])) {
                $query = $query->andWhere('e.discharged = :discharged')->setParameter('discharged', 0);
            }
            
            // и его дочерние
            $departments_repository = $em->getRepository('ArmdProjectBundle:Department');
            $department_parents = $departments_repository->findByParent($_GET['department']);
            foreach ($department_parents as $key=>$department_parent) {
                $query = $query->orWhere('e.department = :department'.$key)->setParameter('department'.$key, $department_parent);
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
        
        foreach ($employees as $employee) {
            $repository = $em->getRepository('ArmdReportBundle:Report');
            $query = $repository->createQueryBuilder('r');
            // вытаскиваем отчеты за выбранную неделю
            $query = $query->select('r.id, r.day, sum(r.minutes) as sm');
            $query = $query->where('r.employee = :employee')->setParameter('employee', $employee->getId());
            $query = $query->andWhere('r.day >= :monday')->setParameter('monday', date('Y-m-d', strtotime($human_days[0])));
            $query = $query->andWhere('r.day <= :friday')->setParameter('friday', date('Y-m-d', strtotime($human_days[count($human_days)-1])));

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

            $query = $query->groupBy('r.day');
            $reports = $query->getQuery()->getResult();

            
            $entities[$employee->getId()]['employee'] = $employee->__toString();
            $entities[$employee->getId()]['employee_id'] = $employee->getId();
            $entities[$employee->getId()]['discharged'] = (integer)$employee->getDischarged();
            $entities[$employee->getId()]['sum'] = 0;
            
            foreach($reports as $report) {
                // записываем и суммируем отчеты за день
                $entities[$employee->getId()][date('d.m.Y',strtotime($report['day']))]['time'] = $report['sm'];
                $entities[$employee->getId()][date('d.m.Y',strtotime($report['day']))]['not_full'] = false;
                $entities[$employee->getId()]['sum'] += $report['sm'];
            }
            
            // если отчетов нет, то заполняем пустыми значениями
            foreach ($human_days as $day) {
                $entities[$employee->getId()][$day]['not_full'] = false;
                
				if ($employee->getTime() == 0) {
					$entities[$employee->getId()][$day]['time'] = '0';
                    $entities[$employee->getId()][$day]['not_full'] = false;
				} elseif (!isset($entities[$employee->getId()][$day]['time']) && ($employee->getTime() != 0)) {
                    $entities[$employee->getId()][$day]['time'] = '0';
                    $entities[$employee->getId()][$day]['not_full'] = true;
                } elseif ($entities[$employee->getId()][$day]['time'] < $employee->getTime()) {
                    $entities[$employee->getId()][$day]['not_full'] = true;
                }
                
                if ($this->isHoliday($day) || in_array(date('N',strtotime($day)), array(6,7))) {
                    $entities[$employee->getId()][$day]['not_full'] = false;
                }
                
            }
            
            $full_sum += $entities[$employee->getId()]['sum'];
            
        }
        
        // считаем суммы отчетов за день для всех сотрудников
        $day_sums = array();
        foreach ($human_days as $day) {
            $day_sums[$day] = 0;
            foreach ($entities as $entity) {
                $day_sums[$day] += $entity[$day]['time'];
            }
        }
        
        $vars = array(
            'employees'   => $this->getEmployees((isset($_GET['employee'])?$_GET['employee']:'0')),
			'employee_search'   => (isset($_GET['employee_search'])?$_GET['employee_search']:''),
            'projects'    => $this->getProjects((isset($_GET['project'])?$_GET['project']:'0')),
            'tasks'       => $this->getTasks((isset($_GET['task'])?$_GET['task']:'0')),
            'managers'    => $this->buildManagers((isset($_GET['manager'])?$_GET['manager']:'0')),
            'saleses'     => $this->buildSalesManagers((isset($_GET['sales'])?$_GET['sales']:'0')),
            'hide_empty'  => isset($_GET['hide_empty']),
            'entities'    => $entities, // список отчетов
            'human_days'  => $human_days, // список дней в формате ДД.ММ.ГГГГ
            'from'        => $human_days[0], // дата понедельника
            'to'          => $human_days[count($human_days)-1], // дата воскресенья
            'prev_monday' => date('d.m.Y', strtotime('last monday', $week)), // дата предыдущего понедельника
            'next_monday' => date('d.m.Y', strtotime('next monday', $week)), // дата следующего понедельника
            'day_sums'    => $day_sums, // суммы по дням
            'full_sum'    => $full_sum, // полная сумма
            'departments' => $this->buildDepartments((isset($_GET['department'])?$_GET['department']:null)), // список подразделений
            'department'  => (isset($_GET['department'])?$_GET['department']:false), // выбранное подразделение
            'week'        => (isset($_GET['week'])?$_GET['week']:false), // выбранная неделя
            'user'        => $this->user,
            'breadcrumbs' => array(
                                array(
                                    'link'  => $this->generateUrl('report_user_day'),
                                    'title' => 'Заполняемость ежедневных отчетов по сотрудникам'
                                )
                            )
        );
        
        if (isset($_GET['export'])) {
            if ($_GET['export'] == 'csv') {
                $template = 'ArmdReportBundle:Report:user_day.csv.twig';
                $this->sendFileToBrowser(
                    iconv('utf-8', (strstr($_SERVER['HTTP_USER_AGENT'], 'Mac')?'MacCyrillic':'windows-1251'),$this->renderView($template, $vars)),
                    'Заполняемость_ежедневных_отчетов_по_сотрудникам_(по_дням)_'.$human_days[0].'_-_'.$human_days[count($human_days)-1].'.csv',
                    'csv',
                    'text/csv'
                );
            }
        } else {
            $template = 'ArmdReportBundle:Report:user_day.html.twig';
            return $this->render($template, $vars);
        }
    }

    public function buildDays($begin, $end)
    {
        $operating_from = $begin;
        $days = array();
        while ($operating_from <= $end) {
            $days[] = array ( 'ts' => $operating_from);
            $human_days[] = date('d.m.Y',$operating_from);
            $operating_from = strtotime('+1 day', $operating_from);
        }
        return array($days, $human_days);
    }

    
}