<?php

namespace App\Presenters;

use App\Model\Entities\SchoolYear;
use Nette\Application\UI\Form;

/**
 * Class SettingsPresenter
 * Page with all settings of application
 * @package App\Presenters
 */
class SettingsPresenter extends AuthorizedBasePresenter
{
	public function startup()
	{
		parent::startup();

		if (!$this->user->isAllowed('settings')) {
			$this->flashMessage('Na vstup do této sekce nemáte dostatečná oprávnění', 'alert');
			$this->redirect('Homepage:default');
		}

		$this->addLinkToNav('Nastavení', 'Settings:default');
	}


	/**
	 * Main page with all available settings
	 */
	public function renderDefault()
	{
		$this->template->schoolYears = $this->em->getRepository(SchoolYear::getClassName())->findBy(array(), array('from' => 'ASC'));
	}

	/*
	 *
	 * SCHOOL YEAR MANAGEMENT
	 *
	 */

	/**
	 * Page with form for school year entity
	 * @param null|int $schoolYearId
	 */
	public function actionSchoolYear($schoolYearId = null)
	{
		$this->checkPermissions("settings", "school-years");

		if ($schoolYearId) {
			$schoolYear = $this->em->getRepository(SchoolYear::getClassName())->find($schoolYearId);
			$this['schoolYearForm']
				->setDefaults(array(
						"id" => $schoolYearId,
						"from" => $schoolYear->getFrom()->format("j. n. Y"),
						"to" => $schoolYear->getTo()->format("j. n. Y")
					)
				);

			$this->addLinkToNav('Editace školního roku', 'Settings:schoolYear', array($schoolYearId));
		} else {
			$this->addLinkToNav('Nový školní rok', 'Settings:schoolYear');
		}
	}

	/**
	 * Factory for school year form
	 * @return Form
	 */
	public function createComponentSchoolYearForm()
	{
		$form = new Form();


		$form->addText("from", "Začátek školního roku")->addRule(Form::FILLED, "Zvolte datum začátku školního roku")->getControlPrototype()->class('fdatepicker');
		$form->addText("to", "Konec školního roku")->addRule(Form::FILLED, "Zvolte datum konce školního roku")->getControlPrototype()->class('fdatepicker');
		$form->addHidden("id");
		$form->addSubmit("save", "Uložit")->getControlPrototype()->class('button');

		$form->onSuccess[] = $this->saveSchoolYear;

		return $form;
	}


	/**
	 * Creates or updates school year
	 * @param Form $form
	 */
	public function saveSchoolYear(Form $form)
	{
		$values = $form->getValues();

		$from = \DateTime::createFromFormat('j. n. Y', $values['from']);
		$to = \DateTime::createFromFormat('j. n. Y', $values['to']);


		if ($values['id']) {
			$schoolYear = $this->em->getRepository(SchoolYear::getClassName())->find($values['id']);

			if (!$schoolYear) {
				$this->flashMessage("Školní rok nebyl nalezen", "error");
				$this->redirect("default");
			}

			$schoolYear->setFrom($from)
						->setTo($to);

			try {
				$this->em->flush();
				$this->flashMessage("Školní rok byl úspěšně upraven.", "success");
			} catch (\Exception $e) {
				$this->flashMessage("Školní rok nebyl upraven.", "alert");
				$this->redirect("this");
			}

		} else {
			$existingYear = $this->em->getRepository(SchoolYear::getClassName())->findBy(array('from' => $from, 'to' => $to));
			if ($existingYear) {
				$this->flashMessage("Školní rok s tímto začátečním a koncovým datem již existuje", "alert");
				$this->redirect('this');
			}


			$schoolYear = new SchoolYear();
			$schoolYear->setFrom($from)
						->setTo($to);
			try {
				$this->em->persist($schoolYear);
				$this->em->flush();
				$this->flashMessage("Školní rok byl úspěšně vytvořen. Přejete si přejít na <a href='".$this->link("classesManagement")."'>vytvoření studentů a skupin</a>?", "success");
			} catch (\Exception $e) {
				$this->flashMessage("Školní rok nebyl vytvořen.", "alert");
				$this->redirect("this");
			}
		}

		$this->redirect('default');
	}





	/*
	 *
	 * CLASSES MANAGEMENT
	 *
	 */



	public function renderClassesManagement()
	{

	}

}