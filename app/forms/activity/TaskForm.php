<?php

namespace App\Forms;

use App\Model\FoundationRenderer;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class TaskForm extends Control
{
	/**
	 * @var int
	 */
	protected $lessonId;

	/**
	 * @var EntityManager
	 */
	protected $em;

	public function __construct($lessonId, EntityManager $em)
	{
		$this->em = $em;
		$this->lessonId = $lessonId;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/taskForm.latte");
		$this->template->lessonId = $this->lessonId;
		$this->template->render();
	}

	public function createComponentForm()
	{
		$form = new Form();
		$form->addText('taskName', "Název úkolu")->setRequired('Zadejte název úkolu');
		$form->addTextArea('taskText', 'Zadání úkolu')->setRequired('Zadejte zadání úkolu');
		$form->addText("start", "Otevření úkolu")->setAttribute('class', 'fdatepicker');
		$form->addText("end", "Uzavření úkolu úkolu")->setAttribute('class', 'fdatepicker');


		$form->addSubmit('save', 'Uložit úkol');

		$form->setRenderer(new FoundationRenderer());
		$form->onSuccess[] = $this->saveTask;

		return $form;
	}


	public function saveTask(Form $form)
	{
		$values = $form->getValues();
	}

}