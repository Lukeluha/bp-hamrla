<?php

namespace App\Forms;

use App\Forms\SubmitTaskForm;

interface ISubmitTaskFormFactory {
	/**
	 * @param $userId
	 * @param $taskId
	 * @return SubmitTaskForm
	 */
	function create($userId, $taskId);
}