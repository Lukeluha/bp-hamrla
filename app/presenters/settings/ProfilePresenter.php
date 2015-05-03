<?php

namespace App\Presenters;


use App\Model\Entities\User;
use App\Model\FoundationRenderer;
use Nette\Application\UI\Form;
use Nette\Neon\Exception;
use Nette\Security\Passwords;
use Nette\Utils\Image;

class ProfilePresenter extends AuthorizedBasePresenter
{

	public function actionDefault()
	{

	}

	public function createComponentPhotoForm()
	{
		$form = new Form();
		$form->addUpload('photo')->setRequired("Vyberte foto")->addRule(Form::IMAGE, "Soubor musí být obrázek");

		$form->addSubmit('save', "Uložit");
		$form->onSuccess[] = $this->savePhoto;
		$form->setRenderer(new FoundationRenderer());
		return $form;
	}

	public function savePhoto(Form $form)
	{
		$values = $form->getValues();

		try {
			/** @var Image $image */
			$image = $values['photo']->toImage();

			$path = IMG_DIR . "/users/user-" . $this->user->id . ".jpg";
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

			$this->flashMessage("Obrázek byl úspěšně uložen", "success");
		} catch (Exception $e) {
			$this->flashMessage("Obrázek nebyl uložen", "alert");
		}

		$this->redirect('this');
	}

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