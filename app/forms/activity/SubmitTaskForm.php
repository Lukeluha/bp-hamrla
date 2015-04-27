<?php

namespace App\Forms;


use App\Model\Entities\TaskCompleted;
use App\Model\FoundationRenderer;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use App\Model\Entities\Task;
use Nette\Application\UI\Form;
use App\Model\Entities\Student;

class SubmitTaskForm extends Control
{

	/**
	 * @var EntityManager
	 */
	protected $em;


	/**
	 * @var Task
	 */
	protected $task;

	/**
	 * @var Student
	 */
	protected $user;

	public function __construct($userId, $taskId, EntityManager $entityManager)
	{
		$this->em = $entityManager;
		$this->task = $this->em->find(Task::getClassName(), $taskId);
		$this->user = $this->em->find(Student::getClassName(), $userId);
	}

	public function createComponentForm()
	{
		$form = new Form();

		$form->addUpload('task', "Soubor k odevzdání")->setRequired('Vyberte soubor k odevzdání');
		$form->addText('note', "Poznámka");
		$form->addSubmit('save', 'Uložit');

		$form->onSuccess[] = $this->saveTask;

		$form->setRenderer(new FoundationRenderer());

		return $form;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/submitTaskForm.latte");
		$this->template->task = $this->task;
		$this->template->render();
	}

	public function saveTask(Form $form)
	{
		$now = new \DateTime();

		if ($this->task->getLimit() == Task::LIMIT_STRICT && $now > $this->task->getEnd()) {
			$this->flashMessage("Limit pro odevzdání již vypršel.", "alert");
			$this->redirect('this');
		}

		$values = $form->getValues();

		$lesson = $this->task->getLesson();
		$schoolYear = $lesson->getTeaching()->getClass()->getSchoolYear();

		$pwd = "files/tasks/" . $schoolYear->getId() . "/" . $lesson->getId() . "/". $this->user->getId() . "-" . $this->task->getId() . "-" . $values['task']->getName();

		$values['task']->move(WWW_DIR . "/" . $pwd);

		$task = new TaskCompleted();
		$task->setStudent($this->user)
				->setCreated($now)
				->setTask($this->task)
				->setFilename($pwd)
				->setNote($values['note']);

		try {
			$this->em->persist($task);
			$this->em->flush();
			$this->presenter->flashMessage("Váš úkol byl úspěšně uložen.", 'success');
		} catch (\Exception $e) {
			$this->presenter->flashMessage("Váš úkol nebyl uložen.", "alert");
			return;
		}

		$this->redirect('this');
	}

}