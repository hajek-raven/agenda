<?php

namespace App\WorksModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class IdeasPresenter extends \App\Presenters\SecuredGridPresenter
{
	/** @var \App\Model\Work\Ideas @inject */
	public $model;
	/** @var \App\Forms\Work\IdeaFormFactory @inject */
	public $formFactory;
	/** @var \App\Forms\NewLineFormFactory @inject */
	public $lineFormFactory;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Náměty prací");
	}

	protected function createComponentGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
    $grid->model = $this->model;
		$grid->addColumnText('name', 'Název')->setSortable()->setFilterText()->setColumn("wrk_assignment.name");
		$grid->addColumnText('subject', 'Předmět')->setSortable()->setFilterText();
		$grid->addColumnText('works', 'Počet prací')->setSortable();
		$grid->addColumnText('firstname', 'Jméno autora')->setSortable()->setFilterText();
		$grid->addColumnText('lastname', 'Příjmení autora')->setSortable()->setFilterText();
		$grid->addColumnText('active', 'Aktivní')->setSortable()
			->setReplacement($grid::$booleanReplacements)
			->setFilterSelect($grid::$booleanFilters)
			->setColumn("wrk_assignment.active");
		$grid->addActionHref("id","Detail");
		if ($this->user->isAllowed("Works:Ideas",'clone'))
		{
			$grid->addActionHref("clone","Kopie")->setConfirm(function($item) {
							return "Opravdu chcete vytvořit kopii námětu {$item->name}?";
				});
		}
		$grid->addActionHref("delete","Odstranit")->setConfirm(function($item) {
						return "Opravdu chcete odstranit námět {$item->name}?";
			})
			->setDisable(function($item)
				{
					if ($item->works > 0) return true;
					if (!($this->user->isInRole("administrator") || $this->user->id == $item->user_id)) return true;
					return false;
				});
		$grid->addActionHref('enable', 'Povolit')->setDisable(function($item)
			{
				if ($item->active == 1) return true;
				if (!($this->user->isInRole("administrator") || ($this->user->id == $item->user_id))) return true;
				return false;
			});
		$grid->addActionHref('disable', 'Zablokovat')->setDisable(function($item)
		{
			if ($item->active == 0) return true;
			if (!($this->user->isInRole("administrator") || ($this->user->id == $item->user_id))) return true;
			return false;
		});
		$operations = array('delete' => 'Odstranit', 'enable' => 'Povolit', 'disable' => 'Zablokovat');
		$grid->setOperation($operations, callback($this, 'gridOperationsHandler'))
            ->setConfirm('delete', 'Opravdu chcete smazat všech %i zadání?');
		$grid->setDefaultSort(array('name' => 'ASC'));
    return $grid;
	}

	protected function createComponentNewGoalForm()
	{
		$form = $this->lineFormFactory->create();
		$form->addClass('ajax');
		$form->addClass('form-inline');
    $form->onSuccess[] = array($this, 'addGoalFormSucceeded');
		return $form;
	}

	protected function createComponentNewOutlineForm()
	{
		$form = $this->lineFormFactory->create();
		$form->addClass('ajax');
		$form->addClass('form-inline');
    $form->onSuccess[] = array($this, 'addOutlineFormSucceeded');
		return $form;
	}

	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

	public function actionAdd()
	{
		$form = $this["formAdd"];
		$this->setTitle("Nový námět");
		$form["active"]->setDefaultValue(true);
		$form["coworkers"]->setDefaultValue(1);
	}

	public function actionEdit($id)
	{
		$form = $this["formEdit"];
		$record = $this->model->get($id);
		if ($record)
		{
			if (!($this->user->isInRole("administrator") || ($this->user->id == $record->user_id)))
			{
				$this->flashMessage("Nemáte oprávnění ke změnám námětu " . $record->name,"danger");
				$this->redirect("id",$id);
			}
			elseif ($record->works > 0)
			{
				$this->flashMessage("Námět nelze editovat, protože od něj již existují odvozené práce.","danger");
				$this->redirect("id",$id);
			}
			else
			{
				$this->setTitle("Editace zadání " . $record->name);
				$form->setDefaults($record);
				$this->template->id = $record->id;
			}
		}
		else
		{
			$this->flashMessage("Takový námět neexistuje.","danger");
			$this->redirect("default");
		}
	}

	public function actionEnable($id)
	{
		$process = explode(',',$id);
		foreach ($process as $record)
		{
			try
			{
				$selector = $this->model->getClonedSelection();
				$data = $selector->where("wrk_assignment.id = ".$record)->fetch();
				if($data)
				{
					if ($this->user->isInRole("administrator") || ($this->user->id == $data->user_id))
					{
						$this->model->update($record,array("active" => 1));
						$this->flashMessage("Námět " . $data->name . " je nyní aktivní.","success");
					}
					else {
						$this->flashMessage("Nemáte oprávnění manipulovat s námětem " . $data->name ,"danger");
					}
				}
				else
				{
					$this->flashMessage("Takový námět (ID:".$record.") neexistuje.","warning");
				}
			}
			catch (Exception $e)
			{
				$this->flashMessage("Při aktivování námětu došlo k chybě.","danger");
			}
		}
		$this->redirect("default");
	}

	public function actionDisable($id)
	{
		$process = explode(',',$id);
		foreach ($process as $record)
		{
			try
			{
				$selector = $this->model->getClonedSelection();
				$data = $selector->where("wrk_assignment.id = ".$record)->fetch();
				if($data)
				{
					if ($this->user->isInRole("administrator") || ($this->user->id == $data->user_id))
					{
						$this->model->update($record,array("active" => 0));
						$this->flashMessage("Námět " . $data->name . " nelze vybrat pro vytvoření práce.","success");
					}
					else {
						$this->flashMessage("Nemáte oprávnění manipulovat s námětem " . $data->name ,"danger");
					}
				}
				else
				{
					$this->flashMessage("Takový námět (ID:".$record.") neexistuje.","warning");
				}
			}
			catch (Exception $e)
			{
				$this->flashMessage("Při blokování námětu došlo k chybě.","danger");
			}
		}
		$this->redirect("default");
	}

	public function actionDelete($id)
	{
		$process = explode(',',$id);
		foreach ($process as $record)
		{
			$selector = $this->model->getClonedSelection();
      $data = $selector->where("wrk_assignment.id = ".$record)->fetch();
      if ($data)
    	{
        if (!($this->user->isInRole("administrator") || ($this->user->id == $data->user_id)))
        {
          $this->flashMessage("Nemáte oprávnění ke smazání námětu " . $data->name . ".","danger");
        }
        elseif ($data->works > 0)
				{
					$this->flashMessage("Námět " . $data->name . " nelze smazat, protože již existují od něj odvozené práce.","danger");
				}
				else
        {
      		try
      		{
      			$this->model->delete($record);
      			$this->flashMessage("Námět " . $data->name . " byl smazán.","success");
      		}
      		catch (Exception $e)
      		{
      			$this->flashMessage("Během pokusu o smazání zadání " . $$data->name . " došlo k chybě.","danger");
      		}
        }
    	}
    	else
    	{
    		$this->flashMessage("Takový námět neexistuje.","danger");
    	}
		}
		$this->redirect("default");
	}

	public function addFormSucceeded($form,$values)
	{
		unset($values->id);
		$values->user_id = $this->user->id;
		try
		{
			$id = $this->model->insert($values);
			$this->flashMessage("Základ námětu byl vytvořen. Nezapomeňte k zadání přidat také nějaké cíle a body osnovy.","success");
			$this->redirect("id",$id);
		}
		catch (Exception $e)
		{
			$this->flashMessage("Vytvoření základu námětu se nepodařilo.","danger");
			$this->redirect("default");
		}
	}

	public function actionId($id)
	{
		$record = $this->model->get($id);
		if ($record)
		{
			if (($record->works == 0) && ($this->user->isInRole("administrator") || ($this->user->id == $record->user_id)))
				$this->template->editable = true;
			else
				$this->template->editable = false;
			$this->template->idea = $record;
			$this->setTitle("Námět " . $record->name);
			$this->template->goals = $this->model->getGoals($id);
			$this->template->outline = $this->model->getOutline($id);
			$addGoalForm = $this["newGoalForm"];
			$addGoalForm["id"]->setValue($id);
			$addOutlineForm = $this["newOutlineForm"];
			$addOutlineForm["id"]->setValue($id);
		}
		else
		{
				$this->flashMessage("Takový námět neexistuje.","danger");
				$this->redirect("default");
		}
	}

	public function actionClone($id)
	{
		$record = $this->model->get($id);
		if ($record)
		{
			$newId = $this->model->makeClone($id);
			$this->model->update($newId,array("user_id" => $this->user->id, "active" => 1));
			$this->flashMessage("Byla vytvořena nová kopie námětu {$record->name}.","success");
			$this->redirect("id",$newId);
		}
		else
		{
				$this->flashMessage("Takový námět neexistuje.","danger");
				$this->redirect("default");
		}
	}

	public function handleRefreshGoal($id)
  	{
		$this->template->goals = $this->model->getGoals($id);
    	if ($this->isAjax()) 
		{
      		$this->redrawControl('goalList');
			$this->redrawControl('flashMessages');
    	}
  	}

	public function handleDeleteGoal($id,$order)
	{
		$data = $this->model->get($id);
		if ($data && ($data->works == 0) && ($this->user->isInRole("administrator") || ($this->user->id == $data->user_id)))
		{
			try {
				$this->model->deleteGoal($id,$order);
				$this->flashMessage("Cíl $order byl odstraněn.","success");
			} catch (Exception $e) {
				$this->flashMessage("Odstranění cíle $order se nepodařilo.","success");
			}
		}
		else
		{
			$this->flashMessage("Cíl nelze smazat.","danger");
		}
		$this->template->goals = $this->model->getGoals($id);
		if ($this->isAjax()) {
      $this->redrawControl('goalList');
			$this->redrawControl('flashMessages');
    }
	}

	public function handleUpGoal($id,$order)
  {
		$data = $this->model->get($id);
		if ($data && ($data->works == 0) && ($this->user->isInRole("administrator") || ($this->user->id == $data->user_id)))
		{
			$this->model->upGoal($id,$order);
		}
		$this->template->goals = $this->model->getGoals($id);
    if ($this->isAjax()) {
      $this->redrawControl('goalList');
			$this->redrawControl('flashMessages');
    }
  }

	public function handleDownGoal($id,$order)
  {
		$data = $this->model->get($id);
		if ($data && ($data->works == 0) && ($this->user->isInRole("administrator") || ($this->user->id == $data->user_id)))
		{
			$this->model->downGoal($id,$order);
		}
		$this->template->goals = $this->model->getGoals($id);
    if ($this->isAjax()) {
      $this->redrawControl('goalList');
			$this->redrawControl('flashMessages');
    }
  }

	public function handleRefreshOutline($id)
  {
		$this->template->outline = $this->model->getOutline($id);
    if ($this->isAjax()) {
      $this->redrawControl('outlineList');
			$this->redrawControl('flashMessages');
    }
  }

	public function handleDeleteOutline($id,$order)
	{
		$data = $this->model->get($id);
		if ($data && ($data->works == 0) && ($this->user->isInRole("administrator") || ($this->user->id == $data->user_id)))
		{
			try {
				$this->model->deleteOutline($id,$order);
				$this->flashMessage("Bod osnovy $order byl odstraněn.","success");
			} catch (Exception $e) {
				$this->flashMessage("Odstranění bodu osnovy $order se nepodařilo.","success");
			}
		}
		else
		{
			$this->flashMessage("Bod osnovy nelze smazat.","danger");
		}
		$this->template->outline = $this->model->getOutline($id);
		if ($this->isAjax()) {
      $this->redrawControl('outlineList');
			$this->redrawControl('flashMessages');
    }
	}

	public function handleUpOutline($id,$order)
  {
		$data = $this->model->get($id);
		if ($data && ($data->works == 0) && ($this->user->isInRole("administrator") || ($this->user->id == $data->user_id)))
		{
			$this->model->upOutline($id,$order);
		}
		$this->template->outline = $this->model->getOutline($id);
    if ($this->isAjax()) {
      $this->redrawControl('outlineList');
			$this->redrawControl('flashMessages');
    }
  }

	public function handleDownOutline($id,$order)
  {
		$data = $this->model->get($id);
		if ($data && ($data->works == 0) && ($this->user->isInRole("administrator") || ($this->user->id == $data->user_id)))
		{
			$this->model->downOutline($id,$order);
		}
		$this->template->outline = $this->model->getOutline($id);
    if ($this->isAjax()) {
      $this->redrawControl('outlineList');
			$this->redrawControl('flashMessages');
    }
  }

	public function addGoalFormSucceeded($form,$values)
	{
		$data = $this->model->get($values->id);
		if ($data && ($data->works == 0) && ($this->user->isInRole("administrator") || ($this->user->id == $data->user_id)))
		{
			try
			{
				$this->model->addGoal($values->id,$values->description);
			}
			catch (Exception $e)
			{
				$this->flashMessage("Přidání cíle se nepodařilo.","danger");
			}
		}
		else
		{
			$this->flashMessage("Cíl nelze přidat.","danger");
		}
		if (!$this->isAjax())
        $this->redirect('this');
    else {
			  $this->template->goals = $this->model->getGoals($values->id);
        $this->invalidateControl('goalList');
				$this->invalidateControl('goalForm');
				$this->invalidateControl('flashMessages');
        $form->setValues(array(), TRUE);
    }
	}

	public function addOutlineFormSucceeded($form,$values)
	{
		$data = $this->model->get($values->id);
		if ($data && ($data->works == 0) && ($this->user->isInRole("administrator") || ($this->user->id == $data->user_id)))
		{
			try
			{
				$this->model->addOutline($values->id,$values->description);
			}
			catch (Exception $e)
			{
				$this->flashMessage("Přidání bodu osnovy se nepodařilo.","danger");
			}
		}
		else
		{
			$this->flashMessage("Bod osnovy nelze přidat.","danger");
		}
		if (!$this->isAjax())
        $this->redirect('this');
    else {
			  $this->template->outline = $this->model->getOutline($values->id);
        $this->invalidateControl('outlineList');
				$this->invalidateControl('outlineForm');
				$this->invalidateControl('flashMessages');
        $form->setValues(array(), TRUE);
    }
	}
}
