<?php

namespace App\Controls;



interface IMenuControlFactory {
	/**
	 * @param int $user
	 * @return MenuControl
	 */
	function create($user);
}