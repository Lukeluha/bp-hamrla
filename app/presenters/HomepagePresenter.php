<?php

namespace App\Presenters;
use App\Controls\IPostsControlFactory;

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

	}
}
