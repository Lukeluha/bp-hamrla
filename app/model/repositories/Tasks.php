<?php

namespace App\Model\Repositories;


use App\Model\Entities\Task;
use App\Model\Entities\TaskCompleted;
use Kdyby\Doctrine\EntityRepository;

/**
 * Class Tasks
 * Repository class for entity Task
 * @package App\Model\Repositories
 */
class Tasks extends EntityRepository
{

	/**
	 * Returns tasks by given text
	 * @param $query
	 * @return array
	 */
	public function findByText($query)
	{
		return $this->createQueryBuilder()
			->select("t")
			->from(Task::getClassName(), 't')
			->where("t.taskText LIKE :query OR t.taskName LIKE :query")
			->setParameter(":query", "%$query%")
			->addOrderBy("t.taskText")
			->groupBy("t.group")
			->setMaxResults(10)
			->getQuery()->getResult();
	}

	/**
	 * Returns all opened tasks for given user
	 * @param $userId
	 * @return array
	 */
	public function findByOpened($userId)
	{
		return $this->createQueryBuilder()
				->select('t')
				->from(Task::getClassName(), 't')
				->join('t.lesson', 'l')
				->join('l.teaching', 'teaching')
				->join('teaching.class', 'c')
				->join('c.students', 's', 'WITH', 's.id = ' . $userId)
				->leftJoin('t.completedTasks', 'tc', 'WITH', 'tc.student = ' . $userId)
				->where('tc.id IS NULL AND t.end IS NOT NULL AND t.visible = 1 AND t.end > :now')
				->setParameter('now', date("Y-m-d H:i"))
				->orderBy('t.end', 'ASC')
				->getQuery()->getResult();
	}

}