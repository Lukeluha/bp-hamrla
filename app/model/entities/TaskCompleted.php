<?php

namespace App\Model\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * Class TaskCompleted
 * @package App\Model\Entities
 * @ORM\Table(name="tasks_completed")
 * @ORM\Entity()
 */
class TaskCompleted extends BaseEntity
{
	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @var Student
	 * @ORM\ManyToOne(targetEntity="Student")
	 * @ORM\JoinColumn(name="student_id")
	 */
	protected $student;

	/**
	 * @var Task
	 * @ORM\ManyToOne(targetEntity="Task")
	 * @ORM\JoinColumn(name="task_id")
	 */
	protected $task;

	/**
	 * @var string
	 * @ORM\Column()
	 */
	protected $note;

	/**
	 * @var int
	 * @ORM\Column()
	 */
	protected $points;

	/**
	 * @var string
	 * @ORM\Column(name="filename")
	 */
	protected $filename;

	/**
	 * @return \DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @param \DateTime $created
	 * @return $this
	 */
	public function setCreated($created)
	{
		$this->created = $created;
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
	 * @return Task
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * @param Task $task
	 * @return $this
	 */
	public function setTask($task)
	{
		$this->task = $task;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getNote()
	{
		return $this->note;
	}

	/**
	 * @param string $note
	 * @return $this
	 */
	public function setNote($note)
	{
		$this->note = $note;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPoints()
	{
		return $this->points;
	}

	/**
	 * @param int $points
	 * @return $this
	 */
	public function setPoints($points)
	{
		$this->points = $points;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * @param string $filename
	 * @return $this
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
		return $this;
	}





}