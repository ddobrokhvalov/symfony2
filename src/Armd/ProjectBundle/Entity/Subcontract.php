<?php

namespace Armd\ProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Armd\ProjectBundle\Entity\Subcontract
 */
class Subcontract
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
     * @var integer $salary
     */
    private $salary;

    /**
     * @var Armd\ProjectBundle\Entity\Subcontract
     */
    private $parent;


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

    /**
     * Set parent
     *
     * @param Armd\ProjectBundle\Entity\Subcontract $parent
     */
    public function setParent(\Armd\ProjectBundle\Entity\Subcontract $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Armd\ProjectBundle\Entity\Subcontract 
     */
    public function getParent()
    {
        return $this->parent;
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

}