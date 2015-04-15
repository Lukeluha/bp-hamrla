<?php

namespace App\Model\Repositories;


use App\Model\Entities\ClassEntity;
use App\Model\Entities\SchoolYear;
use App\Model\Entities\Student;
use App\Model\Entities\Teacher;
use App\Model\Entities\Teaching;
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
			$students = $this->createQueryBuilder()->select('s')
				->from(ClassEntity::getClassName(), 'c')
				->join(Student::getClassName(), 's')
				->where('c.schoolYear = ' . $schoolYear->getId())
				->addOrderBy('s.surname', 'ASC')
				->addOrderBy('s.name', 'ASC')
				->getQuery()->getResult();

			$teachers = $this->createQueryBuilder()
					->select('t')
					->from(Teacher::getClassName(), 't')
					->join('t.teachings', 'tc')
					->join('tc.class', 'c')
					->where('c.schoolYear = ' . $schoolYear->getId())
					->andWhere('t.id != ' . $user->getId())
					->addOrderBy('t.surname', 'ASC')
					->addOrderBy('t.name', 'ASC')
					->getQuery()->getResult();

			return array_merge($teachers, $students);

		} elseif ($user->isInRole('teacher')) {

		} elseif ($user->isInRole('student')) {
			$qb = $this->createQueryBuilder();
			$students = $qb
						->select('s')
						->from(Student::getClassName(), 's')
						->join(ClassEntity::getClassName(), 'c')
						->where( // select all students from all classes in which student participate
							$qb->expr()->in(
								'c.id',
								$this->createQueryBuilder()->select('c2.id')
									->from(Student::getClassName(), 's2')
									->join(ClassEntity::getClassName(), 'c2')
									->where('c2.schoolYear = ' . $schoolYear->getId())
									->andWhere('s2.id = ' . $user->getId())
									->getDQL()
							)
						)
						->andWhere('s.id != ' . $user->getId())->getQuery()->getResult();

			$qb3 = $this->createQueryBuilder();
			$teachers = $qb3
				->select('t')
				->from(Teacher::getClassName(), 't')
				->join('t.teachings', 'tc')
				->where( // select all teachers from all classes in which student participate
					$qb3->expr()->in(
						'tc.class',
						$this->createQueryBuilder()->select('c2.id')
							->from(Student::getClassName(), 's2')
							->join(ClassEntity::getClassName(), 'c2')
							->where('c2.schoolYear = ' . $schoolYear->getId())
							->andWhere('s2.id = ' . $user->getId())
							->getDQL()
					)
				)->getQuery()->getResult();


			return (array_merge($teachers, $students));
		}

	}

}