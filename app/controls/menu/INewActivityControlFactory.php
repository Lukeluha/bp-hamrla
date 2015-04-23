<?php

namespace App\Controls;



interface INewActivityControlFactory {

	/**
	 * @param $lessonId
	 * @return NewActivityControl
	 */
	function create($lessonId);
}