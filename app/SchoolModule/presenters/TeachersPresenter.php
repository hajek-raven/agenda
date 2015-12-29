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
    /** @var \App\Model\School\Loads @inject */
	public $loadsModel;	

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
	$grid->addColumnEmail('email', 'Email')->setSortable()->setFilterText();
	$grid->addColumnText('work_phone', 'Linka')->setSortable()->setFilterText();
	$grid->addColumnText('phone', 'Telefon')->setSortable()->setFilterText();
	$grid->addColumnText('hours', 'Hodin')->setSortable();
	$grid->addActionHref("id","Detail")->setPrimaryKey("user_id");
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
	$grid->addActionHref("id","Detail","Groups:id")->setPrimaryKey("sch_group_id");
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
		$loadsGrid = $this["loadsGrid"];
		$loadsGrid->setModel($this->loadsModel->getSelection()->where(array("sch_teacher_id" => $user_id)));
	}
}