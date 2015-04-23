<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 21.04.15
 * Time: 23:27
 */

namespace App\Controls;


use App\Forms\IQuestionFormFactory;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class NewActivityControl extends Control
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var int
	 */
	protected $lessonId;

	/**
	 * @var IQuestionFormFactory
	 */
	protected $questionFormFactory;

	public function __construct($lessonId, EntityManager $em, IQuestionFormFactory $questionFormFactory)
	{
		$this->em = $em;
		$this->lessonId = $lessonId;
		$this->questionFormFactory = $questionFormFactory;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/newActivity.latte");
		$this->template->lessonId = $this->lessonId;
		$this->template->render();
	}

	public function createComponentQuestionForm()
	{
		return $this->questionFormFactory->create($this->lessonId);
	}

	public function createComponentForm()
	{
		$form = new Form();
		$form->addRadioList('type', null, array('question' => "Otázka", 'task' => 'Úkol', 'exam' => 'Písemka'))->setValue('question');
		$form['type']->addCondition(Form::EQUAL, 'question')->toggle('question');
		$form['type']->addCondition(Form::EQUAL, 'task')->toggle('task');
		$form['type']->addCondition(Form::EQUAL, 'exam')->toggle('exam');
		return $form;
	}

}