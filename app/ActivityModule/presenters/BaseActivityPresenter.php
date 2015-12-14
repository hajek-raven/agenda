<?php
/**
 * Created by PhpStorm.
 * User: ondra
 * Date: 11.11.15
 * Time: 10:49
 */

namespace App\Presenters;

use Nette,
    App\Model;

abstract class BaseActivityPresenter extends SecuredGridPresenter
{
    /** @var \App\Model\Activity\Model @inject */
    public $model;
    /** @var \App\Model\Activity\Usersmodel @inject */
    public $usersmodel;

    public $formFactory;
    protected $pkHead;

    public function __construct()
    {
        parent::__construct();
    }
    public function startup()
    {
        parent::startup();
        if($this->model->getPkUser($this->user->id))
            $this->template->pkh = true;
        else
            $this->template->pkh = false;
    }

    protected function createComponentPreApprovalForm()
    {
        $form = $this->preFormFactory->create($this->model);
        $form->onSuccess[] = array($this, 'approvalFormSucceeded');
        return $form;
    }

    public function actionApprove($id)
    {
        $form = $this["approvalForm"];
        $record = $this->model->get($id);
        $form->setDefaults($record);
    }

    public function actionAddact($id = 0)
    {
        $form = $this["addActForm"];
        if ($id!=0)
        {
            $record = $this->model->get($id);
            $form->setDefaults($record);
        }

    }
    public function actionDelusr($id)
    {
        $process = explode(',',$id);
        foreach ($process as $record)
        {
            try
            {
                $this->usersmodel->deleteUserFromPk($id);
                $this->flashMessage("Uživatel byl odstraněn.","success");
            }
            catch (Exception $e)
            {
                $this->flashMessage("Operace selhala.","danger");
            }
        }
        $this->redirect("default");
    }

    public function renderApprove($id)
    {
        $data = $this->model->get($id);
        $this->template->data = $data;
    }
    protected $messages = array(
        "uneditable" => "Činnost již byla schválena -> nelze ji editovat.",
        "approveSuccessful" => "Změny byly uloženy.",
        "approveFailed" => "Při ukládání došlo k chybě.",
        "addActFormFailed" => "Při ukládání došlo k chybě.",
        "addActFormSuccessful" => "Činnost byla uložena."
    );
}