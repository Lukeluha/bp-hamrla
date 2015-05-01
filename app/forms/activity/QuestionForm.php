<?php

namespace App\Forms;

use App\Model\Entities\CorrectAnswer;
use App\Model\Entities\Group;
use App\Model\Entities\Question;
use App\Model\Entities\QuestionOption;
use App\Model\FoundationRenderer;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;

/**
 * Class QuestionFormFactory
 * @package App\Forms
 */
class QuestionForm extends Control
{
	/**
	 * @var int
	 */
	protected $lessonId;

	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var Question
	 */
	protected $question;

	/**
	 * @var array
	 */
	public $onSave = array();

	public function __construct($lessonId, IContainer $parent = null, $name = "", EntityManager $em)
	{
		parent::__construct($parent, $name);
		$this->lessonId = $lessonId;
		$this->em = $em;
	}

	public function createComponentForm()
	{
		$form = new Form($this, 'form');

		$form->addText('questionText', 'Text otázky')->setRequired('Vyplňte text otázky');

		$form->addSelect('questionType',
							'Typ otázky',
							array(
								'choice' => 'Uzavřená (jedna možná odpověď)',
								'multipleChoice' => 'Uzavřená (více možných odpovědí)',
								'text' => 'Otevřená'
							));

		$form['questionType']->addCondition(Form::EQUAL, 'choice')->toggle($this->getUniqueId() . '-choice')->toggle($this->getUniqueId() . '-reason');
		$form['questionType']->addCondition(Form::EQUAL, 'multipleChoice')->toggle($this->getUniqueId() . '-choice')->toggle($this->getUniqueId() . '-reason');
		$form['questionType']->addCondition(Form::EQUAL, 'text')->toggle($this->getUniqueId() . '-textQuestion');

		$that = $this;
		$redrawCallback = function() use ($that) {$that->redrawControl('form');};
		$removeCallback = $this->removeElement;

		$options = $form->addDynamic('choiceOptions', function(Container $container) use ($removeCallback, $redrawCallback, $form){
			$container->addText('optionText', "Text možnosti")
						->addConditionOn($form['questionType'], Form::NOT_EQUAL, 'text')
						->setRequired('Vyplňte text možnosti');
			$container->addCheckbox('correctAnswer', 'Správná odpověď')->setAttribute('class', 'rightAnswer');
			$container->addSubmit('remove', "Odebrat")
				->setValidationScope(FALSE)
				->setAttribute('class', 'ajax button alert tiny')
				->onClick[] = $removeCallback;
			$container->addHidden('optionId');
			$container['remove']->onClick[] = $redrawCallback;
		}, 1);


		$options->addSubmit('add', 'Přidat možnost')
			->setValidationScope(FALSE)
			->setAttribute('class', 'ajax button success tiny')
			->setAttribute('id', 'addOption')
			->onClick[] = $this->addElement;


		$options['add']->onClick[] = $redrawCallback;

		$form->addText('correctAnswer', 'Jednoznačná odpověď (pokud existuje)');

		$form->addCheckbox('reasonRequired', "Vyžadováno textové zdůvodnění?");

		$form->addCheckbox('visible', 'Ihned viditelné?');

		$form->addHidden('questionId');

		$form->onSuccess[] = $this->saveQuestion;
		$form->onValidate[] = $this->validateRightAnswers;
		$form->addSubmit('save', "Uložit otázku")->setAttribute('class', 'button');
		$form->setRenderer(new FoundationRenderer());

		return $form;
	}

	public function addElement(SubmitButton $button)
	{
		$button->parent->createOne();
	}

	public function removeElement(SubmitButton $button)
	{
		$button->parent->parent->remove($button->parent, TRUE);
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . "/questionForm.latte");
		$this->template->render();
	}

	public function saveQuestion(Form $form)
	{
		$values = $form->getHttpData();

		if (!isset($values['save'])) return false;

		$this->em->beginTransaction();

		$values = $form->getValues();

		if ($values['questionId']) {
			$question = $this->em->find(Question::getClassName(), $values['questionId']);
		} else {
			$question = new Question();
		}


		$question->setQuestionText($values['questionText'])
					->setVisible($values['visible'])
					->setLesson($this->em->getReference(Question::getClassName(), $this->lessonId))
					->setQuestionType($values['questionType']);

		if (!$question->getGroup()) {
			$group = new Group();
			$this->em->persist($group);
			$this->em->flush();
			$question->setGroup($group);
		}


		try {
			$this->em->persist($question);
			$this->em->flush();
		} catch (\Exception $e) {
			$this->presenter->flashMessage('Nepodařilo se uložit otázku.', 'alert');
			$this->em->rollback();
			return;
		}


		if ($values['questionType'] == 'text') {
			$question->setCorrectTextAnswer($values['correctAnswer']);
			try {
				$this->em->persist($question);
				$this->em->flush();
				$this->em->commit();
			} catch (\Exception $e) {
				$this->presenter->flashMessage('Nepodařilo se uložit otázku.', 'alert');
				$this->em->rollback();
				return;
			}
		} else { // choices

			$question->setReasonRequire($values['reasonRequired']);

			foreach ($values['choiceOptions'] as $option) {
				if ($option['optionId']) {
					$optionEntity = $this->em->find(QuestionOption::getClassName(), $option['optionId']);
				} else {
					$optionEntity = new QuestionOption();
				}

				$optionEntity->setOptionText($option['optionText']);
				$optionEntity->setQuestion($question);
				$optionEntity->setCorrect($option['correctAnswer']);

				try {
					$this->em->persist($optionEntity);
					$this->em->flush();
				} catch (\Exception $e) {
					$this->presenter->flashMessage('Nepodařilo se uložit otázku.', 'alert');
					$this->em->rollback();
					return;
				}
			}

			$this->em->commit();
			$this->presenter->flashMessage("Otázka byla úspěšně uložena", 'success');
		}


		$this->onSave($form);
		$this->redirect('this');
	}

	public function setQuestion(Question $question)
	{
		$this->question = $question;
		$defaults = array();

		$defaults['questionText'] = $question->getQuestionText();
		$defaults['questionType'] = $question->getQuestionType();
		$defaults['questionId'] = $question->getId();


		if ($question->getQuestionType() == Question::TYPE_TEXT) {
			$defaults['correctAnswer'] = $question->getCorrectTextAnswer();
		} else {
			$defaults['reasonRequired'] = $question->isReasonRequire();
			$defaults['visible'] = $question->isVisible();

			$i = 0;
			foreach ($question->getOptions() as $option) {
				$this['form']['choiceOptions'][$i]->setDefaults(array(
					'optionText' => $option->getOptionText(),
					'correctAnswer' => $option->isCorrect(),
					'optionId' => $option->getId()
				));
				$i++;
			}
		}

		$this['form']->setDefaults($defaults);
	}

	public function validateRightAnswers(Form $form)
	{
		$values = $form->getHttpData();

		if (!isset($values['save'])) return;

		$values = $form->getValues();

		if ($values['questionType'] == 'choice') {
			$rightAnswer = false;
			foreach ($values['choiceOptions'] as $option) {
				if ($option['correctAnswer']) {
					$rightAnswer = true;
					break;
				}
			}

			if (!$rightAnswer) {
				$form->addError('Vyberte správnou odpověď.');
				$this->parent->parent->template->openModal = true;
			}
		}
	}

}