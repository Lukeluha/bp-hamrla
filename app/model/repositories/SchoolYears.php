<?php
namespace App\Model\Repositories;

use App\Model\Entities\SchoolYear;
use Kdyby\Doctrine\EntityRepository;

class SchoolYears extends EntityRepository
{
	public function findCurrentSchoolYear()
	{
		return $this->createQueryBuilder()
			->select('s')
			->from(SchoolYear::getClassName(), 's')
			->where('s.closed = 0')
			->orderBy('s.from')
			->setMaxResults(1)
			->getQuery()->getOneOrNullResult();
	}

	public function findPreviousSchoolYear(SchoolYear $schoolYear = null)
	{
		if (!$schoolYear) return null;
		return $this->createQueryBuilder()
			->select('s')
			->from(SchoolYear::getClassName(), 's')
			->where("s.to < '" . $schoolYear->getFrom()->format("Y-m-d") . "'")
			->orderBy('s.from', 'DESC')
			->setMaxResults(1)
			->getQuery()->getOneOrNullResult();
	}
}