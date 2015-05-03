<?php

namespace App\Presenters;

use App\Model\Entities\ClassEntity;
use App\Model\Entities\SchoolYear;
use App\Model\Entities\Student;
use App\Model\Services\BaseService;

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
	 * Search for student
	 * @param $query
	 */
	public function handleSearchStudent($query)
	{
		if (strlen(trim($query))) {
			$this->template->students = $this->em->getRepository(Student::getClassName())->findByName($query, $this->actualYear);
		} else {
			$this->template->students = null;
		}
		$this->redrawControl('students');

	}

	/**
	 * Search for class
	 * @param $query
	 */
	public function handleSearchClass($query)
	{
		if (strlen(trim($query))) {
			$this->template->classes = $this->em->getRepository(ClassEntity::getClassName())->findByName($query, $this->actualYear);
		} else {
			$this->template->classes = null;
		}
		$this->redrawControl('classes');
	}

	/**
	 * Main page with all available settings
	 */
	public function renderDefault()
	{
		if (!isset($this->template->classes)) {
			$this->template->classes = $this->em->getRepository(ClassEntity::getClassName())->findByName('', $this->actualYear);
		}

		if (!isset($this->template->students)) {
			$this->template->students = $this->em->getRepository(Student::getClassName())->findByName('', $this->actualYear);
		}


		$this->template->schoolYears = $this->em->getRepository(SchoolYear::getClassName())->findBy(array(), array('from' => 'DESC'));
	}


}