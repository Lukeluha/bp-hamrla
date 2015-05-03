<?php

namespace App\Controls;


use App\Model\Entities\ActivityPoints;
use App\Model\Entities\ClassEntity;
use App\Model\Entities\Lesson;
use App\Model\Entities\Student;
use Doctrine\ORM\Query;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\QueryBuilder;
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

	public function __construct(ClassEntity $class, EntityManager $em)
	{
		$this->em = $em;
		$this->class = $class;
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