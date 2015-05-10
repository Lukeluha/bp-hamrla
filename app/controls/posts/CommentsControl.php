<?php

namespace App\Controls;


use App\Model\Entities\Comment;
use App\Model\Entities\Post;
use App\Model\Entities\User;
use App\Model\FoundationRenderer;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * Class CommentsControl
 * Showing and adding comments for given post
 * @package App\Controls
 */
class CommentsControl extends Control
{
	/**
	 * @var Post
	 */
	protected $post;

	/**
	 * Logged user id
	 * @var int
	 */
	protected $userId;

	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * Should I show all comments in this post?
	 * @var bool
	 */
	protected $showComments = false;

	public function __construct(EntityManager $em, Post $post, $userId)
	{
		$this->post = $post;
		$this->userId = $userId;
		$this->em = $em;
	}

	public function createComponentCommentForm()
	{
		$form = new Form();
		$form->getElementPrototype()->addAttributes(array('class' => 'ajax'));

		$form->addText("comment")->setRequired('Vyplňte text komentáře')
					->setAttribute('placeholder', 'Napište komentář...');

		$form->setRenderer(new FoundationRenderer());

		$form->onSuccess[] = $this->saveComment;

		return $form;
	}

	public function saveComment(Form $form)
	{
		$values = $form->getValues();

		$comment = new Comment();
		$comment->setReplyTo($this->post)
			->setText(strip_tags($values['comment']))
			->setUser($this->em->getReference(User::getClassName(), $this->userId))
			->setCreated(new \DateTime());

		$this->em->persist($comment);
		$this->em->flush();

		$form->setValues(array('comment' => ''));
		$this->showComments = true;
		$this->redrawControl();
	}

	public function handleShow()
	{
		$this->showComments = !$this->showComments;
		$this->redrawControl();
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/comments.latte');
		$this->template->addFilter('img', callback('\App\Filter\TemplateFilters', 'image'));
		$this->template->addFilter('findUrl', callback('\App\Filter\TemplateFilters', 'findUrl'));


		$this->template->post = $this->post;
		$this->template->show = $this->showComments;

		$this->template->render();
	}

}