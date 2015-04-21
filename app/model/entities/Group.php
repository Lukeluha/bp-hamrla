<?php

namespace App\Model\Entities;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * Class Group
 * Only for grouping "instances" of questions, lessons and tasks
 * @package App\Model\Entities
 * @Table(name="groups")
 * @Entity()
 */
class Group extends BaseEntity
{

}