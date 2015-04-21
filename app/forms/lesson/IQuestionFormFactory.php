<?php

namespace App\Forms;


interface IQuestionFormFactory
{
	/**
	 * @param $lessonId
	 * @return mixed
	 */
	function create($lessonId);
}