<?php

namespace App\Presenters;
use App\Controls\IPostsControlFactory;
use App\Model\Entities\Task;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends AuthorizedBasePresenter
{
	/**
	 * @var IPostsControlFactory
	 * @inject
	 */
	public $postsFactory;

	public function createComponentPosts()
	{
		return $this->postsFactory->create($this->getUser(), null);
	}
	
	public function renderDefault()
	{
		if ($this->user->isInRole('student')) {
			$this->template->tasksToFinish = $this->em->getRepository(Task::getClassName())->findByOpened($this->user->id);
		}

	}

	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->title = "Hlavní stránka";
	}
}
