<?php

namespace App\Presenters;


use App\Model\Entities\User;
use App\Model\FoundationRenderer;
use Nette\Application\UI\Form;
use Nette\Neon\Exception;
use Nette\Security\Passwords;
use Nette\Utils\Image;

/**
 * Class ProfilePresenter
 * Page with profile settings, such as changing password and profile picture
 * @package App\Presenters
 */
class ProfilePresenter extends AuthorizedBasePresenter
{

	/**
	 * Default page
	 */
	public function actionDefault()
	{
		$this->addLinkToNav("Nastavení profilu", "this");

	}

	/**
	 * Form for changing profile picture
	 * @return Form
	 */
	public function createComponentPhotoForm()
	{
		$form = new Form();
		$form->addUpload('photo')->setRequired("Vyberte foto")->addRule(Form::IMAGE, "Soubor musí být obrázek")
			->addRule(Form::MAX_FILE_SIZE, "Obrázek musí být menší než 10 MB", 10 * 1024 * 1024);

		$form->addSubmit('save', "Uložit");
		$form->onSuccess[] = $this->savePhoto;
		$form->setRenderer(new FoundationRenderer());
		return $form;
	}

	/**
	 * Save new profile picture
	 * @param Form $form
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 */
	public function savePhoto(Form $form)
	{
		$values = $form->getValues();

		try {
			/** @var Image $image */
			$image = $values['photo']->toImage();

			$path = IMG_DIR . "/users/user-" . $this->user->id . ".jpg";
			$height = $image->getHeight();
			$width = $image->getWidth();

			if ($width > 500) {
				$image->resize(500, null);
			}

			if ($height > 500) {
				$image->resize(null, 500);
			}

			$height = $image->getHeight();
			$width = $image->getWidth();

			if (abs($height - $width) < 5) {
				$image->save($path, 100, Image::JPEG);
			} elseif ($width < $height) {
				$left = 0;
				$top = (ceil(($height - $width) / 2));
				$image->crop($left, $top, $width, $width);
				$image->save($path, 100, Image::JPEG);
			} else {
				$top = 0;
				$left = (ceil(($width - $height) / 2));
				$image->crop($left, $top, $height, $height);
				$image->save($path, 100, Image::JPEG);
			}

			$user = $this->em->find(User::getClassName(), $this->user->getId());
			$this->user->getIdentity()->profilePicture = $user->getProfilePicture();

			$this->flashMessage("Obrázek byl úspěšně uložen. Změna se projeví po aktualizaci stránky.", "success");
		} catch (Exception $e) {
			$this->flashMessage("Obrázek nebyl uložen", "alert");
		}

		$this->redirect('this');
	}


	/**
	 * Form for changing password
	 * @return Form
	 */
	public function createComponentForm()
	{
		$form = new Form();

		$form->addPassword('passwordOld', "Aktuální heslo")->setRequired('Vyplňte původní heslo');
		$form->addPassword('password', "Nové heslo")->setRequired('Vyplňte nové heslo')->addRule(Form::MIN_LENGTH, 'Heslo musí být minimálně 6 znaků dlouhé', 6);

		$form->addPassword('passwordAgain', "Nové heslo znovu")->setRequired('Vyplňte nové heslo znovu pro kontrolu')
			->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);

		$form->addSubmit('save', "Uložit");
		$form->setRenderer(new FoundationRenderer());
		$form->onSuccess[] = $this->saveUser;

		return $form;
	}

	/**
	 * Save new password
	 * @param Form $form
	 */
	public function saveUser(Form $form)
	{
		$values = $form->getValues();
		$user = $this->em->getRepository(User::getClassName())->find($this->user->getId());



		if (!Passwords::verify($values['passwordOld'], $user->getPassword())) {
			$this->flashMessage('Staré heslo neodpovídá', 'alert');
			$this->redirect('this');
		}

		$user->setPassword(Passwords::hash($values['password']));

		try {
			$this->em->flush();
			$this->flashMessage('Heslo bylo úspěšně změněno', 'success');
		} catch (Exception $e) {
			$this->flashMessage('Heslo nebylo změněno', 'alert');
		}

		$this->redirect('this');
	}

	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->title = "Nastavení profilu";
	}

}