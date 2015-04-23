<?php

namespace App\Controls;



use App\Model\Entities\SchoolYear;
use Nette\Security\User;

interface IChatControlFactory {
	/**
	 * @param User $user
	 * @param SchoolYear $year
	 * @return ChatControl
	 */
	function create($user, $year);

}