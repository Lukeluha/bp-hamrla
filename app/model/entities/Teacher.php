<?php

namespace App\Model\Entity;

use \Doctrine\ORM\Mapping as ORM;


/**
 * Class Teacher
 * Entity represents teacher in database
 * @package App\Model\Entity
 * @ORM\Entity()
 * @ORM\Table(name="teachers")
 */
class Teacher extends BaseEntity
{
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $room;

	/**
	 * @ORM\OneToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 * @var User
	 */
	protected $user;

	/**
	 * @return string
	 */
	public function getRoom()
	{
		return $this->room;
	}

	/**
	 * @param string $room
	 * @return $this
	 */
	public function setRoom($room)
	{
		$this->room = $room;
		return $this;
	}

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