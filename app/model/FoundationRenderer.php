<?php

namespace App\Model;


use Nette\Forms\Rendering\DefaultFormRenderer;

/**
 * Class FoundationRenderer
 * Only wrapping Nette renderer - removing table rendering of form
 * @package App\Model
 */
class FoundationRenderer extends DefaultFormRenderer
{
	public function __construct()
	{
		$this->wrappers['controls']['container'] = 'p';
		$this->wrappers['pair']['container'] = 'p';
		$this->wrappers['label']['container'] = null;
		$this->wrappers['control']['container'] = null;
	}


}