<?php

namespace App\Model\Entities;

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
	 * @ORM\Column(type="datetime", name="`date`")
	 */
	protected $date;

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
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @param \DateTime $date
	 * @return $this
	 */
	public function setDate($date)
	{
		$this->date = $date;
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




}