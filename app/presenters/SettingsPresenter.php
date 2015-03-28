<?php

namespace App\Presenters;

/**
 * Class SettingsPresenter
 * Page with all settings of application
 * @package App\Presenters
 */
class SettingsPresenter extends AuthorizedBasePresenter
{
	public function startup()
	{
		parent::startup();

		if (!$this->user->isAllowed('settings')) {
			$this->flashMessage('Na vstup do této sekce nemáte dostatečná oprávnění', 'alert');
			$this->redirect('Homepage:default');
		}

	}

	public function actionDefault()
	{

	}


}