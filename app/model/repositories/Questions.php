<?php

namespace App\Model\Repositories;


use App\Model\Entities\Question;
use Kdyby\Doctrine\EntityRepository;

class Questions extends EntityRepository
{
	public function findByText($query)
	{
		return $this->createQueryBuilder()
			->select("q")
			->from(Question::getClassName(), 'q')
			->where("q.questionText LIKE :query")
			->setParameter(":query", "%$query%")
			->addOrderBy("q.questionText")
			->groupBy("q.group")
			->setMaxResults(10)
			->getQuery()->getResult();

	}

}