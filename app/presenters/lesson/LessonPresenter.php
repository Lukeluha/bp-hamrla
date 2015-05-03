<?php

namespace App\Presenters;

use App\Controls\RatingControl;
use App\Model\Entities\Lesson;
use App\Model\Entities\Question;
use App\Model\Entities\Task;
use App\Model\Entities\TaskCompleted;
use Nette\Application\BadRequestException;
use App\Controls\IPostsControlFactory;
use App\Model\Services\LessonService;
use App\Forms\IAnswerFormFactory;
use App\Forms\ISubmitTaskFormFactory;
use App\Forms\IQuestionFormFactory;
use App\Controls\IRatingControlFactory;
use Nette\Application\UI\Multiplier;
use App\Controls\IQuestionSummaryControlFactory;
use App\Forms\ITaskFormFactory;
use App\Controls\IStudentsControlFactory;

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
	 * @var IQuestionSummaryControlFactory
	 * @inject
	 */
	public $questionSummaryFactory;

	/**
	 * @var IRatingControlFactory
	 * @inject
	 */
	public $ratingControlFactory;

	/**
	 * @var ITaskFormFactory
	 * @inject
	 */
	public $taskFormFactory;

	/**
	 * @var IStudentsControlFactory
	 * @inject
	 */
	public $studentsControlFactory;

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

	public function handleToggleTask($taskId)
	{
		$task = $this->em->find(Task::getClassName(), $taskId);
		if (!$task) throw new BadRequestException;

		$task->setVisible(!$task->isVisible());
		$this->em->flush();

		$this->template->lesson = array($taskId => $task);
		$this->redrawControl('tasks');
	}

	public function handleLoadHistoryTask($count, $taskId)
	{
		$this->task = $this->em->find(Task::getClassName(), $taskId);

		$tasks = $this->em->createQueryBuilder()
				->select('tc')
				->from(TaskCompleted::getClassName(), 'tc')
				->join('tc.task', 't')
				->where('t.group = ' . $this->task->getGroup()->getId())
				->andWhere('t.id != ' . $this->task->getId())
				->andWhere('tc.points IS NOT NULL')
				->orderBy('tc.points', 'DESC')
				->setMaxResults($count)->getQuery()->getResult();

		$this->template->historyTasks = $tasks;
		$this->redrawControl('historyTasks');
	}

	public function handleLoadQuestion($questionId)
	{
		$question = $this->em->find(Question::getClassName(), $questionId);

		if (!$question) throw new BadRequestException;

		$this->template->questionActivity = $this->question = $question;
		$this['questionSummary']->setQuestion($this->question);
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

		$this['taskForm']->setTask($task);

		$this->template->taskActivity = $this->task = $task;
		$this->taskId = $taskId;
		$this->redrawControl('taskModal');
	}

	public function handleEditTask()
	{
		$post = $this->getHttpRequest()->getPost();

		$task = $this->em->find(TaskCompleted::getClassName(), $post['taskId']);
		$task->setPoints($post['points']);
		$this->em->flush();

		$this->redrawControl('taskModal');
	}

	public function actionRating($taskId, $withImage = false)
	{
		$this->template->taskId = $taskId;
		$this->template->withImage = $withImage;
	}

	public function createComponentQuestionForm()
	{
		return $this->questionFormFactory->create($this->lesson->getId());
	}

	public function createComponentTaskForm()
	{
		return $this->taskFormFactory->create($this->lesson->getId());
	}

	public function createComponentQuestionSummary()
	{
		$questionSummary = $this->questionSummaryFactory->create();
		if ($this->questionId) {
			$questionSummary->setQuestion($this->questionId);
		}
		return $questionSummary;
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

	public function createComponentRating()
	{
		$that = $this;
		return new Multiplier(function ($taskId) use ($that){
			$ratingControl = $that->ratingControlFactory->create($that->user->getId());
			$ratingControl->setTask($taskId);
			$ratingControl->onChange[] = function(RatingControl $rating) use ($that){
				$that->redrawControl('completedTasks');
			};
			return $ratingControl;
		});
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

	public function createComponentStudents()
	{
		$control = $this->studentsControlFactory->create($this->lesson->getTeaching()->getClass());
		$control->setLesson($this->lesson);
		return $control;
	}

	/**
	 * Factory for creating chat component
	 */
	public function createComponentChat()
	{
		return $this->chatControlFactory->create($this->user, $this->actualYear, $this->lesson->getTeaching());
	}
}