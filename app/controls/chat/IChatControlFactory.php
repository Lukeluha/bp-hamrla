<?php

namespace App\Controls;


interface IChatControlFactory {
	/**
	 * @return ChatControl
	 */
	function create();

}