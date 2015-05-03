<?php
namespace App\Presenters;


use App\Model\Entities\Subject;
use App\Model\FoundationRenderer;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

class SubjectsPresenter extends AuthorizedBasePresenter
{

	public function actionDefault($subjectId = null)
	{
		$this->checkPermissions("settings", "subjects");

		if (!$this->actualYear) {
			$this->flashMessage("Nebyl nalezen aktuální školní rok", 'alert');
			$this->redirect('Settings:default');
		}

		$this->addLinkToNav('Nastavení', 'Settings:default');

		if ($subjectId) {
			$this->addLinkToNav('Editace předmětu', 'this');
			$subject = $this->em->find(Subject::getClassName(), $subjectId);
			if (!$subject) throw new BadRequestException;
			$this['form']->setDefaults(
				array(
					'name' => $subject->getName(),
					'abbreviation' => $subject->getAbbreviation(),
					'subjectId' => $subjectId
				)
			);
		} else {
			$this->addLinkToNav('Nový předmět', 'this');
		}

	}

	public function createComponentForm()
	{
		$form = new Form();

		$form->addText('name', 'Název předmětu')->setRequired('Vyplňte název předmětu');
		$form->addText('abbreviation', 'Zkratka')->setRequired('Vyplňte zkratku předmětu');

		$form->addHidden('subjectId');
		$form->addSubmit('save', 'Uložit');
		$form->setRenderer(new FoundationRenderer());
		$form->onSuccess[] = $this->saveSubject;
		return $form;
	}

	public function saveSubject(Form $form)
	{
		$values = $form->getValues();

		if ($values['subjectId']) {
			$subject = $this->em->find(Subject::getClassName(), $values['subjectId']);
		} else {
			$subject = new Subject();
		}

		$subject->setName($values['name'])->setAbbreviation($values['abbreviation']);

		try {
			$this->em->persist($subject);
			$this->em->flush();
			$this->flashMessage("Předmět byl úspěšně uložen", "success");
		} catch (\Exception $e) {
			$this->flashMessage("Předmět nebyl uložen", "alert");
			return;
		}

		$this->redirect('Settings:default');
	}

}