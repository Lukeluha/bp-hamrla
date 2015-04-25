<?php

namespace App\Presenters;


use App\Model\Entities\Lesson;
use App\Model\Entities\Teaching;
use Nette\Application\BadRequestException;

class LessonsPresenter extends AuthorizedBasePresenter
{
	/**
	 * @var Teaching
	 */
	protected $teaching;

	public function actionDefault($teachingId)
	{
		$this->teaching = $this->em->find(Teaching::getClassName(), $teachingId);

		if (!$this->teaching) throw new BadRequestException();

		$navText = 'Vyučování | '. $this->teaching->getClass()->getName();
		$navText .= ' | ' . $this->teaching->getSubject();

		$this->addLinkToNav($navText, 'Teaching:default', array($this->teaching->getId()));
		$this->addLinkToNav('Všechny hodiny', 'Lessons:default', array($this->teaching->getId()));
	}

	public function renderDefault()
	{
		$this->template->teaching = $this->teaching;
		$this->template->todayWeek = date('W');
	}

	public function handleDelete($lessonId)
	{
		try {
			$this->em->remove($this->em->getReference(Lesson::getClassName(), $lessonId));
			$this->em->flush();
			$this->flashMessage("Hodina byla úspěšně smazána", "success");
		} catch (\Exception $e) {
			$this->flashMessage("Nepodařilo se vymazat hodinu", "alert");
		}

		$this->redirect('this');
	}

	public function checkUser()
	{
		if (!$this->user->isInRole('admin')) {
			if (!$this->userService->isUserInTeaching($this->user, $this->getTeaching())){
				$this->flashMessage("Nejste součástí tohoto vyučování.", "alert");
				$this->redirect('Homepage:default');
			}
		}
	}

}