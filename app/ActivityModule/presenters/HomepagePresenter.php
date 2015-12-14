<?php
/**
 * Created by PhpStorm.
 * User: ondra
 * Date: 14.10.15
 * Time: 10:12
 */

namespace App\ActivityModule\Presenters;

use App\Presenters\BaseActivityPresenter;
use Nette,
    App\Model;

class HomepagePresenter extends BaseActivityPresenter
{

   // /** @var \App\Forms\Activity\NewActivityFormFactory @inject */
   // public $newFormFactory;

    //private $pkHead;
    private $editable = false;
    public function __construct()
    {
        parent::__construct();
    }
    public function startup(){
        parent::startup();

    }
    public function actionDefault(){
        $this->setTitle("Systém pro odměňování zaměstnanců");

        $pkHead = $this->model->getPkUser($this->user->id);

       // dump($this->usersmodel->getUsersInPk($pkHead['pk_id']));

    }
    public function renderDefault(){
        $this->template->anyVariable = 'any value';
    }

    public function actionAddact($id = null)
    {
        if($id == null)
            $this->setTitle("Nová činnost");
        else{
            $this->editable = true;
            $act = $this->model->getEditableActivity($id);
            if($this->model->isWorksPkHead($act->act_notebooks_id, $this->user->id)) {
                if ($act->approval == 1){
                    $this->flashMessage($this->messages["uneditable"], "danger");
                    $this->redirect("Homepage:");
                }
            }
            else{
                if($act->approval == 1 || $act->preapproval == 1) {
                    $this->flashMessage($this->messages["uneditable"], "danger");
                    $this->redirect("Homepage:");
                }
            }
            $this->setTitle("Editace činnosti");
        }
      //  $form = $this["addActForm"];

    }


