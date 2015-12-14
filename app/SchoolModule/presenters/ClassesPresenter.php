<?php

namespace App\SchoolModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */

class ClassesPresenter extends \App\Presenters\SecuredPresenter
{
 	/** @var \App\Model\Users @inject */
	public $model;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Třídy");
	}

	public function renderDefault()
	{
		//$this->template->users = $this->model->query("SELECT count(*) as total FROM user")->fetch();
		//$this->template->groups = $this->model->query("SELECT count(*) as total FROM `group`")->fetch();
		//$this->template->files = $this->model->query("SELECT count(*) as total FROM `file`")->fetch();
	}

}
