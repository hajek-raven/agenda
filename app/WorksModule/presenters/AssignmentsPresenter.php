<?php

namespace App\WorksModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class AssignmentsPresenter extends \App\Presenters\SecuredGridPresenter
{
	protected $statuses = array("" => "", 0 => "Připravená", 1 => "Běžící", 2 => "Nedokončená", 3 => "Neobhájená", 4 => "Obhájená");

	/** @var \App\Model\Work\Assignments @inject */
	public $model;
	/** @var \App\Model\Users @inject */
	public $userModel;
	/** @var \App\Model\Work\Ideas @inject */
	public $ideaModel;
	/** @var \App\Model\Work\Sets @inject */
	public $setModel;
	/** @var \App\Model\Files @inject */
	public $fileModel;
	/** @var \App\Forms\Work\AssignmentFormFactory @inject */
	public $formFactory;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Zadání prací");
	}

	protected function createComponentGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
		if ($this->user->isInRole("administrator") || $this->user->isInRole("teacher"))
    	$grid->model = $this->model;
		else
			$grid->model = $this->model->getSelection()->where("user_id = ".$this->user->id);
		$grid->addColumnText('name', 'Název')->setSortable()->setFilterText();
	  $grid->addColumnText('firstname', 'Jméno autora')->setSortable()->setFilterText();
	  $grid->addColumnText('lastname', 'Příjmení autora')->setSortable()->setFilterText();
		$grid->addColumnText('class', 'Třída')->setSortable()->setFilterText();
		$grid->addColumnText('year', 'Školní rok')->setSortable()->setFilterText();
		$grid->addColumnText('set_name', 'Sada prací')->setSortable()->setFilterText();
		$grid->addColumnText('status', 'Stav')->setSortable()->setReplacement($this->statuses)->setFilterSelect($this->statuses);
		$grid->addColumnDate('created_date', 'Vytvořeno')->setSortable()->setFilterDate();
		$grid->addActionHref("id","Detail");
		$grid->addActionHref("application","Přihláška");
		$grid->addActionHref("delete","Odstranit")->setConfirm(function($item) {
						return "Opravdu chcete odstranit zadání práce {$item->name}?";
			})
			->setDisable(function($item)
				{
					if ($item->status > 0) return true;
					if (!($this->user->isInRole("administrator") || $this->user->id == $item->created_by)) return true;
					return false;
				});
		$operations = array('delete' => 'Odstranit');
		$grid->setOperation($operations, callback($this, 'gridOperationsHandler'))
            ->setConfirm('delete', 'Opravdu chcete smazat všech %i prací?');
		$grid->setDefaultSort(array('name' => 'ASC'));
    return $grid;
	}

	protected function createComponentFormAdd()
	{
		$form = $this->formFactory->create($this->model, array(), array(), array());
      	$form->onSuccess[] = array($this, 'addFormSucceeded');
		return $form;
	}

	protected function createComponentFormEdit()
	{
		$form = $this->formFactory->create($this->model, array(), array(), array());
      	$form->onSuccess[] = array($this, 'editFormSucceeded');
		return $form;
	}

	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

	public function actionAdd()
	{
		$form = $this["formAdd"];
		$this->setTitle("Nové zadání práce");
		$form["user_id"]->setItems($this->userModel->fromGroupRoleAsArray('student'));
		$form["wrk_assignment_id"]->setItems($this->ideaModel->activeAsArray());
		$form["wrk_set_id"]->setItems($this->setModel->activeAsArray());
		$currentYear = date("Y");
		$currentMonth = date("m");
		if ($currentMonth < 9) $currentYear--;
		$form["year"]->setDefaultValue($currentYear);
	}

	public function addFormSucceeded($form,$values)
	{
		unset($values->id);
		if ($this->user->isAllowed("Works:Assignments","add"))
		{
			try
			{
				$assignment = $this->ideaModel->get($values->wrk_assignment_id);
				if (!$values->name) $values->name = $assignment->name;
				$values->created_by = $this->user->id;
				$values->status = 0;
				$id = $this->model->insert($values);
				$this->flashMessage("Práce byla přidána","success");
				$this->redirect("id",$id);
			}
			catch (Exception $e)
			{
				$this->flashMessage("Přidání práce se nepodařilo.","danger");
				$this->redirect("default");
			}
		}
		else
		{
			$this->flashMessage("Nemáte oprávnění přidávat práce.","danger");
			$this->redirect("default");
		}
	}

	public function actionEdit($id)
	{
		$form = $this["formEdit"];
		$record = $this->model->get($id);
		if ($record)
		{
			if (!($this->user->isInRole("administrator") || ($this->user->id == $record->created_by)))
			{
				$this->flashMessage("Nemáte oprávnění ke změnám nastavení práce " . $record->name,"danger");
				$this->redirect("id",$id);
			}
			elseif ($record->status > 0)
			{
				$this->flashMessage("Práci již nelze editovat.","danger");
				$this->redirect("id",$id);
			}
			else
			{
				$this->setTitle("Editace zadání práce " . $record->name);
				$form["user_id"]->setItems($this->userModel->fromGroupRoleAsArray('student',array($record->user_id)));
				$form["wrk_assignment_id"]->setItems($this->assignmentModel->activeAsArray(array($record->wrk_assignment_id)));
				$form["wrk_set_id"]->setItems($this->setModel->activeAsArray(array($record->wrk_set_id)));
				$form->setDefaults($record);
			}
		}
		else
		{
			$this->flashMessage("Takové zadání práce neexistuje.","danger");
			$this->redirect("default");
		}
	}

	public function editFormSucceeded($form,$values)
	{
		$id = $values->id;
		$record = $this->model->get($id);
		if ($record && ($this->user->isInRole("administrator") || ($this->user->id == $record->created_by)))
		{
			unset($values->id);
			try
			{
				$this->model->update($id,$values);
				$this->flashMessage("Nastavení práce bylo uloženo.","success");
			}
			catch (Exception $e)
			{
				$this->flashMessage("Při ukládání práce došlo k chybě.","danger");
			}
			finally
			{
				$this->redirect("id",$id);
			}
		}
		else
		{
			$this->flashMessage("Nemáte oprávnění editovat toto zadání práce.","danger");
			$this->redirect("id",$id);
		}
	}

	public function actionDelete($id)
	{
		$process = explode(',',$id);
		foreach ($process as $record)
		{
			$selector = $this->model->getClonedSelection();
      $data = $selector->where("wrk_work.id = ".$record)->fetch();
      if ($data)
    	{
        if (!($this->user->isInRole("administrator") || ($this->user->id == $data->created_by)))
        {
          $this->flashMessage("Nemáte oprávnění ke smazání práce " . $data->name . ".","danger");
        }
        elseif ($data->status > 0)
				{
					$this->flashMessage("Práci " . $data->name . " nelze smazat.","danger");
				}
				else
        {
      		try
      		{
      			$this->model->delete($record);
      			$this->flashMessage("Zadání práce " . $data->name . " bylo smazáno.","success");
      		}
      		catch (Exception $e)
      		{
      			$this->flashMessage("Během pokusu o smazání zadání práce " . $$data->name . " došlo k chybě.","danger");
      		}
        }
    	}
    	else
    	{
    		$this->flashMessage("Takové zadání práce neexistuje.","danger");
    	}
		}
		$this->redirect("default");
	}

	public function actionId($id)
	{
		$record = $this->model->get($id);
		if ($record)
		{
			if (($record->status == 0) && (($this->user->isInRole("administrator") || $this->user->id == $record->created_by)))
				$this->template->editable = true;
			else
				$this->template->editable = false;
			$this->template->work = $record;
			$this->template->statuses = $this->statuses;
			$this->template->maker = $this->userModel->get($record->created_by);
		}
		else
		{
				$this->flashMessage("Takové zadání práce neexistuje.","danger");
				$this->redirect("default");
		}
	}

	public function actionApplication($id)
	{
		$record = $this->model->get($id);
		if ($record)
		{
			if (($record->user_id == $this->user->id) || ($this->user->isInRole("teacher")))
			{
				if ($record->application)
				{
					$downloadFileId = $record->application;
				}
				else
				{
					$targetFile = $this->fileModel->reserveStorage($this->user->id,"Přihláška k DP:".$record->name." ({$record->lastname}, {$record->firstname}, {$record->class})".".pdf");
					$this->flashMessage("Byl vytvořen nový soubor s přihláškou. " . $targetFile->filename,"info");
					$this->buildPDFApplication($id,$targetFile->filename);
					$this->model->update($id,array("application" => $targetFile->id));
					$this->fileModel->update($targetFile->id,array("locked" => 1));
					$this->fileModel->refreshData($targetFile->id);
					$downloadFileId = $targetFile->id;
				}
				if (!$this->downloadFile($downloadFileId))
				{
					$this->flashMessage("Soubor není možné uložit.","danger");
				}
				$this->redirect("default");
			}
			else
			{
				$this->flashMessage('Nemáte oprávnění k prohlížení této přihlášky', 'error');
				$this->redirect("default");
			}
		}
		else
		{
			$this->flashMessage("Takové zadání práce neexistuje.","danger");
			$this->redirect("default");
		}
	}

	private function buildPDFApplication($id,$file = null)
	{
		$record = $this->model->get($id);
		if ($record)
		{
			$set = $this->setModel->get($record->wrk_set_id);
			$template = new \Nette\Templating\FileTemplate(__DIR__ . '/../../templates/pdf/workApplication-'.$set->template.'.latte');
		  $template->registerFilter(new \Nette\Latte\Engine);
		  $template->registerHelperLoader('\Nette\Templating\Helpers::loader');
		  $templateHeader = new \Nette\Templating\FileTemplate(__DIR__ . '/../../templates/pdf/generalHeader.latte');
		  $templateHeader->registerFilter(new \Nette\Latte\Engine);
		  $templateHeader->registerHelperLoader('\Nette\Templating\Helpers::loader');
		  $templateOddFooter = new \Nette\Templating\FileTemplate(__DIR__ . '/../../templates/pdf/generalOddFooter.latte');
		  $templateOddFooter->registerFilter(new \Nette\Latte\Engine);
		  $templateOddFooter->registerHelperLoader('\Nette\Templating\Helpers::loader');
			$templateEvenFooter = new \Nette\Templating\FileTemplate(__DIR__ . '/../../templates/pdf/generalEvenFooter.latte');
		  $templateEvenFooter->registerFilter(new \Nette\Latte\Engine);
		  $templateEvenFooter->registerHelperLoader('\Nette\Templating\Helpers::loader');
			$template->date = new \Nette\DateTime();
			$template->work = $record;
			$template->idea = $this->ideaModel->get($record->wrk_assignment_id);
			$template->goals = $this->ideaModel->getGoals($record->wrk_assignment_id);
			$template->outline = $this->ideaModel->getOutline($record->wrk_assignment_id);
			$template->roles = $this->model->getAssignedRolesForPrintApplication($id);
			require("../vendor/mpdf/mpdf/mpdf.php");
		  $mpdf = new \mPDF('', 'A4', 10, 'arial');
			$mpdf->mirrorMargins = true;
		  $mpdf->ignore_invalid_utf8 = true;
		  $mpdf->WriteHTML(file_get_contents('css/pdf.css'),1);
		  $mpdf->SetHTMLHeader($templateHeader->__toString());
		  $mpdf->SetHTMLFooter($templateEvenFooter->__toString(),"E");
			$mpdf->SetHTMLFooter($templateOddFooter->__toString(),"O");
			$mpdf->WriteHTML($template->__toString());
			if($file)
			{
				$mpdf->Output($file,"F");
			}
			else
			{
			  $mpdf->Output();
			}
		}
	}
}