    public function actionApprove($id)
    {
        $this->setTitle("Schválení činnosti");
        $form = $this["approvalForm"];
        $record = $this->model->get($id);
        $this->template->record = $record;
        $pkHead = $this->model->isWorksPkHead($record->act_notebooks_id, $this->user->id);

        $this->pkHead = $pkHead;

        if ($record)
        {
            //to pk head je spatne!!!, nevztahuje se k praci - už je asi správně
            //
            if(!($this->user->isInRole("manager") || $this->model->isWorksPkHead($record->act_notebooks_id, $this->user->id)))
            {
                $this->flashMessage("Nemáte oprávnění ke schvalování činnosti " . $record->name,"danger");
               // $this->redirect("default");
            }
            else
            {
                $this->setTitle("Schvalování činnosti " . $record->name);
                $form->setDefaults($record);
                $this->template->id = $record->id;
                $this->template->record = $record;
            }
        }
        else
        {
            $this->flashMessage("Takova cinnost neexistuje.","danger");
            $this->redirect("default");
        }
    }
    protected function createComponentApprovalForm()
        {
        $form = new \App\Forms\BaseForm();
        $form->addHidden('id');
        $form->addTextArea('note', 'Poznámka');
        if ($this->user->isInRole("manager")) {
            $form->addText('reward', 'Odměna');
            $form->addCheckbox('approval', 'Schválení');
        }
        else
            $form->addCheckbox('preapproval', 'Schválení');
        $form->addSubmit('send', 'Uložit');
            $form->onSuccess[] = array($this, 'approvalFormSucceeded');
        return $form;
    }
    public function approvalFormSucceeded($form, $values)
    {
        $id = $values->id;
        unset($values->id);
        try
        {
            $this->model->update($id, $values);
            $this->flashMessage($this->messages["approveSuccessful"], "success");

        }
        catch (Exception $e)
        {
            $this->flashMessage($this->messages["approveFailed"],"danger");
        }
        //finally
        {
            $this->redirect("default");
        }

    }
    protected function createComponentAddActForm($name)
    {

        if($this->editable) {
            $record = $this->model->getEditableActivity($this->getParameter("id"));
            $pkHeadEditsAct = $record->user_id != $this->user->id;
        }
        else {
            $record = null;
            $pkHeadEditsAct = true;
        }
        $pkHead = $this->model->getPkUser($this->user->id);

        $form = new \App\Forms\BaseForm();
        $form->addHidden('id');
        if ($pkHead != null && $pkHeadEditsAct)
                $form->addSelect('user_id', 'Uživatel', $this->usersmodel->getUsersInPk($pkHead['pk_id']));
        $form->addText('name', 'Název')->setRequired();
        $form->addTextArea('description', 'Popis')->setRequired();
        $form->addText('timeInM', 'Čas (m)')->addRule(\Nette\Forms\Form::INTEGER, "Musí být přirozené číslo nebo nula.");
        if($pkHead == null)
            $form->addSelect('act_notebooks_id', 'PK', $this->model->getListOfPkIAmIn($this->user->id))->setRequired();

        $form->addSubmit('ok', 'Uložit');

        if($this->editable)
            $form->onSuccess[] = array($this, 'addActFormSucceeded');
        else if(!$this->editable)
            $form->onSuccess[] = array($this, 'addActPKFormSucceeded');
        try {
           // $record = $this->model->getEditableActivity($this->getParameter("id"));
            $form->setDefaults($record);
        }
        catch(\Exception $e)
        {
            //$this->flashMessage("Uživatel pravděpodobně není členem PK","danger");
          //  $this->redirect("Homepage:default");
        }
        return $form;
    }
    //TODO: Tohle dodelat - ulozeni, popr update
    public function addActPKFormSucceeded($form, $values)
    {
        try
        {
            if (!isset($values["act_notebooks_id"]))
                $values["act_notebooks_id"] = $this->usersmodel->getPkIdByLoggedUser($this->user->id); //vrátí pouze jednu skupinu, protože nastane pouze pokud je přihlášen předseda pk, který může být v pouze jedné skupině
            else
                $values["act_notebooks_id"] = (string)$values["act_notebooks_id"];
            if(!isset($values["user_id"]))
                $values["user_id"] = (string) $this->user->id;
            $values["created_id"] = (string) $this->user->id;
            try {
                $id = $values->id;
                unset($values->id);
                $this->model->update($id,$values);
                $this->flashMessage($this->messages["addActFormSuccessful"],"success");
            }
            catch (\DibiDriverException $e)
            {
                $this->model->insert($values);
                $this->flashMessage($this->messages["addActFormSuccessful"], "success");
            }
        }
        catch (Exception $e)
        {
            $this->flashMessage($this->messages["addActFormFailed"],"danger");
        }
        //finally
        {
            $this->redirect("default");
        }

    }
    //TODO: Tohle dodelat - ulozeni, popr update
    public function addActFormSucceeded($form, $values)
    {
        try
        {
            if (!isset($values["user_id"]))
                $values["user_id"] = $this->user->id;
            if (!isset($values["act_notebooks_id"]))
                $values["act_notebooks_id"] = $this->model->getEditableActivity($values->id)->act_notebooks_id;
            else
                $values["act_notebooks_id"] = (string)$values["act_notebooks_id"];
            //$values["user"] = (string)$values["act_notebooks_id"];
            try {
                $id = $values->id;
                unset($values->id);
                $this->model->update($id,$values);
                $this->flashMessage($this->messages["addActFormSuccessful"],"success");
            }
            catch (\DibiDriverException $e)
            {
                $this->model->insert($values);
                $this->flashMessage($this->messages["addActFormSuccessful"], "success");
            }
        }
        catch (Exception $e)
        {
            $this->flashMessage($this->messages["addActFormFailed"],"danger");
        }
        //finally
        {
            $this->redirect("default");
        }

    }
    protected function createComponentGrid($name)
    {
        $grid = new \App\Grids\baseGrid($this, $name);

        if ($this->user->isInRole("manager"))
            $grid->model = $this->model;
        else if($this->user->isInRole("teacher")){
                $grid->model = $this->model->getUsersActivity($this->user->id);
        }
        $grid->addColumnText('fn', 'Jméno')->setSortable()->setFilterText();
        $grid->addColumnText('ln', 'Příjmení')->setSortable()->setFilterText();
        $grid->addColumnText('pk', 'PK')->setSortable()->setFilterText();
        $grid->addColumnText('date', 'Datum')->setSortable()->setFilterText();
        $grid->addColumnText('name', 'Název')->setSortable()->setFilterText();
        //$grid->addColumnText('timeInM', 'Čas (m)')->setSortable()->setFilterText();
        $grid->addColumnText('note', 'Poznámka');
        $grid->addColumnText('preapproval', 'Schválená PK')->setSortable()
            ->setReplacement($grid::$booleanReplacements)
            ->setFilterSelect($grid::$booleanFilters)
            ->setColumn("act_works.preapproval");
        //tohle pouzit opuze pokud je prihlasen reditel (aby mohl filtrovat jiz schvalene prace)
        $grid->addColumnText('approval', 'Schválená')->setSortable()
            ->setReplacement($grid::$booleanReplacements)
            ->setFilterSelect($grid::$booleanFilters)
            ->setColumn("act_works.approval");
        //TODO: odmenu vidi jen manager a vlastnik
        $grid->addColumnText('reward', 'Odměna')->setSortable();
        $grid->addActionHref("approve","Schválení")->setDisable(
            function($item){
                return !($this->model->isWorksPkHead($item->act_notebooks_id, $this->user->id) || ($this->user->isInRole("manager")));
            });
        $grid->addActionHref("addact","Editace")->setDisable(
            function($item){
                return !(($item->created_id == $this->user->id) || ($this->user->id == $item->user_id));
            });
        $grid->addActionHref("delete","Smazat")->setDisable(
            function($item){
                return !(($item->created_id == $this->user->id) || ($this->user->id == $item->user_id));
            });

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        return $grid;
    }
    public function actionDelete($id){
        try{
            $record = $this->model->get($id);
            if($record->created_id == $this->user->id)
                $this->model->delete($id);
            else
                $this->flashMessage("Nemáte oprávnění na smazání činnosti.","danger");
        }catch(\Exception $e){
            $this->flashMessage("Činnost neexistuje.","danger");
        }
        $this->redirect("default");
    }
    private function authenticateUser()
    {

    }

}