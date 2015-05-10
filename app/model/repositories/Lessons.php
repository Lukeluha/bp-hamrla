<?php

namespace App\Model\Repositories;


use App\Model\Entities\Lesson;
use App\Model\Entities\Teaching;
use Kdyby\Doctrine\EntityRepository;

class Lessons extends EntityRepository
{
	/**
	 * Returns next lesson for given teaching
	 * @param Teaching $teaching
	 * @return mixed
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function findNext(Teaching $teaching)
	{
		$now = new \DateTime();
		return $this->createQueryBuilder()
						->select('l')
						->from(Lesson::getClassName(), 'l')
						->where('l.teaching = ' . $teaching->getId())
						->andWhere("l.startDate >= '" . $now->format("Y-m-d") . "'")
						->addOrderBy("l.startDate", "ASC")
						->setMaxResults(1)
						->getQuery()->getOneOrNullResult();

	}

	/**
	 * Returns rank of lesson
	 * @param Lesson $lesson
	 * @return int
	 */
	public function findRank(Lesson $lesson){
		$sql = "SELECT id, rank FROM (
				SELECT l.*,	@rownum := @rownum + 1 AS rank
				FROM lessons l,	(SELECT @rownum := 0) r
				WHERE teaching_id = :teachingId
				ORDER BY `start_date` ASC
			) l
			WHERE id = :lessonId";


		$query = $this->getEntityManager()->getConnection()->prepare($sql);
		$query->bindValue("lessonId", $lesson->getId());
		$query->bindValue("teachingId", $lesson->getTeaching()->getId());
		$query->execute();


		return (int) $query->fetchColumn(1);
	}

}