<?php

namespace App\DashboardModule\Presenters;

use Nette,
	App\Model;

/**
 * Homepage presenter.
 */
class GroupsPresenter extends \App\Presenters\SecuredGridPresenter
{
	/** @var \App\Model\Groups @inject */
	public $model;
	/** @var \App\Model\Membership @inject */
	public $membershipModel;
    /** @var \App\Model\Users @inject */
    public $userModel;
	/** @var \App\Forms\GroupFormFactory @inject */
	public $formFactory;
	/** @var \App\Forms\SelectLineFormFactory @inject */
	public $selectFormFactory;

	public function __construct()
	{
		parent::__construct();
		$this->setTitle("Skupiny");
	}

	protected function createComponentGrid($name)
	{
  	$grid = new \App\Grids\baseGrid($this, $name);
    $grid->model = $this->model;
		$grid->addColumnText('name', 'Název')->setSortable()->setFilterText();
		$grid->addColumnText('firstname', 'Jméno správce')->setSortable()->setFilterText();
		$grid->addColumnText('lastname', 'Příjmení správce')->setSortable()->setFilterText();
		$grid->addColumnText('members', 'Členové')->setSortable()->setFilterNumber();
		$grid->addColumnText('active', 'Aktivní')->setSortable()->setReplacement($grid::$booleanReplacements)->setFilterSelect($grid::$booleanFilters);
		$grid->addColumnText('open', 'Otevřená')->setSortable()->setReplacement($grid::$booleanReplacements)->setFilterSelect($grid::$booleanFilters);
		$grid->addColumnText('visible_all', 'Viditelná')->setSortable()->setReplacement($grid::$booleanReplacements)->setFilterSelect($grid::$booleanFilters);
		$grid->addColumnText('visible_members', 'Viditelná členy')->setSortable()->setReplacement($grid::$booleanReplacements)->setFilterSelect($grid::$booleanFilters);
		$grid->addColumnText('list_all', 'Veřejný seznam')->setSortable()->setReplacement($grid::$booleanReplacements)->setFilterSelect($grid::$booleanFilters);
		$grid->addColumnText('list_members', 'Privátní seznam')->setSortable()->setReplacement($grid::$booleanReplacements)->setFilterSelect($grid::$booleanFilters);
		$grid->addColumnText('member', 'Jsem členem?')->setSortable()->setReplacement($grid::$booleanReplacements)->setFilterSelect($grid::$booleanFilters);
		$grid->addActionHref("id","Detail");
        $grid->addActionHref("delete","Odstranit")
            ->setDisable(function($item) {
							if ($item->permanent) return true;
							if (!($this->user->isInRole("administrator") || $this->user->id == $item->user_id)) return true;
							return false;
						})
            ->setConfirm(function($item) {
                return "Opravdu chcete odstranit skupinu {$item->name}?";
        	});
        $grid->addActionHref("out","Opustit")
            ->setDisable(function($item) {return !($item->open && $item->member);});
        $grid->addActionHref("in","Přidat se")
            ->setDisable(function($item) {return !($item->open && !$item->member);});
		$operations = array(/*'delete' => 'Odstranit'*/);
		$grid->setOperation($operations, callback($this, 'gridOperationsHandler'))
            ->setConfirm('delete', 'Opravdu chcete smazat všech %i položek?');
		$grid->setDefaultSort(array('name' => 'ASC'));
    return $grid;
	}
    
    protected function createComponentGridMembers($name)
    {
        $grid = new \App\Grids\baseGrid($this, $name);
        $grid->model = $this->membershipModel;
        $grid->addColumnText('firstname', 'Jméno')->setSortable()->setFilterText();
        $grid->addColumnText('lastname', 'Příjmení')->setSortable()->setFilterText();     
        return $grid; 
    } 

    protected function createComponentFormAdd()
    {
        if ($this->user->isInRole('administrator'))
            $form = $this->formFactory->create($this->model,$this->userModel->activeUsersAsArray());
        else
            $form = $this->formFactory->create($this->model,$this->userModel->listedUsersAsArray(array($this->user->id)));
        $form->onSuccess[] = array($this, 'addFormSucceeded');
        return $form;
    }

