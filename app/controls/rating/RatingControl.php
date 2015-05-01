<?php

namespace App\Controls;


use App\Model\Entities\Rating;
use App\Model\Entities\TaskCompleted;
use App\Model\Entities\User;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;

class RatingControl extends Control
{

	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var int
	 */
	protected $userId;

	/**
	 * @var TaskCompleted
	 */
	protected $task;

	/**
	 * @var Rating
	 */
	protected $rating;

	/**
	 * @var array
	 */
	public $onChange = array();


	public function __construct($userId, EntityManager $entityManager)
	{
		$this->em = $entityManager;
		$this->userId = $userId;
		$this->rating = new Rating();
	}

	public function setTask($taskId)
	{
		$this->task = $this->em->find(TaskCompleted::getClassName(), $taskId);
		$rating = $this->em->getRepository(Rating::getClassName())
			->findOneBy(array(
				'taskCompleted' => $taskId,
				'user' => $this->userId));

		if ($rating) $this->rating = $rating;
	}

	public function render($withImage)
	{
		$this->template->setFile(__DIR__ . "/rating.latte");
		$this->template->task = $this->task;
		$this->template->rating = $this->rating;
		$this->template->withImage = $withImage;
		$this->template->render();
	}

	public function handleRate()
	{
		$points = $this->presenter->request->getParameter('points');

		$this->rating->setUser($this->em->getReference(User::getClassName(), $this->userId));
		$this->rating->setPoints($points);
		$this->rating->setTaskCompleted($this->task);

		$this->em->persist($this->rating);
		$this->em->flush();
		$this->onChange($this);
	}

}