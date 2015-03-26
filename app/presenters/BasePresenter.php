<?php

namespace App\Presenters;

use App\Filter\TemplateFilters;
use Kdyby\Doctrine\EntityManager;
use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/**
	 * @inject
	 * @var EntityManager
	 */
	public $em;


	protected function createTemplate()
	{
		$template = parent::createTemplate();
		$template->addFilter('img', callback('\App\Filter\TemplateFilters', 'image'));

		return $template;
	}

	public function beforeRender()
	{
		$this->template->isProduction = !Nette\Configurator::detectDebugMode();
	}
}
