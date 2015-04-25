<?php

namespace App\Controls;



interface IMenuControlFactory {
	/**
	 * @param int $userId
	 * @param int $lessonId
	 * @return MenuControl
	 */
	function create($userId, $lessonId);
}