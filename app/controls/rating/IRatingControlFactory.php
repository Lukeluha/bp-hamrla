<?php

namespace App\Controls;


interface IRatingControlFactory
{
	/**
	 * @param int $userId
	 * @return RatingControl
	 */
	function create($userId);
}