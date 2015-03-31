<?php

namespace App\Model;


use Nette\Forms\Rendering\DefaultFormRenderer;

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