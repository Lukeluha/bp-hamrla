<?php

namespace App\Forms;


use App\Model\Entities\TaskCompleted;
use App\Model\FoundationRenderer;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use App\Model\Entities\Task;
use Nette\Application\UI\Form;
use App\Model\Entities\Student;
use Nette\Utils\Strings;
use Nette\Utils\Image;

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

	public function __construct($userId, Task $task, EntityManager $entityManager)
	{
		$this->em = $entityManager;
		$this->task = $task;
		$this->user = $this->em->find(Student::getClassName(), $userId);
	}

	public function createComponentForm()
	{
		$form = new Form();

		$form->addUpload('task', "Soubor k odevzdání")->setRequired('Vyberte soubor k odevzdání');
		$form->addTextArea('note', "Poznámka", null, 5);
		$form->addSubmit('save', 'Uložit');

		$form->onSuccess[] = $this->saveTask;

		$form->setRenderer(new FoundationRenderer());

		return $form;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/submitTaskForm.latte");
		$this->template->task = $this->task;
		$this->template->submittedTask = $this->em
												->getRepository(TaskCompleted::getClassName())
												->findOneBy(array('student' => $this->user->getId(), 'task' => $this->task->getId()));
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

		$mainPwd = "files/tasks/" . $schoolYear->getFrom()->format("Y") . "-" . $schoolYear->getTo()->format("Y") . "-" . $schoolYear->getId();
		@mkdir($mainPwd);
		$mainPwd .= "/" . Strings::webalize($lesson->getTeaching()->getSubject()->getAbbreviation());
		@mkdir($mainPwd);
		$mainPwd .= "/" . Strings::webalize($lesson->getTeaching()->getClass()->getName());
		@mkdir($mainPwd);
		$mainPwd .= "/" . Strings::webalize($this->task->getTaskName()) . "-" . Strings::webalize($this->task->getId());
		@mkdir($mainPwd);

		$mainPwd .= "/" . Strings::webalize($this->user->getSurname() . "-".$this->user->getName());
		$mainPwd .= '-' . $this->user->getId();

		$task = new TaskCompleted();
		$task->setStudent($this->user)
			->setCreated($now)
			->setTask($this->task)
			->setNote($values['note']);

		if ($values['task']->isImage()) {
			/** @var Image $image */
			$image = $values['task']->toImage();
			$pwd = $mainPwd . "." . $this->getFileExtension($values['task']->getName());
			$image->save($pwd);

			$task->setFilename($pwd);

			$pwd = $mainPwd . "-web." . $this->getFileExtension($values['task']->getName());

			if ($image->getWidth() > 1300) {
				$image->resize(1300, null);
			}

			$image->save($pwd);


			$image->resize(150, null);
			$pwd = $mainPwd . "-thumbnail." . $this->getFileExtension($values['task']->getName());
			$image->save($pwd);

			$task->setImage(true);
		} else {
			$pwd = $mainPwd . "." . $this->getFileExtension($values['task']->getName());
			$values['task']->move(WWW_DIR . "/" . $pwd);
			$task->setFilename($pwd);
			$task->setImage(false);
		}


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

	private function getFileExtension($file_name) {
		return substr(strrchr($file_name,'.'),1);
	}

}