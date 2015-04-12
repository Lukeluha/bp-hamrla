<?php

namespace App\Model\Entities;


use \Doctrine\ORM\Mapping as ORM;

/**
 * Class represents User in database
 * @package App\Model\Entities
 * @ORM\Entity(repositoryClass="App\Model\Repositories\Users")
 * @ORM\Table(name="users")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="role", type="string")
 * @ORM\DiscriminatorMap( {"admin" = "Admin", "teacher" = "Teacher", "student" = "Student"} )
 */


abstract class User extends BaseEntity
{

	const ROLE_STUDENT = "student";
	const ROLE_TEACHER = "teacher";
	const ROLE_ADMIN = "admin";

	/**
	 * @ORM\Column(type="string", length=255)
	 * @var string
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @var string
	 */
	protected $surname;


	/**
	 * @ORM\Column(type="string", length=255)
	 * @var string
	 */
	protected $login;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @var string
	 */
	protected $password;

	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $lastActivity;

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
	public function getSurname()
	{
		return $this->surname;
	}

	/**
	 * @param string $surname
	 * @return $this
	 */
	public function setSurname($surname)
	{
		$this->surname = $surname;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPhoto()
	{
		return $this->photo;
	}

	/**
	 * @param string $photo
	 * @return $this
	 */
	public function setPhoto($photo)
	{
		$this->photo = $photo;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLogin()
	{
		return $this->login;
	}

	/**
	 * @param mixed $login
	 * @return $this
	 */
	public function setLogin($login)
	{
		$this->login = $login;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 * @return $this
	 */
	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}



	public function getProfilePicture($size = null)
	{
		$path = IMG_DIR . "/users/user-" . $this->id . ".jpg";

		if (file_exists($path)) { // must check filesystem path, but return only relative url
			return "users/user-" . $this->id . ".jpg";
		} else {
			return "users/user-no-picture.jpg";
		}
	}

	/**
	 * Check if user is currently active and online
	 * @return boolean
	 */
	public function isOnline()
	{
		if (!$this->lastActivity) return false;

		$now = new \DateTime();
		$interval = $now->getTimestamp() - $this->lastActivity->getTimestamp();
		return ($interval <= 45);
	}

	/**
	 * @return \DateTime
	 */
	public function getLastActivity()
	{
		return $this->lastActivity;
	}

	/**
	 * @param \DateTime $lastActivity
	 * @return $this
	 */
	public function setLastActivity($lastActivity)
	{
		$this->lastActivity = $lastActivity;
		return $this;
	}





	/**
	 * Get roles of user
	 * @return array
	 */
	abstract public function getRoles();

	/**
	 * Get all teachings of current user
	 * @return mixed
	 */
	abstract public function getTeachings();

}