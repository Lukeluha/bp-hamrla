<?php
namespace App\Model\Repositories;


use Kdyby\Doctrine\EntityRepository;

class ChatMessages extends EntityRepository
{
	public function findChatConversation($user1, $user2, $limit = 30, $page = 1)
	{
		return $this->createQueryBuilder('m')
			->where('m.from = ' . $user1 . " AND m.to = " . $user2)
			->orWhere('m.from = ' . $user2 . " AND m.to = " . $user1)
			->orderBy('m.datetime', 'DESC')
			->setMaxResults($limit)
			->setFirstResult(($page - 1) * $limit)
			->getQuery()->getResult();
	}
}