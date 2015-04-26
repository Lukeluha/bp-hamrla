<?php

namespace App\Presenters;

use App\Model\Entities\Lesson;
use App\Model\Entities\Question;
use Nette\Application\BadRequestException;
use App\Controls\IPostsControlFactory;
use App\Model\Services\LessonService;

class LessonPresenter extends AuthorizedBasePresenter
{
	/**
	 * @var Lesson
	 */
	protected $lesson;

	/**
	 * @var IPostsControlFactory
	 * @inject
	 */
	public $postFactory;

	/**
	 * @var LessonService
	 * @inject
	 */
	public $lessonService;

	public function actionDefault($lessonId)
	{
		$this->lesson = $this->em->find(Lesson::getClassName(), $lessonId);
		if (!$this->lesson) throw new BadRequestException('Unknown lesson');

		$this->checkUser();

		$navText = 'Vyučování | '. $this->lesson->getTeaching()->getClass()->getName();
		$navText .= ' | ' . $this->lesson->getTeaching()->getSubject();

		$rank = $this->em->getRepository(Lesson::getClassName())->findRank($this->lesson);
		$this->lesson->setRank($rank);

		$this->addLinkToNav($navText, 'Teaching:default', array($this->lesson->getTeaching()->getId()));

		$this->addLinkToNav("$rank. hodina", "Lesson:default", array($lessonId));

	}

	public function renderDefault()
	{
		$this->template->lesson = $this->lesson;
		if (!isset($this->template->activities)) {
			$this->template->activities = $this->lessonService->getActivitiesInLesson($this->lesson, $this->user);
		}
		$this->template->ckeditor = true;
	}

	public function handleSaveText()
	{
		$post = $this->getHttpRequest()->getPost();
		$contentId = $post['contentId'];
		if ($contentId == 'lessonName') {
			$this->lesson->setName($post['content']);
		} elseif ($contentId == 'lessonDescription') {
			$this->lesson->setDescription($post['content']);
		}

		$this->em->persist($this->lesson);
		$this->em->flush();

		$this->terminate();
	}

	public function handleToggleQuestion($questionId)
	{
		$question = $this->em->find(Question::getClassName(), $questionId);
		if (!$question) throw new BadRequestException;

		$question->setVisible(!$question->isVisible());
		$this->em->flush();

		$this->template->lesson = array($questionId => $question);
		$this->redrawControl('questions');
	}

	public function createComponentPosts()
	{
		return $this->postFactory->create($this->user, $this->lesson);
	}

	public function checkUser()
	{
		if (!$this->user->isInRole('admin')) {
			if (!$this->userService->isUserInTeaching($this->user, $this->lesson->getTeaching())){
				$this->flashMessage("Nejste součástí tohoto vyučování.", "alert");
				$this->redirect('Homepage:default');
			}
		}
	}

}