<?php

namespace Armd\ProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Armd\ProjectBundle\Entity\Client
 */
class Client
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
     * @var string $full_title
     */
    private $full_title;


    /**
     * Set full_title
     *
     * @param string $fullTitle
     */
    public function setFullTitle($fullTitle)
    {
        $this->full_title = $fullTitle;
    }

    /**
     * Get full_title
     *
     * @return string 
     */
    public function getFullTitle()
    {
        return $this->full_title;
    }
}