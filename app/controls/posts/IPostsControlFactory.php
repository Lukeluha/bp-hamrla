<?php

namespace App\Controls;


interface IPostsControlFactory {

	/**
	 * @param $userId
	 * @param $entity
	 * @return PostsControl
	 */
	function create($userId, $entity);
}