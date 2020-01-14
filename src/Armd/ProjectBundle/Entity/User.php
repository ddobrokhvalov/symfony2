<?php

namespace Armd\ProjectBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * Armd\ProjectBundle\Entity\User
 */
class User  extends BaseUser 
{
    /**
     * @var integer $id
     */
    protected $id;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        if (is_object($this->employee)) {
            return $this->employee->getId();    
        } else {
            return $this->id;            
        }
    }
    
    public function __call($name, $arguments)
    {
        return $this->employee->$name($arguments);
    }
    
    /**
     * @var Armd\ProjectBundle\Entity\Employee
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

    public function __toString()
    {
        return $this->employee->__toString();
    }
    
    public function roleReadAll()
    {
        return  (in_array('ROLE_READ_ALL', $this->getRoles()) ||
                in_array('ROLE_ADMIN', $this->getRoles()) ||
                in_array('ROLE_SUPER_ADMIN', $this->getRoles()));
    }
}