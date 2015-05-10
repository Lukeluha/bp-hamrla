<?php

namespace App\Model\Repositories;


use App\Model\Entities\Post;
use Kdyby\Doctrine\EntityRepository;
use Nette\InvalidArgumentException;
use Nette\Security\User;

/**
 * Class Posts
 * Repository class for entity Post
 * @package App\Model\Repositories
 */
class Posts extends EntityRepository
{

	/**
	 * Returns all posts for given teaching
	 * @param $teachingId
	 * @param null $fromId
	 * @param int $limit
	 * @return array
	 */
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

	/**
	 * Returns all posts for given lesson
	 * @param $lessonId
	 * @param null $fromId
	 * @param int $limit
	 * @return array
	 */
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

	/**
	 * Returns all posts for usage in homepage
	 * @param User $user
	 * @param null $fromId
	 * @param int $limit
	 * @return array
	 */
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