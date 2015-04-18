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

	public function __construct($userId, $entity, EntityManager $em)
	{
		parent::__construct();
		$this->userId = $userId;
		$this->em = $em;

		if ($entity instanceof Teaching) {
			$this->teaching = $entity;
		} elseif ($entity instanceof Lesson) {
			$this->lesson = $entity;
			$this->teaching = $this->lesson->getTeaching();
		} else {
			throw new InvalidArgumentException('Unknown entity');
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
		$form->getElementPrototype()->addAttributes(array('class' => 'ajax'));

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

		$this->redrawControl();
	}

//	public function createComponentPostForm()
//	{
//		$that = $this;
//
//		return new Multiplier(function($replyTo) use ($that){
//			$form = new Form();
//			$form->addHidden('replyTo');
//			$form->getElementPrototype()->addAttributes(array('class' => 'ajax'));
//
//			if (!$replyTo) {
//				$form->addTextArea("post", null, null, 1)
//					->setRequired('Vyplňte text příspěvku')
//					->setAttribute('placeholder', 'Napište něco...')
//					->setAttribute('class', 'no-resize autosize');
//
//				$form->addSubmit("save", "Přidat příspěvek")->setAttribute('class', 'button small');
//			} else {
//				$form->addText("post")->setAttribute('placeholder', 'Napište komentář...');
//				$form['replyTo']->setValue($replyTo);
//			}
//
//			$form->setRenderer(new FoundationRenderer());
//
//			$form->onSuccess[] = $that->savePost;
//
//			return $form;
//		});
//	}

	public function render()
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/posts.latte');

		if ($this->lesson) {

		} else {
			$template->posts = $this->teaching->getPosts();
		}


		$template->render();
	}
}