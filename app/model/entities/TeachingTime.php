<?php

namespace App\Model\Entities;

use Doctrine\ORM\Mapping as ORM;
use Nette\InvalidArgumentException;

/**
 * Class TeachingTime
 * @package App\Model\Entities
 * @ORM\Entity()
 * @ORM\Table(name="teaching_time")
 */
class TeachingTime extends BaseEntity
{
	const WEEK_EVEN = 'even';
	const WEEK_ODD = 'odd';

	/**
	 * @var \DateTime
	 * @ORM\Column(type="time", name="`from`")
	 */
	protected $from;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="time", name="`to`")
	 */
	protected $to;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	protected $weekDay;

	/**
	 * @var string
	 * @ORM\Column(type="enum")
	 */
	protected $weekParity;

	/**
	 * @var Teaching
	 * @ORM\ManyToOne(targetEntity="Teaching")
	 * @ORM\JoinColumn(name="teaching_id")
	 */
	protected $teaching;

	/**
	 * @return \DateTime
	 */
	public function getFrom()
	{
		return $this->from;
	}

	/**
	 * @param \DateTime $from
	 * @return $this
	 */
	public function setFrom($from)
	{
		if (!is_object($from)) {
			$from = new \DateTime($from);
		}
		$this->from = $from;
		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getTo()
	{
		return $this->to;
	}

	/**
	 * @param \DateTime $to
	 * @return $this
	 */
	public function setTo($to)
	{
		if (!is_object($to)) {
			$to = new \DateTime($to);
		}
		$this->to = $to;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getWeekDay()
	{
		return $this->weekDay;
	}

	/**
	 * @param int $weekDay
	 * @return $this
	 */
	public function setWeekDay($weekDay)
	{
		$this->weekDay = $weekDay;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getWeekParity()
	{
		return $this->weekParity;
	}

	/**
	 * @param string $weekParity
	 * @return $this
	 */
	public function setWeekParity($weekParity)
	{
		if ($weekParity != self::WEEK_EVEN && $weekParity != self::WEEK_ODD) {
			throw new InvalidArgumentException('Bad week parity');
		}
		$this->weekParity = $weekParity;
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



}