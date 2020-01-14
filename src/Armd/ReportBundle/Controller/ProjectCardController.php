<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ReportBundle\Entity\Report;
use Armd\ReportBundle\Form\ReportType;
use Armd\ReportBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;

class ProjectCardController extends ReportController

{
    /*
     * Экшн карточки проекта
     */
    public function indexAction()
    {
		
        function max_key($array) {
            foreach ($array as $key => $val) {
                if ($val == max($array)) return $key;
            }
        }
        
        $this->buildRates();
        
        if (isset($_GET['id'])) {
            $project_id = $_GET['id'];
        } else {
            return $this->redirect($this->generateUrl('report'));
        }
        $this->user = $this->container->get('security.context')->getToken()->getUser();
        
        if (!$this->isProjectOwner($project_id) && !$this->isProjectManager($project_id) && !$this->isDepartmentBossAndStaffAreManagers($project_id)) {
            return $this->redirect($this->generateUrl('report'));
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $repository = $em->getRepository('ArmdProjectBundle:Project');
        $project = $repository->findOneById($project_id);
        
        $ratio_subcontract = $project->getRatioSubcontract();
        $ratio_inside      = $project->getRatioInside();
        $ratio_bonus       = $project->getRatioBonus();
        $ratio_outsourcing = $project->getRatioOutsourcing();
        $other_cost        = $project->getOtherCost();
        $project_title     = $project->getTitle();
        
        /* Блок подсчета фактических затрат */
        $reports_repository = $em->getRepository('ArmdReportBundle:Report');
        $query = $reports_repository->createQueryBuilder('r');
        $query = $query->select('e.id as eid, e.surname, e.name, e.patronymic, e.subcontractor, r.minutes, r.day');
        $query = $query->innerJoin('r.project', 'p');
        $query = $query->innerJoin('r.employee', 'e');
        $query = $query->where('r.project = :project')->setParameter('project', $project_id);
        $reports = $query->getQuery()->getResult();
        //print_r("<pre>");
		//print_r($reports);
		//print_r("</pre>");
        $days = $this->week_from_monday(date("d-m-Y", strtotime('-7 days')));
        
        $last_week = array();
        foreach($days as $day) {
            $last_week[] = date('Y-m-d', $day['ts']);
        }
        
        
        $fact_costs               = array();
        
        $total                    = array(
            'sum'                 => 0,
            'last_week_sum'       => 0,
            'hours_sum'           => 0,
            'last_week_hours_sum' => 0,
            'sum_with_inside'     => 0
        );
        
        foreach($reports as $key=>$report) {
            if (!isset($fact_costs[$report['eid']]['sum']))                 $fact_costs[$report['eid']]['sum'] = 0;
            if (!isset($fact_costs[$report['eid']]['sum_with_inside']))     $fact_costs[$report['eid']]['sum_with_inside'] = 0;
            if (!isset($fact_costs[$report['eid']]['hours_sum']))           $fact_costs[$report['eid']]['hours_sum'] = 0;
            if (!isset($fact_costs[$report['eid']]['last_week_sum']))       $fact_costs[$report['eid']]['last_week_sum'] = 0;
            if (!isset($fact_costs[$report['eid']]['last_week_hours_sum'])) $fact_costs[$report['eid']]['last_week_hours_sum'] = 0;
            
            $report_sum = $this->getReportSum($report['eid'], strtotime($report['day']), $report['minutes']);
            
            $fact_costs[$report['eid']]['title']           = $report['surname'].' '.$report['name'].' '.$report['patronymic'] . ($report['subcontractor']?' (субподрядчик) ':'');
            $fact_costs[$report['eid']]['salary']          = $this->salary[$this->getRateByDate($report['eid'], time())];
            
            $fact_costs[$report['eid']]['sum']             += $report_sum;
            $fact_costs[$report['eid']]['sum_with_inside'] += $report_sum * $ratio_inside;
            $total['sum']                                  += $report_sum;
            $total['sum_with_inside']                      += $report_sum * $ratio_inside;
            
            $fact_costs[$report['eid']]['hours_sum']       += $reports[$key]['minutes'];
            $total['hours_sum']                            += $reports[$key]['minutes'];
            
            if (in_array($report['day'], $last_week)) {
                $fact_costs[$report['eid']]['last_week_sum']       += $report_sum;
                $total['last_week_sum']                            += $report_sum;
                
                $fact_costs[$report['eid']]['last_week_hours_sum'] += $reports[$key]['minutes'];
                $total['last_week_hours_sum']                      += $reports[$key]['minutes'];
            }
        }
        /* Конец блока подсчета фактических затрат */
        
        
        
        /* Блок подсчета планируемых затрат */
        $plan_costs = array();
        
        $total['plan_sum'] = 0;
        $total['plan_hours'] = 0;
        foreach($project->getProjectEmployee() as $employee) {
            if (is_object($employee->getEmployee())) {
                $employee_id    = $employee->getEmployee()->getId();
                $rate           = $this->getRateByDate(
                                    $employee->getEmployee()->getId(),
                                    $project->getBegin()->getTimestamp()
                                );
                $plan_costs[$employee_id] = array(
                    'name'  => $employee->getEmployee()->__toString(),
                    'hours' => $employee->getHours(),
                    'salary' => $this->salary[$rate],
                    'rate'   => $rate,
                    'total_cost' => $this->salary[$rate] * $employee->getHours(),
                    'title' => $employee->getTitle(),
                );
                
                $total['plan_sum']   += $plan_costs[$employee_id]['total_cost'];
                $total['plan_hours'] += $plan_costs[$employee_id]['hours'];
            }
        }
        
        if (($project->getFzp() !== null) && ($project->getFzp() != 0)) {
            $total['plan_sum'] = $project->getFzp();
        }
        
        $subcontracts_total_sum   = 0;
        $subcontracts_total_hours = 0;
        
        foreach($project->getProjectSubcontract() as $subcontract) {
            $salary = $subcontract->getSubcontract()->getSalary();
            $subcontracts[] = array(
                'name'       => $subcontract->getSubcontract()->__toString(),
                'hours'      => $subcontract->getHours(),
                'salary'     => $salary,
                'total_cost' => $salary * $subcontract->getHours(),
                'title'      => $subcontract->getTitle()
            );
            
            $subcontracts_total_sum   += $salary * $subcontract->getHours();
            $subcontracts_total_hours += $subcontract->getHours();
        }
        /* Конец блока подсчета планируемых затрат */
        
        /* Блок сбора данных для диаграммы */
        $money_flow = $em
            ->getRepository('ArmdProjectBundle:MoneyFlow')
            ->createQueryBuilder('m')
            ->select('sum(m.value) as sm')
            ->innerJoin('m.project', 'p')
            ->where('m.value > 0')
            ->andWhere('p.id = :project_id')
            ->setParameter('project_id', $project_id)
            ->groupBy('m.project')
            ->getQuery()
            ->getResult();
        
        if (empty($money_flow)) {
            $money_flow[0]['sm'] = 0;
        }
        
        $dia['plan_sum']      = $this->getPlanCost($project_id);
        $dia['fact_sum']      = round($total['sum_with_inside'], 2);
        $dia['contract_cost'] = $project->getContractCost();
        $dia['income_sum']    = $money_flow[0]['sm'];

        $max_key = max_key($dia);
        
        $one_percent = $dia[$max_key]*1.1/200;
        
        if ($one_percent == 0) $one_percent = 1;
        
        $dia_data['plan_sum']      = round($dia['plan_sum']/$one_percent);
        $dia_data['fact_sum']      = round($dia['fact_sum']/$one_percent);
        $dia_data['contract_cost'] = round($dia['contract_cost']/$one_percent);
        $dia_data['income_sum']    = round($dia['income_sum']/$one_percent);
        /* Конец блока сбора данных для диаграммы */
        
        
        $vars = array(
            'project'                     => $project,
			'title'						=>$project->getTitle(),
            'employees'                   => $plan_costs,
            'subcontracts'                => isset($subcontracts)?$subcontracts:array(),
            'total_sum'                   => $total['plan_sum'],
            'total_hours'                 => $total['plan_hours'],
            'subcontracts_total_sum'      => $subcontracts_total_sum,
            'subcontracts_total_hours'    => $subcontracts_total_hours,
            'total_days'                  => round($total['plan_hours']/8, 1),
            'ratio_subcontract'           => $ratio_subcontract,
            'ratio_inside'                => $ratio_inside,
            'ratio_bonus'                 => $ratio_bonus,
            'ratio_outsourcing'           => $ratio_outsourcing,
            'total_sum_with_inside'       => ($ratio_inside * $total['plan_sum']),
            'total_sum_with_subcontract'  => $subcontracts_total_sum * $ratio_subcontract,
            'other_cost'                  => $other_cost,
            'total'                       => ($ratio_inside * $total['plan_sum']) + $other_cost + ($subcontracts_total_sum * $ratio_subcontract) + (($total['plan_sum'] * $ratio_bonus) + ($subcontracts_total_sum * $ratio_subcontract * $ratio_outsourcing)),
            'total_bonus_fund'            => ($total['plan_sum'] * $ratio_bonus) + ($subcontracts_total_sum * $ratio_subcontract * $ratio_outsourcing),
            'user'                        => $this->user,
            'reports'                     => $fact_costs,
            'reports_sum'                 => round($total['sum'], 2),
            'reports_sum_with_inside'     => round($total['sum_with_inside'], 2),
            'reports_last_week_sum'       => round($total['last_week_sum'], 2),
            'reports_hours_sum'           => $total['hours_sum'],
            'reports_last_week_hours_sum' => $total['last_week_hours_sum'],
            'dia'                         => $dia,
            'dia_data'                    => $dia_data,
            'project_id'                  => $project_id,
            'breadcrumbs'                 => array(
                                                array(
                                                    'link'  => $this->generateUrl('report_cost'),
                                                    'title' => 'Плановая и фактическая себестоимость'
                                                ),
                                                array(
                                                    'link'  => $this->generateUrl('report_project_card', array('id' => $project_id)),
                                                    'title' => $project_title
                                                )
                                            )
            
        );
        
        if (isset($_GET['export']) && isset($_GET['type'])) {
            if (($_GET['export'] == 'csv') && (in_array((integer)$_GET['type'], array(1,2,3,4,5)))) {
                $template = 'ArmdReportBundle:Report:project_card.'.$_GET['type'].'.csv.twig';
                
                switch($_GET['type']) {
                    case 1: $title = 'Смета_проекта';break;
                    case 2: $title = 'Стоимость_работ';break;
                    case 3: $title = 'Субподряды';break;
                    case 4: $title = 'Фактические_трудозатраты';break;
                    case 5: $title = 'Дополнительные_сведения';break;
                }
                
                $this->sendFileToBrowser(
                    iconv('utf-8', (strstr($_SERVER['HTTP_USER_AGENT'], 'Mac')?'MacCyrillic':'windows-1251'),$this->renderView($template, $vars)),
                    'Карточка_проекта_'.$project_title.'_('.$title.').csv',
                    'csv',
                    'text/csv'
                );
            }
        } else {
            $template = 'ArmdReportBundle:Report:project_card.html.twig';
            return $this->render($template, $vars);
        }
    }
    
    
}