<?php

namespace App\Model\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Comment
 * @package App\Model\Entities
 * @ORM\Entity()
 * @ORM\Table(name="comments")
 */
class Comment extends BaseEntity
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
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @var Post
	 * @ORM\ManyToOne(targetEntity="Post", inversedBy="replies")
	 * @ORM\JoinColumn(name="reply_to", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $replyTo;

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
	 * @return Post
	 */
	public function getReplyTo()
	{
		return $this->replyTo;
	}

	/**
	 * @param Post $replyTo
	 * @return $this
	 */
	public function setReplyTo($replyTo)
	{
		$this->replyTo = $replyTo;
		return $this;
	}




}