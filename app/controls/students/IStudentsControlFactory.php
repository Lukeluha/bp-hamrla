<?php

namespace App\Controls;


use App\Model\Entities\ClassEntity;

interface IStudentsControlFactory
{
	/**
	 * @param ClassEntity $class
	 * @return StudentsControl
	 */
	function create(ClassEntity $class);

}