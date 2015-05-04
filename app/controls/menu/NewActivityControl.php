<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 21.04.15
 * Time: 23:27
 */

namespace App\Controls;


use App\Forms\IQuestionFormFactory;
use App\Forms\ITaskFormFactory;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class NewActivityControl extends Control
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var int
	 */
	protected $lessonId;

	/**
	 * @var IQuestionFormFactory
	 */
	protected $questionFormFactory;

	/**
	 * @var ITaskFormFactory
	 */
	protected $taskFormFactory;

	public function __construct($lessonId,
								EntityManager $em,
								IQuestionFormFactory $questionFormFactory,
								ITaskFormFactory $taskFormFactory)
	{
		$this->em = $em;
		$this->lessonId = $lessonId;
		$this->questionFormFactory = $questionFormFactory;
		$this->taskFormFactory = $taskFormFactory;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/newActivity.latte");
		$this->template->lessonId = $this->lessonId;
		$this->template->render();
	}

	public function createComponentQuestionForm()
	{
		return $this->questionFormFactory->create($this->lessonId);
	}

	public function createComponentTaskForm()
	{
		return $this->taskFormFactory->create($this->lessonId);
	}

	public function createComponentForm()
	{
		$form = new Form();
		$form->addRadioList('type', null, array('question' => "Otázka", 'task' => 'Úkol'))->setValue('question');
		$form['type']->addCondition(Form::EQUAL, 'question')->toggle('question');
		$form['type']->addCondition(Form::EQUAL, 'task')->toggle('task');
		return $form;
	}

}