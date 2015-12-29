<?php

namespace App\SchoolModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */

class StudentsPresenter extends \App\Presenters\SecuredPresenter
{
 	/** @var \App\Model\School\Students @inject */
	public $model;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Studenti");
	}

	protected function createComponentGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
    $grid->model = $this->model;
	//$grid->addColumnText('title', 'Titul')->setSortable()->setFilterText();
	$grid->addColumnText('firstname', 'Jméno')->setSortable()->setFilterText();
	$grid->addColumnText('lastname', 'Příjmení')->setSortable()->setFilterText();
	//$grid->addColumnText('title_after', 'Titul')->setSortable()->setFilterText();
	$grid->addColumnText('gender', 'Pohlaví')->setSortable()->setReplacement($grid::$genderReplacements)->setFilterSelect($grid::$genderFilters);
	$grid->addColumnText('class', 'Třída')->setSortable();
	$grid->addColumnText('catalog_number', 'Katalogové číslo')->setSortable()->setFilterNumber();
	$grid->addColumnEmail('email', 'Email')->setSortable()->setFilterText();
	$grid->addColumnText('phone', 'Telefon')->setSortable()->setFilterText();
	$grid->addFilterText('class', 'Třída')->setColumn("sch_class.shortname");
	$grid->addActionHref("id","Detail")->setPrimaryKey("user_id");
    return $grid;
	}

	public function renderDefault()
	{
	}

	public function actionId($user_id)
	{
		$data = $this->model->get($user_id);
		$this->setTitle("Student " . $data->firstname . " " . $data->lastname);
		$this->template->data = $data;
	}
}