<?php

namespace App\Model\Repositories;

use App\Model\Entities\ClassEntity;
use App\Model\Entities\SchoolYear;
use Kdyby\Doctrine\EntityRepository;

/**
 * Class Classes
 * Repository class for Class Entity
 * @package App\Model\Repositories
 */
class Classes extends EntityRepository
{
	public function findByOpenedYears()
	{
		return $this->createQueryBuilder()
						->select('c')
						->from(ClassEntity::getClassName(), 'c')
						->join(SchoolYear::getClassName(), 'y')
						->where("y.closed != 0 AND c.type = 'class'")
						->addOrderBy('y.from', 'DESC')
						->addOrderBy('c.name')
						->getQuery()->getResult();
	}

	public function findByName($name, $actualYear = null)
	{
		if (!$actualYear) return null;
		return $this->createQueryBuilder()
						->select('c')
						->from(ClassEntity::getClassName(), 'c')
						->where("c.schoolYear = " . $actualYear->getId() . " AND c.name LIKE :name")
						->setParameter("name", "%".$name."%")
						->addOrderBy('c.name', 'ASC')
						->setMaxResults(10)
						->getQuery()->getResult();
	}

	public function findByYear(SchoolYear $schoolYear)
	{

	}

}