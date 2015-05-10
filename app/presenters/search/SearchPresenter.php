<?php

namespace App\Presenters;


use App\Model\Entities\Question;
use App\Model\Entities\Task;

/**
 * Class SearchPresenter
 * Page with main searching through all data
 * @package App\Presenters
 */
class SearchPresenter extends AuthorizedBasePresenter
{
	/**
	 * Default page
	 */
	public function actionDefault()
	{
		$this->addLinkToNav('Vyhledávání', 'this');
	}


	/**
	 * Search for given query
	 * @param $query
	 */
	public function handleSearch($query)
	{
		if (strlen(trim($query))) {
			$results = array();

			if ($this->user->isInRole('teacher')) {
				$results['questions'] = $this->em->createQueryBuilder()
					->select('q')
					->from(Question::getClassName(), 'q')
					->join('q.lesson', 'l')
					->join('l.teaching', 't')
					->join('t.teachers', 'tc', 'WITH', 'tc.id = ' . $this->user->getId())
					->where('q.questionText LIKE :query')
					->setParameter('query', "%".trim($query)."%")
					->getQuery()->getResult();

				$results['tasks'] = $this->em->createQueryBuilder()
					->select('t')
					->from(Task::getClassName(), 't')
					->join('t.lesson', 'l')
					->join('l.teaching', 'tc')
					->join('tc.teachers', 'teacher', 'WITH', 'teacher.id = ' . $this->user->getId())
					->where('t.taskName LIKE :query')
					->setParameter('query', "%".trim($query)."%")
					->getQuery()->getResult();

			} else { // student
				$results['questions'] = $this->em->createQueryBuilder()
					->select('q')
					->from(Question::getClassName(), 'q')
					->join('q.lesson', 'l')
					->join('l.teaching', 't')
					->join('t.class', 'c')
					->join('c.students', 's', 'WITH', 's.id = ' . $this->user->getId())
					->where('q.questionText LIKE :query')
					->setParameter('query', "%".trim($query)."%")
					->getQuery()->getResult();

				$results['tasks'] = $this->em->createQueryBuilder()
					->select('t')
					->from(Task::getClassName(), 't')
					->join('t.lesson', 'l')
					->join('l.teaching', 'tc')
					->join('tc.class', 'c')
					->join('c.students', 's', 'WITH', 's.id = ' . $this->user->getId())
					->where('t.taskName LIKE :query')
					->setParameter('query', "%".trim($query)."%")
					->getQuery()->getResult();
			}

			$this->template->results = $results;
		} else {
			$this->template->results = null;
		}

		$this->redrawControl();
	}


	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->title = "Vyhledávání";
	}
}