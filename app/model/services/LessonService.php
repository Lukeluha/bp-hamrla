<?php

namespace App\Model\Services;


use App\Model\Entities\Lesson;
use App\Model\Entities\Question;
use App\Model\Entities\Task;
use App\Model\Entities\Teaching;
use App\Model\Entities\TeachingTime;
use Nette\Security\User;

/**
 * Class LessonService
 * Service class for entity lesson
 * @package App\Model\Services
 */
class LessonService extends BaseService
{
	protected $translateWeekDay = array("monday", "tuesday", "wednesday", "thursday", "friday");

	protected $holidays = array('01-01', '05-01', '05-08', '07-05', '07-06', '09-28', '10-28', '11-17', '12-24', '12-25', '12-26');

	/**
	 * Creates lessons by given teaching entity - checks for holidays, week parity, etc.
	 * @param Teaching $teaching
	 */
	public function createLessons(Teaching $teaching)
	{
		$teachingTimes = $teaching->getTeachingTimes();
		if (!count($teachingTimes)) return;

		$schoolYear = $teaching->getClass()->getSchoolYear();
		$startDate = $schoolYear->getFrom();
		$endDate = $schoolYear->getTo();


		foreach ($teachingTimes as $teachingTime) {
			/** @var TeachingTime $teachingTime */

			$parity = $teachingTime->getWeekParity();

			if ($startDate->format("N") != $teachingTime->getWeekDay() + 1) {
				$startDate->modify("next " . $this->translateWeekDay[$teachingTime->getWeekDay()]);
			}

			if ($parity) {
				$interval = new \DateInterval("P2W");

				if ($parity == TeachingTime::WEEK_EVEN) {
					if ($startDate->format("W") % 2 != 0) {
						$startDate->add(new \DateInterval("P1W"));
					}
				} else {
					if ($startDate->format("W") % 2 == 0) {
						$startDate->add(new \DateInterval("P1W"));
					}
				}
			} else {
				$interval = new \DateInterval("P1W");
			}

			$startDate->setTime($teachingTime->getFrom()->format("G"), $teachingTime->getFrom()->format("i"));

			while ($startDate <= $endDate) {
				if (in_array($startDate->format("m-d"), $this->holidays)) {
					$startDate->add($interval);
					continue;
				}

				if ($startDate->format("N") == 1) { // if monday, check for easter
					$easter = \DateTime::createFromFormat('U', easter_date($startDate->format("Y")));
					if ($easter->format('Y-m-d') == $startDate->format("Y-m-d")) {
						$startDate->add($interval);
						continue;
					}
				}

				$lesson = new Lesson();

				$lessonEnd = clone $startDate;
				$lessonEnd->setTime($teachingTime->getTo()->format("G"), $teachingTime->getTo()->format("i"));

				$lesson->setTeaching($teaching)->setStartDate($startDate)->setEndDate($lessonEnd);


				$this->em->persist($lesson);
				$this->em->flush();


				$startDate->add($interval);
			}

		}
	}

	/**
	 * Returns activities (questions, tasks and exams) in given lesson
	 * @param Lesson $lesson
	 * @return array
	 */
	public function getActivitiesInLesson(Lesson $lesson, User $user)
	{
		$activities = array();

		if ($user->isInRole(\App\Model\Entities\User::ROLE_TEACHER)) {
			$activities['questions'] = $lesson->getQuestions();
			$activities['tasks'] = $lesson->getTasks();
		} else { // student
			$activities['questions'] = $this->em
											->getRepository(Question::getClassName())
											->findBy(
												array(
													'visible' => true,
													'lesson' => $lesson->getId()
												),
												array(
												'questionText' => "ASC"
											));
			$activities['tasks'] = $this->em
				->createQueryBuilder()->select('t')
				->from(Task::getClassName(), 't')
				->where('t.lesson = :lessonId AND t.visible = 1')
				->setParameters(array('lessonId' => $lesson->getId()))
				->orderBy('t.taskName', 'ASC')
				->getQuery()->getResult();
		}


		return $activities;
	}
}