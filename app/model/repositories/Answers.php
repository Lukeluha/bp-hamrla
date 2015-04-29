<?php

namespace App\Model\Repositories;


use App\Model\Entities\Answer;
use App\Model\Entities\Question;
use Kdyby\Doctrine\EntityRepository;

class Answers extends EntityRepository
{
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