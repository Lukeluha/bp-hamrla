<?php

namespace App\Forms;

use App\Forms\QuestionFormFactory;

interface IQuestionFormFactory
{
	/**
	 * @param $lessonId
	 * @return QuestionFormFactory
	 */
	function create($lessonId);
}