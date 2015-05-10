<?php

namespace App\Model\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Post
 * @package App\Model\Entities
 * @ORM\Table(name="posts")
 * @ORM\Entity(repositoryClass="App\Model\Repositories\Posts")
 */
class Post extends BaseEntity
{
	/**
	 * @var string
	 * @ORM\Column()
	 */
	protected $text;

	/**
	 * @var User
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $user;


	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="Comment", mappedBy="replyTo", fetch="EXTRA_LAZY")
	 */
	protected $replies;

	/**
	 * @var Teaching
	 * @ORM\ManyToOne(targetEntity="Teaching", inversedBy="posts")
	 * @ORM\JoinColumn(name="teaching_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $teaching;

	/**
	 * @var Lesson
	 * @ORM\ManyToOne(targetEntity="Lesson")
	 * @ORM\JoinColumn(name="lesson_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $lesson;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @var boolean
	 * @ORM\Column(type="integer")
	 */
	protected $anonymous;


	public function __construct()
	{
		$this->replies = new ArrayCollection();
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @param string $text
	 * @return $this
	 */
	public function setText($text)
	{
		$this->text = $text;
		return $this;
	}

	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param User $user
	 * @return $this
	 */
	public function setUser($user)
	{
		$this->user = $user;
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
	 * @return ArrayCollection
	 */
	public function getReplies()
	{
		return $this->replies;
	}

	/**
	 * @param ArrayCollection $replies
	 * @return $this
	 */
	public function setReplies($replies)
	{
		$this->replies = $replies;
		return $this;
	}

	public function getRepliesCount()
	{
		return $this->replies->count();
	}

	/**
	 * @return boolean
	 */
	public function getAnonymous()
	{
		return $this->anonymous;
	}

	/**
	 * @param boolean $anonymous
	 * @return $this
	 */
	public function setAnonymous($anonymous)
	{
		$this->anonymous = $anonymous;
		return $this;
	}

	public function getLocation()
	{

		$location = $this->teaching->getClass()->getName() . " | " . $this->teaching->getSubject()->getAbbreviation();

		if ($this->lesson) {
			if ($this->lesson->getName()) {
				$location .= ' | ' . $this->lesson->getName();
			} else {
				$location .= ' | ' . "Hodina";
			}
		}

		return $location;
	}


}