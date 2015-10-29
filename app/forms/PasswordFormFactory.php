<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


class PasswordFormFactory extends Nette\Object
{
	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new BaseForm();
  		$form->addHidden('id');
  		$form->addPassword('password', 'Heslo')->setRequired("Vyplňte heslo uživatele");
  		$form->addPassword('password2', 'Heslo ještě jednou')->setRequired("Vyplňte také kontrolní verzi hesla");
      	$form->onValidate[] = array($this, 'validityCheck');
      	$form->addSubmit('send', 'Uložit');
		return $form;
	}

	public function validityCheck($form)
	{
		$values = $form->getValues();
		if ($values->password != $values->password2)
			$form->addError('Obě varianty hesla musí být stejné.');
	}
}