<?php

namespace App\Presenters;
use App\Forms\IStudentFormFactory;
use App\Forms\StudentFormFactory;
use App\Model\Entities\Student;

/**
 * Class StudentsPresenter
 * Page with management of students
 * @package App\Presenters
 */
class StudentsPresenter extends AuthorizedBasePresenter
{

	/**
	 * @var \App\Model\Entities\Student
	 */
	private $student;

	/**
	 * @var IStudentFormFactory
	 * @inject
	 */
	public $studentFormFactory;

	public function startup()
	{
		parent::startup();
		$this->checkPermissions('settings', 'student');
		$this->addLinkToNav("Nastavení", "Settings:default");
	}

	/**
	 * Main page with info about student
	 * @param null $studentId
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 */
	public function actionDefault($studentId = null)
	{
		if ($studentId) {
			$this->student = $this->em->find(Student::getClassName(), $studentId);
			if (!$this->student) {
				$this->flashMessage("Student nenalezen", "alert");
				$this->redirect("Settings:default");
			}

			$this->addLinkToNav("Editace studenta", "Students:default", array('studentId' => $studentId));
		} else {
			$this->addLinkToNav("Nový student", "Students:default");

		}
	}

	/**
	 * Factory for student form
	 * @return \App\Forms\StudentForm
	 */
	public function createComponentForm()
	{
		$form = $this->studentFormFactory->create($this->getParameter('studentId'), $this->actualYear);
		$that = $this;

		$form->onCreate[] = function ($component, $student) use ($that) {
			$that->flashMessage("Student byl úspěšně vytvořen. <strong>Jeho login je: " . $student->getLogin() . " a heslo je: " . $component->getStudentPassword() . "</strong>", "success");
			$that->redirect("Students:default", array($student->getId()));
		};

		$form->onUpdate[] = function () use ($that) {
			$that->flashMessage("Student byl úspěšně uložen.", "success");
		};

		$form->onError[] = function () use ($that) {
			$that->flashMessage("Student nebyl uložen.", 'alert');
		};


		return $form;
	}
}