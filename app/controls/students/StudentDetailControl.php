<?php

namespace App\Controls;


use App\Model\Entities\Answer;
use App\Model\Entities\Student;
use App\Model\Entities\TaskCompleted;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use App\Model\Entities\Teaching;
use App\Model\Entities\Lesson;

class StudentDetailControl extends Control
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var Student
	 */
	protected $student;

	/**
	 * @var Teaching
	 */
	protected $teaching;

	/**
	 * @var Lesson
	 */
	protected $lesson;

	public function __construct(Student $student, EntityManager $em)
	{
		$this->em = $em;
		$this->student = $student;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/studentDetail.latte");
		$this->template->student = $this->student;
		if ($this->teaching) {
			$this->template->answers = $this->em->createQueryBuilder()
				->select('a')
				->from(Answer::getClassName(), 'a')
				->join('a.question', 'q')
				->join('q.lesson', 'l', 'WITH', 'l.teaching = ' . $this->teaching->getId())
				->where('a.student = ' . $this->student->getId())->getQuery()->getResult();

			$this->template->tasks = $this->em->createQueryBuilder()
				->select('t')
				->from(TaskCompleted::getClassName(), 't')
				->join('t.task', 'to')
				->join('to.lesson', 'l', 'WITH', 'l.teaching = ' . $this->teaching->getId())
				->where('t.student = ' . $this->student->getId())->getQuery()->getResult();
		}

		if ($this->lesson) {
			$this->template->lessonAnswers = $this->em->createQueryBuilder()
				->select('a')
				->from(Answer::getClassName(), 'a')
				->join('a.question', 'q')
				->where('q.lesson = ' . $this->lesson->getId() . ' AND a.student = ' . $this->student->getId())->getQuery()->getResult();

			$this->template->lessonTasks = $this->em->createQueryBuilder()
				->select('t')
				->from(TaskCompleted::getClassName(), 't')
				->join('t.task', 'to')
				->where('to.lesson = ' . $this->lesson->getId() . ' AND t.student = ' . $this->student->getId())->getQuery()->getResult();
		}


		$this->template->lesson = $this->lesson;
		$this->template->render();
	}

	/**
	 * @param Teaching $teaching
	 * @return $this
	 */
	public function setTeaching($teaching)
	{
		$this->teaching = $teaching;
		return $this;
	}

	/**
	 * @param Lesson $lesson
	 * @return $this
	 */
	public function setLesson($lesson)
	{
		$this->lesson = $lesson;
		return $this;
	}
}