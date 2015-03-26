<?php

namespace App\Presenters;
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
		if (!$this->authenticator->isLoggedIn()) {
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

	public function handleLogout()
	{
		try {
			$this->authenticator->logout();
			$this->flashMessage("Byl jste úspěšně odhlášen.", "success");
		} catch (\Exception $e) {
			$this->flashMessage("Odhlášení se nezdařilo.", "error");
		}

		$this->redirect('Login:default');
	}
}