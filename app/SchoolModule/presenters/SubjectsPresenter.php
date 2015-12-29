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
    /** @var \App\Model\School\Loads @inject */
	public $loadsModel;

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
	$grid->addColumnText('hours', 'Hodin')->setSortable();
	$grid->addActionHref("id","Detail");
    return $grid;
	}

	protected function createComponentLoadsGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
	$grid->addColumnText('teacher_firstname', 'Jméno učitele')->setSortable()->setFilterText()->setColumn("user.firstname");
	$grid->addColumnText('teacher_lastname', 'Příjmení učitele')->setSortable()->setFilterText()->setColumn("user.lastname");
	$grid->addColumnText('subject_name', 'Předmět')->setSortable()->setFilterText()->setColumn("sch_subject.name");
	$grid->addColumnText('group_name', 'Skupina')->setSortable()->setFilterText()->setColumn("sch_group.name");
	$grid->addColumnText('class_name', 'Třída')->setSortable()->setFilterText()->setColumn("sch_class.shortname");
	$grid->addColumnText('hours', 'Hodin')->setSortable();
	$grid->addActionHref("id","Detail","Teachers:id")->setPrimaryKey("user_id");
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
		$loadsGrid = $this["loadsGrid"];
		$loadsGrid->setModel($this->loadsModel->getSelection()->where(array("sch_subject_id" => $id)));
	}
}
