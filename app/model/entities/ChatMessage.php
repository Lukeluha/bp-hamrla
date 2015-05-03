<?php

namespace App\Model\Entities;

use Doctrine\ORM\Mapping as ORM;
/**
 * Class ChatMessage
 * @package App\Model\Entities
 * @ORM\Entity(repositoryClass="\App\Model\Repositories\ChatMessages")
 * @ORM\Table(name="chat_messages")
 */
class ChatMessage extends BaseEntity
{
	/**
	 * @var User
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="from_user_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $from;

	/**
	 * @var User
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="to_user_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $to;

	/**
	 * @var string
	 * @ORM\Column();
	 */
	protected $message;

	/**
	 * @var bool
	 * @ORM\Column(type="integer", name="`read`")
	 */
	protected $read = 0;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime", name="`datetime`")
	 */
	protected $datetime;

	/**
	 * @return User
	 */
	public function getFrom()
	{
		return $this->from;
	}

	/**
	 * @param User $from
	 * @return $this
	 */
	public function setFrom($from)
	{
		$this->from = $from;
		return $this;
	}

	/**
	 * @return User
	 */
	public function getTo()
	{
		return $this->to;
	}

	/**
	 * @param User $to
	 * @return $this
	 */
	public function setTo($to)
	{
		$this->to = $to;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @param string $message
	 * @return $this
	 */
	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getRead()
	{
		return $this->read;
	}

	/**
	 * @return boolean
	 */
	public function isRead()
	{
		return $this->read;
	}

	/**
	 * @param boolean $read
	 * @return $this
	 */
	public function setRead($read)
	{
		$this->read = $read;
		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetime()
	{
		return $this->datetime;
	}

	/**
	 * @param \DateTime $datetime
	 * @return $this
	 */
	public function setDatetime($datetime)
	{
		$this->datetime = $datetime;
		return $this;
	}





}