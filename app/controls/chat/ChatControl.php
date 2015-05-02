<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 27.03.15
 * Time: 20:38
 */

namespace App\Controls;


use App\Filter\TemplateFilters;
use App\Model\Entities\ChatMessage;
use App\Model\Entities\ClassEntity;
use App\Model\Entities\SchoolYear;
use App\Model\Entities\Student;
use App\Model\Entities\Teaching;
use App\Model\Repositories\Classes;
use Nette\Application\UI\Control;
use Kdyby\Doctrine\EntityManager;
use Nette\Security\User;

class ChatControl extends Control
{
	/**
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var SchoolYear
	 */
	private $actualYear;

	/**
	 * @var Teaching
	 */
	private $teaching;

	public function __construct(User $user, SchoolYear $actualYear, Teaching $teaching = null, EntityManager $em)
	{
		$this->em = $em;
		$this->user = $user;
		$this->actualYear = $actualYear;
		$this->teaching = $teaching;
	}

	public function handleToggleChat()
	{
		$status = $this->presenter->request->getParameter('status');
		if ($status === 'true') {
			$this->teaching->setChat(Teaching::CHAT_ALLOWED);
		} else {
			$this->teaching->setChat(Teaching::CHAT_DISALLOWED);
		}

		$this->em->persist($this->teaching);
		$this->em->flush();
	}

	public function render()
	{
		$this->template->addFilter('img', callback('\App\Filter\TemplateFilters', 'image'));

		$this->template->setFile(__DIR__ . '/chat.latte');

		$this->template->users = json_encode($this->getUsersForChat());
		$this->template->teaching = $this->teaching;
		$this->template->chatAllowed = !$this->isChatDisallowedForUser();
		$this->template->render();
	}

	protected function getUsersForChat()
	{
		$usersArray = array();
		$users = $this->em->getRepository(\App\Model\Entities\User::getClassName())->findForChat($this->user, $this->actualYear);
		$i = 0;
		foreach ($users as $user) {
			$roles = $user->getRoles();
			$usersArray[$user->getId()] = array(
				"id" => $user->getId(),
				"name" => $user->getName(),
				"surname" => $user->getSurname(),
				"nameSurname" => $user->getName() . " " . $user->getSurname(),
				"online" => $user->getOnline(),
				"profilePicture" => TemplateFilters::image($user->getProfilePicture(), 30),
				"ordering" => $i,
				"role" => in_array(\App\Model\Entities\User::ROLE_STUDENT, $roles) ? 'student' : 'teacher'
			);
			$i++;
		}
		return $usersArray;
	}

	protected function isChatDisallowedForUser()
	{
		if ($this->user->isInRole('teacher')) return false;

		return (bool) $this->em->createQueryBuilder()
					->select('s')
					->from(Student::getClassName(), 's')
					->join('s.classes', 'c')
					->join('c.teachings', 't')
					->where('s.id = ' . $this->user->id . " AND t.chat = 'disallowed'")->getQuery()->getOneOrNullResult();
	}

	public function handleCheckNewMessages()
	{
		$messages = $this->em->getRepository(ChatMessage::getClassName())
						->findBy(array('to' => $this->user->id, 'read' => 0), array('from' => "ASC", 'datetime' => "ASC"));

		$messagesArray = array();
		foreach ($messages as $message) {
			$messagesArray[] = array(
				'idMessage' => $message->getId(),
				'from' => $message->getFrom()->getId(),
				'message' => $message->getMessage(),
				'date' => $message->getDatetime()->format('Y-m-d')
			);
			$message->setRead(true);
		}

		$this->em->flush();

		$this->presenter->payload->newMessages = $messagesArray;
		$this->presenter->sendPayload();
	}

	public function handleGetConversationWithUser()
	{
		$userId = $this->presenter->getParameter('userId');
		$fromId = $this->presenter->getParameter('fromId');

		$messages = $this->em->getRepository(ChatMessage::getClassName())->findChatConversation($this->user->getId(), $userId, 15, $fromId);

		$messagesArray = array();
		foreach ($messages as $message) {
			array_unshift($messagesArray, array(
				'idMessage' => $message->getId(),
				'from' => $message->getFrom()->getId(),
				'message' => $message->getMessage(),
				'date' => $message->getDatetime()->format('Y-m-d')
			));
		}

		$this->presenter->payload->conversation = $messagesArray;
		$this->presenter->sendPayload();
	}

	public function handleGetConversationsWithUsers()
	{
		$users = $this->presenter->getRequest()->getPost();

		$conversations = array();
		foreach ($users['users'] as $user) {
			$messagesArray = array();
			$messages = $this->em->getRepository(ChatMessage::getClassName())->findChatConversation($this->user->getId(), $user);

			foreach ($messages as $message) {
				array_unshift($messagesArray, array(
					'idMessage' => $message->getId(),
					'from' => $message->getFrom()->getId(),
					'message' => $message->getMessage(),
					'date' => $message->getDatetime()->format('Y-m-d')
				));
			}

			$conversations[$user] = $messagesArray;
		}


		$this->presenter->payload->conversations = $conversations;
		$this->presenter->sendPayload();
	}

	public function handleCheckUsersInChat()
	{
		$users = $this->getUsersForChat();

		$this->presenter->payload->users = $users;
		$this->presenter->payload->chatAllowed = !$this->isChatDisallowedForUser();
		$this->presenter->sendPayload();
	}

	public function handleSendMessage()
	{
		$post = $this->getPresenter()->getRequest()->getPost();
		$message = new ChatMessage();
		$message->setFrom($this->em->getReference(\App\Model\Entities\User::getClassName(), $this->user->getId()))
				->setTo($this->em->getReference(\App\Model\Entities\User::getClassName(), (int) $post['to']))
				->setMessage($post['message'])
				->setDatetime(new \DateTime());

		$this->em->persist($message);
		$this->em->flush();


		$this->presenter->terminate();
	}

}