<?php
namespace App\Presenters;


use App\Model\Entities\Teacher;
use App\Model\FoundationRenderer;
use Nette\Application\UI\Form;
use App\Model\Services\UserService;
use Nette\Security\Passwords;

class TeachersPresenter extends AuthorizedBasePresenter
{
	/**
	 * @var Teacher
	 */
	private $teacher;

	/**
	 * @var UserService
	 * @inject
	 */
	public $userService;

	public function actionDefault($teacherId = null)
	{
		$this->checkPermissions("settings", "teachers");

		$this->addLinkToNav('Nastavení', 'Settings:default');

		if ($teacherId) {
			$this->addLinkToNav('Editace učitele', 'this');
			$teacher = $this->em->find(Teacher::getClassName(), $teacherId);
			if (!$teacher) throw new BadRequestException;
			$this->teacher = $teacher;

			$this['form']->setDefaults(
				array(
					'name' => $teacher->getName(),
					'surname' => $teacher->getSurname(),
					'room' => $teacher->getRoom(),
					'teacherId' => $teacherId,
					'login' => $teacher->getLogin()
				)
			);

		} else {
			$this->addLinkToNav('Nový předmět', 'this');
		}
	}

	public function createComponentForm()
	{
		$form = new Form();

		if ($this->teacher) {
			$form->addText('login', 'Login')->setDisabled();
		}

		$form->addText('name', "Jméno")->setRequired('Vyplňte jméno učitele');
		$form->addText('surname', 'Příjmení')->setRequired('Vyplňte příjmení učitele');
		$form->addText('room', 'Místnost');
		$form->addHidden('teacherId');

		$form->addSubmit('save', 'Uložit');
		$form->setRenderer(new FoundationRenderer());

		$form->onSuccess[] = $this->saveTeacher;

		return $form;
	}

	public function handleGenerateNewPassword()
	{
		try {
			$password = $this->userService->generateNewPassword($this->teacher);
			$this->teacher->setPassword(Passwords::hash($password));
			$this->em->persist($this->teacher);
			$this->em->flush();
			$this->flashMessage("Heslo bylo úspěšně vygenerováno. Nové heslo je: <strong>$password</strong>", "success");
		} catch (\Exception $e) {
			$this->flashMessage("Heslo nebylo vygenerováno. ", "alert");
		}

		$this->redirect('this');
	}

	public function saveTeacher(Form $form)
	{
		$values = $form->getValues();

		if ($values['teacherId']) {
			$teacher = $this->em->find(Teacher::getClassName(), $values['teacherId']);
		} else {
			$teacher = new Teacher();
	 	}

		$teacher->setName($values['name'])->setSurname($values['surname'])->setRoom($values['room']);

		if (!$teacher->getId()) {
			$password = $this->userService->addUser($teacher);
		}

		try {
			$this->em->persist($teacher);
			$this->em->flush();
			if (isset($password)) {
				$this->flashMessage("Učitel byl úspěšně uložen.<br/><strong>Jeho login je: ".$teacher->getLogin()." a heslo ".$password."</strong>", "success");
			} else{
				$this->flashMessage("Učitel byl úspěšně uložen.", "success");
			}
		} catch (\Exception $e) {
			$this->flashMessage("Učitel nebyl uložen", "alert");
			return;
		}

		$this->redirect('Settings:default');
	}

}