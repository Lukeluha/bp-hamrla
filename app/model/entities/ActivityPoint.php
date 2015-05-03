<?php

namespace App\Model\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * Class ActivityPoint
 * @package App\Model\Entities
 * @ORM\Entity()
 * @ORM\Table()
 */
class ActivityPoints extends BaseEntity
{
	/**
	 * @var Lesson
	 * @ORM\ManyToOne(targetEntity="Lesson")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $lesson;

	/**
	 * @var Student
	 * @ORM\ManyToOne(targetEntity="Student")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $student;

	/**
	 * @var int
	 * @ORM\Column(name="`count`")
	 */
	protected $count;

	/**
	 * @return Lesson
	 */
	public function getLesson()
	{
		return $this->lesson;
	}

	/**
	 * @param Lesson $lesson
	 * @return $this
	 */
	public function setLesson($lesson)
	{
		$this->lesson = $lesson;
		return $this;
	}

	/**
	 * @return Student
	 */
	public function getStudent()
	{
		return $this->student;
	}

	/**
	 * @param Student $student
	 * @return $this
	 */
	public function setStudent($student)
	{
		$this->student = $student;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCount()
	{
		return $this->count;
	}

	/**
	 * @param int $count
	 * @return $this
	 */
	public function setCount($count)
	{
		$this->count = $count;
		return $this;
	}



}