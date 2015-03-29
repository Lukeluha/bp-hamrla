<?php

namespace App\Presenters;


use App\Model\Entities\ClassEntity;
use Nette\Application\UI\Form;

class ClassesPresenter extends AuthorizedBasePresenter
{

	public function startup()
	{
		parent::startup();
		$this->checkPermissions("settings", "classes");
		$this->addLinkToNav('Nastavení', 'Settings:default');
	}

	public function actionDefault($classId = null)
	{
		if (!$classId) {
			$this->addLinkToNav('Vytvoření nové třídy/skupiny', 'Classes:default');
		} else {
			$class = $this->em->getRepository(ClassEntity::getClassName())->find($classId);
			if (!$class) {
				$this->flashMessage("Nenalezena žádná třída.", "alert");
				$this->redirect("Settings:default");
			}

			$this->template->class = $class;

			if ($class->getType() == ClassEntity::TYPE_CLASS) {
				$this->addLinkToNav('Editace třídy', 'Classes:default', array($classId));
			} else {
				$this->addLinkToNav('Editace skupiny', 'Classes:default', array($classId));
			}

			$this['classForm']->setDefaults(array(
					'id' => $class->getId(), 'name' => $class->getName(), 'type' => $class->getType()
				));
		}
	}


	public function createComponentClassForm()
	{
		$form = new Form();

		$form->addText("name", "Název třídy")->setRequired('Vyplňte prosím název třídy');

		$form->addSelect('type', "Typ", array(0 => "--Vyberte--", ClassEntity::TYPE_CLASS => "Třída", ClassEntity::TYPE_GROUP => "Skupina" ))
			->addRule(Form::NOT_EQUAL, "Vyberte typ třídy", 0);

		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'p';
		$renderer->wrappers['pair']['container'] = null;
		$renderer->wrappers['label']['container'] = null;
		$renderer->wrappers['control']['container'] = null;

		$form->addSubmit("save", "Uložit")->setAttribute('class', 'button small');
		$form->addHidden("id");
		$form->onSuccess[] = $this->saveClass;

		return $form;
	}

	public function saveClass(Form $form)
	{
		$values = $form->getValues();

		if ($values['id']) {
			$class = $this->em->getRepository(ClassEntity::getClassName())->find($values['id']);
			if (!$class) {
				$this->flashMessage("Třída nebyla nalezena.", "alert");
				$this->redirect("this");
			}

			$class->setName($values['name'])->setType($values['type']);

			try {
				$this->em->flush();
				if ($class->getType() == ClassEntity::TYPE_CLASS) {
					$this->flashMessage("Třída byla úspěšně upravena", "success");
				} else {
					$this->flashMessage("Skupina byla úspěšně upravena", "success");
				}
			} catch (\Exception $e) {
				if ($class->getType() == ClassEntity::TYPE_CLASS) {
					$this->flashMessage("Třída nebyla upravena", "alert");
				} else {
					$this->flashMessage("Skupina nebyla upravena", "alert");
				}
			}
		} else {
			$class = new ClassEntity();
			$class->setName($values['name'])->setType($values['type']);
			try {
				$this->em->persist($class);
				$this->em->flush();
				if ($class->getType() == ClassEntity::TYPE_CLASS) {
					$this->flashMessage("Třída byla úspěšně vytvořena", "success");
				} else {
					$this->flashMessage("Skupina byla úspěšně vytvořena", "success");
				}
			} catch (\Exception $e) {
				if ($class->getType() == ClassEntity::TYPE_CLASS) {
					$this->flashMessage("Třída nebyla vytvořena", "alert");
				} else {
					$this->flashMessage("Skupina nebyla vytvořena", "alert");
				}
			}
		}

		if ($class->getId()) {
			$this->redirect("default", array($class->getId()));
		} else {
			$this->redirect("default");
		}
	}

}