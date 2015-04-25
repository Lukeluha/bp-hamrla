<?php

namespace App\Model\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Lesson
 * @package App\Model\Entities
 * @ORM\Entity(repositoryClass="\App\Model\Repositories\Lessons")
 * @ORM\Table(name="lessons")
 */
class Lesson extends BaseEntity
{
	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $startDate;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $endDate;

	/**
	 * @var Teaching
	 * @ORM\ManyToOne(targetEntity="Teaching", inversedBy="lessons")
	 * @ORM\JoinColumn(name="teaching_id", referencedColumnName="id")
	 */
	protected $teaching;

	/**
	 * Name of lesson
	 * @var string
	 * @ORM\Column()
	 */
	protected $name;

	/**
	 * Description of lesson
	 * @var string
	 * @ORM\Column()
	 */
	protected $description;

	/**
	 * Rank of lesson in schoolYear
	 * @var int
	 */
	protected $rank;

	/**
	 * @ORM\OneToMany(targetEntity="Post", mappedBy="lesson")
 	 * @ORM\OrderBy(value={"created" = "desc"})
	 * @var ArrayCollection
	 */
	protected $posts;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="Question", mappedBy="lesson")
	 */
	protected $questions;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="Task", mappedBy="lesson")
	 */
	protected $tasks;

	public function __construct()
	{
		$this->questions = new ArrayCollection();
		$this->tasks = new ArrayCollection();
	}

	/**
	 * @return \DateTime
	 */
	public function getStartDate()
	{
		return $this->startDate;
	}

	/**
	 * @param \DateTime $startDate
	 * @return $this
	 */
	public function setStartDate($startDate)
	{
		$this->startDate = $startDate;
		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getEndDate()
	{
		return $this->endDate;
	}

	/**
	 * @param \DateTime $endDate
	 * @return $this
	 */
	public function setEndDate($endDate)
	{
		$this->endDate = $endDate;
		return $this;
	}



	/**
	 * @return Teaching
	 */
	public function getTeaching()
	{
		return $this->teaching;
	}

	/**
	 * @param Teaching $teaching
	 * @return $this
	 */
	public function setTeaching($teaching)
	{
		$this->teaching = $teaching;
		return $this;
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
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 * @return $this
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getRank()
	{
		return $this->rank;
	}

	/**
	 * @param int $rank
	 * @return $this
	 */
	public function setRank($rank)
	{
		$this->rank = $rank;
		return $this;
	}

	/**
	 * @return ArrayCollection
	 */
	public function getQuestions()
	{
		return $this->questions;
	}

	/**
	 * @param ArrayCollection $questions
	 * @return $this
	 */
	public function setQuestions($questions)
	{
		$this->questions = $questions;
		return $this;
	}

	/**
	 * @return ArrayCollection
	 */
	public function getPosts()
	{
		return $this->posts;
	}

	/**
	 * @param ArrayCollection $posts
	 * @return $this
	 */
	public function setPosts($posts)
	{
		$this->posts = $posts;
		return $this;
	}

	/**
	 * @return ArrayCollection
	 */
	public function getTasks()
	{
		return $this->tasks;
	}

	/**
	 * @param ArrayCollection $tasks
	 * @return $this
	 */
	public function setTasks($tasks)
	{
		$this->tasks = $tasks;
		return $this;
	}




}