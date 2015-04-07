<?php

namespace App\Model\Services;

use App\Model\Entities\Student;
use App\Model\Entities\User;
use Kdyby\Doctrine\EntityManager;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Utils\Random;
use Nette\Utils\Strings;

class UserService extends BaseService implements IAuthenticator
{

	/**
	 * @var \Kdyby\Doctrine\EntityRepository
	 */
	protected $users;

	public function __construct(EntityManager $em)
	{
		parent::__construct($em);
		$this->users = $em->getRepository(User::getClassName());
	}


	/**
	 * Performs an authentication against database.
	 * and returns IIdentity on success or throws AuthenticationException
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($login, $password) = $credentials;

		/** @var User $user */
		$user = $this->users->findOneByLogin($login);

		if (!$user) {
			throw new AuthenticationException("Nenalezen žádný uživatel.");
		} elseif (!Passwords::verify($password, $user->getPassword())) {
			throw new AuthenticationException("Špatné heslo.");
		} elseif (Passwords::needsRehash($user->getPassword())) {
			$user->setPassword(Passwords::hash($password));
			$this->em->flush();
		}

		$data = array(
			'profilePicture' => $user->getProfilePicture(),
			'name' => $user->getName(),
			'surname' => $user->getSurname()
		);

		return new Identity($user->getId(), $user->getRoles(), $data);
	}

	/**
	 * Method for creating new user, hashing password, ...
	 * @param $user User
	 * @return string New user password
	 */
	public function addUser(&$user)
	{
		$login = substr(Strings::webalize($user->getSurname()), 0, 5) . substr(Strings::webalize($user->getName()), 0, 3);
		$login = str_pad($login, 8, 'x');

		$sameLogin = $this->em->getRepository(Student::getClassName())->findBy(array("login" => $login));

		$i = 1;
		while ($sameLogin) {
			$i++;
			$login[7] = $i;
			$sameLogin = $this->em->getRepository(Student::getClassName())->findBy(array("login" => $login));
		}


		$user->setLogin(Strings::webalize($login));
		$password = $this->generateNewPassword($user);
		$user->setPassword(Passwords::hash($user->getPassword()));
		return $password;
	}

	/**
	 * Generate and save new password for user
	 * @param $user
	 * @return string New user password
	 */
	public function generateNewPassword(&$user)
	{
		$password = Random::generate(8);
		$user->setPassword($password);

		return $password;
	}
}