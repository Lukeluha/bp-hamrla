<?php

namespace App\Model\Repositories;


use App\Model\Entities\Question;
use Kdyby\Doctrine\EntityRepository;

/**
 * Class Questions
 * Repository class for entity question
 * @package App\Model\Repositories
 */
class Questions extends EntityRepository
{
	/**
	 * Returns question by given query
	 * @param $query
	 * @return array
	 */
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