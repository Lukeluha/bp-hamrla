<?php

namespace App\Forms;

use App\Model\Services\UserService;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class StudentFormFactory extends Object
{
	private $em;

	private $userService;

	private $student;

	public function __construct(EntityManager $em, UserService $userService)
	{
		$this->em = $em;
		$this->userService = $userService;
	}

	public function create(){
		return new StudentForm($this->em, $this->userService, $this->student);
	}

	/**
	 * @return mixed
	 */
	public function getStudent()
	{
		return $this->student;
	}

	/**
	 * @param mixed $student
	 * @return $this
	 */
	public function setStudent($student)
	{
		$this->student = $student;
		return $this;
	}



}