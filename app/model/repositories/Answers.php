<?php

namespace App\Model\Repositories;


use App\Model\Entities\Answer;
use App\Model\Entities\Question;
use Kdyby\Doctrine\EntityRepository;

/**
 * Class Answers
 * Repository for answer entity
 * @package App\Model\Repositories
 */
class Answers extends EntityRepository
{
	/**
	 * Returns data for chart, in array formatted as points => count
	 * @param Question $question
	 * @return array
	 */
	public function getDataForChart(Question $question)
	{
		return $this->createQueryBuilder()
				->select('a.points, COUNT(a) as cnt')
				->from(Answer::getClassName(), 'a')
				->where("a.question = " . $question->getId() . " AND a.points IS NOT NULL")
				->groupBy('a.points')
				->getQuery()->getArrayResult();
	}

}