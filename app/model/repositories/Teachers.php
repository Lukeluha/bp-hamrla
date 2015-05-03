<?php

namespace App\Model\Repositories;


use App\Model\Entities\Teacher;
use Kdyby\Doctrine\EntityRepository;

class Teachers extends EntityRepository
{
	public function findByQuery($query)
	{
		return $this->createQueryBuilder()
			->select("t")
			->from(Teacher::getClassName(), 't')
			->where("(CONCAT(t.name, CONCAT(' ', t.surname)) LIKE :query
								OR CONCAT(t.surname, CONCAT(' ', t.name)) LIKE :query)")
			->setParameter(":query", "%$query%")
			->addOrderBy("t.surname")
			->addOrderBy("t.name")
			->setMaxResults(5)
			->getQuery()->getResult();
	}

}