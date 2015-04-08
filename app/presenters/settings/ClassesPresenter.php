<?php

namespace App\Presenters;


use App\Model\Entities\ClassEntity;
use App\Model\Entities\SchoolYear;
use App\Model\Entities\Student;
use App\Model\FoundationRenderer;
use App\Model\Services\BadClassNameException;
use App\Model\Services\BadFormatException;
use Nette\Application\UI\Form;
use App\Forms\IStudentFormFactory;
use App\Model\Services\StudentService;
use App\Model\Services\UserService;

class ClassesPresenter extends AuthorizedBasePresenter
{
	/**
	 * @var IStudentFormFactory
	 * @inject
	 */
	public $studentFormFactory;

	/**
	 * @var StudentService
	 * @inject
	 */
	public $studentService;

	/**
	 * Current loaded class
	 * @var ClassEntity
	 */
	private $class;


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
			$this->class = $this->em->getRepository(ClassEntity::getClassName())->find($classId);
//			dump($this->class->getTeachings());die;
			if (!$this->class) {
				$this->flashMessage("Nenalezena žádná třída.", "alert");
				$this->redirect("Settings:default");
			}

			$this->template->class = $this->class;

			if ($this->class->getType() == ClassEntity::TYPE_CLASS) {
				$this->addLinkToNav('Editace třídy', 'Classes:default', array($classId));
			} else {
				$this->addLinkToNav('Editace skupiny', 'Classes:default', array($classId));
			}

