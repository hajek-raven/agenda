<?php

namespace App\SchoolModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */

class ClassesPresenter extends \App\Presenters\SecuredPresenter
{
 	/** @var \App\Model\School\Classes @inject */
	public $model;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Třídy");
	}

	protected function createComponentGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
    $grid->model = $this->model;
	$grid->addColumnText('shortname', 'Zkratka')->setSortable()->setFilterText();
	$grid->addColumnText('name', 'Plný název')->setSortable()->setFilterText();
	$grid->addColumnText('year', 'Ročník')->setSortable()->setFilterText();
	$grid->addColumnText('teacher_firstname', 'Jméno třídního')->setSortable()->setFilterText();
	$grid->addColumnText('teacher_lastname', 'Příjmení třídního')->setSortable()->setFilterText();
	$grid->addActionHref("id","Detail")->setPrimaryKey("id");
    return $grid;
	}

	public function renderDefault()
	{
	}

	public function actionId($id)
	{
		$data = $this->model->get($id);
		$this->setTitle("Třída " . $data->shortname);
		$this->template->data = $data;
	}
}