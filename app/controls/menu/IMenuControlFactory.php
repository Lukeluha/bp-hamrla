<?php

namespace App\Controls;


interface IMenuControlFactory {
	/**
	 * @return MenuControl
	 */
	function create();
}