<?php

namespace App\Presenters;


use App\Model\Entities\Lesson;
use App\Model\Entities\Teaching;
use App\Model\FoundationRenderer;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

class LessonsPresenter extends AuthorizedBasePresenter
{
	/**
	 * @var Teaching
	 */
	protected $teaching;

	public function startup()
	{
		parent::startup();
		$this->teaching = $this->em->find(Teaching::getClassName(), $this->getHttpRequest()->getQuery('teachingId'));

		if (!$this->teaching) throw new BadRequestException();

		$navText = 'Vyučování | '. $this->teaching->getClass()->getName();
		$navText .= ' | ' . $this->teaching->getSubject();

		$this->addLinkToNav($navText, 'Teaching:default', array($this->teaching->getId()));
	}

	public function actionDefault($teachingId)
	{
		$this->addLinkToNav('Všechny hodiny', 'Lessons:default', array($this->teaching->getId()));
	}

	public function renderDefault()
	{
		$this->template->teaching = $this->teaching;
		$this->template->todayWeek = date('W');
		$this->template->nextWeek = date('W', strtotime('+1 week'));
	}

	public function handleDelete($lessonId)
	{
		try {
			$this->em->remove($this->em->getReference(Lesson::getClassName(), $lessonId));
			$this->em->flush();
			$this->flashMessage("Hodina byla úspěšně smazána", "success");
		} catch (\Exception $e) {
			$this->flashMessage("Nepodařilo se vymazat hodinu", "alert");
		}

		$this->redirect('this');
	}

	public function checkUser()
	{
		if (!$this->user->isInRole('admin')) {
			if (!$this->userService->isUserInTeaching($this->user, $this->getTeaching())){
				$this->flashMessage("Nejste součástí tohoto vyučování.", "alert");
				$this->redirect('Homepage:default');
			}
		}
	}

	public function actionEditLesson($teachingId, $lessonId = null)
	{

		if ($lessonId) {
			$lesson = $this->em->find(Lesson::getClassName(), $lessonId);
			if (!$lesson) throw new BadRequestException;

			$values = array(
				'lessonName' => $lesson->getName(),
				'lessonDescription' => $lesson->getDescription(),
				'startDate' => $lesson->getStartDate()->format("j. n. Y H:i"),
				'endDate' => $lesson->getEndDate() ? $lesson->getEndDate()->format("j. n. Y H:i") : null,
				'lessonId' => $lessonId,
				'teachingId' => $teachingId
			);


			$this->addLinkToNav('Editace hodiny', 'Lessons:editLesson', array($lessonId, $this->teaching->getId()));
		} else {
			$this->addLinkToNav('Vytvoření nové hodiny', 'Lessons:editLesson', array($this->teaching->getId()));
			$values = array('teachingId' => $teachingId);
		}

		$this['lessonForm']->setDefaults($values);

		$this->template->ckeditor = true;
	}

	public function createComponentLessonForm()
	{
		$form = new Form();
		$form->addText('lessonName', "Název hodiny");
		$form->addTextArea('lessonDescription', "Dlouhý popis hodiny", null, 5)->setAttribute('class', 'ckeditor');
		$form->addText('startDate', "Začátek hodiny")->setRequired('Zadejte začátek hodiny')->setAttribute('class', 'fdatetimepicker');;
		$form->addText('endDate', "Konec hodiny")->setRequired('Zadejte konec hodiny')->setAttribute('class', 'fdatetimepicker');
		$form->addHidden('lessonId');
		$form->addHidden('teachingId');

		$form->addSubmit('save', "Uložit");
		$form->setRenderer(new FoundationRenderer());
		$form->onSuccess[] = $this->saveLesson;

		return $form;
	}

	public function saveLesson(Form $form)
	{
		$values = $form->values;
		$startDate = \DateTime::createFromFormat("j. n. Y H:i", $values['startDate']);
		$endDate = \DateTime::createFromFormat("j. n. Y H:i", $values['endDate']);

		if ($startDate > $endDate) {
			$this->flashMessage("Konec hodiny nemůže být před jejím začátkem", 'alert');
			$this->redirect('this');
		}

		if ($values['lessonId']) {
			$lesson = $this->em->find(Lesson::getClassName(), $values['lessonId']);
		} else {
			$lesson = new Lesson();
		}

		$lesson->setName($values['lessonName'])
				->setDescription($values['lessonDescription'])
				->setStartDate($startDate)
				->setEndDate($endDate)
				->setTeaching($this->em->getReference(Teaching::getClassName(), $values['teachingId']));

		try {
			$this->em->persist($lesson);
			$this->em->flush();
			$this->flashMessage("Hodina byla úspěšně uložena", "success");
		} catch (\Exception $e) {
			$this->flashMessage("Hodina nebyla uložena", "alert");
			return;
		}

		$this->redirect('this');
	}

	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->title = "Přehled hodin | " . $this->teaching->getSubject()->getAbbreviation();
	}

}