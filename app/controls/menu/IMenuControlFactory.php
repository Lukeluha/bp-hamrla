<?php

namespace App\Controls;


use App\Model\Entities\User;

interface IMenuControlFactory {
	/**
	 * @param int $user
	 * @return MenuControl
	 */
	function create($user);
}