<?php

namespace App\DashboardModule\Presenters;

use Nette,
	App\Model;

class FilesPresenter extends \App\Presenters\SecuredGridPresenter
{

	/** @var \App\Model\Files @inject */
	public $model;
  /** @var \App\Model\Users @inject */
  public $userModel;
	/** @var \App\Forms\UploadFileFormFactory @inject */
	public $uploadFormFactory;
	/** @var \App\Forms\FileFormFactory @inject */
	public $formFactory;

	public function __construct()
	{
		parent::__construct();
        $this->setTitle("Soubory");
	}

	protected function createComponentGrid($name)
    {
        $grid = new \App\Grids\baseGrid($this, $name);
        if ($this->user->isInRole("administrator"))
        {
          $grid->setModel($this->model);
        }
        else
        {
					//$grid->setModel($this->model);
          $grid->setModel($this->model->getSelection()->where("(user_id = " . $this->user->id . " OR public = 1)"));
        }
        $grid->addColumnText('original', 'Název')->setSortable()->setFilterText();
				$grid->addColumnText('extension', 'Typ')->setSortable()->setFilterText();
				$grid->addColumnText('firstname', 'Jméno vlastníka')->setSortable()->setFilterText();
				$grid->addColumnText('lastname', 'Příjmení vlastníka')->setSortable()->setFilterText();
				$grid->addColumnText('public', 'Veřejný')
					->setSortable()
					->setReplacement($grid::$booleanReplacements)
					->setFilterSelect($grid::$booleanFilters);
				$grid->addColumnText('uploaded', 'Nahráno')->setSortable()->setFilterDate();
				$grid->addColumnText('size', 'Velikost')->setSortable()->setFilterNumber();
				$grid->addActionHref("id","Detail");
        $grid->addActionHref("download","Uložit")
				->setDisable(function($item) {
					if (!($this->user->isInRole("administrator") || $this->user->id == $item->user_id || $item->public)) return true;
					return false;
				});
        $grid->addActionHref("delete","Odstranit")
          ->setDisable(function($item) {
            if ($item->locked) return true;
            if (!($this->user->isInRole("administrator") || $this->user->id == $item->user_id)) return true;
            return false;
          })
          ->setConfirm(function($item) {
                return "Opravdu chcete odstranit soubor {$item->original}?";
        	});
        $operations = array('delete' => 'Odstranit');
    		$grid->setOperation($operations, callback($this, 'gridOperationsHandler'))
                ->setConfirm('delete', 'Opravdu chcete smazat všech %i souborů?');
				return $grid;
	}

	protected function createComponentFormUpload()
	{
			$form = $this->uploadFormFactory->create();
			$form->onSuccess[] = array($this, 'addFormSucceeded');
			return $form;
	}

	protected function createComponentFormEdit()
	{
			$form = $this->formFactory->create();
			$form->onSuccess[] = array($this, 'editFormSucceeded');
			return $form;
	}

  public function actionDefault($filter = array())
  {

  }

  public function actionAdd()
  {
  	$form = $this["formEdit"];
  	$this->setTitle("Nový soubor");
  }

  public function actionEdit($id)
  {
  	$form = $this["formEdit"];
  	$record = $this->model->get($id);
  	if ($record)
  	{
      if ($record->locked && !$this->user->isInRole("administrator"))
      {
        $this->flashMessage("Soubor je uzamčen.","danger");
  			$this->redirect("id",$id);
      }
      elseif (!($this->user->isInRole("administrator") || $this->user->id == $record->user_id))
      {
        $this->flashMessage("Nemáte oprávnění měnit vlastnosti tohoto souboru.","danger");
  			$this->redirect("id",$id);
      }
      else
      {
        $this->setTitle("Změna vlastností souboru " . $record->original);
  			$form->setDefaults($record);
      }
  	}
  	else
  	{
  		$this->flashMessage("Takový soubor neexistuje.","danger");
  		$this->redirect("default");
  	}
  }

