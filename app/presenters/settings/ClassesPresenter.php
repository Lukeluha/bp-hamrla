<?php

namespace App\Presenters;


use App\Model\Entities\ClassEntity;
use App\Model\Entities\SchoolYear;
use App\Model\Entities\Student;
use Nette\Application\UI\Form;

class ClassesPresenter extends AuthorizedBasePresenter
{
	public function startup()
	{
		parent::startup();
		$this->checkPermissions("settings", "classes");

		if (!$this->actualYear) {
			$this->flashMessage("Nebyl nalezen aktuální školní rok", 'alert');
			$this->redirect('Settings:default');
		}
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
				'id' => $class->getId(),
				'name' => $class->getName(),
				'type' => $class->getType(),
				'schoolYear' => $class->getSchoolYear()->getId()
			));
		}
	}


	public function createComponentClassForm()
	{
		$form = new Form();

		$form->addText("name", "Název třídy")->setRequired('Vyplňte prosím název třídy');

		$form->addSelect('type', "Typ", array(0 => "--Vyberte--", ClassEntity::TYPE_CLASS => "Třída", ClassEntity::TYPE_GROUP => "Skupina"))
			->addRule(Form::NOT_EQUAL, "Vyberte typ", 0);

		$schoolYears = $this->em->getRepository(SchoolYear::getClassName())->findBy(array('closed' => 0), array('from' => 'DESC'));

		$schoolYearSelect = array();
		foreach ($schoolYears as $year) {
			$schoolYearSelect[$year->getId()] = $year->getFrom()->format("Y") . "/" . $year->getTo()->format("Y");
		}

		$form->addSelect('schoolYear', "Školní rok", $schoolYearSelect)->setDefaultValue($this->actualYear->getId());


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
		} else {
			$class = new ClassEntity();
		}

		$class->setName($values['name'])->setType($values['type'])->setSchoolYear($this->em->getReference(SchoolYear::getClassName(), $values['schoolYear']));

		$sameClass = $this->checkSameClass($class);

		if ($sameClass) {
			$this->flashMessage("Třída s tímto názvem se již v tomto školním roce vyskytuje", 'alert');
			return;
		}


		try {
			$this->em->persist($class);
			$this->em->flush();
			if ($class->getType() == ClassEntity::TYPE_CLASS) {
				$this->flashMessage("Třída byla úspěšně uložena", "success");
			} else {
				$this->flashMessage("Skupina byla úspěšně uložena", "success");
			}
		} catch (\Exception $e) {
			if ($class->getType() == ClassEntity::TYPE_CLASS) {
				$this->flashMessage("Třída nebyla uložena", "alert");
			} else {
				$this->flashMessage("Skupina nebyla uložena", "alert");
			}

			return;
		}

		$this->redirect('Classes:default', $class->getId());
	}

	public function checkSameClass(ClassEntity $class)
	{
		if ($class->getId()) {
			return $this->em->createQueryBuilder()
					->select('c.id')
					->from(ClassEntity::getClassName(), 'c')
					->where('c.name = :name AND c.schoolYear = :schoolYear AND c.id != :id')
					->setParameters(array('name' => $class->getName(), 'schoolYear' => $class->getSchoolYear()->getId(), 'id' => $class->getId()))
					->getQuery()->getOneOrNullResult();
		} else {
			return $this->em->getRepository(ClassEntity::getClassName())->findOneBy(array('name' => $class->getName(), 'schoolYear' => $class->getSchoolYear()->getId()));
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
			$students = $this->em->getRepository(Student::getClassName())->findByName($query);

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