<?php

namespace App\Model\Repositories;


use App\Model\Entities\Lesson;
use App\Model\Entities\Teaching;
use Kdyby\Doctrine\EntityRepository;

class Lessons extends EntityRepository
{
	public function findNext(Teaching $teaching)
	{
		$now = new \DateTime();
		return $this->createQueryBuilder()
						->select('l')
						->from(Lesson::getClassName(), 'l')
						->where('l.teaching = ' . $teaching->getId())
						->andWhere("l.date >= '" . $now->format("Y-m-d") . "'")
						->addOrderBy("l.date", "ASC")
						->setMaxResults(1)
						->getQuery()->getOneOrNullResult();

	}

}