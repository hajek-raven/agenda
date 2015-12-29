<?php

namespace App\Presenters;

use Nette,
	App\Model;

abstract class GridPresenter extends BasePresenter
{
	public $model;
	public $formFactory;

	public function __construct()
	{
		parent::__construct();
	}

	protected function createComponentGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
    $grid->model = $this->model;

    $operations = array('delete' => 'Odstranit');
		$grid->setOperation($operations, callback($this, 'gridOperationsHandler'))
            ->setConfirm('delete', 'Opravdu chcete smazat všech %i položek?');
		$grid->setDefaultSort(array('name' => 'ASC'));
        return $grid;
	}

	protected function createComponentFormAdd()
	{
		$form = $this->formFactory->create($this->model);
      	$form->onSuccess[] = array($this, 'addFormSucceeded');
		return $form;
	}

	protected function createComponentFormEdit()
	{
		$form = $this->formFactory->create($this->model);
      	$form->onSuccess[] = array($this, 'editFormSucceeded');
		return $form;
	}

	public function gridOperationsHandler($operation,$id)
	{
		if ($id) {
			$row = implode(', ', $id);
			$this->flashMessage("Probíhá operace $operation pro položky: $row", 'info');
			$this->redirect($operation, $row);
		} else {
			$this->flashMessage('Nejsou vybrané žádné položky.', 'warning');
		}
		$this->redirect('default');
	}

	public function renderDefault()
	{

	}

	public function renderId($id)
	{
		$data = $this->model->get($id);
		$this->template->data = $data;
	}

	public function actionDelete($id)
	{
		$process = explode(',',$id);
		foreach ($process as $record)
		{
			try
			{
				$this->model->delete($record);
				$this->flashMessage($this->messages["deleteSuccessfull"],"success");
			}
			catch (Exception $e)
			{
				$this->flashMessage($this->messages["deleteFailed"],"danger");
			}
		}
		$this->redirect("default");
	}

	public function actionAdd()
	{
		$form = $this["formAdd"];
	}

	public function actionEdit($id)
	{
		$form = $this["formEdit"];
		$record = $this->model->get($id);
		$form->setDefaults($record);
	}

	public function addFormSucceeded($form,$values)
	{
		unset($values->id);
		try
		{
			$this->model->insert($values);
			$this->flashMessage($this->messages["addSuccessfull"],"success");
		}
		catch (Exception $e)
		{
			$this->flashMessage($this->messages["addFailed"],"danger");
		}
		//finally
		{
			$this->redirect("default");
		}
	}

	public function editFormSucceeded($form,$values)
	{
		$id = $values->id;
		unset($values->id);
		try
		{
			$this->model->update($id,$values);
			$this->flashMessage($this->messages["editSuccessfull"],"success");
		}
		catch (Exception $e)
		{
			$this->flashMessage($this->messages["editFailed"],"danger");
		}
		//finally
		{
			$this->redirect("default");
		}
	}

	/* General constants */
	protected $messages = array(
		"addSuccessfull" => "Nová data byla přidána úspěšně.",
		"editSuccessfull" => "Data byla změněna úspěšně.",
		"deleteSuccessfull" => "Data byla smazána úspěšně.",
		"addFailed" => "Při přidávání dat došlo k chybě.",
		"editFailed" => "Při ukládání dat došlo k chybě.",
		"deleteFailed" => "Při mazání dat došlo k chybě.",
		);
}
