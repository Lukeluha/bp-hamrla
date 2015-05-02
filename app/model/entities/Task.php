<?php

namespace App\Model\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Nette\InvalidArgumentException;

/**
 * Class Task
 * @package App\Model\Entities
 * @ORM\Table(name="tasks")
 * @ORM\Entity()
 */
class Task extends BaseEntity
{
	const LIMIT_STRICT = 'strict';
	const LIMIT_NO_STRICT = 'nostrict';

	/**
	 * @var string
	 * @ORM\Column()
	 */
	protected $taskName;

	/**
	 * @var string
	 * @ORM\Column()
	 */
	protected $taskText;

	/**
	 * @var boolean
	 * @ORM\Column(type="integer")
	 */
	protected $visible;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $end;

	/**
	 * @var string
	 * @ORM\Column(name="limit_type")
	 */
	protected $limit;

	/**
	 * @var Lesson
	 * @ORM\ManyToOne(targetEntity="Lesson", inversedBy="tasks")
	 */
	protected $lesson;

	/**
	 * @var Group
	 * @ORM\ManyToOne(targetEntity="Group")
	 */
	protected $group;

	/**
	 * @var boolean
	 * @ORM\Column(type="integer")
	 */
	protected $studentRating;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="TaskCompleted", mappedBy="task")
	 */
	protected $completedTasks;


	public function __construct()
	{
		$this->completedTasks = new ArrayCollection();
	}

	/**
	 * @return string
	 */
	public function getTaskText()
	{
		return $this->taskText;
	}

	/**
	 * @param string $taskText
	 * @return $this
	 */
	public function setTaskText($taskText)
	{
		$this->taskText = $taskText;
		return $this;
	}


	/**
	 * @return \DateTime
	 */
	public function getEnd()
	{
		return $this->end;
	}

	/**
	 * @param \DateTime $end
	 * @return $this
	 */
	public function setEnd($end)
	{
		$this->end = $end;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * @param string $limit
	 * @return $this
	 */
	public function setLimit($limit)
	{
		if ($limit != self::LIMIT_NO_STRICT && $limit != self::LIMIT_STRICT) {
			throw new InvalidArgumentException('Unknown limit type');
		}

		$this->limit = $limit;
		return $this;
	}

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
	 * @return Group
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @param Group $group
	 * @return $this
	 */
	public function setGroup($group)
	{
		$this->group = $group;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getStudentRating()
	{
		return $this->studentRating;
	}

	/**
	 * @return bool
	 */
	public function isStudentRating()
	{
		return $this->studentRating;
	}

	/**
	 * @param boolean $studentRating
	 * @return $this
	 */
	public function setStudentRating($studentRating)
	{
		$this->studentRating = $studentRating;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTaskName()
	{
		return $this->taskName;
	}

	/**
	 * @param string $taskName
	 * @return $this
	 */
	public function setTaskName($taskName)
	{
		$this->taskName = $taskName;
		return $this;
	}


	/**
	 * @return ArrayCollection
	 */
	public function getCompletedTasks()
	{
		return $this->completedTasks;
	}

	/**
	 * @param ArrayCollection $completedTasks
	 * @return $this
	 */
	public function setCompletedTasks($completedTasks)
	{
		$this->completedTasks = $completedTasks;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getVisible()
	{
		return $this->visible;
	}

	/**
	 * @param boolean $visible
	 * @return $this
	 */
	public function setVisible($visible)
	{
		$this->visible = $visible;
		return $this;
	}

	public function isVisible()
	{
		return $this->visible;
	}



}