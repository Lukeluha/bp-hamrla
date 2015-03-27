<?php

namespace App\Presenters;
use App\Model\Entity\Teacher;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends AuthorizedBasePresenter
{
	public function actionDefault()
	{
		$teacher = $this->em->getRepository(Teacher::getClassName())->find(1);
	}

	public function renderDefault()
	{

	}
}
