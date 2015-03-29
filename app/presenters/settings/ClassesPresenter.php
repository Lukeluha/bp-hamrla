<?php

namespace App\Presenters;


use App\Model\Entities\ClassEntity;
use App\Model\Entities\Student;
use Nette\Application\UI\Form;

class ClassesPresenter extends AuthorizedBasePresenter
{
	/**
	 * @var \App\Model\Services\StudentService
	 * @inject
	 */
	public $studentService;

	public function startup()
	{
		parent::startup();
		$this->checkPermissions("settings", "classes");
		$this->addLinkToNav('Nastavení', 'Settings:default');
	}

	public function actionDefault($classId = null)
	{
		if (!$classId) {
			$this->addLinkToNav('Vytvoření nové třídy/skupiny', 'Classes:default');
		} else {
			$class = $this->em->getRepository(ClassEntity::getClassName())->find($classId);
			if (!$class) {
				$this->flashMessage("Nenalezena žádná třída.", "alert");
				$this->redirect("Settings:default");
			}

			$this->template->class = $class;

			if ($class->getType() == ClassEntity::TYPE_CLASS) {
				$this->addLinkToNav('Editace třídy', 'Classes:default', array($classId));
			} else {
				$this->addLinkToNav('Editace skupiny', 'Classes:default', array($classId));
			}

			$this['classForm']->setDefaults(array(
					'id' => $class->getId(), 'name' => $class->getName(), 'type' => $class->getType()
				));
		}
	}


	public function createComponentClassForm()
	{
		$form = new Form();

		$form->addText("name", "Název třídy")->setRequired('Vyplňte prosím název třídy');

		$form->addSelect('type', "Typ", array(0 => "--Vyberte--", ClassEntity::TYPE_CLASS => "Třída", ClassEntity::TYPE_GROUP => "Skupina" ))
			->addRule(Form::NOT_EQUAL, "Vyberte typ", 0);

		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'p';
		$renderer->wrappers['pair']['container'] = null;
		$renderer->wrappers['label']['container'] = null;
		$renderer->wrappers['control']['container'] = null;

		$form->addSubmit("save", "Uložit")->setAttribute('class', 'button small');
		$form->addHidden("id");
		$form->onSuccess[] = $this->saveClass;

		return $form;
	}

	public function saveClass(Form $form)
	{
		$values = $form->getValues();

		if ($values['id']) {
			$class = $this->em->getRepository(ClassEntity::getClassName())->find($values['id']);
			if (!$class) {
				$this->flashMessage("Třída nebyla nalezena.", "alert");
				$this->redirect("this");
			}

			$class->setName($values['name'])->setType($values['type']);

			try {
				$this->em->flush();
				if ($class->getType() == ClassEntity::TYPE_CLASS) {
					$this->flashMessage("Třída byla úspěšně upravena", "success");
				} else {
					$this->flashMessage("Skupina byla úspěšně upravena", "success");
				}
			} catch (\Exception $e) {
				if ($class->getType() == ClassEntity::TYPE_CLASS) {
					$this->flashMessage("Třída nebyla upravena", "alert");
				} else {
					$this->flashMessage("Skupina nebyla upravena", "alert");
				}
			}
		} else {
			$class = new ClassEntity();
			$class->setName($values['name'])->setType($values['type']);
			try {
				$this->em->persist($class);
				$this->em->flush();
				if ($class->getType() == ClassEntity::TYPE_CLASS) {
					$this->flashMessage("Třída byla úspěšně vytvořena", "success");
				} else {
					$this->flashMessage("Skupina byla úspěšně vytvořena", "success");
				}
			} catch (\Exception $e) {
				if ($class->getType() == ClassEntity::TYPE_CLASS) {
					$this->flashMessage("Třída nebyla vytvořena", "alert");
				} else {
					$this->flashMessage("Skupina nebyla vytvořena", "alert");
				}
			}
		}

		if ($class->getId()) {
			$this->redirect("default", array($class->getId()));
		} else {
			$this->redirect("default");
		}
	}

	/**
	 * Find all students for given query and check for availability to adding to given groupId
	 * @param string $query
	 * @param null|int $groupId
	 */
	public function handleFindStudents($query, $groupId = null)
	{
		if ($query) {
			$students = $this->studentService->findByName($query);

			$studentsArray = array();
			foreach ($students as $student) {
				$studentArray = array(
					'name' => $student->getName(),
					'surname' => $student->getSurname(),
				);

				if ($student->getMainClass()) {
					$studentArray['mainClass']['name'] = $student->getMainClass()->getName();
					$studentArray['mainClass']['id'] = $student->getMainClass()->getId();
				} else {
					$studentArray['mainClass'] = null;
				}

				if ($groupId) {
					$studentArray['isInClass'] = $student->isInClass($groupId);
				}

				$studentArray['profilePicture'] = $student->getProfilePicture();
				$studentArray['id'] = $student->getId();

				$studentsArray[] = $studentArray;
			}

			$this->sendJson($studentsArray);
		}
	}

	public function handleAddStudentToClass($studentId, $classId)
	{
		$class = $this->em->getRepository(ClassEntity::getClassName())->find($classId);
		$student = $this->em->getRepository(Student::getClassName())->find($studentId);
		$class->addStudent($student);
		$this->em->flush();

		$this->terminate();
	}

	public function handleRemoveStudentFromClass($studentId, $classId)
	{
		$class = $this->em->getRepository(ClassEntity::getClassName())->find($classId);
		$student = $this->em->getRepository(Student::getClassName())->find($studentId);
		$class->removeStudent($student);
		$this->em->flush();

		$this->terminate();
	}

	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->ngApp = 'app';
	}

}