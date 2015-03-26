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
	 * @inject
	 * @var EntityManager
	 */
	public $em;

	/**
	 * @inject
	 * @var Nette\Security\User
	 */
	public $authenticator;

	public function beforeRender()
	{
		$this->template->isProduction = !Nette\Configurator::detectDebugMode();
	}
}
