<?php

namespace Armd\ProjectBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Armd\ProjectBundle\Entity\Employee
 */
class Employee
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $surname
     */
    private $surname;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $patronymic
     */
    private $patronymic;



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
     * Set surname
     *
     * @param string $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * Get surname
     *
     * @return string 
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set patronymic
     *
     * @param string $patronymic
     */
    public function setPatronymic($patronymic)
    {
        $this->patronymic = $patronymic;
    }

    /**
     * Get patronymic
     *
     * @return string 
     */
    public function getPatronymic()
    {
        return $this->patronymic;
    }

    
    
    public function __toString()
    {
        try {
            $title = 
            	$this->surname . ' ' . 
            	$this->name . ' ' . 
            	$this->patronymic . 
            	($this->getDischarged()? ' (уволен)':'') .
            	($this->getSubcontractor()? ' (субподрядчик)':'');
        } catch (Exception $e) {
            $title = $this->id;
        }
        return $title;
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
        return ($this->getSubcontractor()?0:$this->salary);
    }
    
    /**
     * @var Armd\ProjectBundle\Entity\Department
     */
    private $department;


    /**
     * Set department
     *
     * @param Armd\ProjectBundle\Entity\Department $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }

    /**
     * Get department
     *
     * @return Armd\ProjectBundle\Entity\Department 
     */
    public function getDepartment()
    {
        return $this->department;
    }
    /**
     * @var Armd\ProjectBundle\Entity\ProjectEmployee
     */
    private $project_employee;


    /**
     * Set project_employee
     *
     * @param Armd\ProjectBundle\Entity\ProjectEmployee $projectEmployee
     */
    public function setProjectEmployee(\Armd\ProjectBundle\Entity\ProjectEmployee $projectEmployee)
    {
        $this->project_employee = $projectEmployee;
    }

    /**
     * Get project_employee
     *
     * @return Armd\ProjectBundle\Entity\ProjectEmployee 
     */
    public function getProjectEmployee()
    {
        return $this->project_employee;
    }
    public function __construct()
    {
        //parent::__construct();
        $this->project_employee = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add project_employee
     *
     * @param Armd\ProjectBundle\Entity\ProjectEmployee $projectEmployee
     */
    public function addProjectEmployee(\Armd\ProjectBundle\Entity\ProjectEmployee $projectEmployee)
    {
        $this->project_employee[] = $projectEmployee;
    }
    /**
     * @var Armd\ProjectBundle\Entity\User
     */
    private $user;


    /**
     * Set user
     *
     * @param Armd\ProjectBundle\Entity\User $user
     */
    public function setUser(\Armd\ProjectBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Armd\ProjectBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @var string $email
     */
    private $email;


    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * @var boolean $discharged
     */
    private $discharged;


    /**
     * Set discharged
     *
     * @param boolean $discharged
     */
    public function setDischarged($discharged)
    {
        $this->discharged = $discharged;
    }

    /**
     * Get discharged
     *
     * @return boolean 
     */
    public function getDischarged()
    {
        return $this->discharged;
    }
	
		
	//----------------------
	public function setOutside($outside)
    {
        
    }

	/*public function getOutside()
    {
		//$this->outside = false;
		
        return isset($this->outside)?$this->outside:false;
    }*/

	public function isOutside()
    {
        
		$host="10.32.17.6";
		$user="employe";
		$pwd="Oongee6m";
		$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');
		mysql_select_db("employe",$db);
		$outside = mysql_select("select outside from employees where employee_id = ".$this->id);
		if (isset($outside[0]['outside']) && $outside[0]['outside'] == 1){
			$outside = true;
		}else{
			$outside = false;
		}
		mysql_close($db);
		//$this->outside = false;
        return isset($outside)?$outside:false;
    }
	//****************************
	public function setRate($rate)
    {
        if ($this->isOutside()){
			$host="10.32.17.6";
			$user="employe";
			$pwd="Oongee6m";
			$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');
			mysql_select_db("employe",$db);
			mysql_query("update employees set category = ".$rate." where employee_id = ".$this->id);
			mysql_query("update employees_history set category = ".$rate." where employee_id = ".$this->id);
			mysql_close($db);
		}
    }

	public function getRate()
    {
		
		$host="10.32.17.6";
		$user="employe";
		$pwd="Oongee6m";
		$db=mysql_connect($host,$user,$pwd) or die("Could not connect: " . mysql_error().'\n');
		mysql_select_db("employe",$db);
		$rate = mysql_select("select category from employees where employee_id = ".$this->id);
		mysql_close($db);
		//$this->outside = false;
		$rate = isset($rate[0]['category'])?$rate[0]['category']:"0";
        return isset($rate)?$rate:"0";
    }

	/*public function isRate()
    {
        //$this->outside = false;
        return isset($this->outside)?$this->outside:false;
    }*/
	//---------------------------
    
    public function getTitle()
    {
        return $this->__toString();
    }
    /**
     * @var Armd\ProjectBundle\Entity\Post
     */
    private $post;


    /**
     * Set post
     *
     * @param Armd\ProjectBundle\Entity\Post $post
     */
    public function setPost(\Armd\ProjectBundle\Entity\Post $post)
    {
        $this->post = $post;
    }

    /**
     * Get post
     *
     * @return Armd\ProjectBundle\Entity\Post 
     */
    public function getPost()
    {
        return $this->post;
    }
    /**
     * @var integer $time
     */
    private $time;


    /**
     * Set time
     *
     * @param integer $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * Get time
     *
     * @return integer 
     */
    public function getTime()
    {
		if ($this->time === null)
			$this->time = 8;
        return $this->time;
    }
    
    /**
     * @var boolean $subcontractor
     */
    private $subcontractor;


    /**
     * Set subcontractor
     *
     * @param boolean $subcontractor
     */
    public function setSubcontractor($subcontractor)
    {
        $this->subcontractor = $subcontractor;
    }

    /**
     * Get subcontractor
     *
     * @return boolean 
     */
    public function getSubcontractor()
    {
        return $this->subcontractor;
    }
}

function mysql_select($query)
{
	$res = array();
	$result = mysql_query($query);
	
	while ($row = mysql_fetch_assoc($result)) {
		$res[] = $row;
	}
	return $res;
}