<?php

namespace App\Forms;


use App\Model\Entities\Answer;
use App\Model\Entities\Question;
use App\Model\Entities\QuestionOption;
use App\Model\FoundationRenderer;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use App\Model\Entities\User;

class AnswerForm extends Control
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var Question
	 */
	protected $question;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @param int $questionId
	 * @param EntityManager $em
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function __construct($questionId, $userId, EntityManager $em)
	{
		$this->em = $em;
		$this->question = $this->em->find(Question::getClassName(), $questionId);
		$this->user = $this->em->find(User::getClassName(), $userId);
	}

	public function createComponentForm()
	{
		$form = new Form();

		if ($this->question->getQuestionType() == Question::TYPE_CHOICE) {
			$optionsArray = $this->getOptionsArray();
			$form->addRadioList('answer', null, $optionsArray)
				->setRequired('Vyberte odpověď');

			if ($this->question->isReasonRequire()) {
				$form->addText('reason', "Důvod odpovědi")->setRequired('Vyplňte důvod odpovědi');
			}
		} elseif ($this->question->getQuestionType() == Question::TYPE_MULTIPLECHOICE) {
			$optionsArray = $this->getOptionsArray();
			$form->addCheckboxList('answer', null, $optionsArray)
				->setRequired("Vyberte odpověď");

			if ($this->question->isReasonRequire()) {
				$form->addText('reason', "Důvod odpovědi")->setRequired('Vyplňte důvod odpovědi');
			}
		} else { // text question
			$form->addText('answer', "Text odpovědi")->setRequired("Vyplňte odpověď");
		}


		$form->addSubmit('save', 'Uložit odpověď');
		$form->setRenderer(new FoundationRenderer());
		$form->onSuccess[] = $this->saveAnswer;

		return $form;
	}


	/**
	 * Get options array for using in radiolist or checkboxlist
	 * @return array
	 */
	private function getOptionsArray()
	{
		$optionsArray = array();

		foreach ($this->question->getOptions() as $option) {
			$optionsArray[$option->getId()] = $option->getOptionText();
		}

		return $optionsArray;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/answerForm.latte");


		$answer = $this->checkForAnswer();

		if ($answer) {
			if ($this->question->getQuestionType() == Question::TYPE_CHOICE) {
				$options = $this->getOptionsArray();
				$choiceOption = $answer->getOptions()->first()->getId();

				unset($options[$choiceOption]);
				$this['form']['answer']->setValue($choiceOption)->setDisabled(array_keys($options));
				if (isset($this['form']['reason'])) {
					$this['form']['reason']->setDisabled()->setValue($answer->getAnswerText());
				}

				$this->template->answers = array($choiceOption => true);
				$this->template->rightAnswers = $this->getRightAnswers();
			} elseif ($this->question->getQuestionType() == Question::TYPE_MULTIPLECHOICE) {
				$options = $this->getOptionsArray();
				$choiceOptions = array();
				foreach ($answer->getOptions() as $chosen) {
					unset($options[$chosen->getId()]);
					$choiceOptions[] = $chosen->getId();
				}

				$this['form']['answer']->setValue($choiceOptions)->getControlPrototype()->onClick("return false");
				if (isset($this['form']['reason'])) {
					$this['form']['reason']->setDisabled()->setValue($answer->getAnswerText());
				}
				$this->template->answers = array_flip($choiceOptions);
				$this->template->rightAnswers = $this->getRightAnswers();
			} else { // text question

			}
		}



		$this->template->render();
	}

	public function saveAnswer(Form $form)
	{
		$values = $form->getValues();

		if ($this->question->getQuestionType() == Question::TYPE_CHOICE) {
			$answer = new Answer();

			$answer->setStudent($this->user);
			$answer->addOption($this->em->getReference(QuestionOption::getClassName(), $values['answer']));
			$answer->setQuestion($this->question);
			if (isset($values['reason'])) {
				$answer->setAnswerText($values['reason']);
			}

			$this->em->persist($answer);
			$this->em->flush();
		} elseif ($this->question->getQuestionType() == Question::TYPE_MULTIPLECHOICE) {
			$answer = new Answer();
			$answer->setStudent($this->user);
			$answer->setQuestion($this->question);
			if (isset($values['reason'])) {
				$answer->setAnswerText($values['reason']);
			}

			foreach ($values['answer'] as $option) {
				$answer->addOption($this->em->getReference(QuestionOption::getClassName(), $option));
			}

			$this->em->persist($answer);
			$this->em->flush();
		}

		$this->redrawControl();
	}

	private function checkForAnswer()
	{
		$answers = $this->em->getRepository(Answer::getClassName())
						->findOneBy(array(
							'student' => $this->user->getId(),
							'question' => $this->question->getId())
						);

		return $answers;
	}

	private function getRightAnswers()
	{
		if ($this->question->getQuestionType() == Question::TYPE_CHOICE ||
			$this->question->getQuestionType() == Question::TYPE_MULTIPLECHOICE) {
			$rightAnswers = $this->em->getRepository(QuestionOption::getClassName())
				->findAssoc(array(
					'correct' => true,
					'question' => $this->question)
					,'id'
				);
		}

		return $rightAnswers;
	}
}