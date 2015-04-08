<?php

namespace App\Presenters;
use App\Controls\BreadcrumbsControl;
use App\Controls\IMenuControlFactory;
use App\Controls\IChatControlFactory;
use App\Model\Entities\SchoolYear;
use App\Model\Services\SchoolYearService;
use Nette\Forms\Controls\SubmitButton;


/**
 * Class AuthorizedBasePresenter
 * User must be logged in all children presenters
 * @package App\Presenters
 */
abstract class AuthorizedBasePresenter extends BasePresenter
{

	/**
	 * @var IMenuControlFactory
	 * @inject
	 */
	public $menuControlFactory;

	/**
	 * @var IChatControlFactory
	 * @inject
	 */
	public $chatControlFactory;


	/**
	 * @var \App\Model\Entities\SchoolYear
	 */
	protected $actualYear;

	protected $days = array("Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek");

	public function startup()
	{
		parent::startup();
		if (!$this->user->isLoggedIn()) {
			$this->redirect("Login:default");
		}

		$this->actualYear = $this->em->getRepository(SchoolYear::getClassName())->findCurrentSchoolYear();
	}

	/**
	 * Factory for creating menu component
	 */
	public function createComponentMenu()
	{
		return $this->menuControlFactory->create();
	}

	/**
	 * Factory for creating chat component
	 */
	public function createComponentChat()
	{
		return $this->chatControlFactory->create();
	}

	public function handleLogout()
	{
		try {
			$this->user->logout();
			$this->flashMessage("Byl jste úspěšně odhlášen.", "success");
		} catch (\Exception $e) {
			$this->flashMessage("Odhlášení se nezdařilo.", "error");
		}

		$this->redirect('Login:default');
	}

	protected function checkPermissions($resource, $type = NULL)
	{
		if (!$type) {
			if (!$this->user->isAllowed($resource)) {
				$this->flashMessage('Na vstup do této sekce nemáte dostatečná oprávnění', 'alert');
				$this->redirect('Homepage:default');
			}
		} else {
			if (!$this->user->isAllowed($resource, $type)) {
				$this->flashMessage('Na vstup do této sekce nemáte dostatečná oprávnění', 'alert');
				$this->redirect('Homepage:default');
			}
		}
	}

	protected function createComponentBreadcrumbs()
	{
		$breadcrumbs = new BreadcrumbsControl();
		$breadcrumbs->addLink("Hlavní stránka", $this->link("Homepage:default"));
		return $breadcrumbs;
	}

	protected function addLinkToNav($title, $link, $args = null)
	{
		if ($args) {
			$this['breadcrumbs']->addLink($title, $this->link($link, $args));
		} else {
			$this['breadcrumbs']->addLink($title, $this->link($link));
		}
	}

	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->actualYear = $this->actualYear;
	}

	public function addElement(SubmitButton $button)
	{
		$button->parent->createOne();
	}

	public function removeElement(SubmitButton $button)
	{
		$button->parent->parent->remove($button->parent, TRUE);
	}
}