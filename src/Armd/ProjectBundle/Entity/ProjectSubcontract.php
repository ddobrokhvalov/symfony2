<?php

namespace Armd\ProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Armd\ProjectBundle\Entity\ProjectSubcontract
 */
class ProjectSubcontract
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
     * @var Armd\ProjectBundle\Entity\Subcontract
     */
    private $subcontract;

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
     * Set subcontract
     *
     * @param Armd\ProjectBundle\Entity\Subcontract $subcontract
     */
    public function setSubcontract(\Armd\ProjectBundle\Entity\Subcontract $subcontract)
    {
        $this->subcontract = $subcontract;
    }

    /**
     * Get subcontract
     *
     * @return Armd\ProjectBundle\Entity\Subcontract 
     */
    public function getSubcontract()
    {
        return $this->subcontract;
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

    /**
     * @var integer $salary
     */
    private $salary;


    /**
     * Set salary
     *
     * @param integer $salary
     */
    public function setSalary($salary)
    {
        $this->salary = $salary;
    }

    /**
     * Get salary
     *
     * @return integer 
     */
    public function getSalary()
    {
        return $this->salary;
    }
}