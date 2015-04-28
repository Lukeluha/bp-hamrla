<?php

namespace App\Forms;

use App\Forms\SubmitTaskForm;
use App\Model\Entities\Task;

interface ISubmitTaskFormFactory {
	/**
	 * @param $userId
	 * @param Task $task
	 * @return SubmitTaskForm
	 */
	function create($userId, Task $task);
}