<?php

namespace App\Presenters;


use App\Model\Entities\ClassEntity;
use App\Model\Entities\SchoolYear;
use App\Model\Entities\Student;
use App\Model\Entities\Subject;
use App\Model\Entities\Teacher;
use App\Model\Entities\Teaching;
use App\Model\Entities\TeachingTime;
use App\Model\FoundationRenderer;
use App\Model\Services\BadClassNameException;
use App\Model\Services\BadFormatException;
use App\Model\Utils;
use Nette\Application\UI\Form;
use App\Forms\IStudentFormFactory;
use App\Model\Services\StudentService;
use Nette\Forms\Container;
use App\Model\Services\LessonService;
use App\Model\Services\ClassService;

/**
 * Class ClassesPresenter
 * Page with class management, creating and editing students and teaching
 * @package App\Presenters
 */
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

	/**
	 * @var LessonService
	 * @inject
	 */
	public $lessonService;

	/**
	 * @var ClassService
	 * @inject
	 */
	public $classService;

	/**
	 * @var SchoolYear
	 */
	private $prevYear;


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

	/**
	 * Main page for class managing
	 * @param int|null $classId
	 */
	public function actionDefault($classId = null)
	{
		if (!$classId) {
			$this->addLinkToNav('Vytvoření nové třídy/skupiny', 'Classes:default');
			$this->prevYear = $this->em->getRepository(SchoolYear::getClassName())->findPreviousSchoolYear($this->actualYear);
			$this->template->prevYear = $this->prevYear;
		} else {
			$this->class = $this->em->getRepository(ClassEntity::getClassName())->find($classId);
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


	/**
	 * Factory for teaching form
	 * @return Form
	 */
	public function createComponentTeachingForm()
	{
		$that = $this;

		$form = new Form();

		$form->addGroup('Předmět');
		$subjects = $this->em->getRepository(Subject::getClassName())->findBy(array(), array('name' => "ASC"));
		$subjectList = array();

		foreach ($subjects as $subject) {
			$subjectList[$subject->getId()] = $subject;
		}

		$subjectList[0] = "Nový předmět...";

		$form->addSelect('subject', "Předmět", $subjectList)
			->addCondition(Form::EQUAL, 0)
			->toggle('newSubject');


		$form->addText("subjectName", "Název předmětu")
				->addConditionOn($form['subject'], Form::EQUAL, 0)
				->setRequired("Vyplňte název předmětu");

		$form->addText("abbreviation", "Zkratka předmětu")
			->addConditionOn($form['subject'], Form::EQUAL, 0)
			->setRequired("Vyplňte zkratku předmětu");




		$days = $this->days;
		$removeCallback = callback($this, 'removeElement');
		$redrawCallback = function() use ($that) {$that->redrawControl('teachingForm');};

		$teachingTimes = $form->addDynamic('teachingTime', function (Container $teachingTime) use ($days, $redrawCallback, $removeCallback) {
			$teachingTime->addSelect("weekDay", "Den v týdnu", $days);
			$teachingTime->addText("from", "Od")->setType('time')->setRequired('Vyplňte čas výuky od');
			$teachingTime->addText("to", "Do")->setType('time')->setRequired('Vyplňte čas výuky do');
			$teachingTime->addSelect("weekParity", "Parita týdne", array('0' => "oba týdny", TeachingTime::WEEK_EVEN => "sudý", TeachingTime::WEEK_ODD => "lichý"))->setAttribute('class', 'borderBottom');


			$teachingTime->addSubmit('remove', "Odebrat")
				->setValidationScope(FALSE)
				->setAttribute('class', 'ajax button alert tiny')
				->onClick[] = $removeCallback;

			$teachingTime['remove']->onClick[] = $redrawCallback;

		}, 0);


		$teachingTimes->addSubmit('add', 'Přidat dobu výuky')
						->setValidationScope(FALSE)
						->setAttribute('class', 'ajax button success tiny')
						->setAttribute('id', 'addTime')
						->onClick[] = callback($this, 'addElement');

		$teachingTimes['add']->onClick[] = $redrawCallback;


		$form->addGroup('Učitelé');

		$teachers = $this->em->getRepository(Teacher::getClassName())->findBy(array(), array('surname' => "ASC"));
		$teachersSelect = array();
		foreach ($teachers as $teacher) {
			$teachersSelect[$teacher->getId()] = $teacher->getSurname() . " " . $teacher->getName();
		}
		$teachersSelect[0] = 'Nový učitel...';

		$teachers = $form->addDynamic('teachers', function (Container $teacher) use ($teachersSelect, $removeCallback, $redrawCallback) {
			$teacher->addSelect('teacher', 'Učitel', $teachersSelect)->addCondition(Form::EQUAL, 0)->toggle($teacher->getName());

			$teacher->addText('teacherName', 'Jméno')->addConditionOn($teacher['teacher'], Form::EQUAL, 0)->setRequired('Vyplňte jméno učitele');
			$teacher->addText('teacherSurname', 'Příjmení')->addConditionOn($teacher['teacher'], Form::EQUAL, 0)->setRequired('Vyplňte příjmení učitele');
			$teacher->addText('teacherRoom', 'Místnost');

			$teacher->addSubmit('remove', "Odebrat")
				->setValidationScope(FALSE)
				->setAttribute('class', 'ajax button alert tiny')
				->onClick[] = $removeCallback;

			$teacher['remove']->onClick[] = $redrawCallback;

		}, 1);

		$teachers->addSubmit('add', "Přidat dalšího učitele")
				->setValidationScope(FALSE)
				->setAttribute('class', 'ajax button success tiny')
				->onClick[] = callback($this, 'addElement');

		$teachers['add']->onClick[] = $redrawCallback;


		$form->setCurrentGroup();
		$form->addSubmit('save', 'Uložit')->setAttribute('class', 'button');
		$form->onSuccess[] = callback($this, 'saveTeaching');
		$form->setRenderer(new FoundationRenderer());

		return $form;
	}

	/**
	 * Save teaching entity from form
	 * @param Form $form
	 * @return bool
	 */
	public function saveTeaching(Form $form)
	{

		$values = $form->getHttpData();

		if (!isset($values['save'])) return false;

		$values = $form->getValues();

		try {
			$this->em->beginTransaction();

			$teaching = new Teaching();
			$teaching->setClass($this->class);

			if (!$values['subject']) {
				$subject = new Subject();
				$subject->setName($values['subjectName'])->setAbbreviation($values['abbreviation']);

				$this->em->persist($subject);
				$this->em->flush();
			} else {
				$subject = $this->em->getRepository(Subject::getClassName())->find($values['subject']);
			}
			$teaching->setSubject($subject);

			$this->em->persist($teaching);
			$this->em->flush();

			foreach ($values['teachingTime'] as $teachingTime) {
				$time = new TeachingTime();
				$time->setFrom($teachingTime['from'])
						->setTo($teachingTime['to'])
						->setWeekDay($teachingTime['weekDay'])
						->setWeekParity($teachingTime['weekParity'])
						->setTeaching($teaching);

				$this->em->persist($time);
				$this->em->flush();
			}

			$newTeachers = array();
			foreach ($values['teachers'] as $teacher) {
				if ($teacher['teacher']) {
					$teacherEntity = $this->em->getRepository(Teacher::getClassName())->find($teacher['teacher']);
				} else {
					$teacherEntity = new Teacher();
					$teacherEntity->setName($teacher['teacherName'])
									->setSurname($teacher['teacherSurname'])
									->setRoom($teacher['teacherRoom']);

					$pass = $this->userService->addUser($teacherEntity);

					$this->em->persist($teacherEntity);
					$this->em->flush();

					$newTeachers[$teacherEntity->getId()] = $pass;
				}

				$teaching->addTeacher($teacherEntity);
				$this->em->flush();
			}

			$this->em->flush();

			$this->lessonService->createLessons($teaching);

			$this->em->commit();

			$this->flashMessage("Vyučování bylo úspěšně vytvořeno", 'success');
			if (count($newTeachers)) {
				$text = "Byli vytvořeni tito učitelé:<br/>";
				foreach ($newTeachers as $id => $pass) {
					$teacher = $this->em->getRepository(Teacher::getClassName())->find($id);
					$text .= $teacher->getSurname() . " " . $teacher->getName() . " - <strong>login: " . $teacher->getLogin() . ", heslo: " . $pass . "</strong><br/>";
				}

				$this->flashMessage($text, "success");
			}
		} catch (\Exception $e) {
			$this->em->rollback();
			$this->flashMessage('Vyučování nebylo vytvořeno', 'alert');
		}

		$this->redirect('this');
	}

	/**
	 * Factory for import form
	 * @return Form
	 */
	public function createComponentImportForm()
	{
		$form = new Form();
		$form->addUpload('file', 'Soubor pro import')->setRequired('Vyberte prosím soubor pro import');
		$form->addSubmit('import', 'Importovat')->getControlPrototype()->class('button');
		$form->onSuccess[] = $this->importStudents;
		$form->setRenderer(new FoundationRenderer());

		return $form;
	}

	/**
	 * Handler for import form - import students
	 * @param Form $form
	 */
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

	/**
	 * Removing student from group
	 * @param $studentId
	 */
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

	/**
	 * Adding student to group
	 * @param $studentId
	 */
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

	/**
	 * Factory for student form
	 * @return \App\Forms\StudentForm|void
	 */
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


	public function createComponentCopyClassForm()
	{
		$form = new Form();

		$oldClasses = $this->em->getRepository(ClassEntity::getClassName())->findBy(array(
			'schoolYear' => $this->prevYear->getId(),
			'type' => ClassEntity::TYPE_CLASS
		));

		$oldClassSelect = array(
			0 => "--Vyberte--"
		);

		$newNames = array();
		foreach ($oldClasses as $class) {
			$oldClassSelect[$class->getId()] = $class->getName();
//			$newNames[$class->getId()] = Utils::getNewClassName($class->getName());
		}

		$form->addSelect('oldClass', "Vyberte třídu ke kopii", $oldClassSelect)
			->setRequired('Vyberte třídu ke kopii');

		$form->addSubmit('save', "Zkopírovat třídu")->setAttribute('class', 'button small');
		$form->onSuccess[] = $this->copyClass;
		$form->setRenderer(new FoundationRenderer());
		return $form;
	}

	public function copyClass(Form $form)
	{
		$values = $form->getValues();
		$oldClass = $this->em->find(ClassEntity::getClassName(), $values['oldClass']);
		try {
			$newClass = $this->classService->copyClass($oldClass, $this->actualYear);
			$this->flashMessage("Třída byla úspěšně zkopírována", "success");
		} catch (\Exception $e) {
			$this->flashMessage("Třídu se nepodařilo zkopírovat", "alert");
			return;
		}

		$this->redirect('Classes:default', array($newClass->getId()));
	}

	/**
	 * Factory for class form
	 * @return Form
	 */
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

	/**
	 * Save class from form data
	 * @param Form $form
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function saveClass(Form $form)
	{
		$values = $form->getValues();

		if ($values['id']) {
			$class = $this->em->getRepository(ClassEntity::getClassName())->find($values['id']);
		} else {
			$class = new ClassEntity();
		}

		$class->setName(str_replace(' ', '', $values['name']))
			->setType($values['type'])
			->setSchoolYear($this->em->getReference(SchoolYear::getClassName(), $values['schoolYear']));

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

	/**
	 * Check if there is same class in same school year
	 * @param ClassEntity $class
	 * @return mixed|null|object
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
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
	 * Adding student to class
	 * @param $studentId
	 * @param $classId
	 * @throws \Nette\Application\AbortException
	 */
	public function handleAddStudentToClass($studentId, $classId)
	{
		$class = $this->em->getRepository(ClassEntity::getClassName())->find($classId);
		$student = $this->em->getRepository(Student::getClassName())->find($studentId);
		$class->addStudent($student);
		$this->em->flush();

		$this->terminate();
	}

	/**
	 * Removing student from class
	 * @param $studentId
	 * @param $classId
	 * @throws \Nette\Application\AbortException
	 */
	public function handleRemoveStudentFromClass($studentId, $classId)
	{
		$class = $this->em->getRepository(ClassEntity::getClassName())->find($classId);
		$student = $this->em->getRepository(Student::getClassName())->find($studentId);
		$class->removeStudent($student);
		$this->em->flush();

		$this->terminate();
	}
}