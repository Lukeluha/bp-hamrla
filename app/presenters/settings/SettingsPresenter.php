<?php

namespace App\Presenters;

use App\Model\Entities\SchoolYear;

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

		$this->addLinkToNav('Nastavení', 'Settings:default');
	}


	/**
	 * Main page with all available settings
	 */
	public function renderDefault()
	{
		$this->template->schoolYears = $this->em->getRepository(SchoolYear::getClassName())->findBy(array(), array('from' => 'ASC'));
	}


	/*
	 *
	 * CLASSES MANAGEMENT
	 *
	 */



	public function renderClassesManagement()
	{

	}

}