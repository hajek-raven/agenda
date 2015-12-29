<?php

namespace App\DashboardModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */

class MyPresenter extends \App\Presenters\SecuredPresenter
{
 	/** @var \App\Model\Users @inject */
	public $model;
	/** @var \App\Forms\UserSettingsFormFactory @inject */
	public $userSettingsFormFactory;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Moje možnosti");
	}

	public function renderDefault()
	{

	}

	public function createComponentFormSettings()
	{
		$form = $this->userSettingsFormFactory->create();
      	$form->onSuccess[] = array($this, 'settingsFormSucceeded');
		return $form;
	}	

	public function actionSettings()
	{
		$this->setTitle("Nastavení­");
		$form = $this["formSettings"];
		//$row = $this->model->getSettings($this->user->id);
		//$form->setDefaults($row);
	}
	
	public function settingsFormSucceeded($form,$values)
	{
		$id = $this->user->id;
		$data = $this->model->getSelection()->get($id);
		if($data)
		{
			try
			{
				unset($values->user_id);
				//$this->model->updateSettings($id,$values);
				//$this->user->getIdentity()->settings = $values;
				$this->flashMessage("Nastavení bylo uloženo.","success");
			}
			catch (Exception $e)
			{
				$this->flashMessage("Uložení nastavení se nepodařilo.","danger");	
			}
		}
		else
		{
			$this->flashMessage("Takový uživatel neexistuje.","warning");
		}
		$this->redirect("default");	
	}
}
