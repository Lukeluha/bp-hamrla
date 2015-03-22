<?php

namespace App\Presenters;

use Kdyby\Doctrine\EntityManager;
use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var Nette\Security\User
	 */
	protected $userManager;

	public function __construct(EntityManager $em, Nette\Security\User $user)
	{
		$this->em = $em;
		$this->userManager = $user;
	}

	public function startup()
	{
		parent::startup();
		if (!$this->userManager->isLoggedIn()) {
			$this->redirect("Login:default");
		}
	}
}
