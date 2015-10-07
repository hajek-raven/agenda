<?php

namespace App\WorksModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */


class HomepagePresenter extends \App\Presenters\SecuredPresenter
{
	/** @var \App\Model\Work\Ideas @inject */
	public $ideaModel;
	/** @var \App\Model\Work\Sets @inject */
	public $setModel;
	/** @var \App\Model\Work\Assignments @inject */
	public $model;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Dlouhodobé práce");
	}

	public function renderDefault()
	{
		$this->template->ideas = $this->model->query("SELECT count(*) as total FROM wrk_assignment")->fetch();
		$this->template->assignments = $this->model->query("SELECT count(*) as total FROM wrk_work")->fetch();
		$this->template->sets = $this->model->query("SELECT count(*) as total FROM wrk_set")->fetch();
	}

}
