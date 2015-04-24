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

	/**
	 * @var INewActivityControlFactory
	 */
	private $newActivityControlFactory;


	public function __construct($userId, $lessonId, EntityManager $em, INewActivityControlFactory $newActivityControlFactory)
	{
		$this->em = $em;
		$this->user = $this->em->getRepository(User::getClassName())->find($userId);
		$this->lessonId = $lessonId;
		$this->newActivityControlFactory = $newActivityControlFactory;
	}

	public function createComponentNewActivity()
	{
		return $this->newActivityControlFactory->create($this->lessonId);
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