<?php

namespace App\Presenters;


use App\Model\Entities\ChatMessage;
use App\Model\Entities\SchoolYear;
use App\Model\FoundationRenderer;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Form;

/**
 * Class SchoolYearPresenter
 * Page with school year entity management
 * @package App\Presenters
 */
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

	public function createComponentCloseForm()
	{
		$form = new Form();

		$form->addRadioList('closeOptions', null, array(
			'conversationDelete' => 'Smazání konverzací',
			'allDelete' => "Smazání veškerých dat"
		));

		$form->addSubmit('save', "Uzavřít školní rok")->setAttribute('class', 'button alert');
		$form->setRenderer(new FoundationRenderer());
		$form->onSuccess[] = $this->closeYear;
		$form->addHidden('schoolYearId');

		return $form;
	}

	/**
	 * Close given year
	 * @param Form $form
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 */
	public function closeYear(Form $form)
	{
		$values = $form->getValues();
		$schoolYear = $this->em->find(SchoolYear::getClassName(), $values['schoolYearId']);




		if (isset($values['closeOptions']) && $values['closeOptions']) {
			if ($values['closeOptions'] == 'conversationDelete') {
				$this->em->createQueryBuilder()->delete(ChatMessage::getClassName(), 'm')->getQuery()->execute();
				$this->em->flush();
				$schoolYear->setClosed(true);
				$this->em->persist($schoolYear);
			} else {


				$folderName = $schoolYear->getFrom()->format('Y') . "-" . $schoolYear->getTo()->format('Y') . "-" . $schoolYear->getId();


				$rootPath = realpath(WWW_DIR . "/files/tasks/" . $folderName);

				if (file_exists($rootPath . ".zip")) {
					unlink($rootPath . ".zip");
				}


				/** Source: http://stackoverflow.com/questions/3349753/delete-directory-with-files-in-it */

				$it = new \RecursiveDirectoryIterator($rootPath, \RecursiveDirectoryIterator::SKIP_DOTS);
				$files = new \RecursiveIteratorIterator($it,
					\RecursiveIteratorIterator::CHILD_FIRST);
				foreach($files as $file) {
					if ($file->isDir()){
						rmdir($file->getRealPath());
					} else {
						unlink($file->getRealPath());
					}
				}
				rmdir($rootPath);


				$this->em->remove($schoolYear);
			}
		}


		try {
			$this->em->flush();
			$this->flashMessage('Školní rok byl úspěšně uzavřen', "success");
		} catch (\Exception $e) {
			$this->flashMessage('Školní rok nebyl úspěšně uzavřen', "alert");
			return;
		}

		$this->redirect('Settings:default');

	}

	/**
	 * Export tasks from current school year
	 * @param $yearId
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 */
	public function handleExport($yearId)
	{
		$schoolYear = $this->em->find(SchoolYear::getClassName(), $yearId);

		/** Source: http://stackoverflow.com/questions/4914750/how-to-zip-a-whole-folder-using-php */

		$folderName = $schoolYear->getFrom()->format('Y') . "-" . $schoolYear->getTo()->format('Y') . "-" . $schoolYear->getId();


		$rootPath = realpath(WWW_DIR . "/files/tasks/" . $folderName);

		$zip = new \ZipArchive();
		$zip->open($rootPath . '.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

		/** @var SplFileInfo[] $files */
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($rootPath),
			\RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);

				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}

		$zip->close();

		$this->sendResponse(new FileResponse($rootPath . ".zip", $folderName . ".zip", 'application/zip'));

	}

	/**
	 * Page with closing of school year
	 * @param $schoolYearId
	 * @throws BadRequestException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 */
	public function actionClose($schoolYearId)
	{
		$schoolYear = $this->em->find(SchoolYear::getClassName(), $schoolYearId);

		if (!$schoolYear) throw new BadRequestException;

		$this->addLinkToNav('Uzavření školního roku', 'this');

		$this['closeForm']->setDefaults(array(
			'schoolYearId' => $schoolYearId
		));


		$this->template->schoolYear = $schoolYear;
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

	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->title = "Správa školního roku";
	}

}