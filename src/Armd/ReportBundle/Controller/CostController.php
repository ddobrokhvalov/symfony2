<?php

namespace Armd\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Armd\ReportBundle\Entity\Report;
use Armd\ReportBundle\Form\ReportType;
use Armd\ReportBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;
use FOS\UserBundle\Model\UserInterface;

class CostController extends ReportController

{
    /*
     * Экшн отчета по себестоимости
     */
	
	

    public function indexAction()
    {
        $this->user = $this->container->get('security.context')->getToken()->getUser();
		
		if (!$this->isDepartmentBoss() && !$this->isManager() && !$this->isOwner()) {
			echo 'Доступ запрещен';
			exit;
		}

		
        
        $em = $this->getDoctrine()->getEntityManager();
        $projects_repository = $em->getRepository('ArmdProjectBundle:Project');
        $project_groups_repository = $em->getRepository('ArmdProjectBundle:ProjectGroup');
        
        $money_flow_repository = $em->getRepository('ArmdProjectBundle:MoneyFlow');
        $money_flow = $money_flow_repository->createQueryBuilder('m');
        $money_flow = $money_flow->select('p.id, sum(m.value) as sm');
        $money_flow = $money_flow->innerJoin('m.project', 'p');
        $money_flow = $money_flow->where('m.value > 0');
        $money_flow = $money_flow->groupBy('m.project');
        $money_flow = $money_flow->getQuery()->getResult();
		
		//--------------------------------------------------------------
		/*
		$start_time = get_time();

		$this->buildRates();

		$host="10.32.17.6";
		$user="employe";
		$pwd="Oongee6m";
		$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');
		mysql_select_db("employe",$db);

		$rates_test = mysql_select("select employee_id, discharged, category, outside, date from employees_history order by employee_id, date");
		
		mysql_close($db);

		print_r(count($rates_test));
		//unset($rates_test);
		$end_time = get_time();


		print_r($end_time - $start_time);

		exit;*/
		//----------------------------------------------------------------------------------------------
        
        $money_flow_array = array();
        foreach($money_flow as $row) {
            $money_flow_array[$row['id']] = $row['sm'];
        }
        $money_flow = $money_flow_array;
        
        
        $begin = (isset($_GET['begin'])?$_GET['begin']:'01.01.'.date('Y'));
        $end = (isset($_GET['end'])?$_GET['end']:'31.12.'.date('Y'));
        
        
        $projects = $projects_repository->createQueryBuilder('p');
        if (isset($_GET['department']) && ($_GET['department'] != 0)) {
            $projects = $projects->innerJoin('p.manager', 'm');
            $projects = $projects->andWhere('m.department = :department')->setParameter('department', $_GET['department']);    
        }
        
        if (isset($_GET['manager']) && ($_GET['manager'] != 0)) {
            $projects = $projects->andWhere('p.manager = :manager')->setParameter('manager', $_GET['manager']);    
        }
        
        if (isset($_GET['client']) && ($_GET['client'] != 0)) {
            $projects = $projects->andWhere('p.client = :client')->setParameter('client', $_GET['client']);    
        }
        
        if (isset($_GET['type']) && ($_GET['type'] != 0)) {
            $projects = $projects->andWhere('p.project_type = :type')->setParameter('type', $_GET['type']);    
        }
        
        $projects = $projects->andWhere('p.begin <= :end')->setParameter('end', date('Y-m-d', strtotime($end)));
        $projects = $projects->andWhere('p.end >= :begin')->setParameter('begin', date('Y-m-d', strtotime($begin)));

		//$projects = $projects->orderBy('p.project_group', 'asc');
		//$projects = $projects->orderBy('p.id', 'asc');

		//$projects = $projects->setFirstResult(100);
        //$projects = $projects->setMaxResults('20');
        
        $projects = $projects->getQuery()->getResult();

        foreach($projects as $key=>$project) {
		

            if (!$this->isProjectManager($project->getId())
                &&
                !$this->isDepartmentBossAndStaffAreManagers($project->getId())
                &&
				!$this->isProjectOwner($project->getId())	
					) {
                unset($projects[$key]);
            }
        }
		$nav = array();
		
		$count = count($projects);
		print_r($count);exit;
		$from = isset($_GET['from'])?$_GET['from']:0;
		$nav = $this->get_nav_array($count, 20, $from);
		//$projects2 = array();
		foreach($projects as $key=>$project) {
			if ($key < $from*20 || $key > ($from+1)*20 - 1){
				unset($projects[$key]);
			}
		}
		

		/*
		$projects = $projects_repository->createQueryBuilder('p');
        if (isset($_GET['department']) && ($_GET['department'] != 0)) {
            $projects = $projects->innerJoin('p.manager', 'm');
            $projects = $projects->andWhere('m.department = :department')->setParameter('department', $_GET['department']);    
        }
        
        if (isset($_GET['manager']) && ($_GET['manager'] != 0)) {
            $projects = $projects->andWhere('p.manager = :manager')->setParameter('manager', $_GET['manager']);    
        }
        
        if (isset($_GET['client']) && ($_GET['client'] != 0)) {
            $projects = $projects->andWhere('p.client = :client')->setParameter('client', $_GET['client']);    
        }
        
        if (isset($_GET['type']) && ($_GET['type'] != 0)) {
            $projects = $projects->andWhere('p.project_type = :type')->setParameter('type', $_GET['type']);    
        }
        
        $projects = $projects->andWhere('p.begin <= :end')->setParameter('end', date('Y-m-d', strtotime($end)));
        $projects = $projects->andWhere('p.end >= :begin')->setParameter('begin', date('Y-m-d', strtotime($begin)));
		
		$projects = $projects->setFirstResult($from*20);
        $projects = $projects->setMaxResults('20');
        
        $projects = $projects->getQuery()->getResult();*/
		
		//exit;
        
        $project_groups = $project_groups_repository->createQueryBuilder('p')->getQuery()->getResult();

        $project_groups[] = 'NULL';
        
        //$report_sums = array();
        
        foreach($project_groups as $project_group) {
           
            $project_group_id = (is_object($project_group)?$project_group->getId():0);
           
            $entities[$project_group_id]['sums']['report_sum'] = 0;
            $entities[$project_group_id]['sums']['contract_cost'] = 0;
            $entities[$project_group_id]['sums']['plan_sum'] = 0;
            $entities[$project_group_id]['sums']['plan_margin'] = 0;
            $entities[$project_group_id]['sums']['income_sum'] = 0;
            $entities[$project_group_id]['sums']['margin'] = 0;
            
            $entities[$project_group_id]['title'] = (is_object($project_group)?$project_group->getTitle():'Портфель не указан');
            
            $entities[$project_group_id]['clients'] = array();
            
            foreach ($projects as $project) {
                
                if ($project_group_id == (is_object($project->getProjectGroup())?$project->getProjectGroup()->getId():0)) {
                    $project_id = $project->getId();
					
					$begin_r = date("Y-m-d", strtotime($begin));
					$end_r = date("Y-m-d", strtotime($end));
                    $report_sum = $this->getReportSumForProject($project_id);//, $begin_r, $end_r);                
					
					
                    $total_cost = $this->getPlanCost($project_id);
                    
					if($project->getEnd()->getTimestamp() < time()) {
						$done_percentage = 100;
					} else {
						$done_percentage_total_days = round($project->getEnd()->getTimestamp() - $project->getBegin()->getTimestamp())/(3600*24);
						$done_percentage_now = round(time() - $project->getBegin()->getTimestamp())/(3600*24);
						$done_percentage = round($done_percentage_now/$done_percentage_total_days*100);
					}
					
                    $entities[$project_group_id]['projects'][$project_id] = array(
                        'id'            => $project_id,
                        'title'         => $project->getTitle(),
                        'report_sum'    => isset($report_sum) ? $report_sum * $project->getRatioInside() : '0',
                        'contract_cost' => $project->getContractCost(),
                        'manager'       => (is_object($project->getManager())?$project->getManager()->__toString():''),
                        'department'    => (is_object($project->getManager())?$project->getManager()->getDepartment():''),
                        'project_group' => $project->getProjectGroup(),
                        'income_sum'    => isset($money_flow[$project_id]) ? $money_flow[$project_id] : '0',
                        'margin'        => ((isset($money_flow[$project_id]) ? $money_flow[$project_id] : '0') - (isset($report_sum) ? $report_sum : 0)) * $project->getRatioInside(),
                        'plan_margin'   => $project->getContractCost() - $total_cost,
                        'diff'          => ($total_cost?round((($total_cost - (isset($report_sum) ? $report_sum : 0))/$total_cost)*100,2) : ''),
                        'sales_manager' => $project->getSalesManager(),
                        'plan_sum'      => $total_cost,
                        'type'          => $project->getProjectType()->getId(),
						'done_percentage' => $done_percentage,
                        'open'          => $project->getOpen()
                        
                    );
                    
                    if (is_object($project->getClient())) {
                        $entities[$project_group_id]['clients'][] = $project->getClient()->getFullTitle();
                    }
                    
                    $entities[$project_group_id]['projects'][$project_id]['notice'] =
                        (boolean)($entities[$project_group_id]['projects'][$project_id]['diff'] < 30);
                    
                    $entities[$project_group_id]['sums']['report_sum'] += $entities[$project_group_id]['projects'][$project_id]['report_sum'];
                    $entities[$project_group_id]['sums']['contract_cost'] += $entities[$project_group_id]['projects'][$project_id]['contract_cost'];
                    $entities[$project_group_id]['sums']['plan_sum'] += $entities[$project_group_id]['projects'][$project_id]['plan_sum'];
                    $entities[$project_group_id]['sums']['plan_margin'] += $entities[$project_group_id]['projects'][$project_id]['plan_margin'];
                    $entities[$project_group_id]['sums']['income_sum'] += $entities[$project_group_id]['projects'][$project_id]['income_sum'];
                    $entities[$project_group_id]['sums']['margin'] += $entities[$project_group_id]['projects'][$project_id]['margin'];
                    
                }
                
            }
            
            
            
            if (!isset($entities[$project_group_id]['projects'])) {
                $entities[$project_group_id]['projects'] = array();
                 $entities[$project_group_id]['title'] = null;
            } else {
                $entities[$project_group_id]['sums']['diff'] = ($entities[$project_group_id]['sums']['plan_sum']?round((($entities[$project_group_id]['sums']['plan_sum'] - $entities[$project_group_id]['sums']['report_sum'])/$entities[$project_group_id]['sums']['plan_sum'])*100,2): null);
                $entities[$project_group_id]['sums']['notice'] =
                        (boolean)($entities[$project_group_id]['sums']['diff'] < 30);
            }
            
        }

		

        $vars = array(
            'entities'        => isset($entities)?$entities:array(), // список записей
			'nav'			=>$nav,
            'departments'     => $this->buildDepartments((isset($_GET['department'])?$_GET['department']:null)), // список подразделений
            'department'      => (isset($_GET['department'])?$_GET['department']:false), // выбранное подразделение,
            'user'            => $this->user,
            'begin'           => (isset($_GET['begin'])?$_GET['begin']:'01.01.'.date('Y')),
            'end'             => (isset($_GET['end'])?$_GET['end']:'31.12.'.date('Y')),
            'managers'        => $this->buildManagers(isset($_GET['manager'])?$_GET['manager']:null, false, false, true),
            'clients'         => $this->buildClients(isset($_GET['client'])?$_GET['client']:null),
            'types'           => $this->buildProjectTypes(isset($_GET['type'])?$_GET['type']:null),
            'breadcrumbs'     => array(
                                    array(
                                        'link'  => $this->generateUrl('report_cost'),
                                        'title' => 'Плановая и фактическая себестоимость'
                                    )
                                )
        );
        
        if (isset($_GET['export'])) {
            if ($_GET['export'] == 'csv') {
                $template = 'ArmdReportBundle:Report:cost.csv.twig';
                $this->sendFileToBrowser(
                    iconv('utf-8', (strstr($_SERVER['HTTP_USER_AGENT'], 'Mac')?'MacCyrillic':'windows-1251'),$this->renderView($template, $vars)),
                    'Плановые_и_фактические_затраты.csv',
                    'csv',
                    'text/csv'
                );
            }
        } else {
            $template = 'ArmdReportBundle:Report:cost.html.twig';
            return $this->render($template, $vars);
        }
    }
    
