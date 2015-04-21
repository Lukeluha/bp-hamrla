<?php

namespace App\Model\Entities;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;


/**
 * Class Topic
 * @package App\Model\Entities
 * @ORM\Entity()
 * @ORM\Table(name="topics")
 */
class Topic extends BaseEntity
{
	/**
	 * @var string
	 * @ORM\Column()
	 */
	protected $name;

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}



}