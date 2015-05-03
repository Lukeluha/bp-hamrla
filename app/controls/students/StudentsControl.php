<?php

namespace App\Controls;


use App\Model\Entities\ActivityPoints;
use App\Model\Entities\Lesson;
use App\Model\Entities\Student;
use App\Model\Entities\Teaching;
use Doctrine\ORM\Query;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;

class StudentsControl extends Control
{

	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var Class
	 */
	protected $class;

	/**
	 * @var Lesson
	 */
	protected $lesson;

	/**
	 * @var Teaching
	 */
	protected $teaching;

	/**
	 * @var Student
	 */
	protected $student;

	/**
	 * @var IStudentDetailControlFactory
	 */
	protected $studentDetailControlFactory;

	public function __construct(Teaching $teaching, EntityManager $em, IStudentDetailControlFactory $studentDetailControlFactory)
	{
		$this->em = $em;
		$this->class = $teaching->getClass();
		$this->teaching = $teaching;
		$this->studentDetailControlFactory = $studentDetailControlFactory;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/students.latte");
		$this->template->class = $this->class;
		$this->template->lesson = $this->lesson;

		$activityPoints = array();
		
		foreach ($this->class->getStudents() as $student) {
			$points = $this->getActivityPointsForStudent($student->getId());

			if (!is_object($points)) {
				$activityPoints[$student->getId()] = $points ? $points : 0;
			} else {
				$activityPoints[$student->getId()] = $points ? $points->getCount() : 0;
			}
		}
		$this->template->activityPoints = $activityPoints;
		$this->template->render();
	}

	public function handleLoadStudent($studentId)
	{
		$student = $this->em->find(Student::getClassName(), $studentId);
		if (!$student) throw new BadRequestException;

		$this->template->student = $this->student = $student;

		$this->redrawControl('studentDetail');
	}

	public function handleAddActivityPoint($studentId)
	{
		$activityPoints = $this->getActivityPointsForStudent($studentId);
		if (!$activityPoints) {
			$activityPoints = new ActivityPoints();
		}

		$activityPoints->setLesson($this->lesson);
		$activityPoints->setCount($activityPoints->getCount()+1);
		$activityPoints->setStudent($this->em->getReference(Student::getClassName(), $studentId));

		$this->em->persist($activityPoints);
		$this->em->flush();

		$this->redrawControl('students');
	}

	public function handleRemoveActivityPoint($studentId)
	{
		$activityPoints = $this->getActivityPointsForStudent($studentId);
		if (!$activityPoints) {
			$activityPoints = new ActivityPoints();
		}

		$activityPoints->setLesson($this->lesson);
		$activityPoints->setCount($activityPoints->getCount()-1);
		$activityPoints->setStudent($this->em->getReference(Student::getClassName(), $studentId));

		$this->em->persist($activityPoints);
		$this->em->flush();

		$this->redrawControl('students');
	}

	private function getActivityPointsForStudent($studentId)
	{
		if ($this->lesson) {
			return $this->em->getRepository(ActivityPoints::getClassName())->findOneBy(array(
				'student' => $studentId,
				'lesson' => $this->lesson->getId()
			));
		} else {
			return $this->em->createQueryBuilder()
				->select("SUM(a.count)")
				->from(ActivityPoints::getClassName(), 'a')
				->where('a.student = '. $studentId)->getQuery()->setHydrationMode(Query::HYDRATE_SINGLE_SCALAR)->getOneOrNullResult();
		}
	}

	public function createComponentStudentDetail()
	{
		$control = $this->studentDetailControlFactory->create($this->student);
		$control->setTeaching($this->teaching);
		$control->setLesson($this->lesson);
		return $control;
	}

	/**
	 * @param Lesson $lesson
	 * @return $this
	 */
	public function setLesson(Lesson $lesson)
	{
		$this->lesson = $lesson;
		return $this;
	}

}