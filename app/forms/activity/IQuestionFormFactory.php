<?php

namespace App\Forms;

use App\Forms\QuestionFormFactory;
use Nette\ComponentModel\IContainer;

interface IQuestionFormFactory
{
	/**
	 * @param $lessonId
	 * @param IContainer|null $parent
	 * @param string $name
	 * @return QuestionForm
	 */
	function create($lessonId, IContainer $parent = null, $name = "");
}