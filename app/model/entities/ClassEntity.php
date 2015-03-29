<?php

namespace App\Model\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use \Doctrine\ORM\Mapping as ORM;
use Nette\InvalidArgumentException;


/**
 * Class ClassEntity
 * Class representing 'class' entity with students in database
 * @ORM\Entity()
 * @ORM\Table(name="classes")
 * @package App\Entities
 */
class ClassEntity extends BaseEntity
{
	const TYPE_CLASS = 'class';
	const TYPE_GROUP = 'group';

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $type;

	/**
	 * @ORM\ManyToMany(targetEntity="Student")
	 * @ORM\JoinTable(name="student_class",
	 *		joinColumns={@ORM\JoinColumn(name="class_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="student_id", referencedColumnName="id")}
	 *		)
	 * @var ArrayCollection
	 */
	protected $students;

	public function __construct()
	{
		parent::__construct();
		$this->students = new ArrayCollection();
	}

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
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType($type)
	{
		if ($type != self::TYPE_CLASS && $type != self::TYPE_GROUP) {
			throw new InvalidArgumentException("Bad enum value for type of class");
		}

		$this->type = $type;
		return $this;
	}



}