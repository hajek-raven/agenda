<?php

namespace App\WorksModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class SetsPresenter extends \App\Presenters\SecuredGridPresenter
{
	/** @var \App\Model\Work\Sets @inject */
	public $model;
	/** @var \App\Forms\Work\SetFormFactory @inject */
	public $formFactory;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Sady prací");
	}

	protected function createComponentGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
    $grid->model = $this->model;
		$grid->addColumnText('name', 'Název')->setSortable()->setFilterText();
		$grid->addColumnText('active', 'Aktivní')->setSortable()->setReplacement($grid::$booleanReplacements)->setFilterSelect($grid::$booleanFilters);
		$grid->addActionHref("id","Detail");
		$grid->addActionHref("delete","Odstranit")->setConfirm(function($item) {
						return "Opravdu chcete odstranit sadu {$item->name}?";
			});
		$grid->addActionHref("edit","Editovat");
		$grid->addActionHref('enable', 'Povolit')->setDisable(function($item){return ($item->active == 1);});
		$grid->addActionHref('disable', 'Zablokovat')->setDisable(function($item){return ($item->active == 0);});
		$operations = array('delete' => 'Odstranit', 'enable' => 'Povolit', 'disable' => 'Zablokovat');
		$grid->setOperation($operations, callback($this, 'gridOperationsHandler'))
            ->setConfirm('delete', 'Opravdu chcete smazat všech %i sad?');
		$grid->setDefaultSort(array('name' => 'ASC'));
    return $grid;
	}

	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

	public function actionAdd()
	{
		$form = $this["formAdd"];
		$this->setTitle("Nová sada prací");
	}

	public function actionEdit($id)
	{
		$form = $this["formEdit"];
		$record = $this->model->get($id);
		if ($record)
		{
			$this->setTitle("Editace sady " . $record->name);
			$form->setDefaults($record);
		}
		else
		{
			$this->flashMessage("Taková sada neexistuje.","danger");
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
				$data = $selector->where("id = ".$record)->fetch();
				if($data)
				{
					$this->model->update($record,array("active" => 1));
					$this->flashMessage("Sada " . $data->name . " je nyní aktivní.","success");
				}
				else
				{
					$this->flashMessage("Taková sada (ID:".$record.") neexistuje.","warning");
				}
			}
			catch (Exception $e)
			{
				$this->flashMessage("Při aktivování sady došlo k chybě.","danger");
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
				$data = $selector->where("id = ".$record)->fetch();
				if($data)
				{
					$this->model->update($record,array("active" => 0));
					$this->flashMessage("Sada " . $data->name . " je nyní neaktivní.","success");
				}
				else
				{
					$this->flashMessage("Taková sada (ID:".$record.") neexistuje.","warning");
				}
			}
			catch (Exception $e)
			{
				$this->flashMessage("Při deaktivování sady došlo k chybě.","danger");
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
      $data = $selector->where("id = ".$record)->fetch();
      if ($data)
    	{
        if (!$this->user->isInRole("administrator"))
        {
          $this->flashMessage("Nemáte oprávnění ke smazání sady " . $data->name . ".","danger");
        }
        else
        {
      		try
      		{
      			$this->model->delete($record);
      			$this->flashMessage("Sada " . $data->name . " byla smazána.","success");
      		}
      		catch (Exception $e)
      		{
      			$this->flashMessage("Během pokusu o smazání sady " . $$data->name . " došlo k chybě.","danger");
      		}
        }
    	}
    	else
    	{
    		$this->flashMessage("Taková sada neexistuje.","danger");
    	}
		}
		$this->redirect("default");
	}
}
