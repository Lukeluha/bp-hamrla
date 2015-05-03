<?php

namespace App\Model\Repositories;


use App\Model\Entities\Task;
use Kdyby\Doctrine\EntityRepository;

class Tasks extends EntityRepository
{

	public function findByText($query)
	{
		return $this->createQueryBuilder()
			->select("t")
			->from(Task::getClassName(), 't')
			->where("t.taskText LIKE :query OR t.taskName LIKE :query")
			->setParameter(":query", "%$query%")
			->addOrderBy("t.taskText")
			->groupBy("t.group")
			->setMaxResults(10)
			->getQuery()->getResult();
	}

	public function findByOpened()
	{
//		return $this->createQueryBuilder()
//				->select('t')
//				->from(Task::getClassName(), )

	}

}