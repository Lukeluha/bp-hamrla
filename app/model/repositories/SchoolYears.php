<?php
namespace App\Model\Repositories;

use App\Model\Entities\SchoolYear;
use Kdyby\Doctrine\EntityRepository;

/**
 * Class SchoolYears
 * Repository class for school year entity
 * @package App\Model\Repositories
 */
class SchoolYears extends EntityRepository
{
	/**
	 * Returns current school year
	 * @return mixed
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
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

	/**
	 * Returns previous school year
	 * @param SchoolYear $schoolYear
	 * @return mixed|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
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