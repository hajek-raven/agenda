<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class UsernameFormFactory extends Nette\Object
{
	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new BaseForm();
  		$form->addHidden('id');
  		$form->addText('username', 'Uživatelské jméno')->setRequired("Vyplňte uživatelské jméno");
  		$form->onValidate[] = array($this, 'validityCheck');
      	$form->addSubmit('send', 'Uložit');
		return $form;
	}

	public function validityCheck($form)
	{

	}
}