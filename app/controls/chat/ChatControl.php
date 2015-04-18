<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 27.03.15
 * Time: 20:38
 */

namespace App\Controls;


use App\Model\Entities\ChatMessage;
use App\Model\Entities\SchoolYear;
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

	private $actualYear;

	public function __construct(User $user, SchoolYear $actualYear, EntityManager $em)
	{
		$this->em = $em;
		$this->user = $user;
		$this->actualYear = $actualYear;
	}

	public function render()
	{
		$template = $this->template;
		$template->addFilter('img', callback('\App\Filter\TemplateFilters', 'image'));

		$template->setFile(__DIR__ . '/chat.latte');

		$template->users = json_encode($this->getUsersForChat());
		$template->render();
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
				"profilePicture" => $user->getProfilePicture(),
				"ordering" => $i,
				"role" => in_array(\App\Model\Entities\User::ROLE_STUDENT, $roles) ? 'student' : 'teacher'
			);
			$i++;
		}


		return $usersArray;
	}

	public function handleCheckNewMessages()
	{
		$messages = $this->em->getRepository(ChatMessage::getClassName())
						->findBy(array('to' => $this->user->id, 'read' => 0), array('from' => "ASC", 'datetime' => "ASC"));

		$messagesArray = array();
		foreach ($messages as $message) {
			$messagesArray[] = array(
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
		$page = $this->presenter->getParameter('page');

		$messages = $this->em->getRepository(ChatMessage::getClassName())->findChatConversation($this->user->getId(), $userId);

		$messagesArray = array();
		foreach ($messages as $message) {
			array_unshift($messagesArray, array(
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