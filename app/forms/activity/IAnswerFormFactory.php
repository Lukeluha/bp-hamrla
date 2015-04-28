<?php

namespace App\Forms;



use App\Model\Entities\Question;

interface IAnswerFormFactory {
	/**
	 * @param Question $question
	 * @param int $userId
	 * @return AnswerForm
	 */
	function create(Question $question, $userId);
}