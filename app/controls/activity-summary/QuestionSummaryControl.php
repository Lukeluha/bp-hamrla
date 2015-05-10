<?php

namespace App\Controls;


use App\Forms\IQuestionFormFactory;
use App\Model\Entities\Answer;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use App\Model\Entities\Question;
use Nette\Application\UI\Form;

/**
 * Class QuestionSummaryControl
 * Summary fror given questions, all answers, graphs, points editing, ...
 * @package App\Controls
 */
class QuestionSummaryControl extends Control
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var Question
	 */
	protected $question;

	/**
	 * @var IQuestionFormFactory
	 */
	protected $questionFormFactory;

	public function __construct(EntityManager $entityManager, IQuestionFormFactory $questionFormFactory)
	{
		$this->em = $entityManager;
		$this->questionFormFactory = $questionFormFactory;
	}

	/**
	 * @param int $questionId
	 * @return $this
	 */
	public function setQuestion($questionId)
	{
		$this->question = $this->em->find(Question::getClassName(), $questionId);
		return $this;
	}

	public function handleRefresh()
	{
		$this->presenter->payload->chartData = json_encode($this->getDataForChart());
		$this->redrawControl();
	}

	public function handleEditAnswer()
	{
		$post = $this->presenter->request->getPost();

		$answer = $this->em->find(Answer::getClassName(), $post['answerId']);
		$answer->setPoints($post['points']);
		$this->em->flush();

		$data = $this->getDataForChart();
		$this->presenter->payload->chartData = json_encode($data);

		$this->redrawControl('questionModal');
	}

	public function createComponentQuestionForm()
	{
		$questionForm = $this->questionFormFactory->create($this->question->getLesson()->getId(), $this, 'questionForm');
		$questionForm->setQuestion($this->question);
		$that = $this;
		$questionForm->onSave[] = function(Form $form) use ($that) {
			$that->redirect('this');
		};

		return $questionForm;
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

	public function render()
	{
		$this->template->setFile(__DIR__ . '/questionSummary.latte');
		$this->template->question = $this->question;
		$this->template->chartData = json_encode($this->getDataForChart());
		$this->template->render();
	}

}