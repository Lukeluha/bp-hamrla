<?php

namespace App\Forms;


use App\Model\Entities\SchoolYear;

interface IStudentFormFactory {

	/**
	 * @param $studentId
	 * @param SchoolYear $schoolYear
	 * @return StudentForm
	 */
	function create($studentId, SchoolYear $schoolYear);

}