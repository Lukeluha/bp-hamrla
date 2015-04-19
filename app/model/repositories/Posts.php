<?php

namespace App\Model\Repositories;


use App\Model\Entities\Post;
use Kdyby\Doctrine\EntityRepository;
use Nette\InvalidArgumentException;
use Nette\Security\User;

class Posts extends EntityRepository
{

	public function findAllForTeaching($teachingId, $fromId = null, $limit = 8)
	{
		$query = $this->createQueryBuilder()
					->select('p')
					->from(Post::getClassName(), 'p')
					->join('p.teaching', 't', 'WITH','t.id = :teachingId')
					->setParameter('teachingId', $teachingId);
		if ($fromId) {
			$query->where('p.id <= ' . $fromId);
		}

		return $query->orderBy('p.created', 'DESC')
					->setMaxResults($limit)->getQuery()->getResult();
	}

	public function findAllForLesson($lessonId, $fromId = null, $limit = 8)
	{
		$query = $this->createQueryBuilder()
			->select('p')
			->from(Post::getClassName(), 'p')
			->join('p.lesson', 'l', 'WITH', 'l.id = :lessonId')
			->setParameter('lessonId', $lessonId);

		if ($fromId) {
			$query->where('p.id <= ' . $fromId);
		}

		return $query->orderBy('p.created', 'DESC')
			->setMaxResults($limit)->getQuery()->getResult();
	}

	public function findAllForUser(User $user, $fromId = null, $limit = 8)
	{
		if ($user->isInRole('student')) {
			$query = $this->createQueryBuilder()
					->select('p')
					->from(Post::getClassName(), 'p')
					->join('p.teaching', 't')
					->join('t.class', 'c')
					->join('c.students', 's', 'WITH', 's.id = ' . $user->getId());
		} elseif ($user->isInRole('teacher')) {
			$query = $this->createQueryBuilder()
				->select('p')
				->from(Post::getClassName(), 'p')
				->join('p.teaching', 'tc')
				->join('tc.teachers', 't', 'WITH', 't.id = ' . $user->getId());
		} else {
			throw new InvalidArgumentException('Unknown role');
		}

		if ($fromId) {
			$query->where('p.id <= ' . $fromId);
		}

		return $query->orderBy('p.created', 'DESC')->setMaxResults($limit)
			->getQuery()->getResult();
	}

}