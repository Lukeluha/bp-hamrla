<?php

namespace App\Presenters;

use App\Model\Entities\Answer;
use App\Model\Entities\Lesson;
use App\Model\Entities\Question;
use App\Model\Entities\Task;
use Nette\Application\BadRequestException;
use App\Controls\IPostsControlFactory;
use App\Model\Services\LessonService;
use App\Forms\IAnswerFormFactory;
use App\Forms\ISubmitTaskFormFactory;
use App\Forms\IQuestionFormFactory;

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

	/**
	 * @var IAnswerFormFactory
	 * @inject
	 */
	public $answerFormFactory;

	/**
	 * @var ISubmitTaskFormFactory
	 * @inject
	 */
	public $submitTaskFormFactory;

	/**
	 * @var IQuestionFormFactory
	 * @inject
	 */
	public $questionFormFactory;

	/**
	 * @var Question
	 */
	private $question;

	/**
	 * @var Task
	 */
	private $task;

	/**
	 * @persistent
	 */
	public $questionId;

	/**
	 * @persistent
	 */
	public $taskId;

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

	public function handleLoadQuestion($questionId)
	{
		$question = $this->em->find(Question::getClassName(), $questionId);

		if (!$question) throw new BadRequestException;

		$this->template->questionActivity = $this->question = $question;
		$this->questionId = $questionId;
		if ($this->user->isInRole('teacher')) {
			$this['questionForm']->setQuestion($question);

			$data = $this->getDataForChart();
			$this->payload->chartData = json_encode($data);
		}
		$this->redrawControl('questionModal');
	}

	public function handleStartTask($taskId)
	{
		$task = $this->em->find(Task::getClassName(), $taskId);
		if (!$task) throw new BadRequestException;
		$task->setStart(new \DateTime());
		$this->em->persist($task);
		$this->em->flush();

		$this->template->tasks = array($taskId => $task);

		$this->redrawControl('tasks');
	}

	public function handleLoadTask($taskId)
	{
		$task = $this->em->find(Task::getClassName(), $taskId);

		if (!$task) throw new BadRequestException;

		$this->template->taskActivity = $this->task = $task;
		$this->taskId = $taskId;
		$this->redrawControl('taskModal');
	}

	public function handleEditAnswer()
	{
		$post = $this->getHttpRequest()->getPost();

		$answer = $this->em->find(Answer::getClassName(), $post['answerId']);
		$answer->setPoints($post['points']);

		$question = $this->em->find(Question::getClassName(), $answer->getQuestion()->getId());
		$this->question = $question;

		$this->em->flush();
		if ($this->user->isInRole('teacher')) {

			$data = $this->getDataForChart();
			$this->payload->chartData = json_encode($data);
		}
		$this->redrawControl('questionModal');
	}

	public function createComponentQuestionForm()
	{
		return $this->questionFormFactory->create($this->lesson->getId());
	}

	public function createComponentAnswerForm()
	{
		if (!$this->question) {
			$this->question = $this->em->find(Question::getClassName(), $this->questionId);
			if (!$this->question) throw new BadRequestExcepiton;
		}

		return $this->answerFormFactory->create($this->question, $this->user->getId());
	}

	public function createComponentSubmitTaskForm()
	{
		if (!$this->task) {
			$this->task = $this->em->find(Task::getClassName(), $this->taskId);
			if (!$this->task) throw new BadRequestExcepiton;
		}

		return $this->submitTaskFormFactory->create($this->user->getId(), $this->task);
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

	public function getDataForChart()
	{
		$successData = $this->em->getRepository(Answer::getClassName())->getDataForChart($this->question);
		$data = array(0 => array('Úspěšnost v %', 'Počet'));
		foreach ($successData as $points) {
			$data[] = array((string)$points['points'] . ' %', (int) $points['cnt']);
		}

		return $data;
	}
}