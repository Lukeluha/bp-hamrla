<?php

namespace App\Model\Entity;
use \Doctrine\ORM\Mapping as ORM;
use Nette\Security\privilege;
use Nette\Security\role;


/**
 * Class Teacher
 * Entity represents admin in database
 * @ORM\Entity()
 * @ORM\Table(name="users")
 * @package App\Model\Entity
 */
class Admin extends Teacher
{
	public function getRoles()
	{
		$roles = parent::getRoles();
		$roles[] = self::ROLE_ADMIN;

		return $roles;
	}
}