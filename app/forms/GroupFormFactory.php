<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class GroupFormFactory extends Nette\Object
{
	protected $model;
	/**
	 * @return Form
	 */
	public function create($model,$users)
	{
		$form = new BaseForm();
		$this->model = $model;
		$form->addHidden("id");
  		$form->addText('name', 'Název')->setRequired("Vyplňte název");
  		$form->addText('role_name','Identifikátor role')->setRequired("Skupina musí mít jednoznačný a unikátní identifikátor");
  		$form->addCheckbox('active','Aktivní (použitelná)');
  		$form->addCheckbox('open','Otevřená (kdokoli se může přidat i odhlásit)');
  		$form->addCheckbox('visible_all','Veřejná');
  		$form->addCheckbox('visible_members','Viditelná členy');
  		$form->addCheckbox('list_all','Veřejný seznam členů');
  		$form->addCheckbox('list_members','Privátní seznam členů');
  		$form->addSelect('user_id','Správce',$users)->setRequired("Je nutné určit správce skupiny")->setPrompt("-- Vyberte --");
      	$form->onValidate[] = array($this, 'validityCheck');
      	$form->addSubmit('send', 'Uložit');
		return $form;
	}

	public function validityCheck($form)
	{		
		$values = $form->getValues();
		$data = $this->model->getSelection()->where(array("group.role_name" => $values->role_name))->fetch();
		if (($data && !$values->id) || ($data && $values->id && ($data["id"] != $values->id)))
			$form->addError('Identifikátor "'.$values->role_name.'" se již používá pro jinou skupinu.');
	}	
}