<?php

namespace App\Model\Services;


use App\Model\Entities\ClassEntity;
use App\Model\Entities\SchoolYear;
use App\Model\Utils;

/**
 * Class ClassService
 * Service class for entity class
 * @package App\Model\Services
 */
class ClassService extends BaseService
{
	/**
	 * Copy class for usage in current school year
	 * @param ClassEntity $class
	 * @param SchoolYear $schoolYear
	 * @return ClassEntity
	 */
	public function copyClass(ClassEntity $class, SchoolYear $schoolYear)
	{
		$newClass = clone $class;
		$newClass->setSchoolYear($schoolYear);
		$newClass->setTeachings(null);
		$newClass->setId(null);
		$newClass->setName(Utils::getNewClassName($newClass->getName()));

		$this->em->persist($newClass);
		$this->em->flush();

		return $newClass;
	}

}