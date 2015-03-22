<?php

namespace App\Presenters;
use App\Model\Services\UserService;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
	/**
	 * @inject
	 * @var UserService
	 */
	public $userService;

	/**
	 * @inject
	 * @var \Nette\Security\User
	 */
	public $user;

	public function actionDefault()
	{
		//$this->userManager->logout();
		//$this->userService->authenticate(array("admin", "123"));
	}

	public function renderDefault()
	{

	}
}