  public function editFormSucceeded($form,$values)
	{
		$id = $values->id;
    $record = $this->model->get($id);
    if ($record)
  	{
      if ($record->locked && !$this->user->isInRole("administrator"))
      {
        $this->flashMessage("Soubor je uzamčen.","danger");
  			$this->redirect("id",$id);
      }
      elseif (!($this->user->isInRole("administrator") || $this->user->id == $record->user_id))
      {
        $this->flashMessage("Nemáte oprávnění měnit vlastnosti tohoto souboru.","danger");
  			$this->redirect("id",$id);
      }
      else
      {
        unset($values->id);
    		try
    		{
    			$this->model->update($id,$values);
    			$this->flashMessage("Vlastnosti souboru byly uloženy.","success");
    		}
    		catch (Exception $e)
    		{
    			$this->flashMessage("Během ukládání vlastností souboru došlo k chybě.","danger");
    		}
    		finally
    		{
    			$this->redirect("id",$id);
    		}
      }
  	}
  	else
  	{
  		$this->flashMessage("Takový soubor neexistuje.","danger");
  		$this->redirect("default");
  	}
	}

	public function actionDownload($id)
	{
		$record = $this->model->get($id);
		if($record)
		{
      if (!($this->user->isInRole("administrator") || $this->user->id == $record->user_id || $record->public))
      {
        $this->flashMessage("Nemáte oprávnění k uložení souboru.","danger");
      }
      else
      {
				if (!$this->downloadFile($id))
				{
			   $this->flashMessage("Soubor nelze odeslat. Je možné, že fyzicky neexistuje.","danger");
				}
      }
		}
		else
		{
			$this->flashMessage("Neexistuje záznam o takovém souboru.","danger");
		}
		$this->redirect("default");
	}

	public function addFormSucceeded($form,$values)
	{
		unset($values->id);
		try
		{
			$file = $values->file;
			$this->model->storeUploaded($file,$this->user->id);
			$this->flashMessage("Soubor byl úspěšně nahrán.","success");
		}
		catch (Exception $e)
		{
			$this->flashMessage("Během nahrávání souboru došlo k chybě.","danger");
		}
		finally
		{
			$this->redirect("default");
		}
	}

  public function actionDelete($id)
	{
		$process = explode(',',$id);
		foreach ($process as $record)
		{
      $selector = $this->model->getClonedSelection();
      $data = $selector->where("file.id = ".$record)->fetch();
      if ($data)
    	{
        if ($data->locked)
        {
          $this->flashMessage("Soubor je uzamčen.","danger");
        }
        elseif (!($this->user->isInRole("administrator") || $this->user->id == $data->user_id))
        {
          $this->flashMessage("Nemáte oprávnění ke smazání souboru " . $data->original,"danger");
        }
        else
        {
      		try
      		{
      			$this->model->delete($record);
      			$this->flashMessage("Soubor " . $data->original . " byl smazán.","success");
      		}
      		catch (Exception $e)
      		{
      			$this->flashMessage("Během pokusu o smazání souboru " . $data->original . " došlo k chybě.","danger");
      		}
        }
    	}
    	else
    	{
    		$this->flashMessage("Takový soubor neexistuje.","danger");
    	}
		}
		$this->redirect("default");
	}

	public function renderId($id)
	{
		$record = $this->model->get($id);
		if($record)
		{
			$this->template->data = $record;
			$this->template->exists = $this->model->exists($id);
			$this->template->location = $this->model->getStoragePath() . $id;
		}
		else
		{
			$this->flashMessage("Neexistuje záznam o takovém souboru.","danger");
			$this->redirect("default");
		}
	}

	public function actionRefreshFileData($id)
	{
		$record = $this->model->get($id);
		if($record)
		{
			$this->model->refreshData($id);
			$this->flashMessage("Data souboru byla aktualizována.","success");
		}
		else
		{
			$this->flashMessage("Neexistuje záznam o takovém souboru.","danger");
		}
		$this->redirect("id",$id);
	}
}
