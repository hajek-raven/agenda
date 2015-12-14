<?php

namespace App\SchoolModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */

class SubjectsPresenter extends \App\Presenters\SecuredPresenter
{
 	/** @var \App\Model\School\Subjects @inject */
	public $model;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Vyučovací předměty");
	}

	protected function createComponentGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
    $grid->model = $this->model;
	$grid->addColumnText('name', 'Název')->setSortable()->setFilterText();
	$grid->addColumnText('shortname', 'Zkratka')->setSortable()->setFilterText();
	$grid->addActionHref("id","Detail");
    return $grid;
	}

	public function renderDefault()
	{
	}

	public function actionId($id)
	{
		$data = $this->model->get($id);
		$this->setTitle("Vyučovací předmět " . $data->name);
		$this->template->data = $data;
	}
}
