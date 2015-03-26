<?php

namespace App\Presenters;

use Nette\Application\UI\Form;

class LoginPresenter extends BasePresenter
{
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
			$this->user->login($values['login'], $values['password']);
		} catch (\Exception $e) {
			$this->flashMessage("Nepodařilo se přihlásit. " . $e->getMessage(), "alert");
		}

		$this->redirect("this");
	}

	public function startup()
	{
		parent::startup();
		if ($this->user->isLoggedIn()) {
			$this->redirect("Homepage:default");
		}
	}

}