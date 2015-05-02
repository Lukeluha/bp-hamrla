<?php

namespace App\Model\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Rating
 * @package App\Model\Entities
 * @ORM\Table(name="ratings")
 * @ORM\Entity()
 */
class Rating extends BaseEntity
{
	/**
	 * @var float
	 * @ORM\Column()
	 */
	protected $points;

	/**
	 * @var User
	 * @ORM\ManyToOne(targetEntity="User")
	 */
	protected $user;

	/**
	 * @var TaskCompleted
	 * @ORM\ManyToOne(targetEntity="TaskCompleted", inversedBy="ratings")
	 */
	protected $taskCompleted;

	/**
	 * @return float
	 */
	public function getPoints()
	{
		return $this->points;
	}

	/**
	 * @param float $points
	 * @return $this
	 */
	public function setPoints($points)
	{
		$this->points = $points;
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
	 * @return TaskCompleted
	 */
	public function getTaskCompleted()
	{
		return $this->taskCompleted;
	}

	/**
	 * @param TaskCompleted $taskCompleted
	 * @return $this
	 */
	public function setTaskCompleted($taskCompleted)
	{
		$this->taskCompleted = $taskCompleted;
		return $this;
	}

}