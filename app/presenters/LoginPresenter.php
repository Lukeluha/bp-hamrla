<?php

namespace App\Presenters;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

class LoginPresenter extends Presenter
{
	/**
	 * @inject
	 * @var \Nette\Security\User
	 */
	public $userManager;

	public function createComponentLoginForm()
	{
		$form = new Form();

		$form->addText("login", "Login")->setRequired("Vyplňte uživatelské jméno");
		$form->addPassword("password", "Heslo")->setRequired("Vyplňte heslo");

		$form->onSuccess[] = array($this, "loginDo");

		$form->addSubmit("loginDo", "Přihlásit se");

		return $form;
	}

	public function loginDo(Form $form)
	{
		$values = $form->getValues();
		try {
			$this->userManager->login($values['login'], $values['password']);
		} catch (\Exception $e) {
			$this->flashMessage("Nepodařilo se přihlásit. " . $e->getMessage(), "alert");
		}

		$this->redirect("this");
	}

	public function startup()
	{
		parent::startup();
		if ($this->userManager->isLoggedIn()) {
			$this->redirect("Homepage:default");
		}
	}

}