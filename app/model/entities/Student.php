<?php

namespace App\Model\Entity;

use \Doctrine\ORM\Mapping as ORM;


/**
 * Class Teacher
 * Entity represents teacher in database
 * @package App\Model\Entity
 * @ORM\Entity()
 * @ORM\Table(name="students")
 */
class Student extends BaseEntity
{

	/**
	 * @ORM\OneToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 * @var User
	 */
	protected $user;

	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param User $user
	 * @return $this
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}



}