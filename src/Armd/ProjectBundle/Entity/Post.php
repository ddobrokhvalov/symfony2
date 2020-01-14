<?php

namespace Armd\ProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Armd\ProjectBundle\Entity\Post
 */
class Post
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
    
    public function __toString()
    {
        return $this->title;
    }
    /**
     * @var Armd\ProjectBundle\Entity\Task
     */
    private $task;

    public function __construct()
    {
        $this->task = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add task
     *
     * @param Armd\ProjectBundle\Entity\Task $task
     */
    public function addTask(\Armd\ProjectBundle\Entity\Task $task)
    {
        $this->task[] = $task;
    }

    /**
     * Get task
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTask()
    {
        return $this->task;
    }
}