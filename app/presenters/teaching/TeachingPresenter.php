<?php

namespace App\Presenters;

use App\Model\Entities\Comment;
use App\Model\Entities\Lesson;
use App\Model\Entities\Post;
use App\Model\Entities\Teaching;
use App\Model\Entities\User;
use App\Model\FoundationRenderer;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;

/**
 * Class TeachingPresenter
 * @package App\Presenters
 */
class TeachingPresenter extends AuthorizedBasePresenter
{
	/**
	 * @var Teaching
	 */
	protected $teaching;

	public function actionDefault($teachingId)
	{
		$this->teaching = $this->em->find(Teaching::getClassName(), $teachingId);
		if (!$this->teaching) throw new BadRequestException('Unknown teaching.');

		$this->checkUser();

		$this->addLinkToNav('Vyučování | '. $this->teaching->getClass()->getName() . ' | ' . $this->teaching->getSubject(), 'Teaching:default', array($teachingId));
	}

	public function renderDefault()
	{
		$this->template->nextLesson = $this->em->getRepository(Lesson::getClassName())->findNext($this->teaching);
		$this->template->teaching = $this->teaching;
		if (!isset($this->template->posts)) {
			$this->template->posts = $this->teaching->getPosts();
		}

	}

	public function handleShowComments($postId)
	{
		if (!isset($this->template->showComments)) {
			$this->template->showComments = array();
		}

		$this->template->showComments[$postId] = true;
		$this->redrawControl('comments-'.$postId);
	}

	public function createComponentPostForm()
	{

		$that = $this;

		return new Multiplier(function($replyTo) use ($that){
			$form = new Form();
			$form->addHidden('replyTo');
			$form->getElementPrototype()->addAttributes(array('class' => 'ajax'));

			if (!$replyTo) {
				$form->addTextArea("post", null, null, 1)
						->setRequired('Vyplňte text příspěvku')
						->setAttribute('placeholder', 'Napište něco...')
						->setAttribute('class', 'no-resize autosize');

			$form->addSubmit("save", "Přidat příspěvek")->setAttribute('class', 'button small');
			} else {
				$form->addText("post")->setAttribute('placeholder', 'Napište komentář...');
				$form['replyTo']->setValue($replyTo);
			}

			$form->setRenderer(new FoundationRenderer());

			$form->onSuccess[] = $that->savePost;

			return $form;
		});

	}

	public function savePost(Form $form)
	{
		$values = $form->getValues();

		if (isset($values['replyTo']) && $values['replyTo']) {
			$comment = new Comment();
			$comment->setReplyTo($this->em->getReference(Post::getClassName(), $values['replyTo']))
					->setText($values['post'])
					->setUser($this->em->getReference(User::getClassName(), $this->user->getId()))
					->setCreated(new \DateTime());

			$this->em->persist($comment);
			$this->em->flush();
		} else {
			$post = new Post();

			$post->setUser($this->em->getReference(User::getClassName(), $this->user->getId()))
				->setTeaching($this->teaching)
				->setCreated(new \DateTime())
				->setText($values['post']);

			$this->em->persist($post);
			$this->em->flush();
		}


		$this->template->posts = $this->teaching->getPosts();
		$this->redrawControl('posts');

		$form->setValues(array('post' => ""));

		$this->redrawControl('postForm');
	}

	/**
	 * Check if user has permission to view this teaching
	 */
	protected function checkUser()
	{
		if (!$this->user->isInRole('admin')) {
			if (!$this->userService->isUserInTeaching($this->user, $this->teaching)){
				$this->flashMessage("Nejste součástí tohoto vyučování.", "alert");
				$this->redirect('Homepage:default');
			}
		}
	}

	
}