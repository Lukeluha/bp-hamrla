<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 25.03.15
 * Time: 23:51
 */

namespace App\Controls;


use App\Model\Entities\User;
use Nette\Application\UI\Control;
use Kdyby\Doctrine\EntityManager;

class MenuControl extends Control
{
	/**
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @var User
	 */
	private $user;


	public function __construct($userId, EntityManager $em)
	{
		$this->em = $em;
		$this->user = $this->em->getRepository(User::getClassName())->find($userId);
	}

	public function render()
	{
		$template = $this->template;
		$template->addFilter('img', callback('\App\Filter\TemplateFilters', 'image'));

		$template->userEntity = $this->user;
		$template->setFile(__DIR__ . '/menu.latte');
		$template->render();
	}
}