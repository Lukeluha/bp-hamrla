<?php

namespace App\Controls;

use App\Model\Entities\Lesson;
use App\Model\Entities\Post;
use App\Model\Entities\Teaching;
use App\Model\Entities\User;
use App\Model\FoundationRenderer;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\InvalidArgumentException;

/**
 * Class PostsControl
 * Showing and adding posts to dashboard of teaching, lesson or task
 * @package App\Controls
 */
class PostsControl extends Control
{
	/**
	 * @var User
	 */
	protected $user;

	protected $userId;

	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var Teaching
	 */
	protected $teaching;

	/**
	 * @var Lesson
	 */
	protected $lesson;

	/**
	 * @var array
	 */
	protected $posts;

	/**
	 * Is homepage?
	 * @var bool
	 */
	protected $homepage;

	/* Pagination */

	/**
	 * From which id should I show
	 * @var int|null
	 */
	protected $fromId = null;

	/**
	 * Limit of showed posts
	 * @var int
	 */
	protected $limit = 8;



	public function __construct(\Nette\Security\User $user, $entity, EntityManager $em)
	{
		parent::__construct();
		$this->user = $user;
		$this->userId = $user->getId();
		$this->em = $em;

		if (!is_null($entity)) {
			if ($entity instanceof Teaching) {
				$this->teaching = $entity;
			} elseif ($entity instanceof Lesson) {
				$this->lesson = $entity;
				$this->teaching = $this->lesson->getTeaching();
			} else {
				throw new InvalidArgumentException('Unknown entity');
			}
		} else {
			$this->homepage = true;
		}
	}

	public function createComponentComments()
	{
		$userId = $this->userId;
		$em = $this->em;
		return new Multiplier(function($postId) use ($userId, $em){
			$post = $em->find(Post::getClassName(), $postId);
			$comments = new CommentsControl($em, $post, $userId);
			return $comments;
		});
	}

	public function createComponentPostForm()
	{
		$form = new Form();

		$form->addTextArea("post", null, null, 1)
			->setRequired('Vyplňte text příspěvku')
			->setAttribute('placeholder', 'Napište něco...')
			->setAttribute('class', 'no-resize autosize');

		$form->addSubmit("save", "Přidat příspěvek")->setAttribute('class', 'button small');

		$form->setRenderer(new FoundationRenderer());

		$form->onSuccess[] = $this->savePost;

		return $form;
	}

	public function savePost(Form $form)
	{
		$values = $form->getValues();

		$post = new Post();

		$post->setUser($this->em->getReference(User::getClassName(), $this->userId))
			->setTeaching($this->teaching)
			->setCreated(new \DateTime())
			->setText($values['post']);

		$this->em->persist($post);
		$this->em->flush();

		$this->template->posts = $this->teaching->getPosts();
		$form->setValues(array('post' => ""));

		$this->redirect('this');
	}

	public function handleLoadNext($postId)
	{
		$this->fromId = $postId;
		$this->redrawControl();
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/posts.latte');
		$this->template->addFilter('img', callback('\App\Filter\TemplateFilters', 'image'));


		if ($this->homepage) {
			$this->template->posts = $this->em->getRepository(Post::getClassName())->findAllForUser($this->user, $this->fromId, $this->limit);
			$this->template->disableForm = true;
		} elseif ($this->lesson) {

		} else {
			$this->template->posts = $this->em
								->getRepository(Post::getClassName())
								->findAllForTeaching($this->teaching->getId(), $this->fromId, $this->limit);
		}

		$this->template->fromId = isset($this->template->posts[$this->limit-1]) ? $this->template->posts[$this->limit-1]->getId() : null;


		$this->template->render();
	}
}