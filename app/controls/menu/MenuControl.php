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

	/**
	 * @var null
	 */
	private $lessonId;

	public function __construct($userId, $lessonId, EntityManager $em)
	{
		$this->em = $em;
		$this->user = $this->em->getRepository(User::getClassName())->find($userId);
		$this->lessonId = $lessonId;
	}

	public function createComponentNewActivity()
	{
		return new NewActivityControl($this->lessonId, $this->em);
	}

	public function render()
	{
		$template = $this->template;
		$template->addFilter('img', callback('\App\Filter\TemplateFilters', 'image'));

		$template->userEntity = $this->user;
		$template->lessonId = $this->lessonId;
		$template->setFile(__DIR__ . '/menu.latte');
		$template->render();
	}
}