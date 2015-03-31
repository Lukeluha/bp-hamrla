<?php

namespace App\Model\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use \Doctrine\ORM\Mapping as ORM;
use Nette\InvalidArgumentException;


/**
 * Class ClassEntity
 * Class representing 'class' entity with students in database
 * @ORM\Entity(repositoryClass="App\Model\Repositories\Classes")
 * @ORM\Table(name="classes")
 * @package App\Model\Entities
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
	 * @ORM\ManyToMany(targetEntity="Student",inversedBy="classes")
	 * @ORM\JoinTable(name="student_class",
	 *		joinColumns={@ORM\JoinColumn(name="class_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="student_id", referencedColumnName="id")}
	 *		)
	 * @var ArrayCollection
	 */
	protected $students;


	/**
	 * @ORM\ManyToOne(targetEntity="SchoolYear")
	 * @ORM\JoinColumn(name="school_year_id", referencedColumnName="id")
	 * @var SchoolYear
	 */
	protected $schoolYear;

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

	/**
	 * @return ArrayCollection
	 */
	public function getStudents()
	{
		return $this->students;
	}

	/**
	 * @param ArrayCollection $students
	 * @return $this
	 */
	public function setStudents($students)
	{
		$this->students = $students;
		return $this;
	}

	public function addStudent(Student $student)
	{
		$this->students[] = $student;
		$student->addClass($this);
		return $this;
	}

	public function removeStudent(Student $student)
	{
		$this->students->removeElement($student);
		$student->removeFromClass($this);
	}

	/**
	 * @return SchoolYear
	 */
	public function getSchoolYear()
	{
		return $this->schoolYear;
	}

	/**
	 * @param SchoolYear $schoolYear
	 * @return $this
	 */
	public function setSchoolYear($schoolYear)
	{
		$this->schoolYear = $schoolYear;
		return $this;
	}
}