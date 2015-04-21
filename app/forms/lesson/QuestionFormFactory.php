<?php

namespace App\Forms;

use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * Class QuestionFormFactory
 * @package App\Forms
 */
class QuestionFormFactory extends Control
{
	/**
	 * @var int
	 */
	protected $lessonId;

	/**
	 * @var EntityManager
	 */
	protected $em;

	public function __construct($lessonId, EntityManager $em)
	{
		$this->lessonId = $lessonId;
		$this->em = $em;
	}

	public function createComponentForm()
	{
		$form = new Form();



		return $form;
	}



}