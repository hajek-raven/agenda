<?php

namespace App\SchoolModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */

class TeachersPresenter extends \App\Presenters\SecuredPresenter
{
 	/** @var \App\Model\Users @inject */
	public $model;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Procházení školních dat");
	}

	public function renderDefault()
	{
	}

}
