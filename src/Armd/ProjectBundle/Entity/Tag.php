<?php

namespace Armd\ProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Armd\ProjectBundle\Entity\Tag
 */
class Tag
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
}