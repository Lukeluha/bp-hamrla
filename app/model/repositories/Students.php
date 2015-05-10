<?php

namespace App\Model\Repositories;


use App\Model\Entities\ClassEntity;
use App\Model\Entities\SchoolYear;
use App\Model\Entities\Student;
use Kdyby\Doctrine\EntityRepository;

/**
 * Class Students
 * Repository for student entity
 * @package App\Model\Repositories
 */
class Students extends EntityRepository
{

	/**
	 * Get students by given query in given school year
	 * @param $query
	 * @param $schoolYear
	 * @param $prevYear
	 * @return array
	 */
	public function findByName($query, SchoolYear $schoolYear = null, SchoolYear $prevYear = null)
	{
		if (!$schoolYear) return null;

		$qb = $this->createQueryBuilder()
					->select("s")
					->from(Student::getClassName(), 's')
					->leftJoin('s.classes', 'c')
					->where("((CONCAT(s.name, CONCAT(' ', s.surname)) LIKE :query
								OR CONCAT(s.surname, CONCAT(' ', s.name)) LIKE :query)
								AND c.type = '". ClassEntity::TYPE_CLASS . "')");

		if ($prevYear) {
			$qb->andWhere("(c.schoolYear = " . $prevYear->getId() . "OR c.schoolYear = " . $schoolYear->getId() . ")");
		} else {
			$qb->andWhere("(c.schoolYear = " . $schoolYear->getId() . ")");
		}

		return $qb->setParameter(":query", "%$query%")
		->addOrderBy("s.surname")
		->addOrderBy("s.name")
		->setMaxResults(5)
		->getQuery()->getResult();
	}

	/**
	 * Returns user by given name, surname at given class
	 * @param $name
	 * @param $surname
	 * @param $className
	 * @param SchoolYear $schoolYear
	 * @return mixed|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function findByNameInClass($name, $surname, $className, SchoolYear $schoolYear = null)
	{
		if (!$schoolYear) return null;

		return $this->createQueryBuilder()
					->select('s')
					->from(Student::getClassName(), 's')
					->join('s.classes', 'c')
					->where('s.name = :name AND s.surname = :surname AND c.schoolYear = ' . $schoolYear->getId() .' AND c.name = :className')
					->setParameters(array('name' => $name, 'surname' => $surname, 'className' => $className))
					->getQuery()->getOneOrNullResult();
	}


	/**
	 * Find student by given class
	 * @param Student $student
	 * @param ClassEntity $class
	 * @return Student|mixed
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function findByStudentNameInClass(Student $student, ClassEntity $class)
	{
		$student = $this->createQueryBuilder()
				->select('s.id')
				->from(Student::getClassName(), 's')
				->join('s.classes', 'c')
				->where("s.name = '" . $student->getName() . "' AND s.surname = '" . $student->getSurname() . "' AND c.id = " . $class->getId())
				->getQuery()->getOneOrNullResult();

		return $student;
	}
}