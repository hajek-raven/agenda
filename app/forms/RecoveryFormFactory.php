<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class RecoveryFormFactory extends Nette\Object
{
	protected $model;

	/**
	 * @return Form
	 */
	public function create($model)
	{
		$this->model = $model;
		$form = new BaseForm();
  	$form->addHidden('id');
  	$form->addText('username', 'Uživatelské jméno')->setRequired("Vyplňte přihlašovací jméno uživatele (emailovou adresu).");
    $form->addSubmit('send', 'Uložit');
    $form->onValidate[] = array($this, 'validityCheck');
		return $form;
	}

	public function validityCheck($form)
	{
		$values = $form->getValues();
		$userData = $this->model->getBy(array("email" => $values->username));
		if (!$userData)
			$form->addError('Zadané přihlašovací jméno není správné.');
	}
}
