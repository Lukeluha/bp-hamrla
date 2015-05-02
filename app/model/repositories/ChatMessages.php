<?php
namespace App\Model\Repositories;


use Kdyby\Doctrine\EntityRepository;

class ChatMessages extends EntityRepository
{
	public function findChatConversation($user1, $user2, $limit = 15, $fromId = null)
	{
		if ($fromId) {
			return $this->createQueryBuilder('m')
				->where('(m.from = ' . $user1 . " AND m.to = " . $user2 . ") OR (m.from = " . $user2 . " AND m.to = " . $user1 . ")")
				->andWhere("(m.id < $fromId)")
				->orderBy('m.datetime', 'DESC')
				->setMaxResults($limit)
				->getQuery()->getResult();
		} else {
			return $this->createQueryBuilder('m')
				->where('m.from = ' . $user1 . " AND m.to = " . $user2)
				->orWhere('m.from = ' . $user2 . " AND m.to = " . $user1)
				->orderBy('m.datetime', 'DESC')
				->setMaxResults($limit)
				->getQuery()->getResult();
		}
	}
}