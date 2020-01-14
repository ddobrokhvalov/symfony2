<?php

namespace Armd\ProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Armd\ProjectBundle\Entity\Holiday
 */
class Holiday
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
}