<?php

namespace App\Model\Repositories;


use App\Model\Entities\ClassEntity;
use App\Model\Entities\SchoolYear;
use App\Model\Entities\Student;
use App\Model\Entities\Teacher;
use App\Model\Entities\Teaching;
use Kdyby\Doctrine\EntityRepository;
use Nette\Security\User;

/**
 * Class Users
 * Repository class for entity user
 * @package App\Model\Repositories
 */
class Users extends EntityRepository
{
	/**
	 * Find all users for chat
	 * @param User $user
	 * @param SchoolYear $schoolYear
	 * @return array
	 */
	public function findForChat(User $user, SchoolYear $schoolYear)
	{
		if ($user->isInRole('teacher')) {

			// all students from my teachings
			$students = $this->createQueryBuilder()->select('s')
				->from(Student::getClassName(), 's')
				->join('s.classes', 'c', 'WITH', 'c.schoolYear = ' . $schoolYear->getId())
				->join('c.teachings', 'tc')
				->join('tc.teachers', 't', 'WITH', 't.id = ' . $user->getId())
				->addOrderBy('s.surname', 'ASC')
				->addOrderBy('s.name', 'ASC')
				->getQuery()->getResult();

			// all teachers from my teachings
			$qb = $this->createQueryBuilder();
			$teachers = $qb
				->select('t')
				->from(Teacher::getClassName(), 't')
				->join('t.teachings', 'tc')
				->join('tc.class', 'c')
				->where( // select all students from all classes in which student participate
					$qb->expr()->in(
						'c.id',
						$this->createQueryBuilder()->select('c2.id')
							->from(ClassEntity::getClassName(), 'c2')
							->join('c2.teachings', 'tc2')
							->join('tc2.teachers', 't2', 'WITH', 't2.id = ' . $user->getId())
							->where("c2.schoolYear = " . $schoolYear->getId())
							->getDQL()
					)
				)
				->andWhere('t.id != ' . $user->getId())
				->addOrderBy('t.surname', 'ASC')
				->addOrderBy('t.name', 'ASC')
				->getQuery()->getResult();

			return array_merge($teachers, $students);

		} elseif ($user->isInRole('student')) {
			$qb = $this->createQueryBuilder();


			$students = $qb
						->select('s')
						->from(Student::getClassName(), 's')
						->join('s.classes', 'c')
						->where( // select all students from all classes in which student participate
							$qb->expr()->in(
								'c.id',
								$this->createQueryBuilder()->select('c2.id')
									->from(ClassEntity::getClassName(), 'c2')
									->join('c2.students', 's2', 'WITH', 's2.id = ' . $user->getId())
									->where("c2.schoolYear = " . $schoolYear->getId())
									->getDQL()
							)
						)
						->andWhere('s.id != ' . $user->getId())
					->addOrderBy('s.surname', 'ASC')
					->addOrderBy('s.name', 'ASC')
					->getQuery()->getResult();

			$teachers = $this->createQueryBuilder()
				->select('t')
				->from(Teacher::getClassName(), 't')
				->join('t.teachings', 'tc')
				->join('tc.class', 'c')
				->join('c.students', 's', 'WITH', 's.id ='. $user->getId())
				->addOrderBy('t.surname', 'ASC')
				->addOrderBy('t.name', 'ASC')
				->getQuery()->getResult();


			return (array_merge($teachers, $students));
		}

	}
}