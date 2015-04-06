<?php

namespace App\Model\Entities;


use \Doctrine\ORM\Mapping as ORM;

/**
 * Class SchoolYear
 * @package App\Model\Entities
 * @ORM\Entity()
 * @ORM\Table(name="school_years")
 */
class SchoolYear extends BaseEntity
{
	/**
	 * @ORM\Column(type="datetime",name="`from`")
	 * @var \DateTime
	 */
	protected $from;

	/**
	 * @ORM\Column(type="datetime",name="`to`")
	 * @var \DateTime
	 */
	protected $to;

	/**
	 * @ORM\Column(type="boolean")
	 * @var boolean
	 */
	protected $closed = false;

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
		$this->to = $to;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * @return boolean
	 */
	public function getClosed()
	{
		return $this->closed;
	}

	/**
	 * @param boolean $closed
	 * @return $this
	 */
	public function setClosed($closed)
	{
		$this->closed = $closed;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isClosed()
	{
		return $this->closed;
	}

	public function __toString()
	{
		return $this->getFrom()->format("Y") . "/" . $this->getTo()->format("Y");
	}
}