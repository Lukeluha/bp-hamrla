<?php

namespace App\Controls;



use App\Model\Entities\SchoolYear;
use Nette\Security\User;
use App\Model\Entities\Teaching;

interface IChatControlFactory {
	/**
	 * @param User $user
	 * @param SchoolYear $year
	 * @param Teaching $teaching
	 * @return ChatControl
	 */
	function create($user, $year, $teaching);

}