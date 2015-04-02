<?php

namespace App\Forms;


interface IStudentFormFactory {
	/**
	 * @return StudentForm
	 */
	function create($studentId);

}