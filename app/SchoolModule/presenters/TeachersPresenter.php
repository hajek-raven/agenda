<?php

namespace App\SchoolModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */

class TeachersPresenter extends \App\Presenters\SecuredPresenter
{
 	/** @var \App\Model\School\Teachers @inject */
	public $model;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Učitelé");
	}

	protected function createComponentGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
    $grid->model = $this->model;
	$grid->addColumnText('title', 'Titul')->setSortable()->setFilterText();
	$grid->addColumnText('firstname', 'Jméno')->setSortable()->setFilterText();
	$grid->addColumnText('lastname', 'Příjmení')->setSortable()->setFilterText();
	$grid->addColumnText('title_after', 'Titul')->setSortable()->setFilterText();
	$grid->addColumnText('gender', 'Pohlaví')->setSortable()->setReplacement($grid::$genderReplacements)->setFilterSelect($grid::$genderFilters);
	$grid->addColumnText('shortname', 'Zkratka')->setSortable()->setFilterText();
	$grid->addColumnText('email', 'Email')->setSortable()->setFilterText();
	$grid->addColumnText('work_phone', 'Linka')->setSortable()->setFilterText();
	$grid->addColumnText('phone', 'Telefon')->setSortable()->setFilterText();
	$grid->addActionHref("id","Detail")->setPrimaryKey("user_id");
    return $grid;
	}

	public function renderDefault()
	{
	}

	public function actionId($user_id)
	{
		$data = $this->model->get($user_id);
		$this->setTitle("Učitel " . $data->firstname . " " . $data->lastname);
		$this->template->data = $data;
	}
}