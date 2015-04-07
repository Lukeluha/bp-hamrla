<?php

namespace App\Model\Services;

use Kdyby\Doctrine\EntityManager;

abstract class BaseService
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var SchoolYear
	 */
	private $actualYear;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

}