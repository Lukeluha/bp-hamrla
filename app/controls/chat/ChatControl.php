<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 27.03.15
 * Time: 20:38
 */

namespace App\Controls;


use Nette\Application\UI\Control;
use Kdyby\Doctrine\EntityManager;

class ChatControl extends Control
{
	/**
	 * @var EntityManager
	 */
	private $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	public function render()
	{
		$template = $this->template;
		$template->addFilter('img', callback('\App\Filter\TemplateFilters', 'image'));

		$template->setFile(__DIR__ . '/chat.latte');
		$template->render();
	}

}