	protected function get_nav_array($count, $count_on_page, $from)
	{
		$pages_count = (integer)ceil($count / $count_on_page);
		
		$result = array();
		if ($pages_count <= 11){
			for ($x = 0; $x < $pages_count; $x++){
				$begin = $x*$count_on_page +1;
				$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
				$selected = $x==$from?1:0;
				$result[] = array('title'=>"[".$begin." - ".$end."]", 'linkid'=>$x, 'selected'=> $selected);
			}
		}else if ($from < 4 || $from > $pages_count-5){
			for ($x = 0; $x < 5; $x++){
				$begin = $x*$count_on_page +1;
				$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
				$selected = $x==$from?1:0;
				$result[] = array('title'=>"[".$begin." - ".$end."]", 'linkid'=>$x, 'selected'=> $selected);
			}
			$x=5;
			$begin = $x*$count_on_page +1;
			$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
			$result[] = array('title'=>"...", 'linkid'=>$x, 'selected'=> 0);

			for ($x = $pages_count-5; $x < $pages_count; $x++){
				$begin = $x*$count_on_page +1;
				$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
				$selected = $x==$from?1:0;
				$result[] = array('title'=>"[".$begin." - ".$end."]", 'linkid'=>$x, 'selected'=> $selected);
			}
		}else if ($from < 5){
			$x=0;
			$begin = $x*$count_on_page +1;
			$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
			$result[] = array('title'=>"[".$begin." - ".$end."]", 'linkid'=>$x, 'selected'=> 0);
			$x=1;
			$begin = $x*$count_on_page +1;
			$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
			$result[] = array('title'=>"...", 'linkid'=>$x, 'selected'=> 0);
			
			for ($x = $from-2; $x < $from+3; $x++){
				$begin = $x*$count_on_page +1;
				$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
				$selected = $x==$from?1:0;
				$result[] = array('title'=>"[".$begin." - ".$end."]", 'linkid'=>$x, 'selected'=> $selected);
			}

			$x=$from+4;
			$begin = $x*$count_on_page +1;
			$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
			$result[] = array('title'=>"...", 'linkid'=>$x, 'selected'=> 0);

			for ($x = $pages_count-3; $x < $pages_count; $x++){
				$begin = $x*$count_on_page +1;
				$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
				$selected = $x==$from?1:0;
				$result[] = array('title'=>"[".$begin." - ".$end."]", 'linkid'=>$x, 'selected'=> $selected);
			}
		}else if($from > $pages_count-6){
			for ($x = 0; $x < 3; $x++){
				$begin = $x*$count_on_page +1;
				$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
				$selected = $x==$from?1:0;
				$result[] = array('title'=>"[".$begin." - ".$end."]", 'linkid'=>$x, 'selected'=> $selected);
			}
			$x=$from-3;
			$begin = $x*$count_on_page +1;
			$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
			$result[] = array('title'=>"...", 'linkid'=>$x, 'selected'=> 0);
			
			for ($x = $from-2; $x < $from+3; $x++){
				$begin = $x*$count_on_page +1;
				$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
				$selected = $x==$from?1:0;
				$result[] = array('title'=>"[".$begin." - ".$end."]", 'linkid'=>$x, 'selected'=> $selected);
			}
			
			$x=$from+4;
			$begin = $x*$count_on_page +1;
			$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
			$result[] = array('title'=>"...", 'linkid'=>$x, 'selected'=> 0);
			
			$x=$pages_count-1;
			$begin = $x*$count_on_page +1;
			$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
			$result[] = array('title'=>"[".$begin." - ".$end."]", 'linkid'=>$x, 'selected'=> 0);

		}else{
			for ($x = 0; $x < 2; $x++){
				$begin = $x*$count_on_page +1;
				$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
				$selected = $x==$from?1:0;
				$result[] = array('title'=>"[".$begin." - ".$end."]", 'linkid'=>$x, 'selected'=> $selected);
			}

			$x=$from-3;
			$begin = $x*$count_on_page +1;
			$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
			$result[] = array('title'=>"...", 'linkid'=>$x, 'selected'=> 0);
			
			for ($x = $from-2; $x < $from+3; $x++){
				$begin = $x*$count_on_page +1;
				$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
				$selected = $x==$from?1:0;
				$result[] = array('title'=>"[".$begin." - ".$end."]", 'linkid'=>$x, 'selected'=> $selected);
			}
			
			$x=$from+4;
			$begin = $x*$count_on_page +1;
			$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
			$result[] = array('title'=>"...", 'linkid'=>$x, 'selected'=> 0);

			for ($x = $pages_count-2; $x < $pages_count; $x++){
				$begin = $x*$count_on_page +1;
				$end = ($x+1)*$count_on_page<$count?($x+1)*$count_on_page:$count;
				$selected = $x==$from?1:0;
				$result[] = array('title'=>"[".$begin." - ".$end."]", 'linkid'=>$x, 'selected'=> $selected);
			}

		}
		return $result;
	}
    
