<?php

namespace App\Presenters;

use App\Model\Role;
/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function actionDefault()
	{
		$role = new Role();
		$role->setName("student");

		$this->em->persist($role);
		$this->em->flush();
	}

	public function renderDefault()
	{

	}
}
