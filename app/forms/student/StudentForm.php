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

	public function __construct(EntityManager $em, UserService $userService, Student $student = null)
	{
		parent::__construct();
		$this->em = $em;
		$this->userService = $userService;
		$this->student = $student;
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

		if ($this->student) {
			$form->setDefaults(array(
				"name" => $this->student->getName(),
				"surname" => $this->student->getSurname(),
				"class" => $this->student->getMainClass()->getId(),
				"studentId" => $this->student->getId(),
				"login" => $this->student->getLogin()
			));

		}

		$form->addHidden('studentId');

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

		if ($values['studentId']) {
			$password = $this->userService->addUser($student);

			if ($student->getMainClass()->getId() != $values['class']) {
				$class = $this->em->find(ClassEntity::getClassName(), $values['class']);
				$student->getMainClass()->removeStudent($student);
				$student->addClass($class);
			}
		}

		try {
			$this->em->persist($student);
			$this->em->flush();

			if ($values['studentId']) {
				$this->presenter->flashMessage("Student byl úspěšně upraven.", "success");
			} else {
				$this->presenter->flashMessage("Student byl úspěšně vytvořen. <strong>Jeho login je: " . $student->getLogin() . ". Jeho nové heslo je: " . $password . "</strong>", "success");
			}
		} catch (\Exception $e) {
			if ($values['studentId']) {
				$this->presenter->flashMessage("Student nebyl upraven.", "alert");
			} else {
				$this->presenter->flashMessage("Student nebyl vytvořen.", "alert");
			}
		}
	}

}