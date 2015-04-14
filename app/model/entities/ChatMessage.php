<?php

namespace App\Model\Entities;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Entities\User;
/**
 * Class ChatMessage
 * @package App\Model\Entities
 * @ORM\Entity()
 * @ORM\Table()
 */
class ChatMessage extends BaseEntity
{
	/**
	 * @var User
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="`from`")
	 */
	protected $from;

	/**
	 * @var User
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="`to`")
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



}