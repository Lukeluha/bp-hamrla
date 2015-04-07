<?php

namespace App\Presenters;


use App\Model\Entities\SchoolYear;
use App\Model\FoundationRenderer;
use Nette\Application\UI\Form;

class SchoolYearPresenter extends AuthorizedBasePresenter
{
	public function startup()
	{
		parent::startup();
		$this->checkPermissions("settings", "school-years");
		$this->addLinkToNav('Nastavení', 'Settings:default');
	}

	/**
	 * Page with form for school year entity
	 * @param null|int $schoolYearId
	 */
	public function actionDefault($schoolYearId = null)
	{

		if ($schoolYearId) {
			$schoolYear = $this->em->getRepository(SchoolYear::getClassName())->find($schoolYearId);
			if (!$schoolYear) {
				$this->flashMessage("Nenalezen žádný školní rok.", "alert");
				$this->redirect("Settings:default");
			}

			if ($schoolYear->getClosed()) {
				$this->flashMessage("Školní rok již byl uzavřen.", "alert");
				$this->redirect("Settings:default");
			}

			$this['schoolYearForm']
				->setDefaults(array(
						"id" => $schoolYearId,
						"from" => $schoolYear->getFrom()->format("j. n. Y"),
						"to" => $schoolYear->getTo()->format("j. n. Y")
					)
				);

			$this->addLinkToNav('Editace školního roku', 'this', array($schoolYearId));
		} else {
			$this->addLinkToNav('Nový školní rok', 'this');
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
		$form->setRenderer(new FoundationRenderer());

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

		if ($from > $to) {
			$this->flashMessage("Datum 'od' nemůže být později než datum 'do'", 'alert');
			$this->redirect('this');
		}

		if ($values['id']) {
			$schoolYear = $this->em->getRepository(SchoolYear::getClassName())->find($values['id']);
		} else {
			$schoolYear = new SchoolYear();
		}

		$existingYear = $this->em->createQueryBuilder()
			->select('y')
			->from(SchoolYear::getClassName(), 'y')
			->where('y.from < :from AND y.to > :from')
			->orWhere('y.to > :to AND y.from < :to')
			->andWhere('y.id != :id')
			->setParameters(array('from' => $from, 'to' => $to, 'id' => $values['id']))
			->getQuery()->getOneOrNullResult();



		if ($existingYear) {
			$this->flashMessage("Školní rok se již kryje se školním rokem od " . $existingYear->getFrom()->format("j. n. Y") . " do ".$existingYear->getTo()->format('j. n. Y'), "alert");
			$this->redirect('this');
		}

		$schoolYear->setFrom($from)
			->setTo($to);

		try {
			$this->em->persist($schoolYear);
			$this->em->flush();
			if ($values['id']) {
				$this->flashMessage("Školní rok byl úspěšně upraven.", "success");
			} else {
				$this->flashMessage("Školní rok byl úspěšně vytvořen. Přejete si přejít na <a href='" . $this->link("Classes:default") . "'>vytvoření studentů a skupin</a>?", "success");
			}
		} catch (\Exception $e) {
			if ($values['id']) {
				$this->flashMessage("Školní rok nebyl upraven.", "alert");
			} else {
				$this->flashMessage("Školní rok nebyl vytvořen.", "alert");
			}
			$this->redirect("this");
		}

		$this->redirect('Settings:default');
	}

}