<?php

namespace App\Model\Entity;


use \Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity;
use Nette\Utils\Image;

/**
 * Class represents User in database
 * @package App\Model\Entities
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends BaseEntity implements IIdentity
{

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
	 * @var Role
	 * @ORM\ManyToOne(targetEntity="Role")
	 * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
	 */
	protected $role;

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

	/**
	 * @return Role
	 */
	public function getRole()
	{
		return $this->role;
	}

	/**
	 * @param Role $role
	 * @return $this
	 */
	public function setRole($role)
	{
		$this->role = $role;
		return $this;
	}

	public function getRoles()
	{
		return array($this->getRole()->getName());
	}

	public function getProfilePicture()
	{
		$path = IMG_DIR . "/users/user-" . $this->id . ".jpg";

		if (file_exists($path)) { // must check filesystem path, but return only relative url
			return "users/user-" . $this->id . ".jpg";
		} else {
			return "users/user-no-picture.jpg";
		}
	}
}