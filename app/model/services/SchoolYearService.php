<?php

namespace App\Model\Services;


use App\Model\Entities\SchoolYear;

class SchoolYearService extends BaseService
{
	public function getCurrentSchoolYear()
	{
		return $this->em->createQueryBuilder()
				->select('s')
				->from(SchoolYear::getClassName(), 's')
				->where('s.closed = 0')
				->orderBy('s.from')
				->setMaxResults(1)
				->getQuery()->getOneOrNullResult();
	}

}