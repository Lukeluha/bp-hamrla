<?php

namespace App\Controls;


use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;

class QuestionSummaryControl extends Control
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	public function __construct(EntityManager $entityManager)
	{
		$this->em = $entityManager;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/questionSummary.latte');

		$this->template->render();
	}

}