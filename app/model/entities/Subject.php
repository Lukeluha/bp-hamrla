<?php

namespace App\Model\Entities;


use \Doctrine\ORM\Mapping as ORM;

/**
 * Class Subject
 * @package App\Model\Entities
 * @ORM\Entity()
 * @ORM\Table(name="subjects")
 */
class Subject extends BaseEntity
{
	/**
	 * @var string
	 * @ORM\Column()
	 */
	protected $name;

	/**
	 * @var string
	 * @ORM\Column()
	 */
	protected $abbreviation;

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

	/**
	 * @return string
	 */
	public function getAbbreviation()
	{
		return $this->abbreviation;
	}

	/**
	 * @param string $abbreviation
	 * @return $this
	 */
	public function setAbbreviation($abbreviation)
	{
		$this->abbreviation = $abbreviation;
		return $this;
	}

	public function __toString()
	{
		return $this->abbreviation . " - " . $this->name;
	}


}