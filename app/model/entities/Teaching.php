<?php

namespace App\Model\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Nette\InvalidArgumentException;

/**
 * Class Teaching
 * @package App\Model\Entities
 * @ORM\Entity()
 * @ORM\Table(name="teaching")
 */
class Teaching extends BaseEntity
{
	const CHAT_ALLOWED = 'allowed';
	const CHAT_DISALLOWED = 'disallowed';

	/**
	 * @var Subject
	 * @ORM\ManyToOne(targetEntity="Subject")
	 * @ORM\JoinColumn(name="subject_id", referencedColumnName="id")
	 */
	protected $subject;

	/**
	 * @var ClassEntity
	 * @ORM\ManyToOne(targetEntity="ClassEntity", inversedBy="teachings")
	 * @ORM\JoinColumn(name="class_id", referencedColumnName="id")
	 */
	protected $class;

	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="Teacher", inversedBy="teachings")
	 * @ORM\JoinTable(name="teaching_teachers",
	 *		joinColumns={@ORM\JoinColumn(name="teaching_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="teacher_id", referencedColumnName="id")}
	 * )
	 */
	protected $teachers;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="TeachingTime", mappedBy="teaching")
	 */
	protected $teachingTimes;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="Lesson", mappedBy="teaching")
	 */
	protected $lessons;

	/**
	 * @var string
	 * @ORM\Column(type="enum")
	 */
	protected $chat = self::CHAT_ALLOWED;


	public function __construct()
	{
		$this->teachers = new ArrayCollection();
		$this->teachingTimes = new ArrayCollection();
		$this->lessons = new ArrayCollection();
	}

	/**
	 * @return ClassEntity
	 */
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * @param ClassEntity $class
	 * @return $this
	 */
	public function setClass($class)
	{
		$this->class = $class;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getChat()
	{
		return $this->chat;
	}

	/**
	 * @param string $chat
	 * @return $this
	 */
	public function setChat($chat)
	{
		if ($chat != self::CHAT_ALLOWED && $chat != self::CHAT_DISALLOWED) {
			throw new InvalidArgumentException('Bad chat settings');
		}

		$this->chat = $chat;
		return $this;
	}

	/**
	 * @return Subject
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * @param Subject $subject
	 * @return $this
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * @return ArrayCollection
	 */
	public function getTeachers()
	{
		return $this->teachers;
	}

	/**
	 * @param ArrayCollection $teachers
	 * @return $this
	 */
	public function setTeachers($teachers)
	{
		$this->teachers = $teachers;
		return $this;
	}

	public function addTeacher(Teacher $teacher)
	{
		$this->teachers[] = $teacher;
		$teacher->addTeaching($this);
	}

	/**
	 * @return ArrayCollection
	 */
	public function getTeachingTimes()
	{
		return $this->teachingTimes;
	}

	/**
	 * @param ArrayCollection $teachingTimes
	 * @return $this
	 */
	public function setTeachingTimes($teachingTimes)
	{
		$this->teachingTimes = $teachingTimes;
		return $this;
	}

	public function addTeachingTime($teachingTime)
	{
		$this->teachingTimes[] = $teachingTime;
		return $this;
	}

	/**
	 * @return ArrayCollection
	 */
	public function getLessons()
	{
		return $this->lessons;
	}

	/**
	 * @param ArrayCollection $lessons
	 * @return $this
	 */
	public function setLessons($lessons)
	{
		$this->lessons = $lessons;
		return $this;
	}

}