<?php

namespace App\Forms;


use App\Model\Entities\ClassEntity;
use App\Model\Entities\Student;
use App\Model\FoundationRenderer;
use Nette\Application\UI\Control;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;
use App\Model\Services\UserService;

class StudentForm extends Control
{
	/**
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @var Student
	 */
	private $student;

	/**
	 * Password in plain text only for the first creating of user
	 * @var string
	 */
	private $studentPassword;

	public $onCreate = array();
	public $onUpdate = array();
	public $onError = array();


	public function __construct($studentId = null, EntityManager $em, UserService $userService)
	{
		parent::__construct();
		$this->em = $em;
		$this->userService = $userService;

		if ($studentId) {
			$this->student = $this->em->find(Student::getClassName(), $studentId);
		}
	}

	public function createComponentForm()
	{
		$form = new Form();

		if ($this->student) {
			$form->addText("login", "Login")->setDisabled();
		}

		$form->addText("name", "Jméno")->setRequired("Zadejte prosím jméno studenta");
		$form->addText("surname", "Příjmení")->setRequired("Zadejte prosím příjmení studenta");

		$classes = $this->em->getRepository(ClassEntity::getClassName())->findByOpenedYears();

		$classesSelect = array("0" => "--Vyberte--");
		foreach ($classes as $class) {
			$classesSelect[$class->getId()] = $class->getName()." (".$class->getSchoolYear()->__toString().")";
		}

		$form->addSelect("class", "Třída studenta", $classesSelect)->addRule(Form::NOT_EQUAL, "Vyberte prosím třídu studenta", 0);

		$form->addSubmit("save", "Uložit")->getControlPrototype()->class('button small');

		$form->setRenderer(new FoundationRenderer());

		$form->onSuccess[] = $this->saveStudent;
		$form->addHidden('studentId');

		if ($this->student) {
			$form->setDefaults(array(
				"name" => $this->student->getName(),
				"surname" => $this->student->getSurname(),
				"class" => $this->student->getMainClass()->getId(),
				"studentId" => $this->student->getId(),
				"login" => $this->student->getLogin()
			));
		}

		return $form;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/student.latte');
		$this->template->render();
	}

	public function saveStudent(Form $form)
	{
		$values = $form->getValues();

		if ($values['studentId']) {
			$student = $this->em->find(Student::getClassName(), $values['studentId']);
		} else {
			$student = new Student();
		}

		$student->setName($values['name'])->setSurname($values['surname']);
		$class = $this->em->find(ClassEntity::getClassName(), $values['class']);


		if ($values['studentId'] ) {
			if ($student->getMainClass()->getId() != $class->getId()) {
				$sameStudent = $this->findSameStudent($student, $class);

				if ($sameStudent) {
					$this->presenter->flashMessage("Ve třídě " . $class->getName() . " již je student se jménem " . $student->getName() . " " . $student->getSurname().".", "alert");
					return;
				}

				$student->getMainClass()->removeStudent($student);
				$class->addStudent($student);
			}
		} else {
			$sameStudent = $this->findSameStudent($student, $class);

			if ($sameStudent) {
				$this->presenter->flashMessage("Ve třídě " . $class->getName() . " již je student se jménem " . $student->getName() . " " . $student->getSurname().".", "alert");
				return;
			}

			$this->studentPassword = $this->userService->addUser($student);
			$class->addStudent($student);
		}



		try {
			$this->em->persist($student);
			$this->em->flush();
		} catch (\Exception $e) {
			$this->onError($this, $student);
		}

		if ($values['studentId']) {
			$this->onUpdate($this, $student);
		} else {
			$this->onCreate($this, $student);
		}
	}

	/**
	 * @return string
	 */
	public function getStudentPassword()
	{
		return $this->studentPassword;
	}

	/**
	 * @param string $studentPassword
	 * @return $this
	 */
	public function setStudentPassword($studentPassword)
	{
		$this->studentPassword = $studentPassword;
		return $this;
	}

	private function findSameStudent($student, $class)
	{
		return (bool) $this->em->getRepository(Student::getClassName())->findByStudentNameInClass($student, $class);
	}



}