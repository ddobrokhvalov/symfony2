<?php

namespace Armd\ProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Armd\ProjectBundle\Entity\ProjectEmployee
 */
class ProjectEmployee
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $hours
     */
    private $hours;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var Armd\ProjectBundle\Entity\Employee
     */
    private $employee;

    /**
     * @var Armd\ProjectBundle\Entity\Project
     */
    private $project;


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
     * Set hours
     *
     * @param integer $hours
     */
    public function setHours($hours)
    {
        $this->hours = $hours;
    }

    /**
     * Get hours
     *
     * @return integer 
     */
    public function getHours()
    {
        return $this->hours;
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
    
    public function __toString()
    {
        return $this->employee.' ('.$this->project.'/'.$this->title.'/'.$this->hours.')';
    }
}