<?php
/**
 * Created by PhpStorm.
 * User: ondra
 * Date: 11.11.15
 * Time: 10:58
 */

namespace App\ActivityModule\Presenters;

use App\Presenters\BaseActivityPresenter;
use Nette,
    App\Model;



class MembersPresenter extends BaseActivityPresenter {

    /** @var \App\Model\Activity\Model @inject */
    public $model;
    /** @var \App\Model\Activity\Usersmodel @inject */
    public $usersmodel;

    protected $pkHead;
   // /** @var \App\Forms\Activity\NewActivityFormFactory @inject */
    public $newFormFactory;
    public function __construct()
    {
        parent::__construct();
    }
    public function startup(){
        parent::startup();
        $this->pkHead = $this->model->getPkUser($this->user->id);

        if(!(!$this->pkHead == null || $this->user->isInRole("manager")))
        {
            $this->flashMessage("Nemáte oprávnění.","danger");
            $this->redirect("Homepage:default");
        }
    }
    public function actionAddusr(){
        $this->setTitle("Nový člen PK");
    }
    public function actionDefault(){

        $this->setTitle("Editace členů PK");
    }
    public function renderDefault(){

    }
    protected function createComponentMainGrid($name)
    {
        $grid = new \App\Grids\baseGrid($this, $name);

        $grid->model = $this->usersmodel->getUsersInPkWholeRow(($this->pkHead["pk_id"]), $this->user->id);

        $grid->addColumnText('firstname', 'Jméno')->setSortable()->setFilterText();
        $grid->addColumnText('lastname', 'Příjmení')->setSortable()->setFilterText();
        $operations = array('delusr' => 'Odstranit');
        $grid->setOperation($operations, callback($this, 'gridOperationsHandler'))
           ->setConfirm('delusr', 'Opravdu chcete odstranit uživatele z PK?');

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        return $grid;
    }
    protected function createComponentNewUsrForm($name)
    {
        $pkHead = $this->usersmodel->getAllUsersExceptForThoseWhoAreInPk($this->user->id);
        $form = new \App\Forms\BaseForm();
        $form->addHidden('group_id');
        $form->addSelect('user_id','Uživatel', $pkHead);
        $form->addSubmit('ok', 'Přidat');
        $form->onSuccess[] = array($this, 'newUsrFormSucceeded');

        return $form;
    }
    public function newUsrFormSucceeded($form, $values)
    {
        $data["user_id"] = $values["user_id"];
        $data["group_id"] = $this->pkHead["pk_id"];
        try
        {

            $this->usersmodel->insert($data);
            $this->flashMessage("Uživatel přidán","success");
        }
        catch (Exception $e)
        {
            $this->flashMessage("Operace selhala.","danger");
        }
        //finally
        {
            //$this->redirect("addusr");
        }
    }
}