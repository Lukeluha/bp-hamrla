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

	public function getPreviousSchoolYear(SchoolYear $schoolYear = null)
	{
		if (!$schoolYear) return null;
		return $this->em->createQueryBuilder()
				->select('s')
				->from(SchoolYear::getClassName(), 's')
				->where("s.to < '" . $schoolYear->getFrom()->format("Y-m-d") . "'")
				->orderBy('s.from', 'DESC')
				->setMaxResults(1)
				->getQuery()->getOneOrNullResult();
	}

}