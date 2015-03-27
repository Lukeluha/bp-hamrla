<?php

namespace App\Presenters;
use App\Control\ChatControl;
use App\Control\MenuControl;

/**
 * Class AuthorizedBasePresenter
 * User must be logged in all children presenters
 * @package App\Presenters
 */
abstract class AuthorizedBasePresenter extends BasePresenter
{
	public function startup()
	{
		parent::startup();
		if (!$this->user->isLoggedIn()) {
			$this->redirect("Login:default");
		}
	}

	/**
	 * Factory for creating menu component
	 */
	public function createComponentMenu()
	{
		return new MenuControl($this->em);
	}

	/**
	 * Factory for creating chat component
	 */
	public function createComponentChat()
	{
		return new ChatControl($this->em);
	}

	public function handleLogout()
	{
		try {
			$this->user->logout();
			$this->flashMessage("Byl jste úspěšně odhlášen.", "success");
		} catch (\Exception $e) {
			$this->flashMessage("Odhlášení se nezdařilo.", "error");
		}

		$this->redirect('Login:default');
	}

	public function beforeRender()
	{
		parent::beforeRender();
		//$this->template->loggedUser = $this->em->getRepository(User::getClassName())->find($this->authenticator->getId());
	}

}