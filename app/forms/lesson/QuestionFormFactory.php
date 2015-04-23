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
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;

/**
 * Class QuestionFormFactory
 * @package App\Forms
 */
class QuestionFormFactory extends Control
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
		$this->lessonId = $lessonId;
		$this->em = $em;
	}

	public function createComponentForm()
	{
		$form = new Form();

		$form->addText('questionText', 'Text otázky')->setRequired('Vyplňte text otázky');

		$form->addSelect('questionType',
							'Typ otázky',
							array(
								'choice' => 'Uzavřená (jedna možná odpověď)',
								'multipleChoice' => 'Uzavřená (více možných odpovědí)',
								'text' => 'Otevřená'
							));

		$form['questionType']->addCondition(Form::EQUAL, 'choice')->toggle('choice')->toggle('reason');
		$form['questionType']->addCondition(Form::EQUAL, 'multipleChoice')->toggle('multipleChoice')->toggle('reason');
		$form['questionType']->addCondition(Form::EQUAL, 'text')->toggle('text');

		$that = $this;
		$redrawCallback = function() use ($that) {$that->redrawControl('form');};
		$removeCallback = $this->removeElement;

		$options = $form->addDynamic('choiceOptions', function(Container $container) use ($removeCallback, $redrawCallback){
			$container->addText('optionText', "Text možnosti")->setRequired('Vyplňte text možnosti');
			$container->addCheckbox('correctAnswer', 'Správná odpověď');
			$container->addSubmit('remove', "Odebrat")
				->setValidationScope(FALSE)
				->setAttribute('class', 'ajax button alert tiny')
				->onClick[] = $removeCallback;
			$container['remove']->onClick[] = $redrawCallback;
		}, 1);

		$options->addSubmit('add', 'Přidat možnost')
			->setValidationScope(FALSE)
			->setAttribute('class', 'ajax button success tiny')
			->setAttribute('id', 'addOption')
			->onClick[] = $this->addElement;

		$options['add']->onClick[] = $redrawCallback;

		$form->addCheckbox('reasonRequired', "Vyžadováno textové zdůvodnění?")->setAttribute('id', 'reason');

		$form->addCheckbox('visible', 'Ihned viditelné?');

		$form->addHidden('questionId');

		$form->onSuccess[] = $this->saveQuestion;
//		$form->onValidate[] = $this->validateRightAnswers;
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
					->setVisible($values['visible']);

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

		} else { // choices

			$question->setReasonRequire($values['reasonRequired']);

			foreach ($values['choiceOptions'] as $option) {
				$optionEntity = new QuestionOption();
				$optionEntity->setOptionText($option['optionText']);
				$optionEntity->setQuestion($question);
				$optionEntity->setCorrect($option['correctAnswer']);

				try {
					$this->em->persist($optionEntity);
					$this->em->flush();
				} catch (\Exception $e) {
					throw $e;

					$this->presenter->flashMessage('Nepodařilo se uložit otázku.', 'alert');
					$this->em->rollback();
					return;
				}
			}

			$this->em->commit();
			$this->presenter->flashMessage("Otázka byla úspěšně uložena", 'success');
		}

		$this->redirect('this');
	}


	public function validateRightAnswers(Form $form)
	{
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