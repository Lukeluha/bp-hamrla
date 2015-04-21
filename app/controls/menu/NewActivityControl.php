<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 21.04.15
 * Time: 23:27
 */

namespace App\Controls;


use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;

class NewActivityControl extends Control
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var int
	 */
	protected $lessonId;

	public function __construct($lessonId, EntityManager $em)
	{
		$this->em = $em;
		$this->lessonId = $lessonId;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/newActivity.latte");
		$this->template->lessonId = $this->lessonId;
		$this->template->render();
	}

}