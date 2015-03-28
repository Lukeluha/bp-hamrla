<?php

namespace App\Model\Services;

use App\Model\Entities\User;
use Kdyby\Doctrine\EntityManager;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;

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
	 */
	public function addUser($user)
	{
		$user->setPassword(Passwords::hash($user->getPassword()));
	}

}