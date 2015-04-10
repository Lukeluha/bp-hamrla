<?php

namespace App\Presenters;

use App\Model\Entities\Lesson;
use App\Model\Entities\Teaching;
use Nette\Application\BadRequestException;

/**
 * Class TeachingPresenter
 * @package App\Presenters
 */
class TeachingPresenter extends AuthorizedBasePresenter
{
	/**
	 * @var Teaching
	 */
	protected $teaching;

	public function actionDefault($teachingId)
	{
		$this->teaching = $this->em->find(Teaching::getClassName(), $teachingId);
		if (!$this->teaching) throw new BadRequestException('Unknown teaching.');

		$this->checkUser();

		$this->addLinkToNav('Vyučování | '. $this->teaching->getClass()->getName() . ' | ' . $this->teaching->getSubject(), 'Teaching:default', array($teachingId));
	}

	public function renderDefault()
	{
		$this->template->nextLesson = $this->em->getRepository(Lesson::getClassName())->findNext($this->teaching);
		$this->template->teaching = $this->teaching;

	}

	/**
	 * Check if user has permission to view this teaching
	 */
	protected function checkUser()
	{
		if (!$this->user->isInRole('admin')) {
			if (!$this->userService->isUserInTeaching($this->user, $this->teaching)){
				$this->flashMessage("Nejste součástí tohoto vyučování.", "alert");
				$this->redirect('Homepage:default');
			}
		}
	}

	
}