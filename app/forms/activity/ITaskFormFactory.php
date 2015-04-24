<?php

namespace App\Forms;


interface ITaskFormFactory {
	/**
	 * @param int $lessonId
	 * @return TaskForm
	 */
	function create($lessonId);
}