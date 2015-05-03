<?php

namespace App\Model\Services;


use App\Model\Entities\ClassEntity;
use App\Model\Entities\SchoolYear;
use App\Model\Utils;

class ClassService extends BaseService
{
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