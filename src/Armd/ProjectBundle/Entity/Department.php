<?php

namespace Armd\ProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Armd\ProjectBundle\Entity\Department
 */
class Department
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
     * @var Armd\ProjectBundle\Entity\Department
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
     * Set parent
     *
     * @param Armd\ProjectBundle\Entity\Department $parent
     */
    public function setParent(\Armd\ProjectBundle\Entity\Department $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Armd\ProjectBundle\Entity\Department 
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    public function __toString()
    {
        return $this->title;
    }
    /**
     * @var Armd\ProjectBundle\Entity\Employee
     */
    private $boss;


    /**
     * Set boss
     *
     * @param Armd\ProjectBundle\Entity\Employee $boss
     */
    public function setBoss(\Armd\ProjectBundle\Entity\Employee $boss)
    {
        $this->boss = $boss;
    }

    /**
     * Get boss
     *
     * @return Armd\ProjectBundle\Entity\Employee 
     */
    public function getBoss()
    {
        return $this->boss;
    }
    /**
     * @var integer $ext_id
     */
    private $ext_id;


    /**
     * Set ext_id
     *
     * @param integer $extId
     */
    public function setExtId($extId)
    {
        $this->ext_id = $extId;
    }

    /**
     * Get ext_id
     *
     * @return integer 
     */
    public function getExtId()
    {
        return $this->ext_id;
    }
}