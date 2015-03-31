<?php

namespace App\Presenters;
use App\Forms\StudentFormFactory;
use App\Model\Entities\Student;
use Nette\Application\UI\Form;

class StudentsPresenter extends AuthorizedBasePresenter
{

	/**
	 * @var StudentFormFactory
	 * @inject
	 */
	public $studentFormFactory;

	/**
	 * @var \App\Model\Entities\Student
	 */
	private $student;

	public function startup()
	{
		parent::startup();
		$this->checkPermissions('settings', 'student');
		$this->addLinkToNav("Nastavení", "Settings:default");
	}

	public function renderDefault($studentId = null)
	{
		if ($studentId) {
			$this->student = $this->em->find(Student::getClassName(), $studentId);
			if (!$this->student) {
				$this->flashMessage("Student nenalezen", "alert");
				$this->redirect("Settings:default");
			}
		}
	}

	public function createComponentForm()
	{
		$form = $this->studentFormFactory->setStudent($this->student)->create();

		if ($this->student) {
			$form['form']->onSuccess[] = function (Form $form) {
				$this->redirect('this');
			};
		} else {
			$form['form']->onSuccess[] = function (Form $form) {
				$this->redirect('Settings:default');
			};
		}

		return $form;
	}
}