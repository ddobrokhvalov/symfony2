<?php

namespace Armd\ReportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Armd\ReportBundle\Entity\Report
 */
class Report
{
    
       
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var datetime $day
     */
    private $day;

    /**
     * @var float $minutes
     */
    private $minutes;

    /**
     * @var text $description
     * 
     */
    private $description;


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
     * Set day
     *
     * @param datetime $day
     */
    public function setDay($day)
    {
        $this->day = $day;
    }

    /**
     * Get day
     *
     * @return datetime 
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set minutes
     *
     * @param float $minutes
     */
    public function setMinutes($minutes)
    {
        $this->minutes = $minutes;
    }

    /**
     * Get minutes
     *
     * @return float 
     */
    public function getMinutes()
    {
        return $this->minutes;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Task", inversedBy="reports")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     */
    protected $task;

    /**
     * Set task
     *
     * @param Armd\ProjectBundle\Entity\Task $task
     */
    public function setTask(\Armd\ProjectBundle\Entity\Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get task
     *
     * @return Armd\ProjectBundle\Entity\Task 
     */
    public function getTask()
    {
        return $this->task;
    }
    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="reports")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    protected $project;


    /**
     * Set project
     *
     * @param Armd\ProjectBundle\Entity\Project $project
     */
    public function setProject(\Armd\ProjectBundle\Entity\Project $project)
    {
        $this->project = $project;
    }

    /**
     * Get project
     *
     * @return Armd\ProjectBundle\Entity\Project 
     */
    public function getProject()
    {
        return $this->project;
    }
    /**
     * @ORM\ManyToOne(targetEntity="Employee", inversedBy="reports")
     * @ORM\JoinColumn(name="employee_id", referencedColumnName="id")
     */
    private $employee;


    /**
     * Set employee
     *
     * @param Armd\ProjectBundle\Entity\Employee $employee
     */
    public function setEmployee(\Armd\ProjectBundle\Entity\Employee $employee)
    {
        $this->employee = $employee;
    }

    /**
     * Get employee
     *
     * @return Armd\ProjectBundle\Entity\Employee 
     */
    public function getEmployee()
    {
        return $this->employee;
    }
    /**
     * @var integer $generated
     */
    private $generated;


    /**
     * Set generated
     *
     * @param integer $generated
     */
    public function setGenerated($generated)
    {
        $this->generated = $generated;
    }

    /**
     * Get generated
     *
     * @return integer 
     */
    public function getGenerated()
    {
        return $this->generated;
    }
}