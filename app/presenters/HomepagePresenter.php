<?php

namespace App\Presenters;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends AuthorizedBasePresenter
{
	public function actionDefault()
	{
		//$this->userService->authenticate(array("admin", "123"));
	}

	public function renderDefault()
	{

	}
}
