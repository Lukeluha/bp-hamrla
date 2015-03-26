<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 25.03.15
 * Time: 23:51
 */

namespace App\Control;


use Nette\Application\UI\Control;
use Kdyby\Doctrine\EntityManager;

class MenuControl extends Control
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

		$template->setFile(__DIR__ . '/menu.latte');
		$template->render();
	}
}