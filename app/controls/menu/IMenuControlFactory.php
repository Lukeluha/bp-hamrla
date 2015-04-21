<?php

namespace App\Controls;



interface IMenuControlFactory {
	/**
	 * @param int $user
	 * @param int $lessonId
	 * @return MenuControl
	 */
	function create($user, $lessonId);
}