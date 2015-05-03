<?php

namespace App\Presenters;

use App\Model\Entities\Lesson;
use App\Model\Entities\Teaching;
use Nette\Application\BadRequestException;
use App\Controls\IPostsControlFactory;
use App\Controls\IStudentsControlFactory;

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

	/**
	 * @var IPostsControlFactory
	 * @inject
	 */
	public $postsControl;

	/**
	 * @var IStudentsControlFactory
	 * @inject
	 */
	public $studentsControlFactory;

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

	public function createComponentPosts()
	{
		return $this->postsControl->create($this->user, $this->teaching);
	}

	/**
	 * Factory for creating chat component
	 */
	public function createComponentChat()
	{
		return $this->chatControlFactory->create($this->user, $this->actualYear, $this->teaching);
	}

	public function createComponentStudents()
	{
		return $this->studentsControlFactory->create($this->teaching);
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

	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->title = "Vyučování | " . $this->teaching->getSubject()->getAbbreviation();
	}

	
}