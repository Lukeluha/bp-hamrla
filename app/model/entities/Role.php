<?php

namespace App\Model\Entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * Entity represents user role
 * @package App\Model
 * @ORM\Entity
 * @ORM\Table(name="roles")
 */
class Role extends BaseEntity
{
	/**
	 * @ORM\Column(type="string", length=255)
	 * @var string Name of role
	 */
	protected $name;

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
}