<?php

namespace App\Controls;


interface IPostsControlFactory {

	/**
	 * @param $user
	 * @param $entity
	 * @return PostsControl
	 */
	function create($user, $entity);
}