<?php

namespace App\Controls;


use App\Model\Entities\Student;

interface IStudentDetailControlFactory
{
	/**
	 * @param Student $student
	 * @return StudentDetailControl
	 */
	function create(Student $student);
}