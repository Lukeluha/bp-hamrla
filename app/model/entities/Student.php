<?php

namespace App\Model\Entities;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Teacher
 * Entity represents student in database
 * @ORM\Entity(repositoryClass="App\Model\Repositories\Students")
 * @ORM\Table(name="users")
 * @package App\Model\Entities
 */
class Student extends User
{
	/**
	 * Classes which student attends
	 * @ORM\ManyToMany(targetEntity="ClassEntity", mappedBy="students")
	 * @ORM\JoinTable(name="student_class",
	 *      joinColumns={@ORM\JoinColumn(name="student_id", referencedColumnName="id")},
	 *		inverseJoinColumns={@ORM\JoinColumn(name="class_id", referencedColumnName="id")}
	 *		)
	 * @var ArrayCollection
	 */
	protected $classes;


	public function __construct()
	{
		$this->classes = new ArrayCollection();
	}

	/**
	 * Get role of user
	 * @return array
	 */
	public function getRoles()
	{
		return array(self::ROLE_STUDENT);
	}

	/**
	 * @return ArrayCollection
	 */
	public function getClasses()
	{
		return $this->classes;
	}

	/**
	 * @param ArrayCollection $classes
	 * @return $this
	 */
	public function setClasses($classes)
	{
		$this->classes = $classes;
		return $this;
	}

	/**
	 * @param ClassEntity $class
	 * @return $this
	 */
	public function addClass(ClassEntity $class)
	{
		$this->classes[] = $class;
		return $this;
	}

	/**
	 * Get main class of student (e.g. 4.B) for given school year
	 * @param SchoolYear $schoolYear
	 * @return ClassEntity
	 */
	public function getMainClass(SchoolYear $schoolYear)
	{
		foreach ($this->classes as $class) {
			if ($class->getType() == ClassEntity::TYPE_CLASS && $class->getSchoolYear()->getId() == $schoolYear->getId()) {
				return $class;
			}
		}

		return null;
	}

	public function isInClass($classId)
	{
		foreach ($this->classes as $class) {
			if ($class->getId() == $classId) return true;
		}

		return false;
	}

	public function removeFromClass(ClassEntity $class)
	{
		$this->classes->removeElement($class);
	}

}