<?php

namespace App\Model\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Teacher
 * Entity represents teacher in database
 * @ORM\Entity()
 * @ORM\Table(name="users")
 * @package App\Model\Entity
 */
class Student extends User
{
	/**
	 * Get role of user
	 * @return array
	 */
	public function getRoles()
	{
		return array(self::ROLE_STUDENT);
	}

}