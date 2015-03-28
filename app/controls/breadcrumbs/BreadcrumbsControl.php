<?php

namespace App\Controls;

use Nette\Application\UI\Control;

/**
 * Class Breadcrumbs
 * Control for breacrumbs navigation
 * @package App\Controls
 */
class BreadcrumbsControl extends Control
{
	/**
	 * @var array
	 */
	private $links;

	public function render()
	{
		$this->template->links = $this->links;
		$this->template->setFile(__DIR__ . "/breadcrumbs.latte");
		$this->template->render();
	}

	public function addLink($title, $link)
	{
		$this->links[] = array(
			"title" => $title,
			"link" => $link
		);
	}

}