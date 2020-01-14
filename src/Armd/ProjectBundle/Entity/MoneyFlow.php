<?php

namespace Armd\ProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Armd\ProjectBundle\Entity\MoneyFlow
 */
class MoneyFlow
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var date $date
     */
    private $date;

    /**
     * @var float $value
     */
    private $value;

    /**
     * @var string $contractor
     */
    private $contractor;

    /**
     * @var string $client
     */
    private $client;

    /**
     * @var string $ticker
     */
    private $ticker;

    /**
     * @var string $legal
     */
    private $legal;

    /**
     * @var string $analytics
     */
    private $analytics;

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
     * Set date
     *
     * @param date $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return date 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set value
     *
     * @param float $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return float 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set contractor
     *
     * @param string $contractor
     */
    public function setContractor($contractor)
    {
        $this->contractor = $contractor;
    }

    /**
     * Get contractor
     *
     * @return string 
     */
    public function getContractor()
    {
        return $this->contractor;
    }

    /**
     * Set client
     *
     * @param string $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * Get client
     *
     * @return string 
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set ticker
     *
     * @param string $ticker
     */
    public function setTicker($ticker)
    {
        $this->ticker = $ticker;
    }

    /**
     * Get ticker
     *
     * @return string 
     */
    public function getTicker()
    {
        return $this->ticker;
    }

    /**
     * Set legal
     *
     * @param string $legal
     */
    public function setLegal($legal)
    {
        $this->legal = $legal;
    }

    /**
     * Get legal
     *
     * @return string 
     */
    public function getLegal()
    {
        return $this->legal;
    }

    /**
     * Set analytics
     *
     * @param string $analytics
     */
    public function setAnalytics($analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Get analytics
     *
     * @return string 
     */
    public function getAnalytics()
    {
        return $this->analytics;
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
}