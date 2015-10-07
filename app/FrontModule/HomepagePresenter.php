<?php

namespace App\FrontModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends \App\Presenters\BasePresenter
{
	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Zadání dlouhodobých prací");
	}

	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

}
