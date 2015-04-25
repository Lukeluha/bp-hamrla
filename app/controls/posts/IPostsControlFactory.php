<?php

namespace App\Controls;


use Nette\Security\User;

interface IPostsControlFactory {

	/**
	 * @param User $user
	 * @param $entity
	 * @return PostsControl
	 */
	function create(User $user, $entity);
}