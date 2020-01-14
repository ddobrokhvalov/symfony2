<?php

namespace Armd\ProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Armd\ProjectBundle\Entity\Project
 */
class Project
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var integer $contract_cost
     */
    private $contract_cost;

    /**
     * @var date $begin
     */
    private $begin;

    /**
     * @var date $end
     */
    private $end;

    /**
     * @var float $ratio_subcontract
     */
    private $ratio_subcontract = 1.8;

    /**
     * @var float $ratio_inside
     */
    private $ratio_inside = 3.6;

    /**
     * @var float $ratio_bonus
     */
    private $ratio_bonus = 0.15;

    /**
     * @var float $ratio_outsourcing
     */
    private $ratio_outsourcing = 0.01;

    /**
     * @var float $other_cost
     */
    private $other_cost;

    /**
     * @var Armd\ProjectBundle\Entity\ProjectEmployee
     */
    private $project_employee;

    /**
     * @var Armd\ProjectBundle\Entity\ProjectSubcontract
     */
    private $project_subcontract;

    /**
     * @var Armd\ProjectBundle\Entity\ProjectGroup
     */
    private $project_group;

    /**
     * @var Armd\ProjectBundle\Entity\ProjectType
     */
    private $project_type;

    /**
     * @var Armd\ProjectBundle\Entity\Client
     */
    private $client;

    /**
     * @var Armd\ProjectBundle\Entity\Employee
     */
    private $manager;

    /**
     * @var Armd\ProjectBundle\Entity\Employee
     */
    private $sales_manager;

    /**
     * @var Armd\ProjectBundle\Entity\Department
     */
    private $department;

    public function __construct()
    {
        $this->project_employee = new \Doctrine\Common\Collections\ArrayCollection();
    $this->project_subcontract = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set contract_cost
     *
     * @param integer $contractCost
     */
    public function setContractCost($contractCost)
    {
        $this->contract_cost = $contractCost;
    }

    /**
     * Get contract_cost
     *
     * @return integer 
     */
    public function getContractCost()
    {
        return $this->contract_cost;
    }

    /**
     * Set begin
     *
     * @param date $begin
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;
    }

    /**
     * Get begin
     *
     * @return date 
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Set end
     *
     * @param date $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * Get end
     *
     * @return date 
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set ratio_subcontract
     *
     * @param float $ratioSubcontract
     */
    public function setRatioSubcontract($ratioSubcontract)
    {
        $this->ratio_subcontract = $ratioSubcontract;
    }

    /**
     * Get ratio_subcontract
     *
     * @return float 
     */
    public function getRatioSubcontract()
    {
        return $this->ratio_subcontract;
    }

    /**
     * Set ratio_inside
     *
     * @param float $ratioInside
     */
    public function setRatioInside($ratioInside)
    {
        $this->ratio_inside = $ratioInside;
    }

    /**
     * Get ratio_inside
     *
     * @return float 
     */
    public function getRatioInside()
    {
        return $this->ratio_inside;
    }

    /**
     * Set ratio_bonus
     *
     * @param float $ratioBonus
     */
    public function setRatioBonus($ratioBonus)
    {
        $this->ratio_bonus = $ratioBonus;
    }

    /**
     * Get ratio_bonus
     *
     * @return float 
     */
    public function getRatioBonus()
    {
        return $this->ratio_bonus;
    }

    /**
     * Set ratio_outsourcing
     *
     * @param float $ratioOutsourcing
     */
    public function setRatioOutsourcing($ratioOutsourcing)
    {
        $this->ratio_outsourcing = $ratioOutsourcing;
    }

    /**
     * Get ratio_outsourcing
     *
     * @return float 
     */
    public function getRatioOutsourcing()
    {
        return $this->ratio_outsourcing;
    }

    /**
     * Set other_cost
     *
     * @param float $otherCost
     */
    public function setOtherCost($otherCost)
    {
        $this->other_cost = $otherCost;
    }

    /**
     * Get other_cost
     *
     * @return float 
     */
    public function getOtherCost()
    {
        return $this->other_cost;
    }

    /**
     * Add project_employee
     *
     * @param Armd\ProjectBundle\Entity\ProjectEmployee $projectEmployee
     */
    public function addProjectEmployee(\Armd\ProjectBundle\Entity\ProjectEmployee $projectEmployee)
    {
               
        if (!is_object($projectEmployee->getProject())) {
            $projectEmployee->setProject($this);
        }
        
        $this->project_employee[] = $projectEmployee;
    }
    
    /**
     * Set project_employee
     *
     * @param unknown $projectEmployee
     */
    public function setProjectEmployee($projectEmployee)
    {
        foreach($projectEmployee as $pe) {
            if (!is_object($pe->getProject())) {
                $pe->setProject($this);
            }
        }

        $this->project_employee = $projectEmployee;
    }

    

    /**
     * Get project_employee
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getProjectEmployee()
    {
        return $this->project_employee;
    }

    public function setProjectSubcontract($projectSubcontract)
    {
        foreach($projectSubcontract as $ps) {
            if (!is_object($ps->getProject())) {
                $ps->setProject($this);
            }
        }

        $this->project_subcontract = $projectSubcontract;
    }

    /**
     * Add project_subcontract
     *
     * @param Armd\ProjectBundle\Entity\ProjectSubcontract $projectSubcontract
     */
    public function addProjectSubcontract(\Armd\ProjectBundle\Entity\ProjectSubcontract $projectSubcontract)
    {
        $this->project_subcontract[] = $projectSubcontract;
    }

    /**
     * Get project_subcontract
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getProjectSubcontract()
    {
        return $this->project_subcontract;
    }

    /**
     * Set project_group
     *
     * @param Armd\ProjectBundle\Entity\ProjectGroup $projectGroup
     */
    public function setProjectGroup(\Armd\ProjectBundle\Entity\ProjectGroup $projectGroup)
    {
        $this->project_group = $projectGroup;
    }

    /**
     * Get project_group
     *
     * @return Armd\ProjectBundle\Entity\ProjectGroup 
     */
    public function getProjectGroup()
    {
        return $this->project_group;
    }

    /**
     * Set project_type
     *
     * @param Armd\ProjectBundle\Entity\ProjectType $projectType
     */
    public function setProjectType(\Armd\ProjectBundle\Entity\ProjectType $projectType)
    {
        $this->project_type = $projectType;
    }

    /**
     * Get project_type
     *
     * @return Armd\ProjectBundle\Entity\ProjectType 
     */
    public function getProjectType()
    {
        return $this->project_type;
    }

    /**
     * Set client
     *
     * @param Armd\ProjectBundle\Entity\Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * Get client
     *
     * @return Armd\ProjectBundle\Entity\Client 
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set manager
     *
     * @param Armd\ProjectBundle\Entity\Employee $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get manager
     *
     * @return Armd\ProjectBundle\Entity\Employee 
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Set sales_manager
     *
     * @param Armd\ProjectBundle\Entity\Employee $salesManager
     */
    public function setSalesManager($salesManager)
    {
        $this->sales_manager = $salesManager;
    }

    /**
     * Get sales_manager
     *
     * @return Armd\ProjectBundle\Entity\Employee 
     */
    public function getSalesManager()
    {
        return $this->sales_manager;
    }

    /**
     * Set department
     *
     * @param Armd\ProjectBundle\Entity\Department $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }

    /**
     * Get department
     *
     * @return Armd\ProjectBundle\Entity\Department 
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * To String
     *
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }

    /**
     * @var boolean $open
     */
    private $open;


    /**
     * Set open
     *
     * @param boolean $open
     */
    public function setOpen($open)
    {
        $this->open = $open;
    }

    /**
     * Get open
     *
     * @return boolean 
     */
    public function getOpen()
    {
        return $this->open;
    }
    /**
     * @var Armd\ProjectBundle\Entity\Legal
     */
    private $legal;


    /**
     * Set legal
     *
     * @param Armd\ProjectBundle\Entity\Legal $legal
     */
    public function setLegal($legal)
    {
        $this->legal = $legal;
    }

    /**
     * Get legal
     *
     * @return Armd\ProjectBundle\Entity\Legal 
     */
    public function getLegal()
    {
        return $this->legal;
    }
    /**
     * @var date $real_end
     */
    private $real_end;


    /**
     * Set real_end
     *
     * @param date $realEnd
     */
    public function setRealEnd($realEnd)
    {
        $this->real_end = $realEnd;
    }

    /**
     * Get real_end
     *
     * @return date 
     */
    public function getRealEnd()
    {
        return $this->real_end;
    }
    /**
     * @var string $redmine
     */
    private $redmine;


    /**
     * Set redmine
     *
     * @param string $redmine
     */
    public function setRedmine($redmine)
    {
        $this->redmine = $redmine;
    }

    /**
     * Get redmine
     *
     * @return string 
     */
    public function getRedmine()
    {
        return $this->redmine;
    }
    
   
    /**
     * @var Armd\ProjectBundle\Entity\ProjectStage
     */
    private $project_stage;


    /**
     * Add project_stage
     *
     * @param Armd\ProjectBundle\Entity\ProjectStage $projectStage
     */
    public function addProjectStage(\Armd\ProjectBundle\Entity\ProjectStage $projectStage)
    {
        $this->project_stage[] = $projectStage;
    }

    /**
     * Get project_stage
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getProjectStage()
    {
        return $this->project_stage;
    }
    /**
     * @var Armd\ProjectBundle\Entity\Tag
     */
    private $tag;


    /**
     * Add tag
     *
     * @param Armd\ProjectBundle\Entity\Tag $tag
     */
    public function addTag(\Armd\ProjectBundle\Entity\Tag $tag)
    {
        $this->tag[] = $tag;
    }

    /**
     * Get tag
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTag()
    {
        return $this->tag;
    }
    
    public function setTag($tag)
    {
        $this->tag = $tag;
    }
    /**
     * @var float $fzp
     */
    private $fzp;


    /**
     * Set fzp
     *
     * @param float $fzp
     */
    public function setFzp($fzp)
    {
        $this->fzp = $fzp;
    }

    /**
     * Get fzp
     *
     * @return float 
     */
    public function getFzp()
    {
        return $this->fzp;
    }
    /**
     * @var Armd\ProjectBundle\Entity\Employee
     */
    private $owner;


    /**
     * Set owner
     *
     * @param mixed $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner
     *
     * @return Armd\ProjectBundle\Entity\Employee 
     */
    public function getOwner()
    {
		if (is_object($this->owner)) {
			return $this->owner;
		} elseif(is_object($this->manager)) {
			return $this->manager;
		} else {
			return null;
		}
    }
}