			$this['classForm']->setDefaults(array(
				'id' => $this->class->getId(),
				'name' => $this->class->getName(),
				'type' => $this->class->getType(),
				'schoolYear' => $this->class->getSchoolYear()->getId()
			));
		}
	}


	public function createComponentTeachingForm()
	{
		$form = new Form();


		$form->setRenderer(new FoundationRenderer());

		return $form;
	}

	public function createComponentImportForm()
	{
		$form = new Form();
		$form->addUpload('file', 'Soubor pro import')->setRequired('Vyberte prosím soubor pro import');
		$form->addSubmit('import', 'Importovat')->getControlPrototype()->class('button');
		$form->onSuccess[] = $this->importStudents;
		$form->setRenderer(new FoundationRenderer());

		return $form;
	}

	public function importStudents(Form $form)
	{
		$file = $form->getValues()->file;

		try {
			$passwords = $this->studentService->importStudents($file->getTemporaryFile(), $this->class);

			$text = "<p>Import byl proveden úspěšně.";
			$count = count($passwords);
			if ($count == 1) {
				$text .= " Byl vytvořen 1 student.";
			} elseif ($count > 1 && $count < 5) {
				$text .= " Byli vytvořeni $count studenti.";
			} else {
				$text .= " Bylo vytvořeno $count studentů.";
			}

			$text .= "</p>";
			$text .= "<div id='passwordsPrint' class='print-only'>";
			$text .= "<table border='1' cellpadding='8' style='border-collapse: collapse'>";
			$text .= "
				<thead>
					<tr>
						<th>Jméno a příjmení</th>
						<th>Login</th>
						<th>Heslo</th>
					</tr>
				</thead>";

			$text .= "<tbody>";

			foreach ($passwords as $id => $password) {
				$student = $this->em->getRepository(Student::getClassName())->find($id);
				$text .= "
				<tr>
					<td>".$student->getName()." " . $student->getSurname() . "</td>
					<td>".$student->getLogin()."</td>
					<td>".$password."</td>
				</tr>
				";
			}
			$text .= "</tbody></table>";
			$text .= "</div>";

			if ($count) {
				$text .= "<button class='button' onclick='printData(\"passwordsPrint\")'>Tisk hesel studentů</button>";
			}

			$this->flashMessage($text, "success");
		} catch (BadClassNameException $e) {
			$this->flashMessage('Název třídy ze souboru se neshoduje s názvem třídy v aplikaci. Název třídy v souboru je: ' . $e->getMessage(), 'alert');
		} catch (BadFormatException $e) {
			$this->flashMessage('Nesprávný formát souboru.', 'alert');
		} catch (\Exception $e) {
			dump($e);die;
			$this->flashMessage('Import nebyl proveden.', 'alert');
		}

		$this->redirect('this');

	}

	/**
	 * Find all students for given query
	 * @param string $query
	 */
	public function handleFindStudents($query)
	{
		if ($this->isAjax()) {
			$students = $this->em->getRepository(Student::getClassName())->findByName($query, $this->actualYear);
			$this->template->students = $students;
			$this->redrawControl('students');
		}
	}

	public function handleRemoveStudentFromGroup($studentId)
	{
		if ($this->class->getType() == ClassEntity::TYPE_GROUP) {
			if ($this->isAjax()) {
				$student = $this->em->getRepository(Student::getClassName())->find($studentId);
				$this->class->removeStudent($student);
				$this->em->persist($this->class);
				$this->em->flush();
				$this->template->students = array($student->getId() => $student);
				$this->redrawControl('studentsContainer');
				$this->redrawControl('studentsInClass');
			}
		}
	}

	public function handleAddStudentToGroup($studentId)
	{
		if ($this->isAjax()) {
			$student = $this->em->getRepository(Student::getClassName())->find($studentId);

			$this->class->addStudent($student);
			$this->em->persist($this->class);
			$this->em->flush();
			$this->template->students = array($studentId => $student);
			$this->redrawControl('studentsContainer');
			$this->redrawControl('studentsInClass');
		}
	}

	public function createComponentStudentForm()
	{
		$form = $this->studentFormFactory->create(null, $this->actualYear);
		if (!$this->class) return;

		if ($this->class->getType() == ClassEntity::TYPE_CLASS) {
			$disabledClass = array(0);
			foreach ($form['form']['class']->getItems() as $classId => $class) {
				if ($classId != $this->class->getId()) $disabledClass[] = $classId;
			}

			$form['form']['class']->setDisabled($disabledClass);
		}


		if ($this->class->getType() == ClassEntity::TYPE_GROUP) {
			$form['form']->setDefaults(array('groupId' => $this->class->getId()));
		}

		$that = $this;

		$form->onCreate[] = function ($component, $student) use ($that) {
			$that->flashMessage("Student byl úspěšně vytvořen. <strong>Jeho login je: " . $student->getLogin() . " a heslo je: " . $component->getStudentPassword() . "</strong>", "success");
			$that->redirect("this");
		};

		$form->onError[] = function () use ($that) {
			$that->flashMessage("Student nebyl uložen.", 'alert');
		};

		return $form;
	}


	public function createComponentClassForm()
	{
		$form = new Form();

		$form->addText("name", "Název třídy")->setRequired('Vyplňte prosím název třídy');

		$classSelect = array();

		if (!$this->class) {
			$classSelect[0] = '--Vyberte--';
		}

		$classSelect[ClassEntity::TYPE_CLASS] = "Třída";
		$classSelect[ClassEntity::TYPE_GROUP] = "Skupina";

		$form->addSelect('type', "Typ", $classSelect)
			->addRule(Form::NOT_EQUAL, "Vyberte typ", 0);


		if ($this->class && count($this->class->getStudents())) {
			if ($this->class->getType() == ClassEntity::TYPE_CLASS) {
				$disabled = array(ClassEntity::TYPE_GROUP);
			} else {
				$disabled = array(ClassEntity::TYPE_CLASS);
			}

			$form['type']->setDisabled($disabled);
		}

		$schoolYears = $this->em->getRepository(SchoolYear::getClassName())->findBy(array('closed' => 0), array('from' => 'DESC'));

		$schoolYearSelect = array();
		$disabledSchoolYear = array();
		foreach ($schoolYears as $year) {
			if ($this->class && count($this->class->getStudents())) {
				if ($year != $this->class->getSchoolYear()) {
					$disabledSchoolYear[] = $year->getId();
				}
			}
			$schoolYearSelect[$year->getId()] = $year->getFrom()->format("Y") . "/" . $year->getTo()->format("Y");
		}

		$form->addSelect('schoolYear', "Školní rok", $schoolYearSelect)->setDefaultValue($this->actualYear->getId());

		if (count($disabledSchoolYear)) {
			$form['schoolYear']->setDisabled($disabledSchoolYear);
		}


		$form->setRenderer(new FoundationRenderer());

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
}