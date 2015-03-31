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

		$this->template->ngApp = 'app';
	}

	public function handleSearchStudent($query)
	{
		$students = $this->em->getRepository(Student::getClassName())->findByName($query, BaseService::FORMAT_ARRAY);
		$this->sendJson($students);
	}

	public function handleSearchClass($query)
	{
		$classes = $this->em->createQueryBuilder()
						->select('c')
						->from(ClassEntity::getClassName(), 'c')
						->where('c.name LIKE :query')
						->orderBy('c.name')
						->setParameter('query', "%$query%")
						->getQuery()->getArrayResult();

		$this->sendJson($classes);
	}

	/**
	 * Main page with all available settings
	 */
	public function renderDefault()
	{
		$this->template->classes = $this->em->createQueryBuilder()->select('c')->from(ClassEntity::getClassName(), 'c')->orderBy('c.name')->getQuery()->getArrayResult();
		$this->template->students = $this->em->getRepository(Student::getClassName())->findByName(null, BaseService::FORMAT_ARRAY);
		$this->template->schoolYears = $this->em->getRepository(SchoolYear::getClassName())->findBy(array(), array('from' => 'DESC'));
	}


}