    /**
     * Получение фактической стоимости по отчетам проекта
     * @param integer $project_id
     * @return float Сумма
     */
    protected function getReportSumForProject($project_id, $begin = false, $end = false)
    {
		//$this->buildRates();

		
		
        $result = 0;
		$ini = parse_ini_file(dirname(__FILE__).'/../../../../app/config/parameters.ini');
		
		
		$host="localhost";
		$user=$ini['database_user'];
		$pwd=$ini['database_password'];
		$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');
		
		mysql_select_db($ini['database_name'],$db);

		$reports_count = mysql_select("select count(*) as reports_count from Report where project_id = ".$project_id.($begin?" and day >= '".$begin."' ":" ").
			($end?" and day <= '".$end."'":" "));
		$reports_count = $reports_count[0]['reports_count'];
		
		$reports = mysql_select("select id, employee_id as eid, day, minutes from Report where project_id = ".$project_id.($begin?" and day >= '".$begin."' ":" ").
			($end?" and day <= '".$end."'":" "));
		//print_r("<pre>");
		//print_r($reports);
		//print_r("</pre>");
		mysql_close($db);
			
			$host="10.32.17.6";
			$user="employe";
			$pwd="Oongee6m";
			$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');
			mysql_select_db("employe",$db);
			//print_r(" ^ ".$project_id." * ".$reports_count." * ");
			//if ($project_id == 1046 || $project_id == 1011 || $project_id == 1010 || $project_id == 394 /*|| $project_id == 922*/){
			//if($reports_count < 200){
				
				foreach ($reports as $report) {
					
					$rate = mysql_select("select category, discharged, outside  from employees_history where employee_id = ".$report['eid']." and date like '".date("Ymd",strtotime($report['day']))."%'");
					
					//if (isset($rate[0]['outside']) && $rate[0]['outside'] == 1){
					//	$rate = 3;
					//}else{
						if (count($rate) > 0){
							$rate = $rate[0]['category'];
						}else{
							$rate = 0;
						}
					//}
					$result +=	$report['minutes'] * $this->salary[$rate];
					//$result += $this->getReportSum($report['eid'], strtotime($report['day']), $report['minutes']);
					unset($rate);
					
				}
				
			//}
			mysql_close($db);
		
		unset($reports);
		
		return $result;

		/*
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
        print_r($count.": ");return 0;
        for ($i=0;$i<=$count;$i=$i+5000){
            // выбираем по 10000 отчетов за раз (больше уже просто все тупит)
            $query = $reports_repository->createQueryBuilder('r');
            $query = $query->select('e.id as eid, r.minutes, r.day');
            $query = $query->innerJoin('r.project', 'p');
            $query = $query->innerJoin('r.employee', 'e');
            $query = $query->where('p.id = :project_id')->setParameter('project_id', $project_id);
            $query = $query->setFirstResult($i);
            $query = $query->setMaxResults('5000');
            $reports = $query->getQuery()->getResult();
            
            // из-за версионности ставок, которая еще и не хранится у нас приходится считать стоимость каждого отчета отдельно
            // TODO надо что-то с этим делать
            foreach ($reports as $report) {
                $result += $this->getReportSum($report['eid'], strtotime($report['day']), $report['minutes']);
            }
        }
        return $result;*/
    }
    
}

function get_time()
	{
		list($usec, $seconds) = explode(" ", microtime());
		return ((float)$usec + (float)$seconds);
	}