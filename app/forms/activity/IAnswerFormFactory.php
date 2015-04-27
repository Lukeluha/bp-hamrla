<?php

namespace App\Forms;



interface IAnswerFormFactory {
	/**
	 * @param int $questionId
	 * @param int $userId
	 * @return AnswerForm
	 */
	function create($questionId, $userId);
}