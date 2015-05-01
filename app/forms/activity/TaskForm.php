<?php

namespace App\Forms;

use App\Model\Entities\Group;
use App\Model\Entities\Lesson;
use App\Model\Entities\Task;
use App\Model\FoundationRenderer;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class TaskForm extends Control
{
	/**
	 * @var int
	 */
	protected $lessonId;

	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var Task
	 */
	protected $task;

	public function __construct($lessonId, EntityManager $em)
	{
		$this->em = $em;
		$this->lessonId = $lessonId;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/taskForm.latte");
		$this->template->lessonId = $this->lessonId;
		$this->template->render();
	}

	public function createComponentForm()
	{
		$form = new Form();
		$form->addText('taskName', "Název úkolu")->setRequired('Zadejte název úkolu');
		$form->addTextArea('taskText', 'Zadání úkolu', null, 5);
		$form->addText("start", "Spuštění úkolu (pokud nezadáte nic, úkol bude spuštěný ihned)")->setAttribute('class', 'fdatetimepicker');
		$form->addSelect('limitType', "Typ limitu",
							array(0 => "Žádný", Task::LIMIT_NO_STRICT => "Volný", Task::LIMIT_STRICT => "Striktní"))
							->addCondition(Form::NOT_EQUAL, 0)->toggle($this->getUniqueId() . '-taskEnd');

		$form->addText("end", "Termín odevzdání")
			->setAttribute('class', 'fdatetimepicker')
			->addConditionOn($form['limitType'], Form::NOT_EQUAL, 0)->setRequired('Vyplňte termín odevzdání úkolu');
		$form->addHidden('taskId');
		$form->addSubmit('save', 'Uložit úkol');

		$form->addCheckbox('studentRating', 'Vzájemné hodnocení studenty');

		$form->setRenderer(new FoundationRenderer());
		$form->onSuccess[] = $this->saveTask;

		return $form;
	}


	public function saveTask(Form $form)
	{
		$values = $form->getValues();


		if ($values['taskId']) {
			$task = $this->em->find(Task::getClassName(), $values['taskId']);
		} else {
			$task = new Task();
		}

		$task->setTaskName($values['taskName'])
			->setTaskText($values['taskText'])
			->setLesson($this->em->getReference(Lesson::getClassName(), $this->lessonId));

		if ($values['start']) {
			$task->setStart(\DateTime::createFromFormat('j. n. Y H:i', $values['start']));
		} else {
			$task->setStart(new \DateTime());
		}

		if ($values['limitType']) {
			$task->setLimit($values['limitType'])->setEnd(\DateTime::createFromFormat("j. n. Y H:i", $values['end']));
		}

		$task->setStudentRating($values['studentRating']);

		if (!$task->getGroup()) {
			$group = new Group();
			$this->em->persist($group);
			$this->em->flush();
			$task->setGroup($group);
		}

		try {
			$this->em->persist($task);
			$this->em->flush();
			$this->presenter->flashMessage("Úkol byl úspěšně uložen", "success");
		} catch (\Exception $e) {
			throw $e;
			$this->presenter->flashMessage("Úkol nebyla uložen", "alert");
			return;
		}

		$this->redirect('this');

	}

	public function setTask(Task $task)
	{
		$this->task = $task;

		$defaults = array(
			'taskName' => $task->getTaskName(),
			'taskText' => $task->getTaskText(),
			'start' => $task->getStart()->format("j. n. Y H:i"),
			'end' => $task->getEnd()->format("j. n. Y H:i"),
			'limitType' => $task->getLimit(),
			'studentRating' => $task->getStudentRating(),
			'taskId' => $task->getId()
		);

		$this['form']->setDefaults($defaults);
		return $this;
	}

}