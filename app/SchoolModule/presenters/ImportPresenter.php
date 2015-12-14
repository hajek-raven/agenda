<?php

namespace App\SchoolModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */

class ImportPresenter extends \App\Presenters\SecuredPresenter
{
 	/** @var \App\Model\Import\BakalariImport @inject */
	public $model;
	/** @var \App\Model\School\Subjects @inject */
	public $subjectsModel;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Import dat");
	}

	public function renderDefault()
	{
		$this->template->dataSource = $this->model->getInterfaceLocation();
	}
	
	public function actionRefreshAll()
	{
		$result = $this->model->refreshSource();
		$this->template->messages = $result->messages;
		$this->setView("Result");
	}
	
	public function actionRefreshSubjects()
	{
		$result = $this->model->refreshSubjects();
		$this->template->messages = $result->messages;
		$this->setView("Result");
	}	
	
	public function actionRefreshStudents()
	{
		$result = $this->model->refreshStudents();
		$this->template->messages = $result->messages;
		$this->setView("Result");
	}
	
	public function actionRefreshTeachers()
	{
		$result = $this->model->refreshTeachers();
		$this->template->messages = $result->messages;
		$this->setView("Result");
	}	
	
	public function actionRefreshClasses()
	{
		$result = $this->model->refreshClasses();
		$this->template->messages = $result->messages;
		$this->setView("Result");
	}	
	
	public function actionRefreshGroups()
	{
		$result = $this->model->refreshGroups();
		$this->template->messages = $result->messages;
		$this->setView("Result");
	}	
	
	public function actionRefreshLoads()
	{
		$result = $this->model->refreshLoads();
		$this->template->messages = $result->messages;
		$this->setView("Result");
	}
	
	public function actionImportSubjects()
	{
		$result = $this->model->importSubjects();
		$messages = $result->messages;
		$data = $result->data;
		$reports = array();
		foreach ($data as $record)
		{
			$a = $this->subjectsModel->query("SELECT * FROM `sch_subject` WHERE `bakalari_code` = '{$record->bakalari_code}' ")->fetch();
			if (!$a)
			{
				$this->subjectsModel->insert(array("name" => $record->name,"shortname" => $record->shortname,"bakalari_code" => $record->bakalari_code));
				$reports[] = "ADD: Předmět " . $record->name . " byl přidán.";
			}
			else
			{
				$this->subjectsModel->update($a->id,array("name" => $record->name,"shortname" => $record->shortname));
				$reports[] = "UPDATE: Předmět " . $a->name . " byl aktualizován. (" . $a->id . ")";
			}
		}
		$this->template->reports = $reports;
		$this->template->messages = $messages;
		$this->setView("Result");
	}
	
	public function actionImportTeachers()
	{
		$result = $this->model->importTeachers();
		$messages = $result->messages;
		$data = $result->data;
		$reports = array();
		foreach ($data as $record)
		{
			/*
			$a = $this->subjectsModel->query("SELECT * FROM `sch_subject` WHERE `bakalari_code` = '{$record->bakalari_code}' ")->fetch();
			if (!$a)
			{
				$this->subjectsModel->insert(array("name" => $record->name,"shortname" => $record->shortname,"bakalari_code" => $record->bakalari_code));
				$reports[] = "ADD: Předmět " . $record->name . " byl přidán.";
			}
			else
			{
				$this->subjectsModel->update($a->id,array("name" => $record->name,"shortname" => $record->shortname));
				$reports[] = "UPDATE: Předmět " . $a->name . " byl aktualizován. (" . $a->id . ")";
			}
			*/
		}
		$this->template->reports = $reports;
		$this->template->messages = $messages;
		$this->setView("Result");
	}			
}
