<?php

namespace App\Controls;


interface IQuestionSummaryControlFactory
{
	/**
	 * @return QuestionSummaryControl
	 */
	function create();
}