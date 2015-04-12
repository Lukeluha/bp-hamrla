<?php

namespace App\Model\Repositories;


use App\Model\Entities\ClassEntity;
use App\Model\Entities\SchoolYear;
use App\Model\Entities\Student;
use Kdyby\Doctrine\EntityRepository;
use Nette\Security\User;

class Users extends EntityRepository
{
	/**
	 * @param User $user
	 * @param SchoolYear $schoolYear
	 * @return array
	 */
	public function findForChat(User $user, SchoolYear $schoolYear)
	{
		if ($user->isInRole('admin')) {
			return $this->createQueryBuilder()->select('s')
					->from(ClassEntity::getClassName(), 'c')
					->join(Student::getClassName(), 's')
					->where('c.schoolYear = ' . $schoolYear->getId())
					->addOrderBy('s.surname', 'ASC')
					->addOrderBy('s.name', 'ASC')
					->getQuery()->getResult();

		}

	}

}