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
                    finally {
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
}
