<?php

namespace App\Model\Repositories;


use App\Model\Entities\ClassEntity;
use App\Model\Entities\Student;
use Kdyby\Doctrine\EntityRepository;

/**
 * Class Students
 * Repository for student entity
 * @package App\Model\Repositories
 */
class Students extends EntityRepository
{
	const FORMAT_OBJECT = 'object';
	const FORMAT_ARRAY = 'array';

	public function findByName($query, $format = self::FORMAT_OBJECT)
	{
		$students = $this->createQueryBuilder()
			->select("s")
			->from(Student::getClassName(), 's')
			->leftJoin('s.classes', 'c')
			->where("CONCAT(s.name, CONCAT(' ', s.surname)) LIKE :query OR CONCAT(s.surname, CONCAT(' ', s.name)) LIKE :query")
			->setParameter(":query", "%$query%")
			->orderBy("s.surname")
			->orderBy("s.name")
			->setMaxResults(10)
			->getQuery();

		if ($format == self::FORMAT_OBJECT) {
			return $students->getResult();
		} elseif ($format == self::FORMAT_ARRAY) {
			return $students->getArrayResult();
		} else {
			throw new InvalidArgumentException('Bad format');
		}
	}

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