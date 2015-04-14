<?php

namespace App\Controls;



interface IChatControlFactory {
	/**
	 * @param \App\Model\Entities\User $user
	 * @param \App\Model\Entities\SchoolYear $year
	 * @return ChatControl
	 */
	function create($user, $year);

}