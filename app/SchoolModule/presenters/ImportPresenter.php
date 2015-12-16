<?php

namespace App\SchoolModule\Presenters;

use Nette,
	App\Model,
	Nette\Utils\Strings;

/**
 * Homepage presenter.
 */

class ImportPresenter extends \App\Presenters\SecuredPresenter
{
 	/** @var \App\Model\Import\BakalariImport @inject */
	public $model;
	/** @var \App\Model\School\Subjects @inject */
	public $subjectsModel;
	/** @var \App\Model\School\Teachers @inject */
	public $teachersModel;
	/** @var \App\Model\School\Classes @inject */
	public $classesModel;
	/** @var \App\Model\Users @inject */
	public $usersModel;

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
		$this->teachersModel->invalidate();
		foreach ($data as $record)
		{
			$id = $this->teachersModel->existsBakalari($record->bakalari_code);
			if (!$id)
			{
				$newid = $this->usersModel->insert(array(
					"firstname" => $record->firstname,
					"lastname" => $record->lastname,
					"title" => $record->title,
					"title_after" => $record->title_after,
					"gender" => $record->gender,
					"birthdate" => $record->birthdate,
					"phone" => $record->mobile_phone,
					"personal_identification_number" => $record->personal_identification_number,
					"email" => $record->email
				));
				$this->teachersModel->dumbInsert(array(
					"user_id" => $newid,
					"work_phone" => $record->work_phone,
					"shortname" => $record->shortname,
					"network_login" => $record->login,
					"bakalari_code" => $record->bakalari_code
				));
				$reports[] = "ADD: Učitel " . $record->lastname . " byl přidán. ($newid)";
			}
			else
			{
				$recid = $id->user_id;
				$this->usersModel->update($recid,array(
					"firstname" => $record->firstname,
					"lastname" => $record->lastname,
					"title" => $record->title,
					"title_after" => $record->title_after,
					"gender" => $record->gender,
					"birthdate" => $record->birthdate,
					"phone" => $record->mobile_phone,
					"personal_identification_number" => $record->personal_identification_number,
					"email" => $record->email
				));
				$this->teachersModel->update($recid,array(
					"work_phone" => $record->work_phone,
					"shortname" => $record->shortname,
					"network_login" => Strings::lower($record->shortname),
					"invalidated" => 0
				));
				$reports[] = "UPDATE: Učitel " . $record->lastname . " byl aktualizován. ($recid)";
			}
		}
		$this->teachersModel->removeUnused();
		$this->template->reports = $reports;
		$this->template->messages = $messages;
		$this->setView("Result");
	}
	
	public function actionImportClasses()
	{
		$result = $this->model->importClasses();
		$messages = $result->messages;
		$data = $result->data;
		$reports = array();
		$this->classesModel->invalidate();
		foreach ($data as $record)
		{
			$id = $this->classesModel->existsBakalari($record->bakalari_code);
			$teacher = $this->teachersModel->query("SELECT user_id FROM sch_teacher WHERE bakalari_code = \"{$record->teacher_code}\"")->fetch();
			if (!$id)
			{
				$newrec = array(
					"name" => $record->name,
					"shortname" => $record->shortname,
					"year" => $record->year,
					"bakalari_code" => $record->bakalari_code
				);
				if ($teacher) $newrec["teacher_id"] = $teacher["user_id"];
				$newid = $this->classesModel->insert($newrec);
				$reports[] = "ADD: Třída " . $record->shortname . " byla přidána. ($newid)";
			}
			else
			{
				$recid = $id->id;
				$newrec = array(
					"name" => $record->name,
					"shortname" => $record->shortname,
					"year" => $record->year,
					"bakalari_code" => $record->bakalari_code
				);
				if ($teacher) $newrec["teacher_id"] = $teacher["user_id"];
				$this->classesModel->update($recid,$newrec);
				$reports[] = "UPDATE: Třída " . $record->shortname . " byla aktualizována. ($recid)";
			}
		}
		$this->template->reports = $reports;
		$this->template->messages = $messages;
		$this->setView("Result");
	}			
}