<?php

namespace App\Model\Entities;
use Doctrine\Common\Collections\ArrayCollection;
use \Doctrine\ORM\Mapping as ORM;
use Nette\Security\privilege;
use Nette\Security\role;


/**
 * Class Teacher
 * Entity represents teacher in database
 * @ORM\Entity()
 * @ORM\Table(name="users")
 * @package App\Model\Entities
 */
class Teacher extends User
{

	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="Teaching", mappedBy="teachers")
	 * @ORM\JoinTable(name="teaching_teachers",
	 *      joinColumns={@ORM\JoinColumn(name="teacher_id", referencedColumnName="id")},
	 *		inverseJoinColumns={@ORM\JoinColumn(name="teaching_id", referencedColumnName="id")}
	 * )
	 */
	protected $teachings;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $room;

	public function __construct()
	{
		$this->teachings = new ArrayCollection();
	}

	/**
	 * @return string
	 */
	public function getRoom()
	{
		return $this->room;
	}

	/**
	 * @param string $room
	 * @return $this
	 */
	public function setRoom($room)
	{
		$this->room = $room;
		return $this;
	}

	/**
	 * @return ArrayCollection
	 */
	public function getTeachings()
	{
		return $this->teachings;
	}

	/**
	 * @param ArrayCollection $teachings
	 * @return $this
	 */
	public function setTeachings($teachings)
	{
		$this->teachings = $teachings;
		return $this;
	}

	public function addTeaching(Teaching $teaching)
	{
		$this->teachings[] = $teaching;
	}

	/**
	 * Get role of user
	 * @return string
	 */
	public function getRoles()
	{
		return array(self::ROLE_TEACHER);
	}

}