<?php

namespace App\DashboardModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class UsersPresenter extends \App\Presenters\SecuredGridPresenter
{
	/** @var \App\Model\Users @inject */
	public $model;
	/** @var \App\Model\Authenticator\LocalAuthenticator @inject */
	public $localUserModel;
	/** @var \App\Model\Membership @inject */
	public $membershipModel;
	/** @var \App\Forms\UserFormFactory @inject */
	public $formFactory;
	/** @var \App\Forms\PasswordFormFactory @inject */
	public $passwordFormFactory;
	/** @var \App\Forms\SelectLineFormFactory @inject */
	public $selectFormFactory;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Uživatelé");
	}

	protected function createComponentGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
    $grid->model = $this->model;
		$grid->addColumnText('firstname', 'Jméno')->setSortable()->setFilterText();
		$grid->addColumnText('lastname', 'Příjmení')->setSortable()->setFilterText();
		$grid->addColumnText('gender', 'Pohlaví')->setSortable()->setReplacement($grid::$genderReplacements)->setFilterSelect($grid::$genderFilters);
		$grid->addColumnDate('birthdate', 'Narození')->setSortable()->setFilterDate();
		$grid->addColumnText('active', 'Aktivní')->setSortable()->setReplacement($grid::$booleanReplacements)->setFilterSelect($grid::$booleanFilters);
		$grid->addColumnText('enabled', 'Povolený')->setSortable()->setReplacement($grid::$booleanReplacements)->setFilterSelect($grid::$booleanFilters);
		$grid->addActionHref("id","Detail");
		$grid->addActionHref("delete","Odstranit")->setConfirm(function($item) {
						return "Opravdu chcete odstranit uživatele {$item->firstname} {$item->lastname}?";
			});
		$grid->addActionHref('activate', 'Aktivovat')->setDisable(function($item){return ($item->active == 1);});
		$grid->addActionHref('deactivate', 'Deaktivovat')->setDisable(function($item){return ($item->active == 0);});
		$grid->addActionHref('enable', 'Povolit')->setDisable(function($item){return ($item->enabled == 1);});
		$grid->addActionHref('disable', 'Zablokovat')->setDisable(function($item){return ($item->enabled == 0);});
		$operations = array('delete' => 'Odstranit', 'enable' => 'Povolit', 'disable' => 'Zablokovat');
		$grid->setOperation($operations, callback($this, 'gridOperationsHandler'))
            ->setConfirm('delete', 'Opravdu chcete smazat všech %i uživatelů?');
		$grid->setDefaultSort(array('lastname' => 'ASC'));
    return $grid;
	}

	public function createComponentFormPassword()
	{
		$form = $this->passwordFormFactory->create();
      	$form->onSuccess[] = array($this, 'passwordFormSucceeded');
		return $form;
	}

	protected function createComponentAddMembershipForm()
	{
		$form = $this->selectFormFactory->create();
		$form->addClass('ajax');
		$form->addClass('form-inline');
    	$form->onSuccess[] = array($this, 'addMembershipFormSucceeded');
		return $form;
	}

	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

	public function actionAdd()
	{
		$form = $this["formAdd"];
		$this->setTitle("Nový uživatel");
		$form["birthdate"]->setDefaultValue((new \Nette\DateTime())->format("Y-m-d"));
	}

	public function actionEdit($id)
	{
		$form = $this["formEdit"];
		$record = $this->model->get($id);
		if ($record)
		{
			$this->setTitle("Editace uživatele " . $record->lastname . ", " . $record->firstname);
			$form->setDefaults($record);
			$form["birthdate"]->setDefaultValue((new \Nette\DateTime($record->birthdate))->format("Y-m-d"));
		}
		else
		{
			$this->flashMessage("Takový uživatel neexistuje.","danger");
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
					$this->model->update($record,array("enabled" => 1));
					$this->flashMessage("Účet " . $data->lastname . ", " . $data->firstname . " může tuto aplikaci používat.","success");
				}
				else
				{
					$this->flashMessage("Takový účet (ID:".$record.") neexistuje.","warning");
				}
			}
			catch (Exception $e)
			{
				$this->flashMessage("Při povolování účtu došlo k chybě.","danger");
			}
		}
		if (!$this->isAjax())
		{
			$this->redirect('default');
		}      
    	else 
		{
			$this->invalidateControl('flashMessages');
		}
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
					$this->model->update($record,array("enabled" => 0));
					$this->flashMessage("Účet " . $data->lastname . ", " . $data->firstname . " je nyní zablokovaný.","success");
				}
				else
				{
					$this->flashMessage("Takový účet (ID:".$record.") neexistuje.","warning");
				}
			}
			catch (Exception $e)
			{
				$this->flashMessage("Při blokování účtu došlo k chybě.","danger");
			}
		}
		if (!$this->isAjax())
		{
			$this->redirect('default');
		}      
    	else 
		{
			$this->invalidateControl('flashMessages');
		}
	}

	public function actionActivate($id)
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
					$this->flashMessage("Účet " . $data->lastname . ", " . $data->firstname . " je nyní aktivní.","success");
				}
				else
				{
					$this->flashMessage("Takový účet (ID:".$record.") neexistuje.","warning");
				}
			}
			catch (Exception $e)
			{
				$this->flashMessage("Při aktivování účtu došlo k chybě.","danger");
			}
		}
		if (!$this->isAjax())
		{
			$this->redirect('default');
		}      
    	else 
		{
			$this->invalidateControl('flashMessages');
		}
	}

	public function actionDeactivate($id)
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
					$this->flashMessage("Účet " . $data->lastname . ", " . $data->firstname . " je nyní neaktivní.","success");
				}
				else
				{
					$this->flashMessage("Takový účet (ID:".$record.") neexistuje.","warning");
				}
			}
			catch (Exception $e)
			{
				$this->flashMessage("Při blokování účtu došlo k chybě.","danger");
			}
		}
		if (!$this->isAjax())
		{
			$this->redirect('default');
		}      
    	else 
		{
			$this->invalidateControl('flashMessages');
		}
	}

	public function actionPassword($id)
	{
		$form = $this["formPassword"];
		$this->setTitle("Změna hesla");
		$this->template->id = $id;
		$form["id"]->setValue($id);
	}

	public function passwordFormSucceeded($form,$values)
	{
		$id = $values->id;
		unset($values->id);
		$data = $this->model->get($id);
		if($data)
		{
			if($data->email)
			{
				try
				{
					$this->localUserModel->add($id,$data->email,$values->password);
					$this->flashMessage("Heslo bylo nastaveno.","success");
					$this->redirect("id",$id);
				}
				catch (Exception $e)
				{
					$this->flashMessage("Nastavení hesla se nepodařilo.","danger");
				}
			}
			else
			{
				$this->flashMessage("Uživatel nemá nastavený email. Není tak možné ho použít jako přihlašovací jméno.","danger");
			}
		}
		else
		{
			$this->flashMessage("Takový účet neexistuje.","warning");
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
          $this->flashMessage("Nemáte oprávnění ke smazání uživatele " . $data->lastname .",". $data->firstname . ".","danger");
        }
        else
        {
      		try
      		{
      			$this->model->delete($id);
      			$this->flashMessage("Uživatel " . $data->lastname .", ". $data->firstname . " byl smazán.","success");
      		}
      		catch (Exception $e)
      		{
      			$this->flashMessage("Během pokusu o smazání uživatele " . $$data->lastname .",". $data->firstname . " došlo k chybě.","danger");
      		}
        }
    	}
    	else
    	{
    		$this->flashMessage("Takový uživatel neexistuje.","danger");
    	}
		}
		$this->redirect("default");
	}

	public function editFormSucceeded($form,$values)
	{
		$id = $values->id;
		unset($values->id);
		try
		{
			$this->model->update($id,$values);
			$this->flashMessage("Nastavení uživatele bylo uloženo.","success");
		}
		catch (Exception $e)
		{
			$this->flashMessage("Během ukládání dat uživatele došlo k chybě.","danger");
		}
		finally
		{
			$this->redirect("id",$id);
		}
	}
	
	public function actionId($id)
	{
		$data = $this->model->get($id);
		$this->setTitle("Uživatel " . $data->lastname . ", " . $data->firstname);
		$this->template->data = $data;
		$this->template->membership = $this->membershipModel->userIsMember($id);
		$addMembershipForm = $this["addMembershipForm"];
		$addMembershipForm["id"]->setValue($id);
		$addMembershipForm["selection"]->setItems($this->membershipModel->userIsNotMemberOfGroupsAsArray($id));
	}
	
	public function handleKick($user,$group)
  	{
		if ($this->user->isInRole("administrator"))
		{
			$this->membershipModel->out($user,$group);
			$this->flashMessage("Uživatel již není členem skupiny.","success");
		} else {
			$this->flashMessage("Nemáte oprávnění rušit členství uživatelů ve skupinách.","success");
		}
		$this->template->membership = $this->membershipModel->userIsMember($user);
		
    	if ($this->isAjax()) 
		{
			$form = $this["addMembershipForm"];
			$form["selection"]->setItems($this->membershipModel->userIsNotMemberOfGroupsAsArray($user));
			$form["selection"]->setValue(null);
			$this->invalidateControl('addMembershipForm');
      		$this->redrawControl('membershipList');
			$this->redrawControl('flashMessages');
    	}
  	}
	  
	public function addMembershipFormSucceeded($form,$values)
	{	
		$data = $this->model->get($values->id);
		if ($data && ($this->user->isInRole("administrator")))
		{
			try
			{
				$this->membershipModel->in($values->id,$values->selection);
			}
			catch (Exception $e)
			{
				$this->flashMessage("Přidání do skupiny se nepodařilo.","danger");
			}
		}
		else
		{
			$this->flashMessage("Nemáte oprávnění tímto způsobem přidávat někoho do skupiny.","danger");
		}
		if (!$this->isAjax())
		{
			$this->redirect('this');
		}      
    	else 
		{
			$this->template->membership = $this->membershipModel->userIsMember($values->id);
			$this->invalidateControl('membershipList');
			$this->invalidateControl('flashMessages');
        	$form["selection"]->setItems($this->membershipModel->userIsNotMemberOfGroupsAsArray($values->id));
			$form["selection"]->setValue(null);
			$this->invalidateControl('addMembershipForm');
		}
	}
}
