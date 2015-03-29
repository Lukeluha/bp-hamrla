<?php

namespace App\Model\Services;

use App\Model\Entities\Student;
use Nette\InvalidArgumentException;

class StudentService extends BaseService
{
	public function findByName($query, $format = self::FORMAT_OBJECT)
	{
		$students = $this->em->createQueryBuilder()
			->select("s.name, s.surname, c.name as class")
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

}