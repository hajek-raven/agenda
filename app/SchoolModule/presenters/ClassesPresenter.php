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
 	/** @var \App\Model\School\Students @inject */
	public $studentsModel;

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
	
	protected function createComponentStudentsGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
	$grid->setDefaultPerPage(50);
	$grid->addColumnText('firstname', 'Jméno')->setSortable()->setFilterText();
	$grid->addColumnText('lastname', 'Příjmení')->setSortable()->setFilterText();
	$grid->addColumnText('catalog_number', 'Číslo')->setSortable();
	$grid->addActionHref("id","Detail","Students:id")->setPrimaryKey("user_id");
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
		$studentsGrid = $this["studentsGrid"];
		$studentsGrid->setModel($this->studentsModel->getSelection()->where(array("class_id" => $id))->orderBy("catalog_number"));
	}
}