<?php

namespace App\SchoolModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */

class HomepagePresenter extends \App\Presenters\SecuredPresenter
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
		$this->template->subjects = $this->model->query("SELECT count(*) as total FROM sch_subject")->fetch();
		$this->template->groups = $this->model->query("SELECT count(*) as total FROM `sch_group`")->fetch();
		$this->template->teachers = $this->model->query("SELECT count(*) as total FROM `sch_teacher`")->fetch();
		$this->template->students = $this->model->query("SELECT count(*) as total FROM `sch_student`")->fetch();
		$this->template->classes = $this->model->query("SELECT count(*) as total FROM `sch_class`")->fetch();
	}

}
