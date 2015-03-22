<?php

namespace App\Model\Services;

use App\Model\Entity\User;
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
			throw new AuthenticationException("Nenalazen žádný uživatel.");
		} elseif (!Passwords::verify($password, $user->getPassword())) {
			throw new AuthenticationException("Špatné heslo.");
		} elseif (Passwords::needsRehash($user->getPassword())) {
			$user->setPassword(Passwords::hash($password));
			$this->em->flush();
		}

		return new Identity($user->getId(), array($user->getRole()->getName()));
//		return $user;
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