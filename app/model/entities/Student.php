<?php

namespace App\Model\Entities;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Teacher
 * Entity represents student in database
 * @ORM\Entity()
 * @ORM\Table(name="users")
 * @package App\Model\Entities
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