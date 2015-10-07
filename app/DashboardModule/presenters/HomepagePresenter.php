<?php

namespace App\DashboardModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends \App\Presenters\SecuredPresenter
{

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Dashboard");
	}

	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

}
