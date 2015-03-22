<?php

namespace App\Presenters;

use Kdyby\Doctrine\EntityManager;
use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}
}
