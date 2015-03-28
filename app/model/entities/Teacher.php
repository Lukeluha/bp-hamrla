<?php

namespace App\Model\Entities;
use \Doctrine\ORM\Mapping as ORM;
use Nette\Security\privilege;
use Nette\Security\role;


/**
 * Class Teacher
 * Entity represents teacher in database
 * @ORM\Entity()
 * @ORM\Table(name="users")
 * @package App\Model\Entities
 */
class Teacher extends User
{

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $room;

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
	 * Get role of user
	 * @return string
	 */
	public function getRoles()
	{
		return array(self::ROLE_TEACHER);
	}


}