<?php

namespace App\Controls;


use App\Model\Entities\Teaching;

interface IStudentsControlFactory
{
	/**
	 * @param Teaching $teaching
	 * @return StudentsControl
	 */
	function create(Teaching $teaching);

}