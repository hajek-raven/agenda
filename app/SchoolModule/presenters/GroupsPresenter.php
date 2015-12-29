<?php

namespace App\SchoolModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */

class GroupsPresenter extends \App\Presenters\SecuredPresenter
{
 	/** @var \App\Model\School\Groups @inject */
	public $model;
 	/** @var \App\Model\School\GroupMembership @inject */
	public $membersModel;
    /** @var \App\Model\School\Loads @inject */
	public $loadsModel;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Studijní skupiny");
	}

	protected function createComponentGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
    $grid->model = $this->model;
	$grid->addColumnText('shortname', 'Zkratka')->setSortable()->setFilterText();
	$grid->addColumnText('name', 'Plný název')->setSortable()->setFilterText();
    $grid->addColumnText('class', 'Třída')->setSortable()->setFilterText()->setColumn("sch_class.shortname");
    $grid->addColumnText('members', 'Počet členů')->setSortable();
	$grid->addActionHref("id","Detail")->setPrimaryKey("id");
    return $grid;
	}
	
	protected function createComponentMembersGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
	$grid->setDefaultPerPage(50);
	$grid->addColumnText('firstname', 'Jméno')->setSortable()->setFilterText();
	$grid->addColumnText('lastname', 'Příjmení')->setSortable()->setFilterText();
	$grid->addActionHref("id","Detail","Students:id")->setPrimaryKey("user_id");
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
		$get = $this->request->getParameters();
		if (isset($get["sch_group_id"])) $id = $get["sch_group_id"];
		$data = $this->model->get($id);
		$this->setTitle("Studijní skupina " . $data->shortname . " ze třídy " . $data->class);
		$this->template->data = $data;
		$membersGrid = $this["membersGrid"];
		$membersGrid->setModel($this->membersModel->getSelection()->where(array("sch_group_id" => $id))->orderBy("firstname, lastname"));
		$loadsGrid = $this["loadsGrid"];
		$loadsGrid->setModel($this->loadsModel->getSelection()->where(array("sch_group_id" => $id)));
	}
}