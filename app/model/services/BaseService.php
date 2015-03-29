<?php

namespace App\Model\Services;

use Kdyby\Doctrine\EntityManager;

abstract class BaseService
{
	const FORMAT_OBJECT = 'object';

	const FORMAT_ARRAY = 'array';

	/**
	 * @var EntityManager
	 */
	protected $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

}