    protected function createComponentFormEdit()
    {
        $form = $this->formFactory->create($this->model,$this->userModel->activeUsersAsArray());
        $form->onSuccess[] = array($this, 'editFormSucceeded');
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

	public function actionDefault()
	{
		$grid = $this["grid"];
		if ($this->user->isInRole("administrator"))
		{
				$grid->model = $this->model;
		}
		else
		{
				$grid->model = $this->model->getSelection()->having("(visible_all = 1) OR (visible_members = 1 AND member = 1) OR (group.user_id = {$this->user->id})");
		}
	}

  public function actionDelete($id)
  {
    $record = $this->model->get($id);
    if ($record)
    {
	   if ($record->permanent)
	       $this->flashMessage("Skupinu ".$record->name." není možné smazat.","danger");
	   elseif (!$this->user->isInRole("administrator") && !($record->user_id == $this->user->id))
       {
            $this->flashMessage("Ke smazání skupiny ".$record->name." nejste oprávněn.","danger");
       }
       else
       {
            try {
                $this->model->delete($id);
                $this->flashMessage("Smazání skupiny ".$record->name." proběhlo v pořádku.","success");
            }
            catch (Exception $e) {
                $this->flashMessage("Smazání skupiny ".$record->name." se nepodařilo.","danger");
            }
            //finally 
            {
                $this->redirect("default");
            }
       }
   }
   else
   {
        $this->flashMessage("Skupina ".$id." neexistuje.","danger");
   }
   $this->redirect("default");
 }

    public function actionId($id)
    {
        $record = $this->model->get($id);
        if ($record)
        {
            $this->title = "Skupina " . $record["name"];
			if ($this->user->isInRole("administrator") || ($this->user->id == $record->user_id))
				$this->template->editable = true;
			else
				$this->template->editable = false;
            if ($this->user->isInRole("administrator") || ($this->user->id == $record->user_id) || ($record->list_all) || ($record->list_members && ($record->member == 1)))
            {
                $this->template->listVisible = true;
                $grid = $this["gridMembers"];
                $grid->model = $this->membershipModel->getSelection()->where(array('membership.group_id' => $id));
                if (($this->user->isInRole("administrator") || $this->user->id == $record->user_id))
                {
                    $grid->addActionHref("remove","Odstranit",null,array("group" => $id))->setConfirm(function($item) {
                        return "Opravdu chcete odstranit člena {$item->firstname} {$item->lastname}?";
                        });                     
                }
            }    
            else
            {
                $this->template->listVisible = false;
            }
            if ($this->user->isInRole("administrator") || ($this->user->id == $record->user_id))
            {
                $this->template->listManageable = true;
                $addMembershipForm = $this["addMembershipForm"];
                $addMembershipForm["id"]->setValue($id);
		        $addMembershipForm["selection"]->setItems($this->membershipModel->usersAreNotMembersOfGroupAsArray($id));
            }
            else
            {
                $this->template->listManageable = false;
            }
            $this->template->group = $record;
        }
        else
        {
            $this->flashMessage("Skupina ".$id." neexistuje.","danger");
            $this->redirect("default");
        }
    }

    public function actionIn($id)
    {
        $record = $this->model->get($id);
        if ($record)
        {
            if(($this->user->isInRole("administrator") || $record->open) && !$record->member)
            {
                try
                {
                    $this->membershipModel->in($this->user->id,$id);
                    $this->flashMessage("Jste novým členem skupiny " . $record->name . ".","success");
                }
                catch (Exception $e)
                {
                    $this->flashMessage("Zápis do skupiny se nepodařil.","danger");
                }
            }
            else
            {
                $this->flashMessage("Do této skupiny se memůžete přidat.","danger");
            }
        }
        else
        {
            $this->flashMessage("Taková skupina neexistuje.","danger");
        }
        $this->redirect("default");
    }

		public function actionOut($id)
    {
        $record = $this->model->get($id);
        if ($record)
        {
            if(($this->user->isInRole("administrator") || $record->open) && $record->member)
            {
                try
                {
                    $this->membershipModel->out($this->user->id,$id);
                    $this->flashMessage("Skupinu " . $record->name . " jste opustil.","success");
                }
                catch (Exception $e)
                {
                    $this->flashMessage("Odchod ze skupiny se nepodařil.","danger");
                }
            }
            else
            {
                $this->flashMessage("Z této skupiny nemůžete odejít.","danger");
            }
        }
        else
        {
            $this->flashMessage("Taková skupina neexistuje.","danger");
        }
        $this->redirect("default");
    }

    public function actionAdd()
    {
        $form = $this["formAdd"];
        $this->title = "Nová skupina";
    }
    
    public function addFormSucceeded($form,$values)
    {
        try
        {
            unset($values->id);
            $id = $this->model->insert($values);
            $this->flashMessage("Skupina byla přidána úspěšně.","success");           
        }
        catch (Exception $e)
        {
            $this->flashMessage("Přidání skupiny se nepodařilo.","danger"); 
        }
        //finally
        {
            $this->redirect("id",$id);
        }       
    }
    
    public function actionEdit($id)
    {
        $record = $this->model->get($id);
        $this->title = "Editace skupiny " . $record["name"];
        if ($record)
        {
            if (!$this->user->isInRole("administrator") && !($record->user_id == $this->user->id))
            {
                $this->flashMessage("K editaci skupiny ".$record->name." nejste oprávněn.","danger");
                $this->redirect("id",$id);
            }
            else
            {
                $this->template->id = $id;
                $form = $this["formEdit"];
                if ($record->user_id) $form["user_id"]->setItems($this->userModel->activeUsersAsArray(array($record->user_id)));
                $form->setDefaults($record);               
            }         
        }
        else
        {
            $this->flashMessage("Skupina ".$id." neexistuje.","danger");
            $this->redirect("default");    
        }        
    } 
    
	public function editFormSucceeded($form,$values)
	{
		$id = $values->id;
		unset($values->id);
        $record = $this->model->get($id);
        if ($record && ($this->user->isInRole("administrator") || ($record->user_id == $this->user->id)))
        {
			try
		    {
               $this->model->update($id,$values);
			   $this->flashMessage("Nastavení skupiny bylo uloženo.","success");
		     }
		     catch (Exception $e)
		     {
			     $this->flashMessage("Při ukládání dat došlo k chybě.","danger");
		     }
             //finally 
             {
                 $this->redirect("id",$id); 
             }
                        
        }
        else
		{
            $this->flashMessage("Skupina neexistuje nebo nemáte právo měnit její nastavení.","danger");
			$this->redirect("default");
		}
	}
    
    public function actionRemove($id,$group)
    {
        $record = $this->model->getSelection()->get($group);
        if ($record)
        {
            if ($this->user->isInRole("administrator") || ($this->user->id == $record->user_id))
            {
                try
                {
                    $this->membershipModel->out($id,$group);
                    $this->flashMessage("Uživatel již není členem skupiny.","success");
                }
                catch (Exception $e) {
                    $this->flashMessage("Odstranění uživatele ze skupiny se nepodařilo.","danger");
                }
            }
            else
            {
                $this->flashMessage("K odstraňování členů skupiny '".$id."' nemáte oprávnění.","danger");
            }
        }
        else
        {
            $this->flashMessage("Skupina ".$id." neexistuje.","danger");
            $this->redirect("default");
        }
        $this->redirect("id",$group);
    } 
    
	public function addMembershipFormSucceeded($form,$values)
	{	
		$data = $this->model->get($values->id);
		if ($data && ($this->user->isInRole("administrator") || ($this->user->id == $data->user_id)))
		{
			try
			{
				$this->membershipModel->in($values->selection,$values->id);
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
		$this->redirect('id',$values->id);
	}   
}
